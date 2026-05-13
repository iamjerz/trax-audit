<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coaching;
use App\Models\User;
use Illuminate\Support\Str;
class CoachingController extends Controller
{

    function generateReference()
    {
        return 'COA-' . now()->format('YmdHis') . '-' . Str::random(4);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference_type' => 'required|string',
            'reference' => 'required|string',
            'smart' => 'required|array',
            'grow' => 'required|array',
            'apps' => 'required|string',
            'email' => 'nullable|email'
        ]);

        $employeeid = null;

        switch ($validated['apps']) {
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

        $coaching = Coaching::create([
            'reference' => $validated['reference'],
            'reference_type' => $validated['reference_type'],
            'reference_id' => $this->generateReference(),
            'smart' => $validated['smart'],
            'grow' => $validated['grow'],
            'created_by' => $employeeid
        ]);

        return response()->json([
            'message' => 'Saved successfully',
            'data' => $coaching,
            'status' => 200
        ]);
    }
}