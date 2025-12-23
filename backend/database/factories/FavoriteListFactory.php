<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteListFactory extends Factory
{
    public function definition(): array
    {
        $user = User::inRandomOrder()
            ->first();

        return [
            'name' => fake()->word(),
            'user_id' => $user->id,
            'created_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }
}
