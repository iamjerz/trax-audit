<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserInputAudit;
use Illuminate\Support\Facades\DB;


class CoachingTriadController extends Controller
{
    //

    public function coachingRef(Request $request)
    {
        $id = $request->id;

        $reference_list = DB::table('user_input_audits')
            ->select('audit_id', 'invoice_id')
            ->where('lda_id', $id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'list' => $reference_list,
        ]);
    }

    public function triadTicketInformation(Request $request)
    {
        $ticketid = $request->id;


        $data = UserInputAudit::with([
            'verification',
            'processCompliance',
            'engagement',
            'businessAnalytic'
        ])
        ->from('user_input_audits as audit')   // 👈 define alias properly

        // users join
        ->leftJoin('users as u', 'u.employeeid', '=', 'audit.created_by')
        ->leftJoin('users as a', 'a.employeeid', '=', 'audit.lda_id')
        ->leftJoin('users as s', 's.employeeid', '=', 'audit.audit_sup_name')
        ->leftJoin('users as o', 'o.employeeid', '=', 'audit.auditors_name')
        ->select(
            'audit.*',  // 👈 REQUIRED for Eloquent relations to work
            'a.email',
            'o.position',

            // ticket creator
            DB::raw("CONCAT(u.first_name, ' ', u.last_name) as lda_created_by_name"),

            // assigned LDA
            DB::raw("CONCAT(a.first_name, ' ', a.last_name) as lda_name"),

            // auditor sup name
            DB::raw("CONCAT(s.first_name, ' ', s.last_name) as lda_sup_name"),

            // auditors name
            DB::raw("CONCAT(o.first_name, ' ', o.last_name) as lda_auditors_name")
        )

        ->where('audit.audit_id', $ticketid)
        ->first();   // 👈 cleaner than get()->first()


        // return response()->json([
        //     'ticketid' => $ticketid,
        //     'data' => $data
        // ]);

        return view('sub.triadTicket', compact(
            'ticketid',
            'data'
        ));
    }
}
