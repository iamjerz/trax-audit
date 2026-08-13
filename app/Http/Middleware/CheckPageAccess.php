<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Enforces page_access permissions on the server side — access to a web
 * page is granted per Position, not per user.
 *
 * Usage on a route/group:  ->middleware('page:dashboard-qa')
 *                          ->middleware('page:dashboard-qa,eval-individual')  // any-of
 *
 * Users with the 'admin' access type are always allowed, same as CheckAccess.
 * This is completely separate from the Chrome extension's capabilities
 * (extension_action_register/monitoring/coaching/triad) — those are still
 * governed entirely by AccessRoles + extension_access and are untouched here.
 */
class CheckPageAccess
{
    public function handle(Request $request, Closure $next, ...$pageKeys)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $userAccess = \App\Support\AccessRoles::expand(
            DB::table('extension_access')
                ->where('employeeid', $user->employeeid)
                ->pluck('access_type')
                ->all()
        );

        // Admins bypass all page checks, same as CheckAccess.
        if (in_array('admin', $userAccess, true)) {
            return $next($request);
        }

        // Access is keyed by Position, not by the individual user.
        if (! empty($user->position)) {
            $hasAccess = DB::table('page_access')
                ->where('position', $user->position)
                ->whereIn('page_key', $pageKeys)
                ->exists();

            if ($hasAccess) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return redirect()->route('homepage')
            ->with('error', 'You do not have permission to access that page.');
    }
}
