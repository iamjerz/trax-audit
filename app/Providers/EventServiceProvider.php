<?php

namespace App\Providers;

use App\Models\AuditTrail;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Successful login
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;

            AuditTrail::record([
                'employeeid'     => $user->employeeid ?? null,
                'actor_name'     => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'event'          => 'login',
                'description'    => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) . ' logged in on the website',
                'auditable_type' => 'auth',
            ]);
        });

        // Logout
        Event::listen(Logout::class, function (Logout $event) {
            $user = $event->user;

            AuditTrail::record([
                'employeeid'     => $user->employeeid ?? null,
                'actor_name'     => $user
                    ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                    : null,
                'event'          => 'logout',
                'description'    => ($user
                    ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                    : 'User') . ' logged out of the website',
                'auditable_type' => 'auth',
            ]);
        });

        // Failed login attempt
        Event::listen(Failed::class, function (Failed $event) {
            $email = $event->credentials['email'] ?? 'unknown';

            AuditTrail::record([
                'employeeid'     => null,
                'actor_name'     => null,
                'event'          => 'login_failed',
                'description'    => 'Failed login attempt on the website for ' . $email,
                'auditable_type' => 'auth',
                'new_values'     => ['email' => $email],
            ]);
        });
    }
}
