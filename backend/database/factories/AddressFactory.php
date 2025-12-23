<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Pickup;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        $user = User::inRandomOrder()
            ->first();
        
        $so = fake()->randomElement(['pickup', 'address', 'both']);

        return [
            'user_id' => $user->id,
            'pickup_id' =>
                match ($so) {
                    'address' => null,
                    default => Pickup::inRandomOrder()->first()
                },
            'address' =>
                match ($so) {
                    'pickup' => null,
                    default => fake()->address()
                },
            'is_default' => fake()->boolean(15)
        ];
    }
}
