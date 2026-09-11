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
use Illuminate\Support\Facades\Schema;


class DashboardControllerMain extends Controller
{
    use FiltersByManagerScope;

    public function dashbaordCard(Request $request)
    {
        $from = $request->input('date_from');
        $to   = $request->input('date_to');

        // Carrier Name + Client Code + Manager/Supervisor scope (All | My Team) filters
        $carrier            = $request->input('carrier_name') ?: null;
        $clientCode         = $request->input('client_code') ?: null;
        $ldaIds             = $this->resolveScopeLdaIds($request->input('scope'));
        $excludeCalibration = $request->boolean('exclude_calibration');

        $auditQuery = UserInputAudit::query();
        if ($from) $auditQuery->whereDate('audit_date_1', '>=', $from);
        if ($to)   $auditQuery->whereDate('audit_date_1', '<=', $to);
        if ($carrier)          $auditQuery->where('carrier_name', $carrier);
        if ($clientCode)       $auditQuery->where('client_code', $clientCode);
        if ($ldaIds !== null)  $auditQuery->whereIn('lda_id', $ldaIds);
        if ($excludeCalibration) $auditQuery->where('is_calibration', false);
        $this->excludeInactiveLdaAudits($auditQuery);
        $auditCount = $auditQuery->count();

        // Data uses the position label "LDA"; accept the long form too for safety.
        // When a supervisor/manager is selected, scope the LDA count to their subtree.
        // Total LDA is the EXPECTED POPULATION for the selected reporting period:
        // currently-active people always count, and a leaver counts if they
        // hadn't left yet as of the start of the range (Leaver Date strictly
        // after $from — Start Date is exclusive for the leaver date, so a
        // range that *starts* on someone's Leaver Date doesn't count them, but
        // a range that merely *ends* on or after it does). See
        // scopeExpectedLdaPopulation() for the full rule and why End Date
        // deliberately isn't part of it.
        $total_lda_query = $ldaIds !== null
            ? DB::table('users')->whereIn('employeeid', $ldaIds)
            : DB::table('users')->whereIn('position', self::LDA_POSITIONS);
        $this->scopeExpectedLdaPopulation($total_lda_query, $from);
        $total_lda = $total_lda_query->count();

        // Overall score per audit (mirrors the ticket view logic):
        //   - Verification is a gate: if its total_score < 200 the audit scores 0%
        //   - Otherwise the score is process_compliance + engagement (each 0-50, summing to 0-100)
        // "Above Average" = score >= 75, "Below Average" = score < 75.
        $passThreshold = 75;
        $verificationGate = 200;

        // NOTE: total_score is stored as a string column, so we select the raw
        // values and cast in PHP (avoids COALESCE varchar/int type errors on Postgres).
        // a.lda_id is selected too so Audited LDAs (below) can be derived from
        // this exact same filtered/scored row set — never from a separate query.
        $scoresQuery = DB::table('user_input_audits as a')
            ->leftJoin('verifications as v', 'v.audit_id', '=', 'a.audit_id')
            ->leftJoin('process_compliances as p', 'p.audit_id', '=', 'a.audit_id')
            ->leftJoin('engagements as e', 'e.audit_id', '=', 'a.audit_id')
            ->select(
                'a.lda_id',
                'v.total_score as ver',
                'p.total_score as proc',
                'e.total_score as eng'
            );
        if ($from) $scoresQuery->whereDate('a.audit_date_1', '>=', $from);
        if ($to)   $scoresQuery->whereDate('a.audit_date_1', '<=', $to);
        if ($carrier)          $scoresQuery->where('a.carrier_name', $carrier);
        if ($clientCode)       $scoresQuery->where('a.client_code', $clientCode);
        if ($ldaIds !== null)  $scoresQuery->whereIn('a.lda_id', $ldaIds);
        if ($excludeCalibration) $scoresQuery->where('a.is_calibration', false);
        $this->excludeInactiveLdaAudits($scoresQuery, 'a');
        $scores = $scoresQuery->get();

        $aboveAverage = 0;
        $belowAverage = 0;
        $overallSum   = 0;
        $overallCount = 0;
        $auditedLdaIds = [];

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

            if (! empty($s->lda_id)) {
                $auditedLdaIds[$s->lda_id] = true;
            }
        }

        $overallAverage = $overallCount > 0
            ? round($overallSum / $overallCount, 2)
            : 0;

        // Audited LDAs / LDAs With No Audits are coverage metrics, deliberately
        // distinct from Total LDA (the expected population above): Audited
        // LDAs is who actually has a filtered audit row; Total LDA minus that
        // is who was expected to be evaluated this period but has zero audits
        // on file. Every audited LDA is guaranteed to already be part of Total
        // LDA — a row only survives the filters/excludeInactiveLdaAudits()
        // check if its own audit_date is >= $from and before that LDA's Leaver
        // Date (if any), which together imply Leaver Date > $from too — so
        // this subtraction can never go negative in practice; max(0, ...) is
        // just a safety net.
        $auditedLdas      = count($auditedLdaIds);
        $ldasWithNoAudits = max(0, $total_lda - $auditedLdas);

        return response()->json([
            'total' => $auditCount,
            'total_lda' => $total_lda,
            'audited_ldas' => $auditedLdas,
            'ldas_with_no_audits' => $ldasWithNoAudits,
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
                'ticket.is_calibration',
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
        $carrier            = $request->input('carrier_name') ?: null;
        $clientCode         = $request->input('client_code') ?: null;
        $ldaIds             = $this->resolveScopeLdaIds($request->input('scope'));
        $excludeCalibration = $request->boolean('exclude_calibration');
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
            ->whereDate('audit_date_1', '<=', $rangeEnd->toDateString())
            ->select('audit_id', 'audit_date_1');
        if ($carrier)          $datesQuery->where('carrier_name', $carrier);
        if ($clientCode)       $datesQuery->where('client_code', $clientCode);
        if ($ldaIds !== null)  $datesQuery->whereIn('lda_id', $ldaIds);
        if ($excludeCalibration) $datesQuery->where('is_calibration', false);
        $this->excludeInactiveLdaAudits($datesQuery);
        $rows = $datesQuery->get();

        // "Acknowledged" gets its own trend line, bucketed by the SAME
        // evaluation date as the "Evaluations" line above (not by when the
        // acknowledgement itself happened), so the two lines compare 1:1 per
        // period. Acknowledgement is one row per audit in practice — only
        // the owning LDA can create one, and only once (see
        // AcknowledgementController / MyEvaluationController::acknowledge) —
        // so "does at least one ack row exist for this audit_id" is enough;
        // no risk of double-counting a single evaluation.
        $ackIds = [];
        if ($rows->isNotEmpty() && Schema::hasTable('acknowledgements')) {
            $ackIds = array_flip(
                DB::table('acknowledgements')
                    ->where('reference_type', 'audit')
                    ->whereIn('reference_id', $rows->pluck('audit_id'))
                    ->distinct()
                    ->pluck('reference_id')
                    ->all()
            );
        }

        $ackMap = array_fill_keys(array_keys($map), 0);

        foreach ($rows as $r) {
            try {
                $key = \Carbon\Carbon::parse($r->audit_date_1)->format($keyFormat);
            } catch (\Throwable $e) {
                continue;
            }
            if (isset($map[$key])) {
                $map[$key]++;
                if (isset($ackIds[$r->audit_id])) {
                    $ackMap[$key]++;
                }
            }
        }

        return response()->json([
            'labels' => $labels,
            'counts' => array_values($map),
            'acknowledged_counts' => array_values($ackMap),
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
        $from               = $request->input('date_from');
        $to                 = $request->input('date_to');
        $carrier            = $request->input('carrier_name') ?: null;
        $clientCode         = $request->input('client_code') ?: null;
        $ldaIds             = $this->resolveScopeLdaIds($request->input('scope'));
        $excludeCalibration = $request->boolean('exclude_calibration');

        if ($from)             $query->whereDate("$alias.audit_date_1", '>=', $from);
        if ($to)               $query->whereDate("$alias.audit_date_1", '<=', $to);
        if ($carrier)          $query->where("$alias.carrier_name", $carrier);
        if ($clientCode)       $query->where("$alias.client_code", $clientCode);
        if ($ldaIds !== null)  $query->whereIn("$alias.lda_id", $ldaIds);
        if ($excludeCalibration) $query->where("$alias.is_calibration", false);

        $this->excludeInactiveLdaAudits($query, $alias);
    }

    /**
     * Exclude an audit if its LDA is now inactive and this audit is dated on
     * or after their effectivity_date_leaver — Leaver Date is the date they
     * became a leaver (their first inactive day), so audits dated before it
     * still count but audits dated ON it (or later) don't. $alias is the
     * alias of user_input_audits in the query, or null for an unaliased
     * query (columns referenced bare).
     */
    private function excludeInactiveLdaAudits($query, ?string $alias = null): void
    {
        $ldaCol  = $alias ? "$alias.lda_id" : 'lda_id';
        $dateCol = $alias ? "$alias.audit_date_1" : 'audit_date_1';

        $query->whereNotExists(function ($sub) use ($ldaCol, $dateCol) {
            $sub->select(DB::raw(1))
                ->from('users')
                ->whereColumn('users.employeeid', $ldaCol)
                ->where('users.status', 'inactive')
                ->whereNotNull('users.effectivity_date_leaver')
                ->whereColumn($dateCol, '>=', 'users.effectivity_date_leaver');
        });
    }

    /**
     * Restrict a `users` query to the EXPECTED LDA POPULATION for a reporting
     * period starting at $startDate — i.e. people who were part of the team
     * at some point during the selected range, not just people with audit
     * rows in it (a person with zero audits this period is still part of the
     * expected population — see "LDAs With No Audits" in dashbaordCard()).
     *
     * Business rule:
     *   - No Leaver Date at all (still active, or never left): always counts.
     *   - Has a Leaver Date: counts unless they'd already left BEFORE the
     *     range even started, i.e. counts iff Leaver Date > $startDate.
     *     Start Date is exclusive for the Leaver Date (leaver_date ==
     *     $startDate is EXCLUDED — as of day 1 of the range they were
     *     already gone), which also means a Leaver Date landing anywhere
     *     from the day after $startDate onward counts them in — including
     *     Leaver Date == the range's End Date, or Leaver Date well past the
     *     End Date (a range that ends before they even left still counts
     *     them, since they were clearly part of the team for that entire
     *     window).
     *   - $startDate empty (no lower bound / "all time"): always counts,
     *     leavers included — there's no "before the beginning of time" for
     *     leaver_date to fail against.
     *
     * Deliberately does NOT compare against an end date: once someone is
     * confirmed present as of the start of the range, they're part of that
     * period's expected population no matter how the range ends — trimming
     * on the end date as well would wrongly drop someone from a report
     * covering a period entirely before their (later) departure.
     */
    private function scopeExpectedLdaPopulation($query, ?string $startDate): void
    {
        if (! $startDate) {
            return; // no lower bound — every LDA, active or historical leaver, counts
        }

        $query->where(function ($q) use ($startDate) {
            $q->whereNull('effectivity_date_leaver')
                ->orWhere('status', '!=', 'inactive')
                ->orWhereDate('effectivity_date_leaver', '>', $startDate);
        });
    }

}