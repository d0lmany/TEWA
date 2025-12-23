<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    public function definition(): array
    {
        $user = User::inRandomOrder()
            ->first();
        $product = Product::inRandomOrder()
            ->first();
        $attrs = ProductAttribute::where('product_id', $product->id)
            ->where('is_variant', 0)
            ->pluck('id')
            ->toArray();
        $count = fake()->numberBetween(0, min(3, count($attrs)));

        return [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => fake()->numberBetween(0, $product->quantity),
            'product_attributes' => fake()->randomElements($attrs, $count)
        ];
    }
}
