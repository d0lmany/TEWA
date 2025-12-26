<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Product;
use App\Models\Claim;
use App\Models\Shop;

class ClaimFactory extends Factory
{
    protected $model = Claim::class;

    public function definition(): array
    {
        $entity = fake()->randomElement(['product', 'shop']);
        
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'entity' => $entity,
            'entity_id' => (string) match ($entity) {
                'product' => Product::inRandomOrder()->first()->id,
                'shop' => Shop::inRandomOrder()->first()->id,
            },
            'topic' => fake()->words(fake()->numberBetween(1, 3), true),
            'text' => fake()->text(300),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function withoutUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
