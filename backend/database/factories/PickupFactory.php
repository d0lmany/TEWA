<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PickupFactory extends Factory
{
    public function definition(): array
    {
        $city = fake()->city();

        return [
            'name' => $city . '_' . fake()->unique()->numberBetween(1, 1000),
            'country' => fake()->country(),
            'city' => $city,
            'address' => fake()->address()
        ];
    }
}
