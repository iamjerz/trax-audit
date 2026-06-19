<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\UserInputAudit;



class ViewTicket extends Controller
{
    //
    public function viewTicket($id)
    {
        $ticketid = $id;
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

        $triad_exists = DB::table('triad_items')
            ->where('reference', $ticketid)
            ->exists();
        $coaching_exists = DB::table('coachings')
            ->where('reference', $ticketid)
            ->exists();

        // Acknowledgement (sign-off) for this evaluation, if any.
        // Guarded so the ticket page still works if the migration hasn't been run yet.
        $acknowledgement = null;
        if (Schema::hasTable('acknowledgements')) {
            $acknowledgement = DB::table('acknowledgements as a')
                ->leftJoin('users as u', 'u.employeeid', '=', 'a.employeeid')
                ->where('a.reference_type', 'audit')
                ->where('a.reference_id', $ticketid)
                ->orderByDesc('a.id')
                ->select(
                    'a.acknowledged_at',
                    'a.employeeid',
                    DB::raw("CONCAT(u.first_name, ' ', u.last_name) as ack_name")
                )
                ->first();
        }

        return view('viewticket', compact('ticketid', 'data', 'triad_exists', 'coaching_exists', 'acknowledgement'));


    }
}
