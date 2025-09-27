<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Client;
use App\Models\Orders\Order;
use App\Models\Orders\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        if (rand(0, 1)) {
            return [
                'client_id' => Client::factory()->create()->id,
            ];
        } else {
            return [
                'supplier_id' => Supplier::factory()->create()->id,
            ];
        }
    }
}
