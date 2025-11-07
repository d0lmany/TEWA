<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Seller;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = Seller::with('user')->get();
        
        foreach ($sellers as $seller) {
            Shop::factory(rand(1, 3))->create([
                'seller_id' => $seller->id,
                'name' => $seller->full_name . ' ' . fake()->randomElement(['Store', 'Shop', 'Market']),
            ]);
        }

        Shop::factory(10)->create();
    }
}
