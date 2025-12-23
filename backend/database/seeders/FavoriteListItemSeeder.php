<?php

namespace Database\Seeders;

use App\Models\FavoriteListItem;
use Illuminate\Database\Seeder;

class FavoriteListItemSeeder extends Seeder
{
    public function run(): void
    {
        FavoriteListItem::factory()->count(100)->create();
    }
}
