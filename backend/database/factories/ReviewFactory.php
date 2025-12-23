<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()
            ->first();
        $user = User::inRandomOrder()
            ->first();
        return [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'text' => fake()->optional(80)->text(200),
            'evaluation' => fake()->numberBetween(1, 5),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
