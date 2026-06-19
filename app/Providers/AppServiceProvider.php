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
                $types = \App\Support\AccessRoles::expand(
                    DB::table('extension_access')
                        ->where('employeeid', $user->employeeid)
                        ->pluck('access_type')
                        ->all()
                );

                // Keep the ->contains('access_type', X) shape the views use
                $access = collect($types)->map(fn ($t) => (object) ['access_type' => $t]);
            }

            $view->with('access', $access);
        });
    }
}
