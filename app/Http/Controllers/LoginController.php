<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Email is always lowercase — the login form already forces this as
        // you type, this is just the server-side backstop (also matches how
        // email is normalized elsewhere in this app, e.g. the Edit User form).
        $credentials['email'] = strtolower(trim($credentials['email']));

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Password matched, but a deactivated account still isn't
            // allowed in — check status before the session is trusted, and
            // log them right back out if they've been set inactive.
            if ($user->status === 'inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account has been deactivated. Please contact your administrator.',
                ])->onlyInput('email');
            }

            // Some accounts are restricted to Microsoft sign-in only (set on
            // /users, see login_method) — a correct password still isn't
            // enough to get them in through this form.
            if ($user->login_method === 'microsoft') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account must sign in with Microsoft. Please use the "Sign in with Microsoft" button below.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended('/homepage');
        }


        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Kick off the web "Sign in with Microsoft" flow (Socialite redirects to
     * Microsoft's login/consent screen). Separate, stateless-handshake flow
     * from the Chrome extension's token verification (VerifyMicrosoftToken /
     * LoginVerifyController) — this one ends in a normal Laravel session via
     * handleMicrosoftCallback() below, same as the password login above.
     */
    public function redirectToMicrosoft(): RedirectResponse
    {
        return Socialite::driver('microsoft')->redirect();
    }

    /**
     * Handle Microsoft's redirect back. Deliberately does NOT auto-provision
     * an account — this app is admin-managed (accounts are created on
     * /users with an assigned position/role/access), so a Microsoft sign-in
     * only succeeds for an email that already has a users row. Same
     * inactive-account block as the password path, and any
     * still-on-the-default-password account gets its password rotated away
     * (not just left alone) so ForcePasswordChange never catches a
     * Microsoft-only user in a loop it can't get out of.
     */
    public function handleMicrosoftCallback(Request $request): RedirectResponse
    {
        // Microsoft redirects back with ?error=...&error_description=...
        // instead of ?code=... when the user cancels, denies consent, or
        // Azure rejects the request for some other reason (e.g. admin
        // consent required). Catch that explicitly up front — otherwise
        // Socialite silently tries to exchange a code that was never there,
        // and Azure's token endpoint returns a confusing secondary "missing
        // code parameter" error instead of the real reason.
        if ($request->filled('error')) {
            Log::warning('Microsoft callback returned an error instead of a code', [
                'query' => $request->query(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Microsoft sign-in was cancelled or denied ('.$request->input('error').'). Please try again, or sign in with your password.',
            ]);
        }

        try {
            $msUser = Socialite::driver('microsoft')->user();
        } catch (\Throwable $e) {
            // Was silently swallowed before — log it so the actual cause
            // (bad client secret, redirect URI mismatch, etc.) is visible
            // instead of just this generic message. Guzzle truncates the
            // body preview inside getMessage(), so pull the full response
            // body separately when there is one (e.g. Azure's AADSTS error
            // JSON) rather than relying on that truncated summary.
            $context = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];

            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $context['response_body'] = (string) $e->getResponse()->getBody();
            }

            Log::error('Microsoft login callback failed: ' . $e->getMessage(), $context);

            return redirect()->route('login')->withErrors([
                'email' => 'Microsoft sign-in failed or was cancelled. Please try again, or sign in with your password.',
            ]);
        }

        $email = strtolower(trim((string) $msUser->getEmail()));

        $user = $email !== '' ? User::where('email', $email)->first() : null;

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'No Audit Ops account is set up for this Microsoft account. Contact your administrator.',
            ]);
        }

        if ($user->status === 'inactive') {
            return redirect()->route('login')->withErrors([
                'email' => 'This account has been deactivated. Please contact your administrator.',
            ]);
        }

        // Some accounts are restricted to password sign-in only (set on
        // /users, see login_method) — a valid Microsoft identity still
        // isn't enough to get them in through this path.
        if ($user->login_method === 'password') {
            return redirect()->route('login')->withErrors([
                'email' => 'This account must sign in with email and password, not Microsoft.',
            ]);
        }

        // A Microsoft-authenticated user will never use the password field —
        // rotate it off the shared default so ForcePasswordChange has
        // nothing to force them into changing. Plain assignment (not
        // Hash::make()) matches UserPageController::resetPassword() — the
        // "hashed" cast on User::$password hashes it on save either way.
        if (Hash::check(\App\Http\Middleware\ForcePasswordChange::DEFAULT_PASSWORD, $user->password)) {
            $user->password = Str::random(40);
            $user->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/homepage');
    }
}
