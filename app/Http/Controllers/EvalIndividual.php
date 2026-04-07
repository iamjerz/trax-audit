<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UserListMonitoringPage;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class EvalIndividual extends Controller
{

    public function UserList(UserListMonitoringPage $service)
    {
        $data = $service->EvalIndiData();
        return view('individualEval', $data);
    }


    public function userTicket(Request $request)
    {
        $id = $request->id;          // from main.blade
        $date_from = $request->date_from;
        $date_to = $request->date_to;
        return view('body_page.individualEvalBody', compact('id', 'date_from', 'date_to'));

    }

    public function cause_issue_count(Request $request)
    {
        $id = $request->id;
        $date_from = $request->date_from;
        $date_to = $request->date_to;

        $query = DB::table('business_analytics as bus')
            ->join('user_input_audits as audit', 'audit.audit_id', '=', 'bus.audit_id');

        // User filter
        if (!empty($id)) {
            $query->where('audit.lda_id', $id);
        }

        // Date filters
        if (!empty($date_from) && !empty($date_to)) {
            $query->whereBetween('audit.audit_date_1', [$date_from, $date_to]);
        } elseif (!empty($date_from)) {
            $query->whereDate('audit.audit_date_1', '>=', $date_from);
        } elseif (!empty($date_to)) {
            $query->whereDate('audit.audit_date_1', '<=', $date_to);
        }
        
        $data = $query->select('bus.cause_issue', DB::raw('COUNT(*) as total_rows'))
            ->whereNotNull('bus.cause_issue')
            ->where('audit.lda_id',$id)
            ->groupBy('bus.cause_issue')
            ->get();

         return response()->json([
            $data,
        ]);
    }

    public function impact_factor_count(Request $request)
    {

        $id = $request->id;
        $date_from = $request->date_from;
        $date_to = $request->date_to;

        $query = DB::table('business_analytics as bus')
            ->join('user_input_audits as audit', 'audit.audit_id', '=', 'bus.audit_id');

        // User filter
        if (!empty($id)) {
            $query->where('audit.lda_id', $id);
        }

        // Date filters
        if (!empty($date_from) && !empty($date_to)) {
            $query->whereBetween('audit.audit_date_1', [$date_from, $date_to]);
        } elseif (!empty($date_from)) {
            $query->whereDate('audit.audit_date_1', '>=', $date_from);
        } elseif (!empty($date_to)) {
            $query->whereDate('audit.audit_date_1', '<=', $date_to);
        }
        
        $data = $query->select('bus.accountable_factors', DB::raw('COUNT(*) as total_rows'))
            ->whereNotNull('bus.accountable_factors')
            ->where('audit.lda_id',$id)
            ->groupBy('bus.accountable_factors')
            ->get();

         return response()->json([
            $data,
        ]);
    }



    public function recentTableAPI(Request $request)
    {
        $id = $request->id;
        $date_from = $request->date_from;
        $date_to = $request->date_to;

        $query = DB::table('user_input_audits as audit')
            ->join('users as emp', 'emp.employeeid', '=', 'audit.lda_id')
            ->join('users as creator', 'creator.employeeid', '=', 'audit.created_by') // ✅ FIX
            ->join('verifications as ver', 'audit.audit_id', '=', 'ver.audit_id')
            ->join('process_compliances as proc', 'audit.audit_id', '=', 'proc.audit_id')
            ->join('engagements as eng', 'audit.audit_id', '=', 'eng.audit_id') // ✅ FIX
            ->join('business_analytics as bus', 'audit.audit_id', '=', 'bus.audit_id');

        // User filter
        if (!empty($id)) {
            $query->where('audit.lda_id', $id);
        }

        // Date filters
        if (!empty($date_from) && !empty($date_to)) {
            $query->whereBetween('audit.audit_date_1', [$date_from, $date_to]);
        } elseif (!empty($date_from)) {
            $query->whereDate('audit.audit_date_1', '>=', $date_from);
        } elseif (!empty($date_to)) {
            $query->whereDate('audit.audit_date_1', '<=', $date_to);
        }

        $data = $query->select([
                'ver.total_score as ver_total',
                'proc.total_score as pro_total',
                'eng.total_score as eng_total',
                'audit.invoice_id as invoice_id',
                'audit.audit_id as audit_id',
                'audit.audit_date_1 as audit_date',
                DB::raw("CONCAT(creator.first_name, ' ', COALESCE(creator.last_name, '')) as created_by_name")
            ])
            ->orderByDesc('audit.id')
            ->get();

        return response()->json([
            'recent_ticket' => $data,
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);
    }



}
