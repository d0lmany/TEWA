<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $mainCategories = [
            'Электроника' => ['Смартфоны', 'Ноутбуки', 'Телевизоры', 'Наушники', 'Планшеты'],
            'Одежда' => ['Мужская', 'Женская', 'Детская', 'Обувь', 'Аксессуары'],
            'Дом и сад' => ['Мебель', 'Текстиль', 'Посуда', 'Освещение', 'Садовый инвентарь'],
            'Красота и здоровье' => ['Косметика', 'Парфюмерия', 'Уход за волосами', 'Витамины'],
            'Спорт' => ['Фитнес', 'Велоспорт', 'Туризм', 'Йога', 'Тренажеры'],
        ];

        foreach ($mainCategories as $mainCategory => $subcategories) {
            $parent = Category::create(['name' => $mainCategory]);

            foreach ($subcategories as $subcategory) {
                Category::create([
                    'name' => $subcategory,
                    'parent_id' => $parent->id,
                ]);
            }
        }
    }
}
