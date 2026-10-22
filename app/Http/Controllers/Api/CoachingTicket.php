<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\FiltersByManagerScope;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditTrail;
use App\Support\AccessRoles;
use App\Support\PositionScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class CoachingTicket extends Controller
{
    // Reuses the QA/Recon dashboards' Manager/Supervisor picker
    // (managerPickerOptions()) and org-tree subtree walk
    // (resolveScopeLdaIds()) for the Supervisor/Manager filter below.
    use FiltersByManagerScope;

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

        $user_email = auth()->user()->email;
        $user_position = auth()->user()->position;
        $user_employeeid = auth()->user()->employeeid;

        // 👤 LEVEL FILTER — same own/team/all scope as displayTicket(), so
        // this detail page can't be used to bypass what a Position is
        // scoped to see on the /coaching-ticket list.
        $scope = PositionScope::forPosition($user_position);

        $query = DB::table('coachings')->where('reference_id', $id);

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
        ->join('coachings as r', 'r.created_by', '=', 'u.employeeid')
        ->select('u.first_name as FirstName', 'u.last_name as LastName')
        ->where('r.reference_id', $id)
        ->first();

        $coachedEmployee = DB::table('users as u')
        ->join('coachings as r', 'r.employee_id', '=', 'u.employeeid')
        ->select('u.first_name as FirstName', 'u.last_name as LastName')
        ->where('r.reference_id', $id)
        ->first();

        return view("individualcoaching", compact('data', 'usersData', 'created_by', 'coachedEmployee'));
    }

    /**
     * Set/change the coached employee on a coaching ticket. Open to anyone
     * with access to this page (not admin-gated) -- mirrors ReconTiketController::insertAssignTo().
     */
    public function updateEmployee(Request $request, $id)
    {
        $employeeId = $request->input('employee_id');

        $updated = DB::table('coachings')
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
                'description'    => "Coaching ticket {$id} coached employee set to " . ($user ? "{$user->first_name} {$user->last_name}" : $employeeId),
                'auditable_type' => 'coachings',
                'auditable_id'   => $id,
                'new_values'     => ['employee_id' => $employeeId],
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Coached employee updated successfully'
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

            // 🆕 Filter bar inputs — same pattern as MonitoringTicket::displayTicket().
            $f_reference_id   = $request->input('reference_id');
            $f_reference      = $request->input('reference');
            $f_lda            = $request->input('lda');
            $f_manager_scope  = $request->input('manager_scope');
            $f_reference_type = $request->input('reference_type');
            $f_date_from      = $request->input('date_from');
            $f_date_to        = $request->input('date_to');

            $query = DB::table('coachings')
                ->leftJoin('users', 'coachings.created_by', '=', 'users.employeeid')
                ->leftJoin('users as coached', 'coachings.employee_id', '=', 'coached.employeeid')
                ->select(
                    'coachings.*',
                    DB::raw("users.first_name || ' ' || users.last_name as full_name"),
                    DB::raw("coached.first_name || ' ' || coached.last_name as coached_employee_name")
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

            // 🆕 COACHING REFERENCE ID
            if ($f_reference_id) {
                $query->where('coachings.reference_id', 'ilike', "%{$f_reference_id}%");
            }

            // 🆕 COACHING REFERENCE (the originating audit/recon/triad ticket)
            if ($f_reference) {
                $query->where('coachings.reference', 'ilike', "%{$f_reference}%");
            }

            // 🆕 LDA (the coached employee — a dropdown of actual people,
            // replacing the old free-text Name filter)
            if ($f_lda) {
                $query->where('coachings.employee_id', $f_lda);
            }

            // 🆕 SUPERVISOR/MANAGER — reuses the QA/Recon dashboards' org-tree
            // picker: selecting a manager/SME/supervisor (or "My Team")
            // scopes results to every LDA beneath them, not just direct
            // reports. Scoped to the COACHED employee's hierarchy (not the
            // creator's), same role LDA/Coachee plays above. Combines with
            // (never widens past) the own/team/all LEVEL FILTER below, since
            // both are ANDed together.
            $managerLdaIds = $this->resolveScopeLdaIds($f_manager_scope);
            if ($managerLdaIds !== null) {
                $query->whereIn('coachings.employee_id', $managerLdaIds);
            }

            // 🆕 COACHING TYPE
            if ($f_reference_type) {
                $query->where('coachings.reference_type', $f_reference_type);
            }

            // 🆕 DATE RANGE (on created_at — the same value the grid already
            // labels "Coaching Date"; there's no separate date column)
            if ($f_date_from) {
                $query->whereDate('coachings.created_at', '>=', $f_date_from);
            }
            if ($f_date_to) {
                $query->whereDate('coachings.created_at', '<=', $f_date_to);
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
                // "Team" = this person's own tickets PLUS their direct
                // reports' — previously only matched reports (via
                // users.supervisor_id), so a team-scoped Supervisor who
                // created a ticket themselves would never see it in their
                // own list. Also now follows second_supervisor_id, not just
                // the primary supervisor.
                $query->where(function ($q) use ($user_email, $user_employeeid) {
                    $q->where('coachings.created_by', $user_email)
                      ->orWhere('coachings.created_by', $user_employeeid)
                      ->orWhere('users.supervisor_id', $user_employeeid)
                      ->orWhere('users.second_supervisor_id', $user_employeeid);
                });
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

    /**
     * Dropdown options for the filter bar — distinct values actually present
     * on tickets this user can see (same PositionScope own/team/all logic as
     * displayTicket()). Mirrors MonitoringTicket::filterOptions().
     */
    public function filterOptions(Request $request)
    {
        try {
            $user_email      = auth()->user()->email;
            $user_position   = auth()->user()->position;
            $user_employeeid = auth()->user()->employeeid;

            $base = DB::table('coachings')
                ->leftJoin('users', 'coachings.created_by', '=', 'users.employeeid')
                ->leftJoin('users as coached', 'coachings.employee_id', '=', 'coached.employeeid');

            // 👤 LEVEL FILTER — same logic as displayTicket
            $scope = PositionScope::forPosition($user_position);

            if ($scope === 'own') {
                $base->where(function ($q) use ($user_email, $user_employeeid) {
                    $q->where('coachings.created_by', $user_email)
                      ->orWhere('coachings.created_by', $user_employeeid);
                });
            } elseif ($scope === 'team') {
                $base->where(function ($q) use ($user_email, $user_employeeid) {
                    $q->where('coachings.created_by', $user_email)
                      ->orWhere('coachings.created_by', $user_employeeid)
                      ->orWhere('users.supervisor_id', $user_employeeid)
                      ->orWhere('users.second_supervisor_id', $user_employeeid);
                });
            }

            $reference_types = (clone $base)
                ->whereNotNull('coachings.reference_type')
                ->where('coachings.reference_type', '!=', '')
                ->distinct()
                ->orderBy('coachings.reference_type')
                ->pluck('coachings.reference_type');

            // LDA (coached employee) — {value,label} pairs (employeeid +
            // name), since this is a people picker, not a free-text code.
            $ldas = (clone $base)
                ->whereNotNull('coachings.employee_id')
                ->where('coachings.employee_id', '!=', '')
                ->select(
                    'coachings.employee_id as value',
                    DB::raw("TRIM(COALESCE(coached.first_name, '') || ' ' || COALESCE(coached.last_name, '')) as label")
                )
                ->distinct()
                ->orderBy('label')
                ->get();

            return response()->json([
                'reference_types' => $reference_types,
                'ldas'            => $ldas,
                // Unscoped on purpose — same as the QA/Recon dashboards' own
                // Manager/Supervisor picker (FiltersByManagerScope): this
                // just lists who's pickable, it doesn't by itself widen what
                // displayTicket() returns. Picking an unrelated manager here
                // can only narrow the result (via an AND with the existing
                // own/team/all LEVEL FILTER) down to nothing, never expose
                // rows outside this viewer's normal access.
                'managers'        => $this->managerPickerOptions(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
