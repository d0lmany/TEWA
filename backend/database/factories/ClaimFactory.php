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
        $product = Product::inRandomOrder()
            ->first();
        $user = User::inRandomOrder()
            ->first();
        
        return [
            'user_id' => $user->id,
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

    public function withoutUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
