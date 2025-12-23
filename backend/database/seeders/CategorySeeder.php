<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $parent = Category::create(['name' => fake()->words(fake()->numberBetween(1, 3), true)]);

            for ($j = 0; $j < fake()->numberBetween(2, 5); $j++) {
                Category::create([
                    'name' => fake()->words(fake()->numberBetween(1, 3), true),
                    'parent_id' => $parent->id,
                ]);
            }
        }
    }
}
