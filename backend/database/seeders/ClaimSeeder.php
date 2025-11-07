<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClaimSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $users = User::all();

        foreach ($users->take(15) as $user) {
            Claim::factory(fake()->numberBetween(0, 3))->create([
                'user_id' => $user->id,
            ]);
        }

        Claim::factory(5)->withoutUser()->create();

        Claim::factory(3)->urgent()->create();

        $problematicProducts = $products->random(5);
        foreach ($problematicProducts as $product) {
            Claim::factory(fake()->numberBetween(2, 5))->create([
                'entity_id' => (string) $product->id,
            ]);
        }
    }
}
