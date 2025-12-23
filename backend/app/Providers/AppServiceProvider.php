<?php

namespace App\Providers;

use Faker\Factory;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('Faker\Generator', function () {
            return Factory::create('ru_RU');
        });
    }

    public function boot(): void
    {
        //
    }
}
