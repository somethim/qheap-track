<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'description' => $this->faker->optional()->sentence(),
            'contact_email' => $this->faker->optional()->safeEmail(),
            'contact_phone' => $this->faker->optional()->phoneNumber(),
            'address' => $this->faker->optional()->address(),
        ];
    }
}
