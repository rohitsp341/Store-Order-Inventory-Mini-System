<?php

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmationJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_successfully(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'price' => 500.00,
            'tax_percentage' => 18.00,
            'stock_quantity' => 10,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Rohit Patil',
                'email' => 'rohit@example.com',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'subtotal' => '1000.00',
                    'tax_amount' => '180.00',
                    'grand_total' => '1180.00',
                    'customer' => [
                        'name' => 'Rohit Patil',
                        'email' => 'rohit@example.com',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'email' => 'rohit@example.com',
            'name' => 'Rohit Patil',
        ]);

        $this->assertDatabaseHas('orders', [
            'subtotal' => '1000.00',
            'tax_amount' => '180.00',
            'grand_total' => '1180.00',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => '500.00',
            'tax_percentage' => '18.00',
            'subtotal' => '1000.00',
            'tax_amount' => '180.00',
            'total' => '1180.00',
        ]);
    }

    public function test_calculates_subtotal_tax_and_grand_total_accurately_with_multiple_items(): void
    {
        Queue::fake();

        $productA = Product::factory()->create([
            'price' => 299.99,
            'tax_percentage' => 18.00,
            'stock_quantity' => 10,
        ]);

        $productB = Product::factory()->create([
            'price' => 150.50,
            'tax_percentage' => 5.00,
            'stock_quantity' => 10,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Anita Desai',
                'email' => 'anita@example.com',
            ],
            'items' => [
                [
                    'product_id' => $productA->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $productB->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'subtotal' => '750.48',
                    'tax_amount' => '115.53',
                    'grand_total' => '866.01',
                ],
            ]);
    }

    public function test_correctly_deducts_inventory_after_successful_order(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'stock_quantity' => 20,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Suresh Raina',
                'email' => 'suresh@example.com',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 7,
                ],
            ],
        ];

        $this->postJson('/api/orders', $payload)->assertStatus(201);

        $this->assertEquals(13, $product->fresh()->stock_quantity);
    }

    public function test_rejects_order_with_insufficient_stock(): void
    {
        Queue::fake();

        $initialOrderCount = Order::count();

        $product = Product::factory()->create([
            'name' => 'Smart Watch',
            'stock_quantity' => 2,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Rahul Dravid',
                'email' => 'rahul@example.com',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertEquals(2, $product->fresh()->stock_quantity);
        $this->assertEquals($initialOrderCount, Order::count());
    }

    public function test_rejects_order_for_nonexistent_product(): void
    {
        $payload = [
            'customer' => [
                'name' => 'Karan Johar',
                'email' => 'karan@example.com',
            ],
            'items' => [
                [
                    'product_id' => 99999,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_rejects_order_with_invalid_or_zero_quantity(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Vijay Kumar',
                'email' => 'vijay@example.com',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_reuses_existing_customer_without_creating_duplicate(): void
    {
        Queue::fake();

        $existingCustomer = Customer::factory()->create([
            'name' => 'Initial Name',
            'email' => 'shared@example.com',
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Another Name',
                'email' => 'shared@example.com',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201);

        $this->assertEquals(1, Customer::where('email', 'shared@example.com')->count());
        $this->assertDatabaseHas('orders', [
            'customer_id' => $existingCustomer->id,
        ]);
    }

    public function test_rolls_back_entire_transaction_when_one_item_has_insufficient_stock(): void
    {
        Queue::fake();

        $initialOrderCount = Order::count();

        $productA = Product::factory()->create([
            'stock_quantity' => 10,
        ]);

        $productB = Product::factory()->create([
            'stock_quantity' => 1,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Manish Sharma',
                'email' => 'manish@example.com',
            ],
            'items' => [
                [
                    'product_id' => $productA->id,
                    'quantity' => 5,
                ],
                [
                    'product_id' => $productB->id,
                    'quantity' => 10,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertEquals(10, $productA->fresh()->stock_quantity);
        $this->assertEquals(1, $productB->fresh()->stock_quantity);
        $this->assertEquals($initialOrderCount, Order::count());
    }

    public function test_dispatches_confirmation_job_after_successful_order(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'stock_quantity' => 5,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Neha Kakkar',
                'email' => 'neha@example.com',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $this->postJson('/api/orders', $payload)->assertStatus(201);

        Queue::assertPushed(SendOrderConfirmationJob::class, 1);
    }

    public function test_does_not_dispatch_confirmation_job_when_order_fails(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'stock_quantity' => 2,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Sonu Nigam',
                'email' => 'sonu@example.com',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                ],
            ],
        ];

        $this->postJson('/api/orders', $payload)->assertStatus(422);

        Queue::assertNothingPushed();
    }

    public function test_consolidates_duplicate_product_ids_within_single_request(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'price' => 100.00,
            'tax_percentage' => 10.00,
            'stock_quantity' => 10,
        ]);

        $payload = [
            'customer' => [
                'name' => 'Arijit Singh',
                'email' => 'arijit@example.com',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'subtotal' => '500.00',
                    'tax_amount' => '50.00',
                    'grand_total' => '550.00',
                ],
            ]);

        $this->assertEquals(5, $product->fresh()->stock_quantity);
    }
}
