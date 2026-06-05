<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ForcePasswordChange;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function show()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Don't allow re-setting the default password (would loop forever).
        if ($request->input('password') === ForcePasswordChange::DEFAULT_PASSWORD) {
            return back()->withErrors([
                'password' => 'You cannot reuse the default password. Please choose a different one.',
            ]);
        }

        $user = $request->user();
        $user->password = $request->input('password'); // auto-hashed via the model's "hashed" cast
        $user->save();

        AuditTrail::record([
            'event'          => 'password_changed',
            'description'    => 'Changed own password',
            'auditable_type' => 'User',
            'auditable_id'   => $user->employeeid,
        ]);

        // Mark verified so the middleware lets them straight through.
        $request->session()->put('password_verified', true);

        return redirect()->route('homepage')
            ->with('success', 'Your password has been updated successfully.');
    }
}
