<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_customer_order_history(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'history@example.com',
            'name' => 'History Customer',
        ]);

        $product = Product::factory()->create([
            'name' => 'Mechanical Keyboard',
            'code' => 'PRD-KEY-001',
            'price' => 2000.00,
            'tax_percentage' => 18.00,
            'stock_quantity' => 15,
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'subtotal' => 2000.00,
            'tax_amount' => 360.00,
            'grand_total' => 2360.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 2000.00,
            'tax_percentage' => 18.00,
            'subtotal' => 2000.00,
            'tax_amount' => 360.00,
            'total' => 2360.00,
        ]);

        $response = $this->getJson('/api/customers/orders?email=history@example.com');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Customer orders retrieved successfully',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'email' => 'history@example.com',
                    ],
                    'orders' => [
                        [
                            'id' => $order->id,
                            'subtotal' => '2000.00',
                            'tax_amount' => '360.00',
                            'grand_total' => '2360.00',
                            'order_items' => [
                                [
                                    'product_id' => $product->id,
                                    'quantity' => 1,
                                    'product' => [
                                        'name' => 'Mechanical Keyboard',
                                        'code' => 'PRD-KEY-001',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_returns_404_when_customer_email_does_not_exist(): void
    {
        $response = $this->getJson('/api/customers/orders?email=nonexistent@example.com');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Customer not found',
            ]);
    }

    public function test_returns_422_when_email_parameter_is_missing_or_invalid(): void
    {
        $this->getJson('/api/customers/orders')
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->getJson('/api/customers/orders?email=not-an-email')
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
