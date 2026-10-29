<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_screen_can_be_rendered(): void
    {
        Product::factory()->create([
            'name' => 'Demo Keyboard',
            'stock_quantity' => 10,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Demo Keyboard')
            ->assertSee('Retail Counter & Inventory System', false);
    }
}
