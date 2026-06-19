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


class DashboardControllerMain extends Controller
{
    public function dashbaordCard(Request $request)
    {
        $from = $request->input('date_from');
        $to   = $request->input('date_to');

        $auditQuery = UserInputAudit::query();
        if ($from) $auditQuery->whereDate('audit_date_1', '>=', $from);
        if ($to)   $auditQuery->whereDate('audit_date_1', '<=', $to);
        $auditCount = $auditQuery->count();

        // Data uses the position label "LDA"; accept the long form too for safety.
        $total_lda = DB::table('users')
            ->whereIn('position', ['LDA', 'Logistics Data Analyst'])
            ->count();

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
        $scores = $scoresQuery->get();

        $aboveAverage = 0;
        $belowAverage = 0;

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
        }

        return response()->json([
            'total' => $auditCount,
            'total_lda' => $total_lda,
            'above_average' => $aboveAverage,
            'below_average' => $belowAverage,
        ]);
    }


    public function dashboardRecentTableTicket()
    {
        $results = DB::table('user_input_audits as ticket')
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
            )
            ->orderByDesc('ticket.id')
            ->limit(20)
            ->get();

        return response()->json([
            'recent_ticket' => $results,
        ]);
    }


    public function impact_factor_count()
    {

        $data = DB::table('business_analytics')
            ->select('accountable_factors', DB::raw('COUNT(*) as total_rows'))
            ->whereNotNull('accountable_factors')
            ->groupBy('accountable_factors')
            ->get();


         return response()->json([
            'accountable_factor' => $data,
        ]);
    }

    public function cause_issue_count()
    {

        $data = DB::table('business_analytics')
            ->select('cause_issue', DB::raw('COUNT(*) as total_rows'))
            ->whereNotNull('cause_issue')
            ->groupBy('cause_issue')
            ->get();


         return response()->json([
            $data,
        ]);
    }

    public function root_cause_count()
    {

        $data = DB::table('business_analytics')
            ->select('root_cause', DB::raw('COUNT(*) as total_rows'))
            ->whereNotNull('root_cause')
            ->groupBy('root_cause')
            ->get();


         return response()->json([
            $data,
        ]);
    }

    /**
     * Evaluations per month for the last 12 months (trend line).
     */
    public function trend()
    {
        $dates = DB::table('user_input_audits')
            ->whereNotNull('audit_date_1')
            ->pluck('audit_date_1');

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

}