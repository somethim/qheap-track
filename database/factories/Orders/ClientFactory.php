<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->optional()->sentence(),
            'contact_email' => $this->faker->optional()->safeEmail(),
            'contact_phone' => $this->faker->optional()->phoneNumber(),
            'address' => $this->faker->optional()->address(),
        ];
    }
}
