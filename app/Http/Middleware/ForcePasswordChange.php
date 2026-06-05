<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChange
{
    /**
     * The password every account is seeded/imported with by default.
     * While a user still has this password they are forced to set a new one.
     */
    public const DEFAULT_PASSWORD = 'password123';

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Not logged in (guest pages, extension JWT routes, etc.) — nothing to enforce.
        if (! $user) {
            return $next($request);
        }

        // Always let the change-password screen and logout through to avoid a redirect loop.
        if ($request->routeIs('password.change', 'password.update', 'logout')) {
            return $next($request);
        }

        // Once verified this session, don't re-hash on every request (bcrypt is expensive).
        if ($request->session()->get('password_verified')) {
            return $next($request);
        }

        if (Hash::check(self::DEFAULT_PASSWORD, $user->password)) {
            return redirect()->route('password.change');
        }

        $request->session()->put('password_verified', true);

        return $next($request);
    }
}
