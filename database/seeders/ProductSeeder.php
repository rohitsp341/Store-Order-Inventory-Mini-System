<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Wireless Optical Mouse',
                'code' => 'PRD-MOU-001',
                'price' => 599.00,
                'tax_percentage' => 18.00,
                'stock_quantity' => 25,
            ],
            [
                'name' => 'Mechanical Gaming Keyboard',
                'code' => 'PRD-KEY-002',
                'price' => 2499.00,
                'tax_percentage' => 18.00,
                'stock_quantity' => 12,
            ],
            [
                'name' => 'USB-C Fast Charging Cable (1m)',
                'code' => 'PRD-CAB-003',
                'price' => 299.00,
                'tax_percentage' => 18.00,
                'stock_quantity' => 45,
            ],
            [
                'name' => 'Stainless Steel Water Bottle (750ml)',
                'code' => 'PRD-BOT-004',
                'price' => 450.00,
                'tax_percentage' => 12.00,
                'stock_quantity' => 3,
            ],
            [
                'name' => 'Hardbound Ruled Notebook (A5)',
                'code' => 'PRD-NOT-005',
                'price' => 180.00,
                'tax_percentage' => 5.00,
                'stock_quantity' => 50,
            ],
            [
                'name' => 'Ergonomic Aluminum Laptop Stand',
                'code' => 'PRD-STA-006',
                'price' => 1299.00,
                'tax_percentage' => 18.00,
                'stock_quantity' => 2,
            ],
            [
                'name' => 'Organic Darjeeling Green Tea (100g)',
                'code' => 'PRD-TEA-007',
                'price' => 320.00,
                'tax_percentage' => 5.00,
                'stock_quantity' => 15,
            ],
            [
                'name' => 'Portable Bluetooth Speaker (5W)',
                'code' => 'PRD-SPK-008',
                'price' => 1499.00,
                'tax_percentage' => 18.00,
                'stock_quantity' => 4,
            ],
            [
                'name' => 'Noise Cancelling Wireless Earbuds',
                'code' => 'PRD-EAR-009',
                'price' => 2999.00,
                'tax_percentage' => 18.00,
                'stock_quantity' => 8,
            ],
            [
                'name' => 'Ceramic Coffee Mug (350ml)',
                'code' => 'PRD-MUG-010',
                'price' => 220.00,
                'tax_percentage' => 12.00,
                'stock_quantity' => 1,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['code' => $product['code']], $product);
        }
    }
}
