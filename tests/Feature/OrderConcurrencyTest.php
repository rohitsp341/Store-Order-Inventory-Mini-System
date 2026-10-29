<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class OrderConcurrencyTest extends TestCase
{
    public function test_concurrent_orders_for_last_unit_prevents_overselling(): void
    {
        $product = Product::create([
            'name' => 'Limited Stock Watch',
            'code' => 'LTD-CONCUR-' . time(),
            'price' => 100.00,
            'tax_percentage' => 10.00,
            'stock_quantity' => 1,
        ]);

        $phpBinary = PHP_BINARY;
        $artisanPath = base_path('artisan');

        $env = array_merge($_SERVER, [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'store_order_inventory_test',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
        ]);

        $processA = new Process([
            $phpBinary,
            $artisanPath,
            'order:create',
            '--name=Customer A',
            '--email=customerA@example.com',
            "--product={$product->id}",
            '--quantity=1',
        ], base_path(), $env);

        $processB = new Process([
            $phpBinary,
            $artisanPath,
            'order:create',
            '--name=Customer B',
            '--email=customerB@example.com',
            "--product={$product->id}",
            '--quantity=1',
        ], base_path(), $env);

        $processA->start();
        $processB->start();

        $processA->wait();
        $processB->wait();

        $exitA = $processA->getExitCode();
        $exitB = $processB->getExitCode();

        $successCount = ($exitA === 0 ? 1 : 0) + ($exitB === 0 ? 1 : 0);
        $failureCount = ($exitA !== 0 ? 1 : 0) + ($exitB !== 0 ? 1 : 0);

        $this->assertEquals(1, $successCount);
        $this->assertEquals(1, $failureCount);

        $freshProduct = $product->fresh();
        $this->assertEquals(0, $freshProduct->stock_quantity);

        $ordersCount = Order::whereHas('orderItems', function ($query) use ($product) {
            $query->where('product_id', $product->id);
        })->count();

        $this->assertEquals(1, $ordersCount);

        $createdOrders = Order::whereHas('orderItems', function ($query) use ($product) {
            $query->where('product_id', $product->id);
        })->get();

        foreach ($createdOrders as $order) {
            $order->delete();
        }

        $product->delete();
    }
}
