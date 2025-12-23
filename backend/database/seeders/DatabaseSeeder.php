<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            SellerSeeder::class,
            ShopSeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
            ClaimSeeder::class, 
            PickupSeeder::class,
            AddressSeeder::class,
            CartItemSeeder::class,
            FavoriteListSeeder::class,
            FavoriteListItemSeeder::class,
        ]);
    }
}
