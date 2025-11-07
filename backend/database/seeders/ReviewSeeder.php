<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::active()->get();
        $users = User::all();

        foreach ($products as $product) {
            $reviewCount = fake()->numberBetween(0, 8);
            
            for ($i = 0; $i < $reviewCount; $i++) {
                Review::factory()->create([
                    'product_id' => $product->id,
                    'user_id' => $users->random()->id,
                ]);
            }

            $this->updateProductRating($product);
        }

        $popularProducts = $products->random(10);
        foreach ($popularProducts as $product) {
            Review::factory(fake()->numberBetween(5, 15))->create([
                'product_id' => $product->id,
            ]);
        }
    }

    private function updateProductRating(Product $product): void
    {
        $reviews = $product->reviews;
        
        if ($reviews->isNotEmpty()) {
            $averageRating = $reviews->avg('evaluation');
            
            if (Schema::hasColumn('products', 'rating')) {
                $product->update([
                    'rating' => round($averageRating, 1)
                ]);
            }
        }
    }
}
