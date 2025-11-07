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

                $attributeCount = fake()->numberBetween(1, 4);
                $attributes = ProductAttribute::factory($attributeCount)
                    ->create(['product_id' => $product->id]);

                if ($attributes->isNotEmpty()) {
                    $attributes->first()->update(['is_default' => true]);
                }

                if (fake()->boolean(30)) {
                    $this->createVariants($product);
                }
            }
        }
    }

    private function createVariants(Product $product): void
    {
        $colors = ['Red', 'Blue', 'Black', 'White', 'Green'];
        foreach ($colors as $color) {
            ProductAttribute::factory()->create([
                'product_id' => $product->id,
                'attr_key' => 'color',
                'attr_value' => $color,
                'is_variant' => true,
                'price' => fake()->randomFloat(2, -50, 100),
            ]);
        }

        $sizes = ['S', 'M', 'L', 'XL'];
        foreach ($sizes as $size) {
            ProductAttribute::factory()->create([
                'product_id' => $product->id,
                'attr_key' => 'size', 
                'attr_value' => $size,
                'is_variant' => true,
                'price' => fake()->randomFloat(2, -20, 1500),
            ]);
        }
    }
}
