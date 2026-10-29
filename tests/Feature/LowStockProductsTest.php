<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_retrieves_products_below_default_threshold_of_five(): void
    {
        $lowStockProduct = Product::factory()->create([
            'name' => 'Low Stock Product',
            'stock_quantity' => 4,
        ]);

        $boundaryProduct = Product::factory()->create([
            'name' => 'Boundary Product',
            'stock_quantity' => 5,
        ]);

        $highStockProduct = Product::factory()->create([
            'name' => 'High Stock Product',
            'stock_quantity' => 15,
        ]);

        $response = $this->getJson('/api/products/low-stock');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Low-stock products retrieved successfully',
            ])
            ->assertJsonFragment(['id' => $lowStockProduct->id])
            ->assertJsonMissing(['id' => $boundaryProduct->id])
            ->assertJsonMissing(['id' => $highStockProduct->id]);
    }

    public function test_retrieves_products_below_custom_threshold(): void
    {
        $productA = Product::factory()->create(['stock_quantity' => 8]);
        $productB = Product::factory()->create(['stock_quantity' => 12]);

        $response = $this->getJson('/api/products/low-stock?threshold=10');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $productA->id])
            ->assertJsonMissing(['id' => $productB->id]);
    }

    public function test_rejects_negative_or_invalid_threshold(): void
    {
        $this->getJson('/api/products/low-stock?threshold=-1')
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->getJson('/api/products/low-stock?threshold=invalid')
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
