<?php

namespace Database\Factories;

use App\Models\SellerVerification;
use Illuminate\Database\Eloquent\Factories\Factory;
use \App\Models\User;

class SellerVerificationFactory extends Factory
{
    protected $model = SellerVerification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'passport_numbers' => fake()->numerify('#### ######'),
            'passport_scan' => fake()->imageUrl(800, 600, 'document', true, 'passport')
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
