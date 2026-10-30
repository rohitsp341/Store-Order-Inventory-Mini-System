<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_new_product_successfully(): void
    {
        $payload = [
            'name' => 'Wireless Gaming Mouse',
            'code' => 'PRD-NEW-999',
            'price' => 1299.50,
            'tax_percentage' => 18.00,
            'stock_quantity' => 25,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => [
                    'name' => 'Wireless Gaming Mouse',
                    'code' => 'PRD-NEW-999',
                    'price' => '1299.50',
                    'tax_percentage' => '18.00',
                    'stock_quantity' => 25,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'code' => 'PRD-NEW-999',
            'stock_quantity' => 25,
        ]);
    }

    public function test_can_update_product_stock_quantity(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 5,
        ]);

        $response = $this->patchJson("/api/products/{$product->id}/stock", [
            'stock_quantity' => 40,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Stock quantity updated successfully',
                'data' => [
                    'id' => $product->id,
                    'stock_quantity' => 40,
                ],
            ]);

        $this->assertEquals(40, $product->fresh()->stock_quantity);
    }

    public function test_rejects_negative_stock_quantity_update(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 5,
        ]);

        $response = $this->patchJson("/api/products/{$product->id}/stock", [
            'stock_quantity' => -10,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_rejects_duplicate_product_code(): void
    {
        Product::factory()->create([
            'code' => 'PRD-DUP-001',
        ]);

        $payload = [
            'name' => 'Duplicate Code Product',
            'code' => 'PRD-DUP-001',
            'price' => 500.00,
            'tax_percentage' => 12.00,
            'stock_quantity' => 10,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_rejects_invalid_price_or_stock_quantity(): void
    {
        $payload = [
            'name' => 'Invalid Product',
            'code' => 'PRD-INV-001',
            'price' => -10.00,
            'tax_percentage' => 150.00,
            'stock_quantity' => -5,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
