<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditTrail;
use App\Support\AccessRoles;
use App\Support\PositionScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class CoachingTicket extends Controller
{
    //
    public function getUsersData()
    {
        return [
            'logisticsUsers' => User::select('employeeid', 'first_name', 'last_name')
                ->where('position', 'LDA')
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
    public function index(){
        $access = AccessRoles::expand(
            DB::table('extension_access')
                ->where('employeeid', auth()->user()->employeeid)
                ->pluck('access_type')
                ->all()
        );

        $canDelete = in_array('admin', $access, true);

        return view('ticketcoaching', compact('canDelete'));
    }

    public function destroy($id)
    {
        $record = DB::table('coachings')->where('reference_id', $id)->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.',
            ], 404);
        }

        DB::transaction(function () use ($id) {
            if (Schema::hasTable('acknowledgements') && Schema::hasColumn('acknowledgements', 'reference_id')) {
                DB::table('acknowledgements')
                    ->where('reference_type', 'coaching')
                    ->where('reference_id', $id)
                    ->delete();
            }

            DB::table('coachings')->where('reference_id', $id)->delete();
        });

        AuditTrail::record([
            'event'          => 'deleted',
            'description'    => 'Deleted coaching ticket ' . $id,
            'auditable_type' => 'coachings',
            'auditable_id'   => $id,
            'old_values'     => (array) $record,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coaching ticket deleted successfully.',
        ]);
    }

    public function fullDetails($id)
    {

        $usersData = $this->getUsersData();


        $data = DB::table('coachings')
            ->where('reference_id', $id)
            ->first();

        $created_by = DB::table('users as u')
        ->join('coachings as r', 'r.created_by', '=', 'u.employeeid')
        ->select('u.first_name as FirstName', 'u.last_name as LastName')
        ->where('r.reference_id', $id)
        ->first(); 
        return view("individualcoaching", compact('data', 'usersData', 'created_by'));
    }

    public function displayTicket(Request $request)
    {
        try {
            $user_email = auth()->user()->email;
            $user_position = auth()->user()->position;
            $user_employeeid = auth()->user()->employeeid;

            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $search = $request->input('search');

            $query = DB::table('coachings')
                ->leftJoin('users', 'coachings.created_by', '=', 'users.employeeid')
                ->select(
                    'coachings.*',
                    DB::raw("users.first_name || ' ' || users.last_name as full_name")
                );

            // 🔍 SEARCH (PostgreSQL-safe with ILIKE)
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('coachings.reference', 'ilike', "%{$search}%")
                    ->orWhere('coachings.reference_id', 'ilike', "%{$search}%")

                    // 🔥 NAME SEARCH
                    ->orWhere('users.first_name', 'ilike', "%{$search}%")
                    ->orWhere('users.last_name', 'ilike', "%{$search}%")
                    ->orWhere(DB::raw("users.first_name || ' ' || users.last_name"), 'ilike', "%{$search}%");
                });
            }

            // 👤 LEVEL FILTER — scope comes from the positions table (see
            // App\Support\PositionScope), not a hardcoded string match.
            $scope = PositionScope::forPosition($user_position);

            if ($scope === 'own') {
                $query->where(function ($q) use ($user_email, $user_employeeid) {
                    $q->where('coachings.created_by', $user_email)
                    ->orWhere('coachings.created_by', $user_employeeid);
                });
            } elseif ($scope === 'team') {
                $query->where('users.supervisor_id', $user_employeeid);
            }

            // ✅ COUNT
            $total = (clone $query)->count();

            // ✅ DATA
            $data = $query
                ->orderBy('coachings.id', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => $data,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
