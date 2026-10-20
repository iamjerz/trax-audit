<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->alias([
            'ms.jwt' => \App\Http\Middleware\VerifyMicrosoftToken::class,
            'access' => \App\Http\Middleware\CheckAccess::class,
            'page' => \App\Http\Middleware\CheckPageAccess::class,
        ]);

        // Force users still on the default password to change it before using the app.
        // EndStaleImpersonation must run every request too, so an admin who
        // forgets to end a "View as" session isn't left in it indefinitely.
        $middleware->web(append: [
            \App\Http\Middleware\ForcePasswordChange::class,
            \App\Http\Middleware\EndStaleImpersonation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
