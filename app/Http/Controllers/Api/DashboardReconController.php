<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\FiltersByManagerScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DashboardReconController extends Controller
{
    use FiltersByManagerScope;

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
        $query = DB::table('recon_action_items as rai')
            ->leftJoin('users as u', 'rai.lda_email', '=', 'u.email')
            ->whereNotNull('rai.status');
            // 👉 OPTIONAL: exclude closed (same as charts)
            // ->whereRaw("LOWER(rai.status) != 'closed'");

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
        $query = DB::table('recon_action_items as rai')
            ->join('users as u', 'rai.lda_email', '=', 'u.email')
            ->whereNotNull('rai.status')
            ->whereRaw("LOWER(rai.status) != 'closed'");

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
        $today = \Carbon\Carbon::today();

        $query = DB::table('recon_action_items as rai')
            ->whereRaw("LOWER(COALESCE(rai.status, '')) != 'closed'")
            ->whereNotNull('rai.recon_call_date')
            // Exclude items dated today — count as of yesterday, not the current day.
            ->whereDate('rai.recon_call_date', '!=', $today->toDateString());

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
        $query = DB::table('recon_action_items as rai')
            ->join('users as u', 'rai.lda_email', '=', 'u.email')
            ->whereNotNull('rai.status')
            ->whereRaw("LOWER(rai.status) != 'closed'");

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
     * Carrier Code + Client Code + Manager/Supervisor options for the Recon
     * dashboard's filter bar — mirrors DashboardControllerMain::filterOptions()
     * for the QA dashboard, just sourced from recon_action_items instead of
     * user_input_audits. managerPickerOptions() itself is data-model-generic
     * (reads only from users), so it's reused as-is via the shared trait.
     */
    public function filterOptions()
    {
        $carrierCodes = DB::table('recon_action_items')
            ->whereNotNull('carrier_code')
            ->where('carrier_code', '!=', '')
            ->distinct()
            ->orderBy('carrier_code')
            ->pluck('carrier_code');

        $clientCodes = DB::table('recon_action_items')
            ->whereNotNull('client_code')
            ->where('client_code', '!=', '')
            ->distinct()
            ->orderBy('client_code')
            ->pluck('client_code');

        return response()->json([
            'carrier_codes' => $carrierCodes,
            'client_codes'  => $clientCodes,
            'managers'      => $this->managerPickerOptions(),
        ]);
    }

    /**
     * Apply the whole-dashboard filters (date range, carrier code, client
     * code, Manager/Supervisor scope) to a query, where $alias is the alias
     * of the recon_action_items table in it.
     *
     * Scope is resolved via resolveScopeLdaEmails() (not resolveScopeLdaIds())
     * because recon_action_items identifies its LDA by lda_email, not the
     * employeeid user_input_audits.lda_id uses — same underlying org-tree
     * walk, just keyed differently to match this table.
     *
     * This supersedes the old per-method `if ($scope === 'team') { ... }`
     * checks (direct reports only, via a users join) — those only handled
     * one level and didn't support second_supervisor_id or picking a specific
     * person, which resolveScopeLdaEmails() now does for every method here,
     * including CardCount, which previously had no scope handling at all.
     */
    private function applyReconFilters($query, Request $request, string $alias = 'rai'): void
    {
        $from        = $request->input('date_from');
        $to          = $request->input('date_to');
        $carrierCode = $request->input('carrier_code') ?: null;
        $clientCode  = $request->input('client_code') ?: null;
        $ldaEmails   = $this->resolveScopeLdaEmails($request->input('scope'));

        if ($from)              $query->whereDate("$alias.recon_call_date", '>=', $from);
        if ($to)                $query->whereDate("$alias.recon_call_date", '<=', $to);
        if ($carrierCode)       $query->where("$alias.carrier_code", $carrierCode);
        if ($clientCode)        $query->where("$alias.client_code", $clientCode);
        if ($ldaEmails !== null) $query->whereIn("$alias.lda_email", $ldaEmails);
    }
}
