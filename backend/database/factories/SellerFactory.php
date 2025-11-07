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
        $timestamp = fake()->dateTimeBetween('-2 years', 'now');
        
        return [
            'full_name' => fake()->name(),
            'user_id' => User::factory(),
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

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'individual',
        ]);
    }

    public function llc(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'LLC',
        ]);
    }

    public function selfEmployed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'self-employed',
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now(),
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => null,
        ]);
    }

    public function withUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'full_name' => $user->name,
        ]);
    }
}
