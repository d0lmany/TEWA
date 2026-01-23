<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Product;
use App\Models\ProductTag;
use Exception;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        try {
            Tag::factory(30)->create();
        } catch (Exception $e) {}

        $productIds = Product::pluck('id')->all();

        foreach ($productIds as $productId) {
            $tagCount = fake()->numberBetween(0, 9);

            if ($tagCount === 0) continue;

            $tagIds = Tag::inRandomOrder()
                ->limit($tagCount)
                ->pluck('id')
                ->all();

            $records = [];
            foreach ($tagIds as $tagId) {
                $records[] = [
                    'product_id' => $productId,
                    'tag_id' => $tagId,
                ];
            }

            if (!empty($records)) {
                ProductTag::insert($records);
            }
        }
    }
}
