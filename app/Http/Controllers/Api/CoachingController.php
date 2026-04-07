<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coaching;
use App\Models\User;

class CoachingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Reference' => 'required|string',
            'Smart' => 'required|array',
            'Grow' => 'required|array',
            'Origin' => 'required|string',
            'email' => 'nullable|email'
        ]);

        $employeeid = null;

        switch ($validated['Origin']) {
            case 'Web':
                $employeeid = optional(auth()->user())->employeeid;
                break;

            case 'Extension':
                $user = User::where('email', $request->email)->first();
                $employeeid = optional($user)->employeeid;
                break;
        }

        if (!$employeeid) {
            return response()->json([
                'message' => 'Unable to resolve employee ID'
            ], 401);
        }

        $coaching = Coaching::updateOrCreate(
            ['reference' => $validated['Reference']],
            [
                'smart' => $validated['Smart'],
                'grow' => $validated['Grow'],
                'created_by' => $employeeid
            ]
        );

        return response()->json([
            'message' => 'Saved successfully',
            'data' => $coaching,
            'status' => 200
        ]);
    }
}