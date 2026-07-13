<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DashboardReconController extends Controller
{
    //
    public function index(){

        return view("dashboardrecon");
    }

    public function CardCount(Request $request)
    {
        $query = DB::table('recon_action_items as rai');
        $this->applyReconFilters($query, $request);

        $result = $query
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'To Do' THEN 1 END) as todo,
                COUNT(CASE WHEN status = 'Closed' THEN 1 END) as closed,
                COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'In Progress' THEN 1 END) as in_progress
            ")
            ->first();

        return response()->json($result);
    }
    public function Top10Breakdown(Request $request)
    {
        $scope = $request->input('scope', 'all');
        $user = auth()->user();

        $query = DB::table('recon_action_items as rai')
            ->leftJoin('users as u', 'rai.lda_email', '=', 'u.email')
            ->whereNotNull('rai.status');
            // 👉 OPTIONAL: exclude closed (same as charts)
            // ->whereRaw("LOWER(rai.status) != 'closed'");

        // 🔥 Apply dropdown filter
        if ($scope === 'team') {
            $query->where('u.supervisor_id', $user->employeeid);
        }

        $this->applyReconFilters($query, $request);

        $data = $query
            ->selectRaw("
                rai.client_code,
                rai.carrier_code,

                COUNT(*) as total,

                COUNT(CASE WHEN rai.status = 'To Do' THEN 1 END) as todo,
                COUNT(CASE WHEN rai.status = 'Closed' THEN 1 END) as closed,
                COUNT(CASE WHEN rai.status = 'Pending' THEN 1 END) as pending,
                COUNT(CASE WHEN rai.status = 'In Progress' THEN 1 END) as in_progress
            ")
            ->groupBy('rai.client_code', 'rai.carrier_code')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    public function TopClientsChart(Request $request)
    {
        $scope = $request->input('scope', 'all');
        $user = auth()->user();

        $query = DB::table('recon_action_items as rai')
            ->join('users as u', 'rai.lda_email', '=', 'u.email')
            ->whereNotNull('rai.status')
            ->whereRaw("LOWER(rai.status) != 'closed'");

        // 🔥 Apply filter
        if ($scope === 'team') {
            $query->where('u.supervisor_id', $user->employeeid);
        }

        $this->applyReconFilters($query, $request);

        $data = $query
            ->selectRaw("rai.client_code, COUNT(*) as total")
            ->groupBy('rai.client_code')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        return response()->json($data);
    }

    public function Aging(Request $request)
    {
        $scope = $request->input('scope', 'all');
        $user = auth()->user();

        $today = \Carbon\Carbon::today();

        $query = DB::table('recon_action_items as rai')
            ->whereRaw("LOWER(COALESCE(rai.status, '')) != 'closed'")
            ->whereNotNull('rai.recon_call_date')
            // Exclude items dated today — count as of yesterday, not the current day.
            ->whereDate('rai.recon_call_date', '!=', $today->toDateString());

        if ($scope === 'team') {
            $query->join('users as u', 'rai.lda_email', '=', 'u.email')
                  ->where('u.supervisor_id', $user->employeeid);
        }

        $this->applyReconFilters($query, $request);

        $rows = $query->select('rai.recon_call_date')->get();
        $buckets = ['0-3' => 0, '4-7' => 0, '8-14' => 0, '15+' => 0];
        $overdue = 0;

        foreach ($rows as $r) {
            $age = \Carbon\Carbon::parse($r->recon_call_date)->diffInDays($today);

            if ($age <= 3)       $buckets['0-3']++;
            elseif ($age <= 7)   $buckets['4-7']++;
            elseif ($age <= 14)  $buckets['8-14']++;
            else                 $buckets['15+']++;

            if ($age >= 7) $overdue++;
        }

        return response()->json([
            'open_total' => $rows->count(),
            'overdue'    => $overdue,
            'buckets'    => $buckets,
        ]);
    }

    public function TopCarriers(Request $request)
    {
        $scope = $request->input('scope', 'all');
        $user = auth()->user();

        $query = DB::table('recon_action_items as rai')
            ->join('users as u', 'rai.lda_email', '=', 'u.email')
            ->whereNotNull('rai.status')
            ->whereRaw("LOWER(rai.status) != 'closed'");

        if ($scope === 'team') {
            $query->where('u.supervisor_id', $user->employeeid);
        }

        $this->applyReconFilters($query, $request);

        $data = $query
            ->selectRaw("rai.carrier_code, COUNT(*) as total")
            ->groupBy('rai.carrier_code')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        return response()->json($data);
    }

    /**
     * Apply the whole-dashboard Date Range filter (on recon_call_date).
     * $alias is the alias of the recon_action_items table in the query.
     */
    private function applyReconFilters($query, Request $request, string $alias = 'rai'): void
    {
        $from = $request->input('date_from');
        $to   = $request->input('date_to');

        if ($from) $query->whereDate("$alias.recon_call_date", '>=', $from);
        if ($to)   $query->whereDate("$alias.recon_call_date", '<=', $to);
    }
}
