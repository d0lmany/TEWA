<?php

namespace Database\Factories;

use App\Utils\Utils;
use \App\Models\User;
use App\Models\SellerVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerVerificationFactory extends Factory
{
    protected $model = SellerVerification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'passport_numbers' => fake()->numerify('#### ######'),
            'passport_scan' => Utils::generateImage('passport scan')
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
