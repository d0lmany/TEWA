<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Shop;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductAttribute;
use App\Models\ProductDetail;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        $tags = ['Изготовление на заказ', 'Открыт предзаказ', 'Есть возврат', 'Экологичный товар', 'Б/У', 'DIY', 'Оригинал', 'Уценён', 'Без упаковки', 'Веганский', 'Гипоаллергенный'];
        
        return [
            'name' => $name,
            'quantity' => fake()->numberBetween(0, 1000),
            'base_price' => fake()->randomFloat(2, 10, 100000),
            'photo' => fake()->imageUrl(400, 400, 'product', true, $name),
            'category_id' => Category::factory(),
            'tags' => json_encode(fake()->randomElements($tags, fake()->randomDigit())),
            'discount' => fake()->randomFloat(2, 0, 100),
            'status' => fake()->randomElement(['on', 'off', 'draft']),
            'shop_id' => Shop::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function withCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    public function withShop(Shop $shop): static
    {
        return $this->state(fn (array $attributes) => [
            'shop_id' => $shop->id,
        ]);
    }

    public function withAttributes($count = 3): static
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            ProductAttribute::factory($count)->create(['product_id' => $product->id]);
        });
    }

    public function withDetails(): static
    {
        return $this->afterCreating(function (Product $product) {
            ProductDetail::factory()->create(['product_id' => $product->id]);
        });
    }

        public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on',
            'quantity' => fake()->numberBetween(1, 100),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }

    public function withDiscount($percent): static
    {
        return $this->state(fn (array $attributes) => [
            'discount' => $percent,
        ]);
    }
}