<?php

namespace Database\Factories;

use App\Utils\Utils;
use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductDetailFactory extends Factory
{
    protected $model = ProductDetail::class;


    public function definition(): array
    {
        $product = Product::inRandomOrder()
            ->first();
        $images = [];
        for ($i = 0; $i < fake()->numberBetween(2, 10); $i++) {
            $images[] = 'products/product.png';
        }
        
        return [
            'product_id' => $product->id,
            'album' => $images,
            'description' => fake()->text(1000),
            'application' => fake()->text(500),
        ];
    }
}
