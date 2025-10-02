<?php

namespace Database\Seeders;

use App\Models\Orders\Client;
use App\Models\Orders\Order;
use App\Models\Orders\Product;
use App\Models\Orders\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Str;

class DatabaseSeeder extends Seeder
{
    const int PRODUCT_COUNT = 127;

    const int CLIENT_COUNT = 59;

    const int SUPPLIER_COUNT = 23;

    const int ORDER_PER_CLIENT_COUNT = 7;

    const int ORDER_PER_SUPPLIER_COUNT = 106;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->id;

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $startDate = now()->subMonths(2)->startOfDay();
        $endDate = now()->endOfDay();
        foreach (User::all() as $user) {
            $products = $this->createProductsData($user->id);
            $this->createClientsData($user->id, $startDate, $endDate, $products);
            $this->createSuppliersData($user->id, $startDate, $endDate, $products);
        }
    }

    private function createProductsData(int $userId): Collection
    {
        $products = collect();
        foreach (range(1, self::PRODUCT_COUNT) as $i) {
            $products->push(Product::create([
                'name' => 'Product '.$i,
                'description' => 'Description for product '.$i,
                'price' => rand(100, 100000000) / 100,
                'stock' => rand(0, 100),
                'sku' => Str::upper(Str::random(3)).'-'.Str::random(8),
                'user_id' => $userId,
            ]));
        }

        return $products;
    }

    private function createClientsData(int $userId, Carbon $startDate, Carbon $endDate, Collection $products): void
    {
        $clients = Client::factory()->count(self::CLIENT_COUNT)->create([
            'user_id' => $userId,
        ]);
        foreach ($clients as $client) {
            for ($i = 1; $i <= self::ORDER_PER_CLIENT_COUNT; $i++) {
                $orderDate = $this->getBiasedDate($startDate, $endDate);
                $order = Order::create([
                    'client_id' => $client->id,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                    'user_id' => $userId,
                ]);
                $orderProductsData = [];
                foreach ($products->random(rand(1, 10)) as $product) {
                    $orderProductsData[] = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'stock' => rand(1, 10),
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

    private function createSuppliersData(int $userId, Carbon $startDate, Carbon $endDate, Collection $products): void
    {
        $suppliers = Supplier::factory()->count(self::SUPPLIER_COUNT)->create([
            'user_id' => $userId,
        ]);
        foreach ($suppliers as $supplier) {
            for ($i = 1; $i <= self::ORDER_PER_CLIENT_COUNT; $i++) {
                $orderDate = $this->getBiasedDate($startDate, $endDate);
                $order = Order::create([
                    'supplier_id' => $supplier->id,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                    'user_id' => $userId,
                ]);
                $orderProductsData = [];
                foreach ($products->random(rand(1, 10)) as $product) {
                    $orderProductsData[] = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'stock' => rand(1, 10),
                        'price' => $product->price,
                    ];
                }
                $order->orderProducts()->insert($orderProductsData);
            }
        }
    }
}
