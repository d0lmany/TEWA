<?php

namespace Database\Factories;

use App\Models\User;
use App\Utils\Utils;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => explode(' ', fake()->name())[0],
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'birthday' => fake()->date('Y-m-d', '-13 years'),
            'picture' => fake()->randomElement([
                Utils::generateImage('people'),
                'NULL'
            ]),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin'
        ]);
    }
}
