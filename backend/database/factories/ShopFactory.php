<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'picture' => 'shops/shop.png',
            'description' => fake()->text(300),
            'seller_id' => Seller::factory(),
        ];
    }
}
