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

class TriadTicket extends Controller
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

        return view('ticketriad', compact('canDelete'));
    }

    public function destroy($id)
    {
        $record = DB::table('triad_items')->where('reference_id', $id)->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.',
            ], 404);
        }

        DB::transaction(function () use ($id) {
            if (Schema::hasTable('acknowledgements') && Schema::hasColumn('acknowledgements', 'reference_id')) {
                DB::table('acknowledgements')
                    ->where('reference_type', 'triad')
                    ->where('reference_id', $id)
                    ->delete();
            }

            DB::table('triad_items')->where('reference_id', $id)->delete();
        });

        AuditTrail::record([
            'event'          => 'deleted',
            'description'    => 'Deleted triad ticket ' . $id,
            'auditable_type' => 'triad_items',
            'auditable_id'   => $id,
            'old_values'     => (array) $record,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Triad ticket deleted successfully.',
        ]);
    }

    public function fullDetails($id)
    {

        $usersData = $this->getUsersData();

        $user_email = auth()->user()->email;
        $user_position = auth()->user()->position;
        $user_employeeid = auth()->user()->employeeid;

        // 👤 LEVEL FILTER — same own/team/all scope as displayTicket(), so
        // this detail page can't be used to bypass what a Position is
        // scoped to see on the /triad-ticket list.
        $scope = PositionScope::forPosition($user_position);

        $query = DB::table('triad_items')->where('reference_id', $id);

        if ($scope === 'own') {
            $query->where(function ($q) use ($user_email, $user_employeeid) {
                $q->where('created_by', $user_email)
                  ->orWhere('created_by', $user_employeeid);
            });
        } elseif ($scope === 'team') {
            // Same fix as displayTicket(): include this person's own
            // tickets (not just their direct reports'), and follow
            // second_supervisor_id as well as the primary supervisor.
            $query->where(function ($q) use ($user_email, $user_employeeid) {
                $q->where('created_by', $user_email)
                  ->orWhere('created_by', $user_employeeid)
                  ->orWhereIn('created_by', function ($sub) use ($user_employeeid) {
                      $sub->select('email')->from('users')
                          ->where('supervisor_id', $user_employeeid)
                          ->orWhere('second_supervisor_id', $user_employeeid);
                  })->orWhereIn('created_by', function ($sub) use ($user_employeeid) {
                      $sub->select('employeeid')->from('users')
                          ->where('supervisor_id', $user_employeeid)
                          ->orWhere('second_supervisor_id', $user_employeeid);
                  });
            });
        }

        $data = $query->first();

        if (! $data) {
            abort(403, 'You do not have access to this ticket.');
        }

        $created_by = DB::table('users as u')
        ->join('triad_items as r', 'r.created_by', '=', 'u.employeeid')
        ->select('u.first_name as FirstName', 'u.last_name as LastName')
        ->where('r.reference_id', $id)
        ->first();

        $triadEmployee = DB::table('users as u')
        ->join('triad_items as r', 'r.employee_id', '=', 'u.employeeid')
        ->select('u.first_name as FirstName', 'u.last_name as LastName')
        ->where('r.reference_id', $id)
        ->first();

        return view("individualtriad", compact('data', 'usersData', 'created_by', 'triadEmployee'));
    }

    /**
     * Set/change the triad'd employee on a triad ticket. Open to anyone
     * with access to this page (not admin-gated) -- mirrors ReconTiketController::insertAssignTo().
     */
    public function updateEmployee(Request $request, $id)
    {
        $employeeId = $request->input('employee_id');

        $updated = DB::table('triad_items')
            ->where('reference_id', $id)
            ->update([
                'employee_id' => $employeeId
            ]);

        if ($updated) {
            $user = DB::table('users')
                ->where('employeeid', $employeeId)
                ->first();

            AuditTrail::record([
                'event'          => 'updated',
                'description'    => "Triad ticket {$id} triad employee set to " . ($user ? "{$user->first_name} {$user->last_name}" : $employeeId),
                'auditable_type' => 'triad_items',
                'auditable_id'   => $id,
                'new_values'     => ['employee_id' => $employeeId],
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Triad employee updated successfully'
            ]);
        }

        return response()->json([
            'status' => 404,
            'message' => 'Record not found or no changes made'
        ], 404);
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

            // 🆕 Filter bar inputs — same pattern as CoachingTicket::displayTicket().
            // No reference_type/status/client-code equivalent exists on
            // triad_items, so unlike Coaching/Monitoring there's no dropdown
            // filter here, just identifiers + name + date range.
            $f_reference_id = $request->input('reference_id');
            $f_reference    = $request->input('reference');
            $f_name         = $request->input('name');
            $f_date_from    = $request->input('date_from');
            $f_date_to      = $request->input('date_to');

            $query = DB::table('triad_items')
                ->leftJoin('users', 'triad_items.created_by', '=', 'users.employeeid')
                ->leftJoin('users as triaded', 'triad_items.employee_id', '=', 'triaded.employeeid')
                ->select(
                    'triad_items.*',
                    DB::raw("users.first_name || ' ' || users.last_name as full_name"),
                    DB::raw("triaded.first_name || ' ' || triaded.last_name as triaded_employee_name")
                );

            // 🔍 SEARCH (PostgreSQL-safe with ILIKE)
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('triad_items.reference', 'ilike', "%{$search}%")
                    ->orWhere('triad_items.reference_id', 'ilike', "%{$search}%")

                    // 🔥 NAME SEARCH
                    ->orWhere('users.first_name', 'ilike', "%{$search}%")
                    ->orWhere('users.last_name', 'ilike', "%{$search}%")
                    ->orWhere(DB::raw("users.first_name || ' ' || users.last_name"), 'ilike', "%{$search}%");
                });
            }

            // 🆕 TRIAD REFERENCE ID
            if ($f_reference_id) {
                $query->where('triad_items.reference_id', 'ilike', "%{$f_reference_id}%");
            }

            // 🆕 REFERENCE (the originating audit/recon ticket)
            if ($f_reference) {
                $query->where('triad_items.reference', 'ilike', "%{$f_reference}%");
            }

            // 🆕 NAME FILTER (the triad'd employee's first, last, or full
            // name — not the creator, which the global search above already
            // covers)
            if ($f_name) {
                $query->where(function ($q) use ($f_name) {
                    $q->where('triaded.first_name', 'ilike', "%{$f_name}%")
                      ->orWhere('triaded.last_name', 'ilike', "%{$f_name}%")
                      ->orWhere(DB::raw("triaded.first_name || ' ' || triaded.last_name"), 'ilike', "%{$f_name}%");
                });
            }

            // 🆕 DATE RANGE (on created_at — the same value the grid already
            // labels "Triad Date"; there's no separate date column)
            if ($f_date_from) {
                $query->whereDate('triad_items.created_at', '>=', $f_date_from);
            }
            if ($f_date_to) {
                $query->whereDate('triad_items.created_at', '<=', $f_date_to);
            }

            // 👤 LEVEL FILTER — scope comes from the positions table (see
            // App\Support\PositionScope), not a hardcoded string match.
            $scope = PositionScope::forPosition($user_position);

            if ($scope === 'own') {
                $query->where(function ($q) use ($user_email, $user_employeeid) {
                    $q->where('triad_items.created_by', $user_email)
                    ->orWhere('triad_items.created_by', $user_employeeid);
                });
            } elseif ($scope === 'team') {
                // "Team" = this person's own tickets PLUS their direct
                // reports' — previously only matched reports (via
                // users.supervisor_id), so a team-scoped Supervisor who
                // created a ticket themselves would never see it in their
                // own list. Also now follows second_supervisor_id, not just
                // the primary supervisor.
                $query->where(function ($q) use ($user_email, $user_employeeid) {
                    $q->where('triad_items.created_by', $user_email)
                      ->orWhere('triad_items.created_by', $user_employeeid)
                      ->orWhere('users.supervisor_id', $user_employeeid)
                      ->orWhere('users.second_supervisor_id', $user_employeeid);
                });
            }

            // ✅ COUNT
            $total = (clone $query)->count();

            // ✅ DATA
            $data = $query
                ->orderBy('triad_items.id', 'desc')
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
