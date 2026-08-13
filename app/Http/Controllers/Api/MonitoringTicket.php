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

    public function displayTicket(Request $request)
    {
        try {
            $user_position   = auth()->user()->position;
            $user_employeeid = auth()->user()->employeeid;

            $limit  = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $search = $request->input('search');

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

            // 👤 LEVEL FILTER — scope comes from the positions table (see
            // App\Support\PositionScope), not a hardcoded string match.
            $scope = PositionScope::forPosition($user_position);

            if ($scope === 'own') {
                $query->where('a.lda_id', $user_employeeid);
            } elseif ($scope === 'team') {
                $query->where('lda.supervisor_id', $user_employeeid);
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
}
