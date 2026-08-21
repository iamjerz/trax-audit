<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserInputAudit;
use App\Models\Engagement;
use App\Models\BusinessAnalytic;
use App\Models\ProcessCompliance;
use App\Models\Verification;
use App\Http\Controllers\Api\Concerns\FiltersByManagerScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class DashboardControllerMain extends Controller
{
    use FiltersByManagerScope;

    public function dashbaordCard(Request $request)
    {
        $from = $request->input('date_from');
        $to   = $request->input('date_to');

        // Carrier Name + Client Code + Manager/Supervisor scope (All | My Team) filters
        $carrier    = $request->input('carrier_name') ?: null;
        $clientCode = $request->input('client_code') ?: null;
        $ldaIds     = $this->resolveScopeLdaIds($request->input('scope'));

        $auditQuery = UserInputAudit::query();
        if ($from) $auditQuery->whereDate('audit_date_1', '>=', $from);
        if ($to)   $auditQuery->whereDate('audit_date_1', '<=', $to);
        if ($carrier)          $auditQuery->where('carrier_name', $carrier);
        if ($clientCode)       $auditQuery->where('client_code', $clientCode);
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
        if ($clientCode)       $scoresQuery->where('a.client_code', $clientCode);
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
     * Evaluations trend line. With no date filter this is the original fixed
     * "last 12 calendar months" view. With a date filter active (date_from +
     * date_to), the window and bucket size follow the selected range instead:
     * daily buckets for a range of up to 31 days, monthly for up to ~24
     * months, yearly beyond that — chosen so every preset on the date filter
     * (Today, Last 7 days, Last 30 days, Last 6 months, Last 1 year) lands
     * comfortably inside one tier instead of on a boundary.
     */
    public function trend(Request $request)
    {
        $carrier    = $request->input('carrier_name') ?: null;
        $clientCode = $request->input('client_code') ?: null;
        $ldaIds     = $this->resolveScopeLdaIds($request->input('scope'));
        $from       = $request->input('date_from');
        $to         = $request->input('date_to');

        $rangeStart = null;
        $rangeEnd   = null;

        if ($from && $to) {
            try {
                $rangeStart = \Carbon\Carbon::parse($from)->startOfDay();
                $rangeEnd   = \Carbon\Carbon::parse($to)->startOfDay();
                if ($rangeEnd->lt($rangeStart)) {
                    [$rangeStart, $rangeEnd] = [$rangeEnd, $rangeStart];
                }
            } catch (\Throwable $e) {
                $rangeStart = null;
                $rangeEnd   = null;
            }
        }

        if ($rangeStart && $rangeEnd) {
            $spanDays = $rangeStart->diffInDays($rangeEnd) + 1;
            $unit = $spanDays <= 31 ? 'day' : ($spanDays <= 730 ? 'month' : 'year');
        } else {
            // No (usable) date filter: unchanged default — fixed last 12 calendar months.
            $rangeEnd   = \Carbon\Carbon::now();
            $rangeStart = \Carbon\Carbon::now()->startOfMonth()->subMonths(11);
            $unit       = 'month';
        }

        // Build the ordered bucket labels/keys for the chosen unit, spanning
        // the calendar unit containing $rangeStart through the one containing $rangeEnd.
        $labels = [];
        $map    = [];
        $cursor = $rangeStart->copy();

        if ($unit === 'day') {
            $keyFormat = 'Y-m-d';
            while ($cursor->lte($rangeEnd)) {
                $labels[] = $cursor->format('M j');
                $map[$cursor->format($keyFormat)] = 0;
                $cursor->addDay();
            }
        } elseif ($unit === 'month') {
            $keyFormat = 'Y-m';
            $cursor->startOfMonth();
            $endBucket = $rangeEnd->copy()->startOfMonth();
            while ($cursor->lte($endBucket)) {
                $labels[] = $cursor->format('M Y');
                $map[$cursor->format($keyFormat)] = 0;
                $cursor->addMonth();
            }
        } else { // year
            $keyFormat = 'Y';
            $cursor->startOfYear();
            $endBucket = $rangeEnd->copy()->startOfYear();
            while ($cursor->lte($endBucket)) {
                $labels[] = $cursor->format('Y');
                $map[$cursor->format($keyFormat)] = 0;
                $cursor->addYear();
            }
        }

        $datesQuery = DB::table('user_input_audits')
            ->whereNotNull('audit_date_1')
            ->whereDate('audit_date_1', '>=', $rangeStart->toDateString())
            ->whereDate('audit_date_1', '<=', $rangeEnd->toDateString());
        if ($carrier)          $datesQuery->where('carrier_name', $carrier);
        if ($clientCode)       $datesQuery->where('client_code', $clientCode);
        if ($ldaIds !== null)  $datesQuery->whereIn('lda_id', $ldaIds);
        $dates = $datesQuery->pluck('audit_date_1');

        foreach ($dates as $d) {
            try {
                $key = \Carbon\Carbon::parse($d)->format($keyFormat);
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
            'unit'   => $unit,
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

        $clientCodes = DB::table('user_input_audits')
            ->whereNotNull('client_code')
            ->where('client_code', '!=', '')
            ->distinct()
            ->orderBy('client_code')
            ->pluck('client_code');

        return response()->json([
            'carriers' => $carriers,
            'client_codes' => $clientCodes,
            'managers' => $this->managerPickerOptions(),
        ]);
    }

    // managerPickerOptions() / resolveScopeLdaIds() / allUsersMinimal() /
    // descendants() / isLda() now live in the FiltersByManagerScope trait
    // (used above) — shared with DashboardReconController so both dashboards'
    // Manager/Supervisor scope logic stays identical going forward.

    /**
     * Apply the dashboard filters (date range, carrier, client code, All/My
     * Team scope) to a query, where $alias is the alias of the
     * user_input_audits table in it.
     */
    private function applyAuditFilters($query, Request $request, string $alias): void
    {
        $from       = $request->input('date_from');
        $to         = $request->input('date_to');
        $carrier    = $request->input('carrier_name') ?: null;
        $clientCode = $request->input('client_code') ?: null;
        $ldaIds     = $this->resolveScopeLdaIds($request->input('scope'));

        if ($from)             $query->whereDate("$alias.audit_date_1", '>=', $from);
        if ($to)               $query->whereDate("$alias.audit_date_1", '<=', $to);
        if ($carrier)          $query->where("$alias.carrier_name", $carrier);
        if ($clientCode)       $query->where("$alias.client_code", $clientCode);
        if ($ldaIds !== null)  $query->whereIn("$alias.lda_id", $ldaIds);
    }

}