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
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'text' => fake()->optional(80)->text(200),
            'evaluation' => fake()->numberBetween(1, 5),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function withText(): static
    {
        return $this->state(fn (array $attributes) => [
            'text' => fake()->text(200),
        ]);
    }

    public function withoutText(): static
    {
        return $this->state(fn (array $attributes) => [
            'text' => null,
        ]);
    }

    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'evaluation' => fake()->numberBetween(4, 5),
            'text' => fake()->optional(90)->text(150),
        ]);
    }

    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'evaluation' => fake()->numberBetween(1, 2),
            'text' => fake()->optional(95)->text(250),
        ]);
    }

    public function neutral(): static
    {
        return $this->state(fn (array $attributes) => [
            'evaluation' => 3,
            'text' => fake()->optional(70)->text(180),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-1 year', '-6 months'),
            'updated_at' => fake()->dateTimeBetween('-1 year', '-6 months'),
        ]);
    }
}
