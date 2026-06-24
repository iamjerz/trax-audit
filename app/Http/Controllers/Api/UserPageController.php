<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ForcePasswordChange;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserPageController extends Controller
{

    

    public function check(Request $request)
    {
        $email = $request->query('email');

        // Validate input
        if (!$email) {
            return response()->json([
                'valid' => false,
                'message' => 'Email is required'
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid email format'
            ]);
        }

        // Check if exists
        $exists = User::where('email', $email)->exists();

        return response()->json([
            'valid' => true,
            'exists' => $exists
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validate based on your schema
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'supervisor_id' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Create user
        $user = User::create([
            'employeeid' => $this->generateEmployeeId(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'position' => $request->position,
            'department' => $request->department,
            'supervisor_id' => $request->supervisor_id,
            'email' => $request->email,
            'password' => Hash::make('password123'), // 👈 default password

            // Optional fields (with defaults)
            'role' => $request->role ?? 'user',
            'status' => $request->status ?? 'active',
            'profile_photo_path' => $request->profile_photo_path ?? null,
        ]);

        // 3. Response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    private function generateEmployeeId()
    {
        $latestUser = User::orderBy('id', 'desc')->first();

        if (!$latestUser || !$latestUser->employeeid) {
            return 'EMP-202601'; // starting value
        }

        // Extract number from EMP-202601
        $number = (int) str_replace('EMP-', '', $latestUser->employeeid);

        // Increment
        $newNumber = $number + 1;

        return 'EMP-' . $newNumber;
    }

    public function getUsersData()
    {
        return [
            'logisticsUsers' => User::select('employeeid', 'first_name', 'last_name')
                ->where('position', 'Logistics Data Analyst')
                ->orderBy('first_name')
                ->get(),

            'supervisors' => User::select('employeeid', 'first_name', 'last_name')
                ->where('position', '!=', 'LDA')
                ->orderBy('first_name')
                ->get(),

            'allusers' => User::select('employeeid', 'first_name', 'last_name')
                ->orderBy('first_name')
                ->get(),
        ];
    }

    public function index($employeeid)
    {
        $user = User::leftJoin('users as supervisors', 'users.supervisor_id', '=', 'supervisors.employeeid')
            ->where('users.employeeid', $employeeid)
            ->select(
                'users.*',
                'supervisors.first_name as supervisor_first_name',
                'supervisors.last_name as supervisor_last_name',
                'supervisors.email as supervisor_email'
            )
            ->firstOrFail();

        $data = $this->getUsersData();

        $access = DB::table('extension_access')
            ->where('employeeid', $employeeid)
            ->pluck('access_type')
            ->toArray();

        return view('sub.edituser', [
            'user' => $user,
            'supervisors' => $data['supervisors'],
            'logisticsUsers' => $data['logisticsUsers'],
            'allusers' => $data['allusers'],
            'access' => $access
        ]);
    }

    public function updateUser(Request $request, $id){
     // ✅ Validate
        $validated = $request->validate([
            'email' => 'required|email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'role' => 'required|string',
            'supervisor_id' => 'nullable|string',
            'status' => 'required|string',
            'position' => 'required|string'
        ]);

        // ✅ Find user
        $user = User::where('employeeid', $id)->firstOrFail();

        // ✅ Update
        $user->update($validated);

        // ✅ Redirect
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    }

    public function resetPassword($employeeid)
    {
        $user = User::where('employeeid', $employeeid)->firstOrFail();

        // Reset to the application default; the "hashed" cast hashes it on save.
        $user->password = ForcePasswordChange::DEFAULT_PASSWORD;
        $user->save();

        AuditTrail::record([
            'event'          => 'password_reset',
            'description'    => 'Reset password to default for '
                . trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                . ' (' . $user->employeeid . ')',
            'auditable_type' => 'User',
            'auditable_id'   => $user->employeeid,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset to the default. The user must change it at next login.',
        ]);
    }

    public function updateAccessOnly(Request $request, $employeeid)
    {
        try {
            $accessList = $request->access ?? [];

            // capture old access for the audit diff
            $oldAccess = DB::table('extension_access')
                ->where('employeeid', $employeeid)
                ->pluck('access_type')
                ->toArray();

            // delete old
            DB::table('extension_access')
                ->where('employeeid', $employeeid)
                ->delete();

            // insert new
            $data = collect($accessList)
                ->filter()
                ->map(fn($access) => [
                    'employeeid' => $employeeid,
                    'access_type' => $access,
                    'created_by' => auth()->user()->employeeid ?? 'system',
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->toArray();

            if (!empty($data)) {
                DB::table('extension_access')->insert($data);
            }

            AuditTrail::record([
                'event'          => 'access_updated',
                'description'    => 'Updated extension access for employee ' . $employeeid,
                'auditable_type' => 'extension_access',
                'auditable_id'   => $employeeid,
                'old_values'     => ['access_type' => array_values($oldAccess)],
                'new_values'     => ['access_type' => array_values(collect($accessList)->filter()->all())],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Access updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}