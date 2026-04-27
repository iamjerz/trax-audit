<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
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
        View::composer('partials.bodyheader', function ($view) {
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
