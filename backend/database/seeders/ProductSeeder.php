<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Shop;
use App\Models\ProductAttribute;
use App\Models\ProductDetail;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $shops = Shop::all();
        $categories = Category::whereNotNull('parent_id')->get();

        foreach ($shops as $shop) {
            $products = Product::factory(fake()->numberBetween(3, 8))
                ->create([
                    'shop_id' => $shop->id,
                    'category_id' => fn() => $categories->random(),
                ]);

            foreach ($products as $product) {
                ProductDetail::factory()->create(['product_id' => $product->id]);

                $attributeCount = fake()->numberBetween(1, 5);
                $attributes = ProductAttribute::factory($attributeCount)
                    ->create(['product_id' => $product->id]);

                if ($attributes->isNotEmpty()) {
                    $attributes->first()->update(['is_default' => true]);
                }
            }
        }
    }
}
