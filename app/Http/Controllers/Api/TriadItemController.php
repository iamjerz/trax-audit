<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TriadItems;
use App\Models\User;
use Illuminate\Support\Str;
class TriadItemController extends Controller
{
    function generateReference()
    {
        return 'TRIAD-' . now()->format('YmdHis') . '-' . Str::random(4);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Reference' => 'required|string',
            'Triad' => 'required|array',
            'Origin' => 'required|string',
            'email' => 'nullable|email' // for Extension
        ]);

        $employeeid = null;

        // ✅ Web (logged-in user)
        if ($validated['Origin'] === "Web") {
            $employeeid = optional(auth()->user())->employeeid;
        }

        // ✅ Extension (email-based)
        if ($validated['Origin'] === "Extension") {

            if (!$request->email) {
                return response()->json([
                    'message' => 'Email is required for Extension'
                ], 400);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            $employeeid = $user->employeeid;
        }

        // ❗ Final safety check
        if (!$employeeid) {
            return response()->json([
                'message' => 'Unable to resolve employee ID'
            ], 401);
        }

        // $triad = TriadItems::updateOrCreate(
        //     ['reference' => $validated['Reference']],
        //     [
        //         'triad' => $validated['Triad'],
        //         'created_by' => $employeeid
        //     ]
        // );

        $triad = TriadItems::create([
            'reference' => $validated['Reference'],
            'reference_id' => $this->generateReference(),
            'triad' => $validated['Triad'],
            'created_by' => $employeeid
        ]);

        return response()->json([
            'message' => 'Saved successfully',
            'data' => $triad,
            'status' => 200
        ]);
    }

    // ✅ GET BY REFERENCE
    public function show($reference)
    {
        $triad = TriadItems::where('reference', $reference)->first();

        if (!$triad) {
            return response()->json([
                'message' => 'Not found'
            ], 404);
        }

        return response()->json($triad);
    }

    // ✅ UPDATE (explicit)
    public function update(Request $request, $reference)
    {
        $triad = TriadItems::where('reference', $reference)->first();

        if (!$triad) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $triad->update([
            'triad' => $request->Triad
        ]);

        return response()->json([
            'message' => 'Updated successfully',
            'data' => $triad
        ]);
    }

    // ✅ LIST ALL (optional)
    public function index()
    {
        return response()->json(
            TriadItems::latest()->paginate(10)
        );
    }
}