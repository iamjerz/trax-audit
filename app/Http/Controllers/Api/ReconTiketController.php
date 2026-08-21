<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\AuditTrail;
use App\Support\AccessRoles;
use App\Support\PositionScope;

class ReconTiketController extends Controller
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

        return view("inputrecon", compact('canDelete'));
    }

    public function destroy($id)
    {
        $record = DB::table('recon_action_items')->where('submission_id', $id)->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.',
            ], 404);
        }

        DB::transaction(function () use ($id) {
            if (Schema::hasTable('recon_item_comments') && Schema::hasColumn('recon_item_comments', 'submission_id')) {
                DB::table('recon_item_comments')->where('submission_id', $id)->delete();
            }

            DB::table('recon_action_items')->where('submission_id', $id)->delete();
        });

        AuditTrail::record([
            'event'          => 'deleted',
            'description'    => 'Deleted recon ticket ' . $id,
            'auditable_type' => 'recon_action_items',
            'auditable_id'   => $id,
            'old_values'     => (array) $record,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recon ticket deleted successfully.',
        ]);
    }

    /**
     * Open recon items that are overdue (>= 7 days since recon_call_date, not closed).
     */
    public function overdue(Request $request)
    {
        $today = \Carbon\Carbon::today();

        $f_client  = $request->input('client_code');
        $f_carrier = $request->input('carrier_code');
        $f_region  = $request->input('region');
        $f_status  = $request->input('status');
        $f_name    = $request->input('name');
        $minDays   = (int) $request->input('min_days', 7);
        if ($minDays < 7) {
            $minDays = 7; // overdue baseline
        }

        $query = DB::table('recon_action_items as r')
            ->leftJoin('users as u', 'u.email', '=', 'r.lda_email')
            ->whereRaw("LOWER(COALESCE(r.status, '')) != 'closed'")
            ->whereNotNull('r.recon_call_date');

        if ($f_client)  $query->where('r.client_code', $f_client);
        if ($f_carrier) $query->where('r.carrier_code', $f_carrier);
        if ($f_region)  $query->where('r.region', $f_region);
        if ($f_status)  $query->where('r.status', $f_status);
        if ($f_name) {
            $query->where(function ($q) use ($f_name) {
                $q->where('u.first_name', 'ilike', "%{$f_name}%")
                  ->orWhere('u.last_name', 'ilike', "%{$f_name}%")
                  ->orWhere(DB::raw("u.first_name || ' ' || u.last_name"), 'ilike', "%{$f_name}%");
            });
        }

        $rows = $query
            ->select('r.submission_id', 'r.client_code', 'r.carrier_code', 'r.region',
                'r.status', 'r.recon_call_date', 'r.assigned_to',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as lda_name"))
            ->orderBy('r.recon_call_date')
            ->get()
            ->map(function ($r) use ($today) {
                $r->days_open = \Carbon\Carbon::parse($r->recon_call_date)->diffInDays($today);
                return $r;
            })
            ->filter(fn ($r) => $r->days_open >= $minDays)
            ->values();

        // Filter dropdown options (from open, dated items)
        $base = DB::table('recon_action_items')
            ->whereRaw("LOWER(COALESCE(status, '')) != 'closed'")
            ->whereNotNull('recon_call_date');

        $clientOptions  = (clone $base)->whereNotNull('client_code')->where('client_code', '!=', '')->distinct()->orderBy('client_code')->pluck('client_code');
        $carrierOptions = (clone $base)->whereNotNull('carrier_code')->where('carrier_code', '!=', '')->distinct()->orderBy('carrier_code')->pluck('carrier_code');
        $regionOptions  = (clone $base)->whereNotNull('region')->where('region', '!=', '')->distinct()->orderBy('region')->pluck('region');
        $statusOptions  = (clone $base)->whereNotNull('status')->where('status', '!=', '')->distinct()->orderBy('status')->pluck('status');

        return view('reconoverdue', compact(
            'rows', 'clientOptions', 'carrierOptions', 'regionOptions', 'statusOptions',
            'f_client', 'f_carrier', 'f_region', 'f_status', 'f_name', 'minDays'
        ));
    }
    public function fullDetails($id)
    {

        $usersData = $this->getUsersData();

        $userEmail = auth()->user()->email;
        $userEmployeeid = auth()->user()->employeeid;
        $scope = PositionScope::forPosition(auth()->user()->position);

        // Same LEVEL FILTER as displayTicket(), applied to this one ticket —
        // so a ticket only opens directly if it would also show up in this
        // user's own list. "Secondary Owner" on the individualrecon view is
        // assigned_to, which is exactly what the 'own' branch already checks
        // (an LDA can see a ticket either as the primary LDA on it, via
        // lda_email, or as the assigned/secondary owner, via assigned_to).
        $query = DB::table('recon_action_items')->where('submission_id', $id);

        if ($scope === 'own') {
            $query->where(function ($q) use ($userEmail, $userEmployeeid) {
                $q->where('lda_email', $userEmail)
                  ->orWhere('assigned_to', $userEmployeeid);
            });
        } elseif ($scope === 'team') {
            $query->where(function ($q) use ($userEmployeeid) {
                $q->whereIn('lda_email', function ($sub) use ($userEmployeeid) {
                    $sub->select('email')->from('users')->where('supervisor_id', $userEmployeeid);
                })->orWhereIn('assigned_to', function ($sub) use ($userEmployeeid) {
                    $sub->select('employeeid')->from('users')->where('supervisor_id', $userEmployeeid);
                });
            });
        }

        $data = $query->first();

        if (! $data) {
            abort(403, 'You do not have access to this ticket.');
        }

        $assignTo = DB::table('users as u')
        ->join('recon_action_items as r', 'r.assigned_to', '=', 'u.employeeid')
        ->select('u.first_name as FirstName', 'u.last_name as LastName')
        ->where('r.submission_id', $id)
        ->first();
        return view("individualrecon", compact('data', 'usersData', 'assignTo'));
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

        // 🆕 New filter inputs
        $f_name         = $request->input('name');
        $f_client_code  = $request->input('client_code');
        $f_carrier_code = $request->input('carrier_code');
        $f_status       = $request->input('status');
        $f_date_from    = $request->input('date_from');
        $f_date_to      = $request->input('date_to');

        $query = DB::table('recon_action_items')
            ->leftJoin('users', 'recon_action_items.lda_email', '=', 'users.email')
            ->select(
                'recon_action_items.*',
                DB::raw("users.first_name || ' ' || users.last_name as full_name")
            );

        // 🔍 SEARCH (global)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('recon_action_items.submission_id', 'ilike', "%{$search}%")
                  ->orWhere('recon_action_items.client_code', 'ilike', "%{$search}%")
                  ->orWhere('recon_action_items.carrier_code', 'ilike', "%{$search}%")
                  ->orWhere('recon_action_items.region', 'ilike', "%{$search}%")
                  ->orWhere('recon_action_items.status', 'ilike', "%{$search}%")
                  ->orWhere('users.first_name', 'ilike', "%{$search}%")
                  ->orWhere('users.last_name', 'ilike', "%{$search}%")
                  ->orWhere(DB::raw("users.first_name || ' ' || users.last_name"), 'ilike', "%{$search}%");
            });
        }

        // 🆕 NAME FILTER (first, last, or full name)
        if ($f_name) {
            $query->where(function ($q) use ($f_name) {
                $q->where('users.first_name', 'ilike', "%{$f_name}%")
                  ->orWhere('users.last_name', 'ilike', "%{$f_name}%")
                  ->orWhere(DB::raw("users.first_name || ' ' || users.last_name"), 'ilike', "%{$f_name}%");
            });
        }

        // 🆕 CLIENT CODE
        if ($f_client_code) {
            $query->where('recon_action_items.client_code', $f_client_code);
        }

        // 🆕 CARRIER CODE
        if ($f_carrier_code) {
            $query->where('recon_action_items.carrier_code', $f_carrier_code);
        }

        // 🆕 STATUS
        if ($f_status) {
            $query->where('recon_action_items.status', $f_status);
        }

        // 🆕 DATE RANGE (on recon_call_date)
        if ($f_date_from) {
            $query->whereDate('recon_action_items.recon_call_date', '>=', $f_date_from);
        }
        if ($f_date_to) {
            $query->whereDate('recon_action_items.recon_call_date', '<=', $f_date_to);
        }

        // 👤 LEVEL FILTER — scope comes from the positions table (see
        // App\Support\PositionScope), not a hardcoded string match.
        $scope = PositionScope::forPosition($user_position);

        if ($scope === 'own') {
            $query->where(function ($q) use ($user_email, $user_employeeid) {
                $q->where('recon_action_items.lda_email', $user_email)
                  ->orWhere('recon_action_items.assigned_to', $user_employeeid);
            });
        } elseif ($scope === 'team') {
            $query->where(function ($q) use ($user_employeeid) {
                $q->whereIn('recon_action_items.lda_email', function ($sub) use ($user_employeeid) {
                    $sub->select('email')->from('users')->where('supervisor_id', $user_employeeid);
                })->orWhereIn('recon_action_items.assigned_to', function ($sub) use ($user_employeeid) {
                    $sub->select('employeeid')->from('users')->where('supervisor_id', $user_employeeid);
                });
            });
        }

        // ✅ COUNT
        $total = (clone $query)->count();

        // ✅ DATA
        $data = $query
            ->orderBy('recon_action_items.id', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Aging / SLA: days open since recon_call_date for items that aren't closed
        $today = \Carbon\Carbon::today();
        $data->transform(function ($row) use ($today) {
            $isClosed = strtolower($row->status ?? '') === 'closed';
            $start = !empty($row->recon_call_date) ? \Carbon\Carbon::parse($row->recon_call_date) : null;
            $age = $start ? $start->diffInDays($today) : null;

            $row->days_open  = $isClosed ? null : $age;
            $row->is_overdue = (!$isClosed && $age !== null && $age >= 7);
            return $row;
        });

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

    public function filterOptions(Request $request)
    {
        try {
            $user_email = auth()->user()->email;
            $user_position = auth()->user()->position;
            $user_employeeid = auth()->user()->employeeid;

            $base = DB::table('recon_action_items');

            // 👤 LEVEL FILTER — same logic as displayTicket
            $scope = PositionScope::forPosition($user_position);

            if ($scope === 'own') {
                $base->where(function ($q) use ($user_email, $user_employeeid) {
                    $q->where('lda_email', $user_email)
                    ->orWhere('assigned_to', $user_employeeid);
                });
            } elseif ($scope === 'team') {
                $base->where(function ($q) use ($user_employeeid) {
                    $q->whereIn('lda_email', function ($sub) use ($user_employeeid) {
                        $sub->select('email')->from('users')->where('supervisor_id', $user_employeeid);
                    })->orWhereIn('assigned_to', function ($sub) use ($user_employeeid) {
                        $sub->select('employeeid')->from('users')->where('supervisor_id', $user_employeeid);
                    });
                });
            }

            $client_codes = (clone $base)
                ->select('client_code')
                ->whereNotNull('client_code')
                ->where('client_code', '!=', '')
                ->distinct()
                ->orderBy('client_code')
                ->pluck('client_code');

            $carrier_codes = (clone $base)
                ->select('carrier_code')
                ->whereNotNull('carrier_code')
                ->where('carrier_code', '!=', '')
                ->distinct()
                ->orderBy('carrier_code')
                ->pluck('carrier_code');

            $statuses = (clone $base)
                ->select('status')
                ->whereNotNull('status')
                ->where('status', '!=', '')
                ->distinct()
                ->orderBy('status')
                ->pluck('status');

            return response()->json([
                'client_codes'  => $client_codes,
                'carrier_codes' => $carrier_codes,
                'statuses'      => $statuses,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function viewComment($id)
    {
        $comment = DB::table('recon_item_comments as ric')
            ->leftJoin('users as u', 'ric.employeeid', '=', 'u.employeeid')
            ->where('ric.submission_id', $id)
            ->select(
                'ric.*',
                'u.first_name as employee_first_name',
                'u.last_name as employee_last_name',
            )
            ->orderBy('ric.id', 'desc') // 👈 DESC here
            ->get();
        
        return view("sub.reconcomment", compact('comment'));
    }


    public function insertAssignTo(Request $request, $id)
    {
        $updated = DB::table('recon_action_items')
            ->where('submission_id', $id)
            ->update([
                'assigned_to' => $request->input('assigned_to')
            ]);

        if ($updated) {

            $old_user = DB::table('users')
                ->where('employeeid', $request->input('assigned_to'))
                ->first();

            AuditTrail::record([
                'event'          => 'assigned',
                'description'    => "Recon ticket {$id} assigned to {$old_user->first_name} {$old_user->last_name}",
                'auditable_type' => 'recon_action_items',
                'auditable_id'   => $id,
                'new_values'     => ['assigned_to' => $request->input('assigned_to')],
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Assignment updated successfully'
            ]);
        }

        return response()->json([
            'status' => 404,
            'message' => 'Record not found or no changes made'
        ], 404);
    }

    public function addCommentToTicket(Request $request)
    {
        $request->validate([
            'submission_id' => 'required',
            'comments' => 'required'
        ]);

        DB::table('recon_item_comments')->insert([
            'submission_id' => $request->submission_id,
            'comments'      => $request->comments,
            'employeeid'      => auth()->user()->employeeid,
            'created_at'    => now(),
            'updated_at'    => now()
        ]);

        AuditTrail::record([
            'event'          => 'commented',
            'description'    => 'Added comment to recon ticket ' . $request->submission_id,
            'auditable_type' => 'recon_action_items',
            'auditable_id'   => $request->submission_id,
            'new_values'     => ['comments' => $request->comments],
        ]);
    }



    /**
     * Assign/status-change history for one ticket — sourced from audit_trails
     * (already written by insertAssignTo()/ChangeStatus() below via
     * AuditTrail::record(), which auto-fills actor_name from the logged-in
     * user) instead of recon_item_comments, which is genuine user comments
     * only. This used to be logged into recon_item_comments too (via a
     * logComment() call alongside the AuditTrail::record() call), making a
     * ticket's real comments indistinguishable from its system-generated
     * assign/status history — that dual-write has been removed; anything
     * from before this change stays in recon_item_comments as-is (not
     * retroactively cleaned up), but is quite bounded and only affected
     * tickets that changed status/assignment before this file was updated.
     */
    public function historyList($id)
    {
        $history = DB::table('audit_trails')
            ->where('auditable_type', 'recon_action_items')
            ->where('auditable_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        return view('sub.reconhistory', compact('history'));
    }

    /**
     * Edit the Action Item Summary field on a ticket. Only logs to
     * audit_trails (surfaces on the History tab) when the value actually
     * changed, so re-saving the same text via the edit modal doesn't create
     * pointless history noise.
     */
    public function updateActionItemSummary(Request $request, $id)
    {
        $request->validate([
            'action_item_summary' => 'nullable|string',
        ]);

        $record = DB::table('recon_action_items')->where('submission_id', $id)->first();

        if (! $record) {
            return response()->json([
                'status'  => 404,
                'message' => 'Record not found.',
            ], 404);
        }

        $old = $record->action_item_summary;
        $new = $request->input('action_item_summary');

        DB::table('recon_action_items')
            ->where('submission_id', $id)
            ->update(['action_item_summary' => $new]);

        if ($old !== $new) {
            AuditTrail::record([
                'event'          => 'updated',
                'description'    => "Recon ticket {$id} Action Item Summary updated",
                'auditable_type' => 'recon_action_items',
                'auditable_id'   => $id,
                'old_values'     => ['action_item_summary' => $old],
                'new_values'     => ['action_item_summary' => $new],
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Action Item Summary updated successfully',
        ]);
    }

    /**
     * Edit the Action Item Details field on a ticket — same shape as
     * updateActionItemSummary() above, just the other field.
     */
    public function updateActionItemDetails(Request $request, $id)
    {
        $request->validate([
            'action_item_details' => 'nullable|string',
        ]);

        $record = DB::table('recon_action_items')->where('submission_id', $id)->first();

        if (! $record) {
            return response()->json([
                'status'  => 404,
                'message' => 'Record not found.',
            ], 404);
        }

        $old = $record->action_item_details;
        $new = $request->input('action_item_details');

        DB::table('recon_action_items')
            ->where('submission_id', $id)
            ->update(['action_item_details' => $new]);

        if ($old !== $new) {
            AuditTrail::record([
                'event'          => 'updated',
                'description'    => "Recon ticket {$id} Action Item Details updated",
                'auditable_type' => 'recon_action_items',
                'auditable_id'   => $id,
                'old_values'     => ['action_item_details' => $old],
                'new_values'     => ['action_item_details' => $new],
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Action Item Details updated successfully',
        ]);
    }

    public function ChangeStatus(Request $request, $id)
    {
        $old = DB::table('recon_action_items')
            ->where('submission_id', $id)
            ->value('status');


        $updated = DB::table('recon_action_items')
            ->where('submission_id', $id)
            ->update([
                'status' => $request->input('status')
            ]);

        if ($updated) {

            AuditTrail::record([
                'event'          => 'status_changed',
                'description'    => "Recon ticket {$id} status changed from {$old} to " . $request->input('status'),
                'auditable_type' => 'recon_action_items',
                'auditable_id'   => $id,
                'old_values'     => ['status' => $old],
                'new_values'     => ['status' => $request->input('status')],
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Assignment updated successfully'
            ]);



            // call the addCommentToTicket to log in the comment
        }

        return response()->json([
            'status' => 404,
            'message' => 'Record not found or no changes made'
        ], 404);
    }
}
