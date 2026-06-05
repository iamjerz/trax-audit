<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Enforces extension_access permissions on the server side.
 *
 * Usage on a route/group:  ->middleware('access:web_dashboard')
 *                          ->middleware('access:web_dashboard,web_report_monitoring')  // any-of
 *
 * Users with the 'admin' access type are always allowed.
 */
class CheckAccess
{
    public function handle(Request $request, Closure $next, ...$types)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Look up the user's access types once per request.
        $userAccess = DB::table('extension_access')
            ->where('employeeid', $user->employeeid)
            ->pluck('access_type')
            ->all();

        // Admins bypass all access checks.
        if (in_array('admin', $userAccess, true)) {
            return $next($request);
        }

        // Allow if the user holds ANY of the required access types.
        foreach ($types as $type) {
            if (in_array($type, $userAccess, true)) {
                return $next($request);
            }
        }

        // Not authorized.
        if ($request->expectsJson()) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return redirect()->route('homepage')
            ->with('error', 'You do not have permission to access that page.');
    }
}
