<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    /**
     * Swap the dates if the user entered them backwards.
     */
    private function orderDates(?string $from, ?string $to): array
    {
        if ($from && $to && $from > $to) {
            return [$to, $from];
        }
        return [$from, $to];
    }

    /**
     * Per-auditor output: evaluations completed, avg score given, pass rate, last activity.
     */
    public function auditorProductivity(Request $request)
    {
        $user = $request->input('user');
        [$from, $to] = $this->orderDates($request->input('date_from'), $request->input('date_to'));

        $q = DB::table('user_input_audits as a')
            ->leftJoin('users as u', 'u.employeeid', '=', 'a.created_by')
            ->leftJoin('verifications as v', 'v.audit_id', '=', 'a.audit_id')
            ->leftJoin('process_compliances as p', 'p.audit_id', '=', 'a.audit_id')
            ->leftJoin('engagements as e', 'e.audit_id', '=', 'a.audit_id')
            ->select('a.created_by', 'a.audit_date_1',
                'v.total_score as ver', 'p.total_score as proc', 'e.total_score as eng',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as auditor_name"));

        if ($from) $q->whereDate('a.audit_date_1', '>=', $from);
        if ($to)   $q->whereDate('a.audit_date_1', '<=', $to);
        if ($user) $q->where('a.created_by', $user);

        $g = [];
        foreach ($q->get() as $r) {
            $id = $r->created_by ?: 'unknown';
            if (!isset($g[$id])) {
                $g[$id] = ['auditor' => trim($r->auditor_name) ?: $id, 'count' => 0, 'sum' => 0, 'above' => 0, 'last' => null];
            }
            $overall = ((float) $r->ver >= 200) ? ((float) $r->proc + (float) $r->eng) : 0;
            $g[$id]['count']++;
            $g[$id]['sum'] += $overall;
            if ($overall >= 75) $g[$id]['above']++;
            if ($r->audit_date_1 && (!$g[$id]['last'] || $r->audit_date_1 > $g[$id]['last'])) {
                $g[$id]['last'] = $r->audit_date_1;
            }
        }

        $rows = array_map(fn ($x) => [
            'auditor'   => $x['auditor'],
            'count'     => $x['count'],
            'avg'       => $x['count'] ? round($x['sum'] / $x['count'], 1) : 0,
            'pass_rate' => $x['count'] ? round($x['above'] / $x['count'] * 100, 1) : 0,
            'last'      => $x['last'],
        ], array_values($g));

        usort($rows, fn ($a, $b) => $b['count'] <=> $a['count']);

        $users = DB::table('users')->orderBy('first_name')->get(['employeeid', 'first_name', 'last_name']);

        return view('analytics.auditor-productivity', [
            'rows' => $rows,
            'from' => $from,
            'to'   => $to,
            'user' => $user,
            'users' => $users,
            'chartLabels' => array_column($rows, 'auditor'),
            'chartCounts' => array_column($rows, 'count'),
        ]);
    }

    /**
     * Client / Carrier health from reconciliation volume.
     */
    public function clientCarrierHealth()
    {
        $today = \Carbon\Carbon::today();
        $items = DB::table('recon_action_items')->get(['client_code', 'carrier_code', 'status', 'recon_call_date']);

        $byClient = [];
        $byCarrier = [];

        $tally = function (&$bucket, $key, $item) use ($today) {
            $key = $key ?: '(none)';
            if (!isset($bucket[$key])) {
                $bucket[$key] = ['key' => $key, 'total' => 0, 'open' => 0, 'overdue' => 0, 'closed' => 0];
            }
            $bucket[$key]['total']++;
            $isClosed = strtolower($item->status ?? '') === 'closed';
            if ($isClosed) {
                $bucket[$key]['closed']++;
            } else {
                $bucket[$key]['open']++;
                if (!empty($item->recon_call_date)
                    && \Carbon\Carbon::parse($item->recon_call_date)->diffInDays($today) >= 7) {
                    $bucket[$key]['overdue']++;
                }
            }
        };

        foreach ($items as $item) {
            $tally($byClient, $item->client_code, $item);
            $tally($byCarrier, $item->carrier_code, $item);
        }

        $sort = function ($bucket) {
            $arr = array_values($bucket);
            usort($arr, fn ($a, $b) => $b['overdue'] <=> $a['overdue'] ?: $b['total'] <=> $a['total']);
            return $arr;
        };

        return view('analytics.client-carrier-health', [
            'clients'  => $sort($byClient),
            'carriers' => $sort($byCarrier),
        ]);
    }

    /**
     * Root-cause Pareto + breakdowns + monthly trend (from business_analytics).
     */
    public function rootCause()
    {
        $rows = DB::table('business_analytics as b')
            ->leftJoin('user_input_audits as a', 'a.audit_id', '=', 'b.audit_id')
            ->select('b.cause_issue', 'b.root_cause', 'b.accountable_factors', 'a.audit_date_1', 'b.created_at')
            ->get();

        $count = function ($rows, $field) {
            $c = [];
            foreach ($rows as $r) {
                $v = trim((string) ($r->$field ?? ''));
                if ($v === '') continue;
                $c[$v] = ($c[$v] ?? 0) + 1;
            }
            arsort($c);
            return $c;
        };

        // Pareto for cause_issue
        $cause = $count($rows, 'cause_issue');
        $total = array_sum($cause);
        $pareto = [];
        $cum = 0;
        foreach ($cause as $label => $n) {
            $cum += $n;
            $pareto[] = [
                'label' => $label,
                'count' => $n,
                'cum_pct' => $total ? round($cum / $total * 100, 1) : 0,
            ];
        }

        // Monthly trend (last 12 months) of issues recorded
        $labels = [];
        $map = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = \Carbon\Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $m->format('M Y');
            $map[$m->format('Y-m')] = 0;
        }
        foreach ($rows as $r) {
            $d = $r->audit_date_1 ?: $r->created_at;
            if (!$d) continue;
            try { $k = \Carbon\Carbon::parse($d)->format('Y-m'); } catch (\Throwable $e) { continue; }
            if (isset($map[$k])) $map[$k]++;
        }

        return view('analytics.root-cause', [
            'pareto'        => $pareto,
            'rootCause'     => $count($rows, 'root_cause'),
            'accountable'   => $count($rows, 'accountable_factors'),
            'trendLabels'   => $labels,
            'trendCounts'   => array_values($map),
        ]);
    }

    /**
     * Audit coverage: how many LDAs were evaluated in the period.
     */
    public function auditCoverage(Request $request)
    {
        $user = $request->input('user');
        [$from, $to] = $this->orderDates($request->input('date_from'), $request->input('date_to'));

        // Full LDA list for the dropdown
        $allLdas = DB::table('users')
            ->whereIn('position', ['LDA', 'Logistics Data Analyst'])
            ->orderBy('first_name')
            ->get(['employeeid', 'first_name', 'last_name']);

        // The set the report is computed over (one LDA if selected)
        $ldas = $user ? $allLdas->where('employeeid', $user)->values() : $allLdas;

        $cq = DB::table('user_input_audits');
        if ($from) $cq->whereDate('audit_date_1', '>=', $from);
        if ($to)   $cq->whereDate('audit_date_1', '<=', $to);
        $counts = $cq->select('lda_id', DB::raw('COUNT(*) as c'))->groupBy('lda_id')->pluck('c', 'lda_id');

        $rows = $ldas->map(function ($l) use ($counts) {
            $l->eval_count = (int) $counts->get($l->employeeid, 0);
            return $l;
        })
        // Surface the gaps first: not-audited LDAs at the top, then by name
        ->sortBy([
            ['eval_count', 'asc'],
            ['first_name', 'asc'],
        ])->values();

        $totalLda = $rows->count();
        $evaluated = $rows->where('eval_count', '>', 0)->count();

        return view('analytics.audit-coverage', [
            'rows'       => $rows,
            'totalLda'   => $totalLda,
            'evaluated'  => $evaluated,
            'notAudited' => $totalLda - $evaluated,
            'coverage'   => $totalLda ? round($evaluated / $totalLda * 100, 1) : 0,
            'from'       => $from,
            'to'         => $to,
            'user'       => $user,
            'users'      => $allLdas,
        ]);
    }

    /**
     * Full chronological activity timeline for a single evaluation.
     */
    public function timeline($auditId)
    {
        $audit = DB::table('user_input_audits as a')
            ->leftJoin('users as l', 'l.employeeid', '=', 'a.lda_id')
            ->select('a.audit_id', 'a.invoice_id',
                DB::raw("CONCAT(l.first_name, ' ', l.last_name) as lda_name"))
            ->where('a.audit_id', $auditId)
            ->first();

        abort_unless($audit, 404);

        $events = collect();
        if (Schema::hasTable('audit_trails')) {
            $events = DB::table('audit_trails')
                ->where('auditable_id', $auditId)
                ->orderBy('id')
                ->get(['event', 'actor_name', 'employeeid', 'description', 'old_values', 'new_values', 'created_at']);
        }

        return view('analytics.timeline', compact('audit', 'events'));
    }
}
