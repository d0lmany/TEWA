<?php

namespace Database\Factories;

use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductAttributeFactory extends Factory
{
    protected $model = ProductAttribute::class;

    public function definition(): array
    {
        $key = fake()->randomElement(['color', 'size', 'material', 'weight']);
        $product = Product::inRandomOrder()
            ->first();
        
        return [
            'product_id' => $product->id,
            'attr_key' => $key,
            'attr_value' => $this->makeValue($key),
            'price' => fake()->randomFloat(2, 0, 3000),
            'is_variant' => fake()->boolean(30),
            'is_default' => false,
        ];
    }

    private function makeValue(string $key) {
        switch ($key) {
            case 'color': return fake()->safeColorName();
            case 'size': return fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL']);
            case 'material': return fake()->randomElement(['cotton', 'silk', 'synthetic', 'denim']);
            case 'weight': return fake()->randomElement(['500g', '1kg', '250g', '100g']);
        }
    }
}
