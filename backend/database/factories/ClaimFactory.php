<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Product;
use App\Models\Claim;

class ClaimFactory extends Factory
{
    protected $model = Claim::class;

    public function definition(): array
    {
        $product = Product::factory()->create();
        
        return [
            'user_id' => User::factory(),
            'entity' => 'product',
            'entity_id' => (string) $product->id,
            'topic' => fake()->randomElement([
                'Несоответствие описанию',
                'Проблемы с качеством',
                'Не доставлен товар',
                'Обман с ценой', 
                'Неправильный размер',
                'Поврежденный товар',
                'Поддельный товар',
                'Навязывание услуг',
            ]),
            'text' => fake()->text(300),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
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
            'entity_id' => (string) $product->id,
        ]);
    }

    public function withTopic(string $topic): static
    {
        return $this->state(fn (array $attributes) => [
            'topic' => $topic,
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'topic' => fake()->randomElement([
                'Не доставлен товар',
                'Поврежденный товар', 
                'Обман с ценой'
            ]),
            'text' => fake()->text(400),
            'created_at' => fake()->dateTimeBetween('-2 days', 'now'),
        ]);
    }

    public function withoutUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
