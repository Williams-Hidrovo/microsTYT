<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan.perez@example.com',
                'phone' => '+52 555 123 4567',
            ],
            [
                'name' => 'María García',
                'email' => 'maria.garcia@example.com',
                'phone' => '+52 555 234 5678',
            ],
            [
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos.rodriguez@example.com',
                'phone' => '+52 555 345 6789',
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana.martinez@example.com',
                'phone' => '+52 555 456 7890',
            ],
            [
                'name' => 'Luis Hernández',
                'email' => 'luis.hernandez@example.com',
                'phone' => '+52 555 567 8901',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'phone' => $customer['phone'],
                ]
            );
        }

        $this->command->info('Clientes de prueba creados exitosamente');
    }
}
