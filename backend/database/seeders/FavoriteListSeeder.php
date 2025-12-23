<?php

namespace Database\Seeders;

use App\Models\FavoriteList;
use Illuminate\Database\Seeder;

class FavoriteListSeeder extends Seeder
{
    public function run(): void
    {
        FavoriteList::factory()->count(35)->create();
    }
}
