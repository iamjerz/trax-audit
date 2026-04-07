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
    public function dashbaordCard()
    {
        $auditCount = UserInputAudit::count();

        $total_lda = DB::table('users')
            ->where('position', 'Logistics Data Analyst')
            ->count();

        return response()->json([
            'total' => $auditCount,
            'total_lda' => $total_lda
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

}