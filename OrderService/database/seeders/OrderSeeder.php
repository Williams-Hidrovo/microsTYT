<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los clientes
        $customers = Customer::all();
        
        if ($customers->isEmpty()) {
            $this->command->warn('No hay clientes disponibles. Ejecuta CustomerSeeder primero.');
            return;
        }

        $products = [
            'Laptop Dell XPS 13',
            'iPhone 15 Pro',
            'Samsung Galaxy S24',
            'Monitor LG UltraWide',
            'Teclado Mecánico Logitech',
            'Mouse Gamer Razer',
            'Audífonos Sony WH-1000XM5',
            'iPad Pro 12.9',
            'MacBook Pro 14',
            'Apple Watch Series 9',
        ];

        $statuses = ['pending', 'processing', 'completed', 'cancelled'];

        $orders = [];

        // Crear 15 órdenes de ejemplo
        for ($i = 1; $i <= 15; $i++) {
            $customer = $customers->random();
            $product = $products[array_rand($products)];
            $quantity = rand(1, 5);
            $unitPrice = rand(500, 5000);
            
            $orders[] = [
                'customer_id' => $customer->id,
                'product' => $product,
                'quantity' => $quantity,
                'total_amount' => $quantity * $unitPrice,
                'status' => $statuses[array_rand($statuses)],
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
            ];
        }

        foreach ($orders as $order) {
            Order::firstOrCreate(
                [
                    'customer_id' => $order['customer_id'],
                    'product' => $order['product'],
                    'created_at' => $order['created_at'],
                ],
                $order
            );
        }

        $this->command->info('Órdenes de prueba creadas exitosamente');
    }
}
