<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * "View as" — lets an admin temporarily authenticate as another user to see
 * exactly what they see. Deliberately session-based rather than a DB table:
 * it's inherently tied to one browser session and doesn't need to survive a
 * restart or work across devices.
 *
 * This needs zero changes to any existing scoping logic (CheckAccess,
 * CheckPageAccess, FiltersByManagerScope, PositionScope, the
 * $access/$pageAccess view composer in AppServiceProvider) because every
 * one of them reads Auth::user() fresh on each request — swapping who that
 * resolves to is enough for all of it to correctly reflect the target's own
 * access, automatically.
 *
 * Both endpoints return JSON rather than redirecting directly: the "View
 * as" button (user.js) and the banner's "Return to my account" button
 * (impersonation.js) both call these via fetch so they can use this app's
 * usual confirm()/notifyError() conventions, then navigate the browser on
 * success — a full navigation is genuinely needed here since every page's
 * content depends on which identity is authenticated.
 */
class ImpersonationController extends Controller
{
    // Safety net in case an admin forgets to return — see
    // EndStaleImpersonation, which enforces this independently of the
    // general SESSION_LIFETIME (which can be a couple of hours).
    public const MAX_MINUTES = 30;

    public function start(Request $request, string $employeeid)
    {
        $admin = Auth::user();

        if (session('impersonator_id')) {
            return response()->json([
                'success' => false,
                'message' => 'You are already viewing as another account — return to your own account first.',
            ], 422);
        }

        if ($employeeid === $admin->employeeid) {
            return response()->json([
                'success' => false,
                'message' => "You're already signed in as yourself.",
            ], 422);
        }

        $target = User::where('employeeid', $employeeid)->first();
        if (! $target) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($target->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => "That account is deactivated and can't be viewed as.",
            ], 422);
        }

        $targetIsAdmin = DB::table('extension_access')
            ->where('employeeid', $target->employeeid)
            ->where('access_type', 'admin')
            ->exists();

        if ($targetIsAdmin) {
            return response()->json([
                'success' => false,
                'message' => "Can't view as another admin account.",
            ], 422);
        }

        $adminName = trim(($admin->first_name ?? '') . ' ' . ($admin->last_name ?? ''));
        $targetName = trim(($target->first_name ?? '') . ' ' . ($target->last_name ?? ''));

        // Written BEFORE the identity swap below, so AuditTrail::record()'s
        // own actor defaults (auth()->user()) still correctly resolve to
        // the admin, not the target.
        AuditTrail::record([
            'event' => 'impersonate_start',
            'description' => "{$adminName} started viewing as {$targetName} ({$target->employeeid})",
            'auditable_type' => 'user',
            'auditable_id' => $target->employeeid,
            'new_values' => ['target_employeeid' => $target->employeeid, 'target_email' => $target->email],
        ]);

        session([
            'impersonator_id' => $admin->employeeid,
            'impersonator_name' => $adminName,
            'impersonation_started_at' => now()->toDateTimeString(),
        ]);

        Auth::login($target);
        $request->session()->regenerate();

        return response()->json(['success' => true, 'redirect' => route('homepage')]);
    }

    public function stop(Request $request)
    {
        $impersonatorId = session('impersonator_id');
        if (! $impersonatorId) {
            return response()->json([
                'success' => false,
                'message' => 'Not currently viewing as another account.',
            ], 422);
        }

        $target = Auth::user();
        $admin = User::where('employeeid', $impersonatorId)->first();

        if (! $admin) {
            // The admin account disappeared mid-session — clear the stale
            // state and send them to login rather than leaving them stuck.
            session()->forget(['impersonator_id', 'impersonator_name', 'impersonation_started_at']);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['success' => true, 'redirect' => route('login')]);
        }

        $adminName = session('impersonator_name', $admin->employeeid);
        $targetName = $target ? trim(($target->first_name ?? '') . ' ' . ($target->last_name ?? '')) : 'unknown';

        // auth()->user() is still the TARGET at this point (we haven't
        // swapped back yet) — override the actor explicitly so this reads
        // as the admin's own action, not the target's.
        AuditTrail::record([
            'employeeid' => $admin->employeeid,
            'actor_name' => $adminName,
            'event' => 'impersonate_stop',
            'description' => "{$adminName} stopped viewing as {$targetName}" . ($target ? " ({$target->employeeid})" : ''),
            'auditable_type' => 'user',
            'auditable_id' => $target->employeeid ?? null,
        ]);

        session()->forget(['impersonator_id', 'impersonator_name', 'impersonation_started_at']);

        Auth::login($admin);
        $request->session()->regenerate();

        return response()->json(['success' => true, 'redirect' => '/users']);
    }
}
