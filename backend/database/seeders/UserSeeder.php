<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    protected $model = User::class;

    public function run(): void
    {
        User::factory()->count(70)->create();

        User::create([
            'name' => 'Джонни Гэт',
            'email' => 'master@gmail.com',
            'password' => Hash::make('iLefw$62jey'),
            'birthday' => '2006-04-14',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Алина Малевич',
            'email' => 'AlinaMalevich01@mail.com',
            'password' => Hash::make('498t3598tyigh3oGLea!'),
            'birthday' => '2002-07-12',
        ]);

        User::create([
            'name' => 'Уэйн',
            'email' => 'waynemccallow@memphis.com',
            'password' => Hash::make('fwefwJBI69##;'),
            'birthday' => '1996-05-05',
        ]);
    }
}
