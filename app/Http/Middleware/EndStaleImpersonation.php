<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\ImpersonationController;
use App\Models\AuditTrail;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Safety net for "View as" (see ImpersonationController): if an admin
 * forgets to click "Return to my account", this ends the impersonation
 * automatically after ImpersonationController::MAX_MINUTES, rather than
 * leaving the session authenticated as someone else indefinitely — bounded
 * independently of the general SESSION_LIFETIME, which can be a couple of
 * hours.
 */
class EndStaleImpersonation
{
    public function handle(Request $request, Closure $next)
    {
        $startedAt = session('impersonation_started_at');

        if (! $startedAt || Carbon::parse($startedAt)->diffInMinutes(now()) < ImpersonationController::MAX_MINUTES) {
            return $next($request);
        }

        $target = Auth::user();
        $admin = User::where('employeeid', session('impersonator_id'))->first();
        // Must read this before forget() below clears it.
        $adminName = session('impersonator_name', $admin->employeeid ?? session('impersonator_id'));

        // Always clear the stale flag, whatever else happens below.
        session()->forget(['impersonator_id', 'impersonator_name', 'impersonation_started_at']);

        if (! $admin) {
            // The admin account disappeared mid-session — nothing to return
            // to; just drop the flag and let the request continue as
            // whichever account is currently authenticated.
            return $next($request);
        }

        $targetName = $target ? trim(($target->first_name ?? '') . ' ' . ($target->last_name ?? '')) : 'unknown';

        AuditTrail::record([
            'employeeid' => $admin->employeeid,
            'actor_name' => $adminName,
            'event' => 'impersonate_stop',
            'description' => "{$adminName}'s \"view as {$targetName}\" session expired after "
                . ImpersonationController::MAX_MINUTES . ' minutes and was ended automatically',
            'auditable_type' => 'user',
            'auditable_id' => $target->employeeid ?? null,
        ]);

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('homepage')->with(
            'info',
            'Your "View as" session expired after ' . ImpersonationController::MAX_MINUTES . ' minutes — you\'re back on your own account.'
        );
    }
}
