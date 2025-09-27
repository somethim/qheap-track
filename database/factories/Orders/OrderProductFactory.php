<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\OrderProduct;
use App\Models\Orders\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderProductFactory extends Factory
{
    protected $model = OrderProduct::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'quantity' => $this->faker->numberBetween(1, 100),
            'price' => $this->faker->randomFloat(2, 1, 1000),
        ];
    }
}
