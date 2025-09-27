<?php

namespace Database\Seeders;

use App\Models\Orders\Client;
use App\Models\Orders\Order;
use App\Models\Orders\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $clients = Client::factory()->count(10)->create();
        foreach ($clients as $client) {
            for ($i = 1; $i <= 5; $i++) {
                Order::create([
                    'client_id' => $client->id,
                ]);
            }
        }

        $suppliers = Supplier::factory()->count(10)->create();
        foreach ($suppliers as $supplier) {
            for ($i = 1; $i <= 5; $i++) {
                Order::create([
                    'supplier_id' => $supplier->id,
                ]);
            }
        }
    }
}
