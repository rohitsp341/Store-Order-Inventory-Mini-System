<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'code' => 'PRD-' . fake()->unique()->numerify('#####'),
            'price' => fake()->randomFloat(2, 10, 500),
            'tax_percentage' => fake()->randomElement([0.00, 5.00, 12.00, 18.00, 28.00]),
            'stock_quantity' => fake()->numberBetween(5, 100),
        ];
    }
}
