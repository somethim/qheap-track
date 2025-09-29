<?php

namespace Database\Seeders;

use App\Models\Orders\Client;
use App\Models\Orders\Order;
use App\Models\Orders\Product;
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

        $productCount = 127;
        $clientCount = 59;
        $supplierCount = 23;
        $ordersPerClient = 7;
        $ordersPerSupplier = 106;

        foreach (range(1, $productCount) as $i) {
            Product::create([
                'name' => 'Product '.$i,
                'description' => 'Description for product '.$i,
                'price' => rand(100, 100000000) / 100,
                'stock_quantity' => rand(0, 100),
            ]);
        }

        $products = Product::all();
        $clients = Client::factory()->count($clientCount)->create();
        $startDate = now()->subMonths(2)->startOfDay();
        $endDate = now()->endOfDay();

        foreach ($clients as $client) {
            for ($i = 1; $i <= $ordersPerClient; $i++) {
                $orderDate = $this->getBiasedDate($startDate, $endDate);
                $order = Order::create([
                    'client_id' => $client->id,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
                $orderProductsData = [];
                foreach ($products->random(rand(1, 10)) as $product) {
                    $orderProductsData[] = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => rand(1, 10),
                        'price' => $product->price,
                    ];
                }
                $order->orderProducts()->insert($orderProductsData);
            }
        }

        $suppliers = Supplier::factory()->count($supplierCount)->create();
        foreach ($suppliers as $supplier) {
            for ($i = 1; $i <= $ordersPerSupplier; $i++) {
                $orderDate = $this->getBiasedDate($startDate, $endDate);
                $order = Order::create([
                    'supplier_id' => $supplier->id,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
                $orderProductsData = [];
                foreach ($products->random(rand(1, 10)) as $product) {
                    $orderProductsData[] = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => rand(1, 10),
                        'price' => $product->price,
                    ];
                }
                $order->orderProducts()->insert($orderProductsData);
            }
        }
    }

    /**
     * Generate a random date between start and end, biased toward more recent dates.
     */
    private function getBiasedDate($start, $end)
    {
        $diff = $start->diffInSeconds($end);
        $randomRatio = pow(mt_rand() / mt_getrandmax(), 0.5);
        $seconds = intval($diff * $randomRatio);

        return $start->copy()->addSeconds($seconds);
    }
}
