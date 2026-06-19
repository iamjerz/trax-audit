<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    /**
     * Evaluations the evaluated LDA has not acknowledged yet.
     */
    public function pendingAcknowledgements(Request $request)
    {
        $minDays = (int) $request->input('days', 0);
        $user = $request->input('user');

        $q = DB::table('user_input_audits as a')
            ->leftJoin('users as lda', 'lda.employeeid', '=', 'a.lda_id')
            ->leftJoin('users as s', 's.employeeid', '=', 'a.audit_sup_name')
            ->select(
                'a.audit_id', 'a.invoice_id', 'a.audit_date_1', 'a.lda_id',
                DB::raw("CONCAT(lda.first_name, ' ', lda.last_name) as lda_name"),
                DB::raw("CONCAT(s.first_name, ' ', s.last_name) as sup_name")
            );

        if ($user) $q->where('a.lda_id', $user);

        // Exclude evaluations already acknowledged by their own LDA
        if (Schema::hasTable('acknowledgements')) {
            $q->leftJoin('acknowledgements as ack', function ($j) {
                $j->on('ack.reference_id', '=', 'a.audit_id')
                  ->where('ack.reference_type', '=', 'audit')
                  ->whereColumn('ack.employeeid', '=', 'a.lda_id');
            })->whereNull('ack.id');
        }

        $today = \Carbon\Carbon::today();

        $rows = $q->orderByDesc('a.id')->get()
            ->map(function ($r) use ($today) {
                $r->days_waiting = !empty($r->audit_date_1)
                    ? \Carbon\Carbon::parse($r->audit_date_1)->diffInDays($today)
                    : null;
                return $r;
            })
            ->filter(fn ($r) => $minDays <= 0 || ($r->days_waiting !== null && $r->days_waiting >= $minDays))
            ->values();

        $users = DB::table('users')
            ->whereIn('position', ['LDA', 'Logistics Data Analyst'])
            ->orderBy('first_name')
            ->get(['employeeid', 'first_name', 'last_name']);

        return view('reports.pending-acknowledgements', [
            'rows' => $rows, 'days' => $minDays, 'user' => $user, 'users' => $users,
        ]);
    }
}
