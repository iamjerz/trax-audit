<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\AuditTrail;

class ReconTiketController extends Controller
{
    //
    public function getUsersData()
    {
        return [
            'logisticsUsers' => User::select('employeeid', 'first_name', 'last_name')
                ->where('position', 'Logistics Data Analyst')
                ->orderBy('first_name')
                ->get(),

            'supervisors' => User::select('employeeid', 'first_name', 'last_name')
                ->where('position', '!=', 'Logistics Data Analyst')
                ->orderBy('first_name')
                ->get(),

            'allusers' => User::select('employeeid', 'first_name', 'last_name')
                ->orderBy('first_name')
                ->get(),
        ];
    }

    public function index(){

        return view("inputrecon");
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


        $data = DB::table('recon_action_items')
            ->where('submission_id', $id)
            ->first();

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

        // 👤 ROLE FILTER
        if ($user_position == "LDA") {
            $query->where(function ($q) use ($user_email, $user_employeeid) {
                $q->where('recon_action_items.lda_email', $user_email)
                  ->orWhere('recon_action_items.assigned_to', $user_employeeid);
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

            // 👤 ROLE FILTER — same logic as displayTicket
            if ($user_position == "LDA") {
                $base->where(function ($q) use ($user_email, $user_employeeid) {
                    $q->where('lda_email', $user_email)
                    ->orWhere('assigned_to', $user_employeeid);
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


            $this->logComment($id, "Assigned to {$old_user->first_name} {$old_user->last_name}");

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



    private function logComment($submission_id, $comment)
    {
        DB::table('recon_item_comments')->insert([
            'submission_id' => $submission_id,
            'comments'      => $comment,
            'employeeid'    => auth()->user()->employeeid,
            'created_at'    => now(),
            'updated_at'    => now()
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

        
            $this->logComment($id, "Status changed from $old to " . $request->input('status'));

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
