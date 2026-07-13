<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserInputAudit;
use App\Models\Engagement;
use App\Models\BusinessAnalytic;
use App\Models\ProcessCompliance;
use App\Models\Verification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class DashboardControllerMain extends Controller
{
    private const LDA_POSITIONS = ['LDA', 'Logistics Data Analyst'];

    public function dashbaordCard(Request $request)
    {
        $from = $request->input('date_from');
        $to   = $request->input('date_to');

        // Carrier Name + Manager/Supervisor scope (All | My Team) filters
        $carrier = $request->input('carrier_name') ?: null;
        $ldaIds  = $this->resolveScopeLdaIds($request->input('scope'));

        $auditQuery = UserInputAudit::query();
        if ($from) $auditQuery->whereDate('audit_date_1', '>=', $from);
        if ($to)   $auditQuery->whereDate('audit_date_1', '<=', $to);
        if ($carrier)          $auditQuery->where('carrier_name', $carrier);
        if ($ldaIds !== null)  $auditQuery->whereIn('lda_id', $ldaIds);
        $auditCount = $auditQuery->count();

        // Data uses the position label "LDA"; accept the long form too for safety.
        // When a supervisor/manager is selected, scope the LDA count to their subtree.
        if ($ldaIds !== null) {
            $total_lda = count($ldaIds);
        } else {
            $total_lda = DB::table('users')
                ->whereIn('position', self::LDA_POSITIONS)
                ->count();
        }

        // Overall score per audit (mirrors the ticket view logic):
        //   - Verification is a gate: if its total_score < 200 the audit scores 0%
        //   - Otherwise the score is process_compliance + engagement (each 0-50, summing to 0-100)
        // "Above Average" = score >= 75, "Below Average" = score < 75.
        $passThreshold = 75;
        $verificationGate = 200;

        // NOTE: total_score is stored as a string column, so we select the raw
        // values and cast in PHP (avoids COALESCE varchar/int type errors on Postgres).
        $scoresQuery = DB::table('user_input_audits as a')
            ->leftJoin('verifications as v', 'v.audit_id', '=', 'a.audit_id')
            ->leftJoin('process_compliances as p', 'p.audit_id', '=', 'a.audit_id')
            ->leftJoin('engagements as e', 'e.audit_id', '=', 'a.audit_id')
            ->select(
                'v.total_score as ver',
                'p.total_score as proc',
                'e.total_score as eng'
            );
        if ($from) $scoresQuery->whereDate('a.audit_date_1', '>=', $from);
        if ($to)   $scoresQuery->whereDate('a.audit_date_1', '<=', $to);
        if ($carrier)          $scoresQuery->where('a.carrier_name', $carrier);
        if ($ldaIds !== null)  $scoresQuery->whereIn('a.lda_id', $ldaIds);
        $scores = $scoresQuery->get();

        $aboveAverage = 0;
        $belowAverage = 0;
        $overallSum   = 0;
        $overallCount = 0;

        foreach ($scores as $s) {
            $ver  = (float) ($s->ver ?? 0);
            $proc = (float) ($s->proc ?? 0);
            $eng  = (float) ($s->eng ?? 0);

            $overall = ($ver >= $verificationGate) ? ($proc + $eng) : 0;

            if ($overall >= $passThreshold) {
                $aboveAverage++;
            } else {
                $belowAverage++;
            }

            $overallSum += $overall;
            $overallCount++;
        }

        $overallAverage = $overallCount > 0
            ? round($overallSum / $overallCount, 2)
            : 0;

        return response()->json([
            'total' => $auditCount,
            'total_lda' => $total_lda,
            'above_average' => $aboveAverage,
            'below_average' => $belowAverage,
            'overall_average' => $overallAverage,
        ]);
    }


    public function dashboardRecentTableTicket(Request $request)
    {
        $query = DB::table('user_input_audits as ticket')
            ->join('users as emp', 'emp.employeeid', '=', 'ticket.lda_id')
            ->leftJoin('users as creator', 'creator.employeeid', '=', 'ticket.created_by')
            ->select(
                'ticket.lda_id',
                'ticket.audit_id',
                'ticket.audit_date_1',
                'ticket.audit_date_2',
                'emp.employeeid as employee_id',
                DB::raw("CONCAT(emp.first_name, ' ', COALESCE(emp.last_name, '')) as employee_name"),
                'ticket.invoice_id',
                DB::raw("CONCAT(creator.first_name, ' ', COALESCE(creator.last_name, '')) as created_by_name")
            );

        $this->applyAuditFilters($query, $request, 'ticket');

        $results = $query
            ->orderByDesc('ticket.id')
            ->limit(20)
            ->get();

        return response()->json([
            'recent_ticket' => $results,
        ]);
    }


    public function impact_factor_count(Request $request)
    {
        $data = DB::table('business_analytics as b')
            ->leftJoin('user_input_audits as a', 'a.audit_id', '=', 'b.audit_id')
            ->select('b.accountable_factors', DB::raw('COUNT(*) as total_rows'))
            ->whereNotNull('b.accountable_factors')
            ->groupBy('b.accountable_factors');

        $this->applyAuditFilters($data, $request, 'a');

        $data = $data->get();

         return response()->json([
            'accountable_factor' => $data,
        ]);
    }

    public function cause_issue_count(Request $request)
    {
        $data = DB::table('business_analytics as b')
            ->leftJoin('user_input_audits as a', 'a.audit_id', '=', 'b.audit_id')
            ->select('b.cause_issue', DB::raw('COUNT(*) as total_rows'))
            ->whereNotNull('b.cause_issue')
            ->groupBy('b.cause_issue');

        $this->applyAuditFilters($data, $request, 'a');

        $data = $data->get();

         return response()->json([
            $data,
        ]);
    }

    public function root_cause_count(Request $request)
    {
        $data = DB::table('business_analytics as b')
            ->leftJoin('user_input_audits as a', 'a.audit_id', '=', 'b.audit_id')
            ->select('b.root_cause', DB::raw('COUNT(*) as total_rows'))
            ->whereNotNull('b.root_cause')
            ->groupBy('b.root_cause');

        $this->applyAuditFilters($data, $request, 'a');

        $data = $data->get();

         return response()->json([
            $data,
        ]);
    }

    /**
     * Evaluations per month for the last 12 months (trend line).
     */
    public function trend(Request $request)
    {
        $carrier = $request->input('carrier_name') ?: null;
        $ldaIds  = $this->resolveScopeLdaIds($request->input('scope'));

        $datesQuery = DB::table('user_input_audits')
            ->whereNotNull('audit_date_1');
        if ($carrier)          $datesQuery->where('carrier_name', $carrier);
        if ($ldaIds !== null)  $datesQuery->whereIn('lda_id', $ldaIds);
        $dates = $datesQuery->pluck('audit_date_1');

        $labels = [];
        $map = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = \Carbon\Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $m->format('M Y');
            $map[$m->format('Y-m')] = 0;
        }

        foreach ($dates as $d) {
            try {
                $key = \Carbon\Carbon::parse($d)->format('Y-m');
            } catch (\Throwable $e) {
                continue;
            }
            if (isset($map[$key])) {
                $map[$key]++;
            }
        }

        return response()->json([
            'labels' => $labels,
            'counts' => array_values($map),
        ]);
    }

    /* -----------------------------------------------------------------
     | Filter helpers (Carrier Name + hierarchical Manager/Supervisor)
     | ----------------------------------------------------------------*/

    /**
     * Carrier Name options for the dashboard filter.
     */
    public function filterOptions()
    {
        $carriers = DB::table('user_input_audits')
            ->whereNotNull('carrier_name')
            ->where('carrier_name', '!=', '')
            ->distinct()
            ->orderBy('carrier_name')
            ->pluck('carrier_name');

        return response()->json([
            'carriers' => $carriers,
        ]);
    }

    /**
     * Resolve the "Manager / Supervisor" scope toggle into a list of LDA ids.
     *  - scope = "my_team": every LDA beneath the current user in the org tree
     *    (a manager gets their supervisors' LDAs too, all levels down).
     *  - anything else ("All"): null, meaning no scope filter.
     */
    private function resolveScopeLdaIds(?string $scope): ?array
    {
        if (trim((string) $scope) !== 'my_team') {
            return null;
        }

        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $users  = $this->allUsersMinimal();
        $ldaIds = [];

        foreach ($this->descendants($user->employeeid, $users) as $eid => $u) {
            if ($this->isLda($u->position)) {
                $ldaIds[] = $eid;
            }
        }

        // If the current user is themselves an LDA, include their own audits.
        if ($this->isLda($user->position ?? null)) {
            $ldaIds[] = $user->employeeid;
        }

        return array_values(array_unique($ldaIds));
    }

    private function allUsersMinimal()
    {
        return DB::table('users')
            ->select('employeeid', 'supervisor_id', 'position', 'first_name', 'last_name')
            ->get();
    }

    /**
     * All employees strictly beneath $root in the supervisor_id tree,
     * keyed by employeeid.
     */
    private function descendants(string $root, $users): array
    {
        $childrenBySup = [];
        foreach ($users as $u) {
            $sup = $u->supervisor_id;
            if ($sup === null || $sup === '') {
                continue;
            }
            $childrenBySup[$sup][] = $u;
        }

        $out   = [];
        $stack = [$root];
        while ($stack) {
            $cur = array_pop($stack);
            foreach ($childrenBySup[$cur] ?? [] as $child) {
                if (!isset($out[$child->employeeid])) {
                    $out[$child->employeeid] = $child;
                    $stack[] = $child->employeeid;
                }
            }
        }

        return $out;
    }

    private function isLda(?string $position): bool
    {
        return in_array($position, self::LDA_POSITIONS, true);
    }

    /**
     * Apply the dashboard filters (date range, carrier, All/My Team scope) to a
     * query, where $alias is the alias of the user_input_audits table in it.
     */
    private function applyAuditFilters($query, Request $request, string $alias): void
    {
        $from    = $request->input('date_from');
        $to      = $request->input('date_to');
        $carrier = $request->input('carrier_name') ?: null;
        $ldaIds  = $this->resolveScopeLdaIds($request->input('scope'));

        if ($from)             $query->whereDate("$alias.audit_date_1", '>=', $from);
        if ($to)               $query->whereDate("$alias.audit_date_1", '<=', $to);
        if ($carrier)          $query->where("$alias.carrier_name", $carrier);
        if ($ldaIds !== null)  $query->whereIn("$alias.lda_id", $ldaIds);
    }

}