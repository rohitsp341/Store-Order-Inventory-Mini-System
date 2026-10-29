<?php

namespace App\Console\Commands;

use App\Exceptions\InsufficientStockException;
use App\Services\OrderService;
use Illuminate\Console\Command;

class CreateOrderCommand extends Command
{
    protected $signature = 'order:create {--name=} {--email=} {--product=} {--quantity=}';

    public function handle(OrderService $orderService): int
    {
        $name = (string) $this->option('name');
        $email = (string) $this->option('email');
        $productId = (int) $this->option('product');
        $quantity = (int) $this->option('quantity');

        try {
            $orderService->createOrder(
                ['name' => $name, 'email' => $email],
                [['product_id' => $productId, 'quantity' => $quantity]]
            );

            $this->info('ORDER_CREATED');
            return Command::SUCCESS;
        } catch (InsufficientStockException $e) {
            $this->error('INSUFFICIENT_STOCK: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
