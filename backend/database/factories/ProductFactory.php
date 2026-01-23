<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Shop;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        
        return [
            'name' => $name,
            'quantity' => fake()->numberBetween(0, 1000),
            'base_price' => fake()->randomFloat(2, 10, 100000),
            'photo' => 'products/product.png',
            'category_id' => Category::whereNotNull('parent_id')->inRandomOrder()->value('id'),
            'discount' => fake()->randomFloat(2, 0, 100),
            'status' => fake()->randomElement(['on', 'off', 'draft']),
            'shop_id' => Shop::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}