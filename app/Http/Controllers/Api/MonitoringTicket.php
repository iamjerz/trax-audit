<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Support\AccessRoles;
use App\Support\PositionScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MonitoringTicket extends Controller
{
    public function index()
    {
        $access = AccessRoles::expand(
            DB::table('extension_access')
                ->where('employeeid', auth()->user()->employeeid)
                ->pluck('access_type')
                ->all()
        );

        $canDelete = in_array('admin', $access, true);

        return view('ticketmonitoring', compact('canDelete'));
    }

    public function destroy($id)
    {
        $audit = DB::table('user_input_audits')->where('audit_id', $id)->first();

        if (! $audit) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.',
            ], 404);
        }

        DB::transaction(function () use ($id) {
            // Score/analytics + workflow rows tied to this audit via an audit_id column.
            $auditIdTables = [
                'verifications', 'process_compliances', 'engagements', 'business_analytics',
                'disputes', 'score_corrections',
            ];
            foreach ($auditIdTables as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'audit_id')) {
                    DB::table($table)->where('audit_id', $id)->delete();
                }
            }

            // Acknowledgements are keyed by reference_type + reference_id (not audit_id).
            if (Schema::hasTable('acknowledgements') && Schema::hasColumn('acknowledgements', 'reference_id')) {
                DB::table('acknowledgements')
                    ->where('reference_type', 'audit')
                    ->where('reference_id', $id)
                    ->delete();
            }

            DB::table('user_input_audits')->where('audit_id', $id)->delete();
        });

        AuditTrail::record([
            'event'          => 'deleted',
            'description'    => 'Deleted QA monitoring audit ' . $id,
            'auditable_type' => 'user_input_audits',
            'auditable_id'   => $id,
            'old_values'     => (array) $audit,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Audit deleted successfully.',
        ]);
    }

    /**
     * Admin-only: flip the "Is this Calibration?" flag on a QA monitoring
     * ticket. Route is already gated with access:admin, so no extra check
     * needed here. Only writes/logs when the value actually changes.
     */
    public function updateCalibration(Request $request, $id)
    {
        $validated = $request->validate([
            'is_calibration' => 'required|boolean',
        ]);

        $audit = DB::table('user_input_audits')->where('audit_id', $id)->first();

        if (! $audit) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Record not found.',
            ], 404);
        }

        $newValue = (bool) $validated['is_calibration'];
        $oldValue = (bool) $audit->is_calibration;

        if ($newValue !== $oldValue) {
            DB::table('user_input_audits')
                ->where('audit_id', $id)
                ->update(['is_calibration' => $newValue]);

            AuditTrail::record([
                'event'          => 'updated',
                'description'    => 'Changed "Is this Calibration?" from '
                    . ($oldValue ? 'Yes' : 'No') . ' to ' . ($newValue ? 'Yes' : 'No')
                    . ' on audit ' . $id,
                'auditable_type' => 'user_input_audits',
                'auditable_id'   => $id,
                'old_values'     => ['is_calibration' => $oldValue],
                'new_values'     => ['is_calibration' => $newValue],
            ]);
        }

        return response()->json([
            'status'         => 'success',
            'message'        => 'Calibration status updated.',
            'is_calibration' => $newValue,
        ]);
    }

    public function displayTicket(Request $request)
    {
        try {
            $user_position   = auth()->user()->position;
            $user_employeeid = auth()->user()->employeeid;

            $limit  = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $search = $request->input('search');

            // 🆕 Filter bar inputs — same pattern as ReconTiketController::displayTicket().
            $f_audit_id          = $request->input('audit_id');
            $f_invoice_id        = $request->input('invoice_id');
            $f_name              = $request->input('name');
            $f_client_code       = $request->input('client_code');
            $f_carrier_name      = $request->input('carrier_name');
            $f_exception_status  = $request->input('exception_status');
            $f_date_from         = $request->input('date_from');
            $f_date_to           = $request->input('date_to');

            $query = DB::table('user_input_audits as a')
                ->leftJoin('users as lda', 'a.lda_id', '=', 'lda.employeeid')
                ->leftJoin('users as creator', 'a.created_by', '=', 'creator.employeeid')
                ->select(
                    'a.*',
                    DB::raw("lda.first_name || ' ' || lda.last_name as lda_name"),
                    DB::raw("creator.first_name || ' ' || creator.last_name as created_by_name")
                );

            // 🔍 SEARCH (PostgreSQL-safe with ILIKE)
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('a.audit_id', 'ilike', "%{$search}%")
                      ->orWhere('a.invoice_id', 'ilike', "%{$search}%")
                      ->orWhere('a.carrier_name', 'ilike', "%{$search}%")
                      ->orWhere('lda.first_name', 'ilike', "%{$search}%")
                      ->orWhere('lda.last_name', 'ilike', "%{$search}%")
                      ->orWhere(DB::raw("lda.first_name || ' ' || lda.last_name"), 'ilike', "%{$search}%");
                });
            }

            // 🆕 AUDIT ID
            if ($f_audit_id) {
                $query->where('a.audit_id', 'ilike', "%{$f_audit_id}%");
            }

            // 🆕 INVOICE ID
            if ($f_invoice_id) {
                $query->where('a.invoice_id', 'ilike', "%{$f_invoice_id}%");
            }

            // 🆕 NAME FILTER (the audited LDA's first, last, or full name)
            if ($f_name) {
                $query->where(function ($q) use ($f_name) {
                    $q->where('lda.first_name', 'ilike', "%{$f_name}%")
                      ->orWhere('lda.last_name', 'ilike', "%{$f_name}%")
                      ->orWhere(DB::raw("lda.first_name || ' ' || lda.last_name"), 'ilike', "%{$f_name}%");
                });
            }

            // 🆕 CLIENT CODE
            if ($f_client_code) {
                $query->where('a.client_code', $f_client_code);
            }

            // 🆕 CARRIER
            if ($f_carrier_name) {
                $query->where('a.carrier_name', $f_carrier_name);
            }

            // 🆕 EXCEPTION STATUS
            if ($f_exception_status) {
                $query->where('a.exception_status', $f_exception_status);
            }

            // 🆕 DATE RANGE (on audit_date_1, the primary audit date)
            if ($f_date_from) {
                $query->whereDate('a.audit_date_1', '>=', $f_date_from);
            }
            if ($f_date_to) {
                $query->whereDate('a.audit_date_1', '<=', $f_date_to);
            }

            // 👤 LEVEL FILTER — scope comes from the positions table (see
            // App\Support\PositionScope), not a hardcoded string match.
            $scope = PositionScope::forPosition($user_position);

            if ($scope === 'own') {
                $query->where('a.lda_id', $user_employeeid);
            } elseif ($scope === 'team') {
                // "Team" = audits about this person (if they're also an
                // audited LDA) PLUS audits about their direct reports —
                // previously only matched reports (via lda.supervisor_id),
                // so a team-scoped Supervisor who is themselves audited
                // would never see their own audit in this list. Also now
                // follows second_supervisor_id, not just the primary
                // supervisor.
                $query->where(function ($q) use ($user_employeeid) {
                    $q->where('a.lda_id', $user_employeeid)
                      ->orWhere('lda.supervisor_id', $user_employeeid)
                      ->orWhere('lda.second_supervisor_id', $user_employeeid);
                });
            }

            // ✅ COUNT
            $total = (clone $query)->count();

            // ✅ DATA
            $data = $query
                ->orderBy('a.id', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            return response()->json([
                'data'  => $data,
                'total' => $total,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dropdown options for the filter bar — distinct values actually present
     * on tickets this user can see (same PositionScope own/team/all logic as
     * displayTicket(), so e.g. a team-scoped Supervisor only gets client
     * codes/carriers/statuses that appear on their own + their reports'
     * audits, not the whole table). Mirrors
     * ReconTiketController::filterOptions().
     */
    public function filterOptions(Request $request)
    {
        try {
            $user_position   = auth()->user()->position;
            $user_employeeid = auth()->user()->employeeid;

            $base = DB::table('user_input_audits as a')
                ->leftJoin('users as lda', 'a.lda_id', '=', 'lda.employeeid');

            // 👤 LEVEL FILTER — same logic as displayTicket
            $scope = PositionScope::forPosition($user_position);

            if ($scope === 'own') {
                $base->where('a.lda_id', $user_employeeid);
            } elseif ($scope === 'team') {
                $base->where(function ($q) use ($user_employeeid) {
                    $q->where('a.lda_id', $user_employeeid)
                      ->orWhere('lda.supervisor_id', $user_employeeid)
                      ->orWhere('lda.second_supervisor_id', $user_employeeid);
                });
            }

            $client_codes = (clone $base)
                ->whereNotNull('a.client_code')
                ->where('a.client_code', '!=', '')
                ->distinct()
                ->orderBy('a.client_code')
                ->pluck('a.client_code');

            $carrier_names = (clone $base)
                ->whereNotNull('a.carrier_name')
                ->where('a.carrier_name', '!=', '')
                ->distinct()
                ->orderBy('a.carrier_name')
                ->pluck('a.carrier_name');

            $exception_statuses = (clone $base)
                ->whereNotNull('a.exception_status')
                ->where('a.exception_status', '!=', '')
                ->distinct()
                ->orderBy('a.exception_status')
                ->pluck('a.exception_status');

            return response()->json([
                'client_codes'       => $client_codes,
                'carrier_names'      => $carrier_names,
                'exception_statuses' => $exception_statuses,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
