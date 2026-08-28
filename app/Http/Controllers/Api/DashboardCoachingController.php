<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardCoachingController extends Controller
{
    public function index()
    {
        return view('dashboardcoaching');
    }

    /**
     * "My Team" here means coaches (created_by) who report directly to the
     * logged-in user -- the coach is the actor on a coaching session, not an
     * LDA, so this mirrors DashboardTriadController::scoped() (evaluator/coach
     * hierarchy) rather than the LDA-descendant-tree FiltersByManagerScope
     * trait used by the QA/Recon dashboards.
     */
    private function resolveScopeCoachIds(?string $scope): ?array
    {
        if ($scope !== 'my_team') {
            return null;
        }

        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return DB::table('users')
            ->where('supervisor_id', $user->employeeid)
            ->pluck('employeeid')
            ->all();
    }

    /**
     * Apply the whole-dashboard filters (date range, coach, coached
     * employee, coaching type, All/My Team scope) to a query, where $alias
     * is the alias of the coachings table in it.
     */
    private function applyFilters($query, Request $request, string $alias = 'c'): void
    {
        $from             = $request->input('date_from');
        $to               = $request->input('date_to');
        $coach            = $request->input('coach') ?: null;
        $coachedEmployee  = $request->input('coached_employee') ?: null;
        $type             = $request->input('type') ?: null;
        $coachIds         = $this->resolveScopeCoachIds($request->input('scope'));

        if ($from)             $query->whereDate("$alias.created_at", '>=', $from);
        if ($to)               $query->whereDate("$alias.created_at", '<=', $to);
        if ($coach)            $query->where("$alias.created_by", $coach);
        if ($coachedEmployee)  $query->where("$alias.employee_id", $coachedEmployee);
        if ($type)             $query->where("$alias.reference_type", $type);
        if ($coachIds !== null) $query->whereIn("$alias.created_by", $coachIds);
    }

    public function CardCount(Request $request)
    {
        $query = DB::table('coachings as c');
        $this->applyFilters($query, $request);

        $total = (clone $query)->count();

        $thisMonth = (clone $query)
            ->where('c.created_at', '>=', now()->startOfMonth())
            ->count();

        $last7Days = (clone $query)
            ->where('c.created_at', '>=', now()->subDays(7))
            ->count();

        $uniqueCoaches = (clone $query)
            ->distinct()
            ->count('c.created_by');

        $uniqueCoachedEmployees = (clone $query)
            ->whereNotNull('c.employee_id')
            ->where('c.employee_id', '!=', '')
            ->distinct()
            ->count('c.employee_id');

        $avgSessionsPerCoach = $uniqueCoaches > 0 ? round($total / $uniqueCoaches, 1) : 0;

        // "Most" cards exclude the Unassigned/blank buckets on purpose --
        // a missing employee_id or created_by isn't a real person to name.
        $topEmployee = (clone $query)
            ->join('users as emp', 'emp.employeeid', '=', 'c.employee_id')
            ->whereNotNull('c.employee_id')
            ->where('c.employee_id', '!=', '')
            ->selectRaw("TRIM(CONCAT(emp.first_name, ' ', COALESCE(emp.last_name, ''))) as name, COUNT(*) as total")
            ->groupBy('emp.first_name', 'emp.last_name')
            ->orderByDesc('total')
            ->first();

        $topCoach = (clone $query)
            ->join('users as coach', 'coach.employeeid', '=', 'c.created_by')
            ->selectRaw("TRIM(CONCAT(coach.first_name, ' ', COALESCE(coach.last_name, ''))) as name, COUNT(*) as total")
            ->groupBy('coach.first_name', 'coach.last_name')
            ->orderByDesc('total')
            ->first();

        return response()->json([
            'total'                      => $total,
            'this_month'                 => $thisMonth,
            'last_7_days'                => $last7Days,
            'unique_coaches'             => $uniqueCoaches,
            'unique_coached_employees'   => $uniqueCoachedEmployees,
            'avg_sessions_per_coach'     => $avgSessionsPerCoach,
            'most_coached_employee_name' => $topEmployee->name ?? 'N/A',
            'most_active_coach_name'     => $topCoach->name ?? 'N/A',
        ]);
    }

    /**
     * Coaching sessions trend. Mirrors DashboardControllerMain::trend()'s
     * range/bucket logic exactly (daily for <=31 days, monthly for <=~24
     * months, yearly beyond that; fixed last-12-months default with no date
     * filter) -- based on `created_at`, a real timestamp, so no bare-date
     * timezone concerns apply here.
     */
    public function Trend(Request $request)
    {
        $from = $request->input('date_from');
        $to   = $request->input('date_to');

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
            $rangeEnd   = \Carbon\Carbon::now();
            $rangeStart = \Carbon\Carbon::now()->startOfMonth()->subMonths(11);
            $unit       = 'month';
        }

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

        $datesQuery = DB::table('coachings as c')
            ->whereNotNull('c.created_at')
            ->whereDate('c.created_at', '>=', $rangeStart->toDateString())
            ->whereDate('c.created_at', '<=', $rangeEnd->toDateString());

        // Non-date filters still apply to the trend too.
        $coach           = $request->input('coach') ?: null;
        $coachedEmployee = $request->input('coached_employee') ?: null;
        $type            = $request->input('type') ?: null;
        $coachIds        = $this->resolveScopeCoachIds($request->input('scope'));

        if ($coach)            $datesQuery->where('c.created_by', $coach);
        if ($coachedEmployee)  $datesQuery->where('c.employee_id', $coachedEmployee);
        if ($type)             $datesQuery->where('c.reference_type', $type);
        if ($coachIds !== null) $datesQuery->whereIn('c.created_by', $coachIds);

        // One line per coaching type instead of a single combined total --
        // buckets follow the same Uncategorized fallback as PerTypeBreakdown().
        $rows = $datesQuery
            ->selectRaw("c.created_at, COALESCE(NULLIF(c.reference_type, ''), 'Uncategorized') as type")
            ->get();

        $seriesMap = [];
        foreach ($rows as $row) {
            try {
                $key = \Carbon\Carbon::parse($row->created_at)->format($keyFormat);
            } catch (\Throwable $e) {
                continue;
            }
            if (!isset($map[$key])) {
                continue;
            }
            if (!isset($seriesMap[$row->type])) {
                $seriesMap[$row->type] = $map; // zeroed skeleton with the same bucket keys
            }
            $seriesMap[$row->type][$key]++;
        }

        $series = [];
        foreach ($seriesMap as $type => $counts) {
            $series[] = [
                'name' => $type,
                'data' => array_values($counts),
            ];
        }
        usort($series, fn ($a, $b) => array_sum($b['data']) <=> array_sum($a['data']));

        return response()->json([
            'labels' => $labels,
            'series' => $series,
            'unit'   => $unit,
        ]);
    }

    /**
     * How many coaching sessions each coach (created_by) has logged.
     */
    public function PerCoachBreakdown(Request $request)
    {
        $query = DB::table('coachings as c')
            ->leftJoin('users as coach', 'coach.employeeid', '=', 'c.created_by');
        $this->applyFilters($query, $request);

        $data = $query
            ->selectRaw("
                c.created_by as employeeid,
                TRIM(CONCAT(COALESCE(coach.first_name, ''), ' ', COALESCE(coach.last_name, ''))) as name,
                COUNT(*) as total
            ")
            ->groupBy('c.created_by', 'coach.first_name', 'coach.last_name')
            ->orderByDesc('total')
            ->limit(15)
            ->get()
            ->map(function ($row) {
                $row->name = $row->name !== '' ? $row->name : ($row->employeeid ?: 'Unknown');
                return $row;
            });

        return response()->json($data);
    }

    /**
     * How many times each employee has been coached, via the employee_id
     * column (added 2026-08-27). Sessions with no employee_id set (legacy
     * rows created before that column existed) are bucketed as "Unassigned"
     * rather than silently dropped.
     */
    public function PerCoachedEmployeeBreakdown(Request $request)
    {
        $query = DB::table('coachings as c')
            ->leftJoin('users as emp', 'emp.employeeid', '=', 'c.employee_id');
        $this->applyFilters($query, $request);

        $data = $query
            ->selectRaw("
                c.employee_id as employeeid,
                TRIM(CONCAT(COALESCE(emp.first_name, ''), ' ', COALESCE(emp.last_name, ''))) as name,
                COUNT(*) as total
            ")
            ->groupBy('c.employee_id', 'emp.first_name', 'emp.last_name')
            ->orderByDesc('total')
            ->limit(15)
            ->get()
            ->map(function ($row) {
                if (empty($row->employeeid)) {
                    $row->name = 'Unassigned';
                } elseif ($row->name === '') {
                    $row->name = $row->employeeid;
                }
                return $row;
            });

        return response()->json($data);
    }

    /**
     * Breakdown by reference_type (which kind of ticket triggered the
     * coaching -- QA/Triad/Recon). This field is client-submitted and not
     * always populated, so blanks are bucketed as "Uncategorized" instead of
     * being dropped.
     */
    public function PerTypeBreakdown(Request $request)
    {
        $query = DB::table('coachings as c');
        $this->applyFilters($query, $request);

        $data = $query
            ->selectRaw("COALESCE(NULLIF(c.reference_type, ''), 'Uncategorized') as type, COUNT(*) as total")
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        return response()->json($data);
    }

    /**
     * How many coaching sessions fall on each day of the week, Monday
     * through Sunday, respecting the current filters.
     */
    public function DayOfWeek(Request $request)
    {
        $query = DB::table('coachings as c')->whereNotNull('c.created_at');
        $this->applyFilters($query, $request);

        $order  = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $counts = array_fill_keys($order, 0);

        foreach ($query->pluck('c.created_at') as $d) {
            try {
                $day = \Carbon\Carbon::parse($d)->format('l');
            } catch (\Throwable $e) {
                continue;
            }
            if (isset($counts[$day])) {
                $counts[$day]++;
            }
        }

        return response()->json([
            'labels' => array_keys($counts),
            'counts' => array_values($counts),
        ]);
    }

    /**
     * Recent coaching sessions table.
     */
    public function RecentSessions(Request $request)
    {
        $query = DB::table('coachings as c')
            ->leftJoin('users as coach', 'coach.employeeid', '=', 'c.created_by')
            ->leftJoin('users as emp', 'emp.employeeid', '=', 'c.employee_id')
            ->select(
                'c.reference_id',
                'c.reference_type',
                'c.created_at',
                DB::raw("TRIM(CONCAT(COALESCE(coach.first_name, ''), ' ', COALESCE(coach.last_name, ''))) as coach_name"),
                DB::raw("TRIM(CONCAT(COALESCE(emp.first_name, ''), ' ', COALESCE(emp.last_name, ''))) as coached_employee_name")
            );

        $this->applyFilters($query, $request);

        $results = $query
            ->orderByDesc('c.id')
            ->limit(20)
            ->get();

        return response()->json([
            'recent_sessions' => $results,
        ]);
    }

    /**
     * Coach / Coached Employee / Type options for the filter bar.
     */
    public function filterOptions()
    {
        $coaches = DB::table('coachings as c')
            ->join('users as u', 'u.employeeid', '=', 'c.created_by')
            ->whereNotNull('c.created_by')
            ->distinct()
            ->select('u.employeeid', DB::raw("TRIM(CONCAT(u.first_name, ' ', COALESCE(u.last_name, ''))) as name"))
            ->orderBy('name')
            ->get();

        $coachedEmployees = DB::table('coachings as c')
            ->join('users as u', 'u.employeeid', '=', 'c.employee_id')
            ->whereNotNull('c.employee_id')
            ->where('c.employee_id', '!=', '')
            ->distinct()
            ->select('u.employeeid', DB::raw("TRIM(CONCAT(u.first_name, ' ', COALESCE(u.last_name, ''))) as name"))
            ->orderBy('name')
            ->get();

        $types = DB::table('coachings')
            ->whereNotNull('reference_type')
            ->where('reference_type', '!=', '')
            ->distinct()
            ->orderBy('reference_type')
            ->pluck('reference_type');

        return response()->json([
            'coaches'           => $coaches,
            'coached_employees' => $coachedEmployees,
            'types'             => $types,
        ]);
    }
}
