<?php

namespace Database\Factories;

use App\Models\FavoriteList;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteListItemFactory extends Factory
{
    public function definition(): array
    {
        $list = FavoriteList::inRandomOrder()
            ->first();
        $product = Product::inRandomOrder()
            ->first();

        return [
            'list_id' => $list->id,
            'product_id' => $product->id,
            'added_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }
}
