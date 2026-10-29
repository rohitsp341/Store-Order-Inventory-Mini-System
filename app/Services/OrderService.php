<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmationJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $customerData, array $itemsData): Order
    {
        $consolidatedItems = [];
        foreach ($itemsData as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (int) $item['quantity'];
            $consolidatedItems[$productId] = ($consolidatedItems[$productId] ?? 0) + $quantity;
        }

        $productIds = array_keys($consolidatedItems);
        sort($productIds, SORT_NUMERIC);

        $order = DB::transaction(function () use ($customerData, $consolidatedItems, $productIds) {
            $customer = $this->resolveCustomer($customerData);

            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($consolidatedItems as $productId => $quantity) {
                $product = $products->get($productId);

                if (!$product) {
                    throw new InsufficientStockException("Product with ID {$productId} not found.");
                }

                if ($product->stock_quantity < $quantity) {
                    throw new InsufficientStockException("Insufficient stock for product: {$product->name}. Requested: {$quantity}, Available: {$product->stock_quantity}");
                }
            }

            $subtotalSum = '0.00';
            $taxSum = '0.00';
            $orderItemsData = [];

            foreach ($consolidatedItems as $productId => $quantity) {
                $product = $products->get($productId);

                $unitPrice = number_format((float) $product->price, 2, '.', '');
                $taxPercentage = number_format((float) $product->tax_percentage, 2, '.', '');

                $lineSubtotal = bcmul($unitPrice, (string) $quantity, 2);
                $rawTax = bcdiv(bcmul($lineSubtotal, $taxPercentage, 4), '100', 4);
                $lineTax = number_format(round((float) $rawTax, 2), 2, '.', '');
                $lineTotal = bcadd($lineSubtotal, $lineTax, 2);

                $subtotalSum = bcadd($subtotalSum, $lineSubtotal, 2);
                $taxSum = bcadd($taxSum, $lineTax, 2);

                $product->decrement('stock_quantity', $quantity);

                $orderItemsData[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_percentage' => $taxPercentage,
                    'subtotal' => $lineSubtotal,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal,
                ];
            }

            $grandTotal = bcadd($subtotalSum, $taxSum, 2);

            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => $subtotalSum,
                'tax_amount' => $taxSum,
                'grand_total' => $grandTotal,
            ]);

            $order->orderItems()->createMany($orderItemsData);

            return $order;
        });

        SendOrderConfirmationJob::dispatch($order);

        return $order->load(['customer', 'orderItems.product']);
    }

    protected function resolveCustomer(array $customerData): Customer
    {
        try {
            return Customer::firstOrCreate(
                ['email' => $customerData['email']],
                ['name' => $customerData['name']]
            );
        } catch (QueryException $e) {
            return Customer::where('email', $customerData['email'])->firstOrFail();
        }
    }
}
