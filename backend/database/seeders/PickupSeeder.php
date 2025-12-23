<?php

namespace Database\Seeders;

use App\Models\Pickup;
use Illuminate\Database\Seeder;

class PickupSeeder extends Seeder
{
    public function run(): void
    {
        Pickup::factory()->count(70)->create();
    }
}
