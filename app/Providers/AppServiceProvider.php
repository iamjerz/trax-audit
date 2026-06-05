<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
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
        // Render Laravel's paginator with Bootstrap 5 markup (matches the theme)
        Paginator::useBootstrapFive();

        View::composer(['partials.bodyheader', 'homepage'], function ($view) {
            $user = auth()->user();

            $access = collect();

            if ($user) {
                $access = DB::table('extension_access')
                    ->select('access_type')
                    ->where('employeeid', $user->employeeid)
                    ->get();
            }

            $view->with('access', $access);
        });
    }
}
