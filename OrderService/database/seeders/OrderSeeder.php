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

        $orderTypes = [
            ['notes' => 'Laptop Dell XPS 13 - 1 unidad', 'amount' => 1299.99],
            ['notes' => 'iPhone 15 Pro - 2 unidades', 'amount' => 2199.98],
            ['notes' => 'Samsung Galaxy S24 - 1 unidad', 'amount' => 899.99],
            ['notes' => 'Monitor LG UltraWide 34" - 1 unidad', 'amount' => 599.99],
            ['notes' => 'Teclado Mecánico Logitech + Mouse Gamer Razer', 'amount' => 189.99],
            ['notes' => 'Audífonos Sony WH-1000XM5', 'amount' => 349.99],
            ['notes' => 'iPad Pro 12.9" - 1 unidad', 'amount' => 1099.99],
            ['notes' => 'MacBook Pro 14" - 1 unidad', 'amount' => 1999.99],
            ['notes' => 'Apple Watch Series 9 - 2 unidades', 'amount' => 799.98],
            ['notes' => 'Kit de oficina completo (monitor, teclado, mouse)', 'amount' => 499.99],
        ];

        $statuses = ['pending', 'processing', 'completed', 'cancelled'];

        // Crear 15 órdenes de ejemplo
        for ($i = 1; $i <= 15; $i++) {
            $customer = $customers->random();
            $orderType = $orderTypes[array_rand($orderTypes)];
            $orderDate = Carbon::now()->subDays(rand(0, 30));
            
            Order::create([
                'customer_id' => $customer->id,
                'order_number' => 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'status' => $statuses[array_rand($statuses)],
                'total_amount' => $orderType['amount'],
                'currency' => 'USD',
                'notes' => $orderType['notes'],
                'order_date' => $orderDate,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);
        }

        $this->command->info('Órdenes de prueba creadas exitosamente');
    }
}
