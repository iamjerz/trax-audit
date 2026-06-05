<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardTriadController extends Controller
{
    /**
     * The 10 triad criteria stored inside the `triad` JSON column,
     * mapped to readable labels for the dashboard.
     */
    private array $criteria = [
        'body_language'    => 'Body Language (set the mood)',
        'clear_mind'       => 'Clear the Mind (expectations)',
        'permission_notes' => 'Permission to Take Notes',
        'choices_question' => 'Word Choices & Questions',
        'was_sme'          => 'SME Trust, Buy-in & Commitment',
        'recap_summary'    => 'Recap / Summary Provided',
        'sme_adhere'       => 'Adhered to 80/20 Rule',
        'clearly_defined'  => 'SMART Goal Clearly Defined',
        'rca'              => 'RCA Documented',
        'line_situation'   => 'Actions In Line with Situation',
    ];

    public function index()
    {
        return view('dashboardtriad');
    }

    /**
     * Base query honoring the all / team scope.
     * "team" = triads created by people who report to the logged-in user.
     */
    private function scoped(Request $request)
    {
        $scope = $request->input('scope', 'all');
        $user = auth()->user();

        $query = DB::table('triad_items as t');

        if ($scope === 'team' && $user) {
            $query->join('users as u', 't.created_by', '=', 'u.employeeid')
                  ->where('u.supervisor_id', $user->employeeid);
        }

        return $query;
    }

    private function decode($triad): array
    {
        if (is_array($triad)) {
            return $triad;
        }

        return json_decode($triad ?? '[]', true) ?: [];
    }

    /**
     * Summary cards: totals + overall pass rate.
     */
    public function CardCount(Request $request)
    {
        $base = $this->scoped($request);

        $rows = (clone $base)->get(['t.triad']);

        $pass = 0;
        $fail = 0;

        foreach ($rows as $row) {
            $triad = $this->decode($row->triad);
            foreach (array_keys($this->criteria) as $key) {
                $score = $triad[$key]['score'] ?? null;
                if ($score === 'Pass') {
                    $pass++;
                } elseif ($score === 'Fail') {
                    $fail++;
                }
            }
        }

        $scored = $pass + $fail;
        $passRate = $scored > 0 ? round($pass / $scored * 100, 1) : 0;

        $thisMonth = (clone $base)
            ->where('t.created_at', '>=', now()->startOfMonth())
            ->count();

        return response()->json([
            'total'      => $rows->count(),
            'pass'       => $pass,
            'fail'       => $fail,
            'pass_rate'  => $passRate,
            'this_month' => $thisMonth,
        ]);
    }

    /**
     * Pass / Fail tally for each of the 10 criteria (chart + table).
     */
    public function CriteriaBreakdown(Request $request)
    {
        $rows = $this->scoped($request)->get(['t.triad']);

        $result = [];

        foreach ($this->criteria as $key => $label) {
            $pass = 0;
            $fail = 0;

            foreach ($rows as $row) {
                $triad = $this->decode($row->triad);
                $score = $triad[$key]['score'] ?? null;
                if ($score === 'Pass') {
                    $pass++;
                } elseif ($score === 'Fail') {
                    $fail++;
                }
            }

            $total = $pass + $fail;

            $result[] = [
                'key'       => $key,
                'label'     => $label,
                'total'     => $total,
                'pass'      => $pass,
                'fail'      => $fail,
                'pass_rate' => $total > 0 ? round($pass / $total * 100, 1) : 0,
            ];
        }

        return response()->json($result);
    }

    /**
     * Per-evaluator triad count and average pass rate.
     */
    public function EvaluatorBreakdown(Request $request)
    {
        $rows = $this->scoped($request)
            ->leftJoin('users as ev', 't.created_by', '=', 'ev.employeeid')
            ->get([
                't.triad',
                't.created_by',
                DB::raw("TRIM(COALESCE(ev.first_name,'') || ' ' || COALESCE(ev.last_name,'')) as evaluator"),
            ]);

        $grouped = [];

        foreach ($rows as $row) {
            $id = $row->created_by ?? 'unknown';

            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'evaluator' => trim($row->evaluator) !== '' ? $row->evaluator : $id,
                    'count'     => 0,
                    'pass'      => 0,
                    'fail'      => 0,
                ];
            }

            $grouped[$id]['count']++;

            $triad = $this->decode($row->triad);
            foreach (array_keys($this->criteria) as $key) {
                $score = $triad[$key]['score'] ?? null;
                if ($score === 'Pass') {
                    $grouped[$id]['pass']++;
                } elseif ($score === 'Fail') {
                    $grouped[$id]['fail']++;
                }
            }
        }

        $result = array_map(function ($g) {
            $scored = $g['pass'] + $g['fail'];
            $g['pass_rate'] = $scored > 0 ? round($g['pass'] / $scored * 100, 1) : 0;
            return $g;
        }, array_values($grouped));

        // Most active evaluators first
        usort($result, fn($a, $b) => $b['count'] <=> $a['count']);

        return response()->json($result);
    }
}
