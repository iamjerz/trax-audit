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
        $result = DB::table('recon_action_items')
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

        $data = $query
            ->selectRaw("rai.client_code, COUNT(*) as total")
            ->groupBy('rai.client_code')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        return response()->json($data);
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

        $data = $query
            ->selectRaw("rai.carrier_code, COUNT(*) as total")
            ->groupBy('rai.carrier_code')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        return response()->json($data);
    }
}
