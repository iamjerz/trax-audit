<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // REMOVE THIS IS YOU WANT TO RUN IN LOCALHOST
        // if (app()->environment('local')) {
        //     URL::forceScheme('https');
        // }

        // if (request()->getHost() !== 'localhost') {
        //     URL::forceScheme('https');
        // }
    }
}
