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
        $color = fake()->safeColorName();
        $size = fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL']);
        
        return [
            'product_id' => Product::factory(),
            'attr_key' => fake()->randomElement(['color', 'size', 'material', 'weight']),
            'attr_value' => fake()->randomElement([$color, $size, 'Cotton', '500g']),
            'price' => fake()->randomFloat(2, 0, 3000),
            'is_variant' => fake()->boolean(30),
            'is_default' => false,
        ];
    }

    public function variant(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_variant' => true,
            'attr_key' => fake()->randomElement(['color', 'size']),
            'attr_value' => fake()->randomElement(['Red', 'Blue', 'Black', 'S', 'M', 'L']),
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public function colorVariant(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_variant' => true,
            'attr_key' => 'color',
            'attr_value' => fake()->safeColorName(),
            'price' => fake()->randomFloat(2, 0, 200),
        ]);
    }

    public function sizeVariant(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_variant' => true,
            'attr_key' => 'size',
            'attr_value' => fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
            'price' => fake()->randomFloat(2, 0, 100),
        ]);
    }
}
