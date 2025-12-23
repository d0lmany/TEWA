<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition(): array
    {
        $user = User::inRandomOrder()
            ->first();
        $timestamp = fake()->dateTimeBetween('-2 years', 'now');
        
        return [
            'full_name' => fake()->name(),
            'user_id' => $user->id,
            'code' => 'SELL' . fake()->unique()->numberBetween(1000, 9999),
            'type' => fake()->randomElement(['individual', 'LLC', 'self-employed']),
            'payment_account' => fake()->iban('RU'),
            'verified_at' => fake()->optional(70)->dateTimeBetween('-1 year', 'now'),
            'created_at' => $timestamp,
            'updated_at' => fake()->randomElement([
                $timestamp, fake()->dateTimeBetween('-2 years', 'now')
            ]),
        ];
    }
}
