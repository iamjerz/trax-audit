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
            $pageAccess = collect();

            if ($user) {
                $types = \App\Support\AccessRoles::expand(
                    DB::table('extension_access')
                        ->where('employeeid', $user->employeeid)
                        ->pluck('access_type')
                        ->all()
                );

                // Keep the ->contains('access_type', X) shape the views use
                $access = collect($types)->map(fn ($t) => (object) ['access_type' => $t]);

                // Web-page access is granted per Position, not per user (see
                // page_access + the `page:` middleware). Admins see every
                // page regardless of Position, same bypass as CheckAccess.
                if (in_array('admin', $types, true)) {
                    $pageAccess = collect(array_keys(\App\Support\PageRegistry::$pages));
                } elseif (! empty($user->position)) {
                    $pageAccess = DB::table('page_access')
                        ->where('position', $user->position)
                        ->pluck('page_key');
                }
            }

            $view->with('access', $access);
            $view->with('pageAccess', $pageAccess);
        });
    }
}
