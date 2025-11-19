<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductDetailFactory extends Factory
{
    protected $model = ProductDetail::class;


    public function definition(): array
    {
        $images = [];
        for ($i = 0; $i < fake()->numberBetween(2, 6); $i++) {
            $images[] = str_replace('https://via.placeholder.com/', '', fake()->imageUrl(400, 400, 'product', true));
        }
        
        return [
            'product_id' => Product::factory(),
            'album' => json_encode($images),
            'description' => fake()->text(1000),
            'application' => fake()->text(500),
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public function withMinimalInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'application' => null,
            'album' => json_encode([str_replace('https://via.placeholder.com/', '', fake()->imageUrl(400, 400, 'product', true))]),
        ]);
    }
}
