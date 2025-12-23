<?php

namespace Database\Factories;

use \App\Models\User;
use App\Models\SellerVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerVerificationFactory extends Factory
{
    protected $model = SellerVerification::class;

    public function definition(): array
    {
        $user = User::inRandomOrder()
            ->first();
        return [
            'user_id' => $user->id,
            'passport_numbers' => fake()->numerify('#### ######'),
            'passport_scan' => 'scans/scan.png'
        ];
    }
}
