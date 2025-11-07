<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        $name = fake()->company() . ' ' . fake()->randomElement(['Store', 'Shop', 'Market', 'Outlet']);

        return [
            'name' => $name,
            'picture' => fake()->imageUrl(640, 480, 'logo'),
            'description' => fake()->text(300),
            'seller_id' => Seller::factory(),
        ];
    }

    public function withProducts($count): static
    {
        return $this->afterCreating(function (Shop $shop) use ($count) {
            \App\Models\Product::factory($count)->create(['shop_id' => $shop->id]);
        });
    }
}
