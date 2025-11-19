<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Seller;
use App\Models\SellerVerification;
use Illuminate\Database\Seeder;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereDoesntHave('seller')->get();
        
        foreach ($users->take(8) as $user) {
            $seller = Seller::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'code' => 'SELL' . fake()->unique()->numberBetween(1000, 9999),
                'type' => fake()->randomElement(['individual', 'LLC', 'self-employed']),
                'payment_account' => fake()->iban(),
                'verified_at' => fake()->optional(70)->dateTimeBetween('-1 year', 'now'),
                'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            ]);

            if (fake()->boolean(60)) {
                SellerVerification::create([
                    'user_id' => $user->id,
                    'passport_numbers' => fake()->numerify('#### ######'),
                    'passport_scan' => str_replace('https://via.placeholder.com/', '', fake()->imageUrl(400, 400, 'document', true, 'passport')),
                ]);

                if (fake()->boolean(80)) {
                    $seller->update(['verified_at' => now()]);
                }
            }
        }

        Seller::factory(5)->create();
        
        $sellersWithoutVerification = Seller::whereDoesntHave('user.sellerVerification')
            ->with('user')
            ->get();

        foreach ($sellersWithoutVerification->take(5) as $seller) {
            SellerVerification::create([
                'user_id' => $seller->user_id,
                'passport_numbers' => fake()->numerify('#### ######'),
                'passport_scan' => str_replace('https://via.placeholder.com/', '', fake()->imageUrl(400, 400, 'document', true, 'passport')),
            ]);
        }
    }
}