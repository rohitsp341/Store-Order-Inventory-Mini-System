<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Rohit Patil',
                'email' => 'rohit@example.com',
            ],
            [
                'name' => 'Aarav Sharma',
                'email' => 'aarav.sharma@example.com',
            ],
            [
                'name' => 'Priya Nair',
                'email' => 'priya.nair@example.com',
            ],
            [
                'name' => 'Ananya Verma',
                'email' => 'ananya.verma@example.com',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(['email' => $customer['email']], $customer);
        }
    }
}
