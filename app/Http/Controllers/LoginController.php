<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

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
            // Password matched, but a deactivated account still isn't
            // allowed in — check status before the session is trusted, and
            // log them right back out if they've been set inactive.
            if (Auth::user()->status === 'inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account has been deactivated. Please contact your administrator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended('/homepage');
        }


        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
