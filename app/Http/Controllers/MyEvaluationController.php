<?php

namespace App\Http\Controllers;

use App\Models\Acknowledgement;
use App\Models\AuditTrail;
use App\Models\Dispute;
use App\Models\UserInputAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MyEvaluationController extends Controller
{
    /**
     * List the logged-in user's own QA evaluations (where they are the evaluated LDA).
     */
    public function index()
    {
        $employeeid = auth()->user()->employeeid;

        $rows = DB::table('user_input_audits as a')
            ->leftJoin('verifications as v', 'v.audit_id', '=', 'a.audit_id')
            ->leftJoin('process_compliances as p', 'p.audit_id', '=', 'a.audit_id')
            ->leftJoin('engagements as e', 'e.audit_id', '=', 'a.audit_id')
            ->where('a.lda_id', $employeeid)
            ->select('a.audit_id', 'a.invoice_id', 'a.audit_date_1',
                'v.total_score as ver', 'p.total_score as proc', 'e.total_score as eng')
            ->orderByDesc('a.id')
            ->get();

        // Which of these has this user already acknowledged?
        $ackSet = [];
        if (Schema::hasTable('acknowledgements')) {
            $ackSet = array_flip(
                DB::table('acknowledgements')
                    ->where('reference_type', 'audit')
                    ->where('employeeid', $employeeid)
                    ->pluck('reference_id')
                    ->all()
            );
        }

        // Latest dispute (with outcome) per evaluation for this user
        $disputeMap = [];
        if (Schema::hasTable('disputes')) {
            foreach (
                DB::table('disputes')
                    ->where('employeeid', $employeeid)
                    ->orderByDesc('id')
                    ->get(['audit_id', 'status', 'resolution_note', 'resolved_at']) as $d
            ) {
                if (!isset($disputeMap[$d->audit_id])) {
                    $disputeMap[$d->audit_id] = $d;
                }
            }
        }

        // Which evaluations had their scores corrected (approved), and which have a pending correction
        $correctedSet = [];
        $pendingCorrSet = [];
        if (Schema::hasTable('score_corrections')) {
            $correctedSet = array_flip(
                DB::table('score_corrections')->where('status', 'approved')->distinct()->pluck('audit_id')->all()
            );
            $pendingCorrSet = array_flip(
                DB::table('score_corrections')->where('status', 'pending')->distinct()->pluck('audit_id')->all()
            );
        }

        $rows->transform(function ($r) use ($ackSet, $disputeMap, $correctedSet, $pendingCorrSet) {
            $ver = (float) ($r->ver ?? 0);
            $proc = (float) ($r->proc ?? 0);
            $eng = (float) ($r->eng ?? 0);
            $r->overall = ($ver >= 200) ? ($proc + $eng) : 0;
            $r->acknowledged = isset($ackSet[$r->audit_id]);
            $r->dispute = $disputeMap[$r->audit_id] ?? null;
            $r->corrected = isset($correctedSet[$r->audit_id]);
            $r->correction_pending = isset($pendingCorrSet[$r->audit_id]);
            return $r;
        });

        return view('myevaluations', ['rows' => $rows]);
    }

    /**
     * Read-only detail of one of the user's own evaluations (ownership enforced).
     * Reuses the same full ticket view the supervisors see.
     */
    public function show($auditId)
    {
        $employeeid = auth()->user()->employeeid;
        $ticketid = $auditId;

        $data = UserInputAudit::with(['verification', 'processCompliance', 'engagement', 'businessAnalytic'])
            ->from('user_input_audits as audit')
            ->leftJoin('users as u', 'u.employeeid', '=', 'audit.created_by')
            ->leftJoin('users as a', 'a.employeeid', '=', 'audit.lda_id')
            ->leftJoin('users as s', 's.employeeid', '=', 'audit.audit_sup_name')
            ->leftJoin('users as o', 'o.employeeid', '=', 'audit.auditors_name')
            ->select(
                'audit.*',
                'a.email',
                'o.position',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as lda_created_by_name"),
                DB::raw("CONCAT(a.first_name, ' ', a.last_name) as lda_name"),
                DB::raw("CONCAT(s.first_name, ' ', s.last_name) as lda_sup_name"),
                DB::raw("CONCAT(o.first_name, ' ', o.last_name) as lda_auditors_name")
            )
            ->where('audit.audit_id', $ticketid)
            ->first();

        // Ownership: only the evaluated LDA can open their own evaluation here
        if (! $data || $data->lda_id !== $employeeid) {
            abort(403, 'You can only view your own evaluation.');
        }

        $triad_exists = DB::table('triad_items')->where('reference', $ticketid)->exists();
        $coaching_exists = DB::table('coachings')->where('reference', $ticketid)->exists();

        $acknowledgement = null;
        if (Schema::hasTable('acknowledgements')) {
            $acknowledgement = DB::table('acknowledgements as a')
                ->leftJoin('users as u', 'u.employeeid', '=', 'a.employeeid')
                ->where('a.reference_type', 'audit')
                ->where('a.reference_id', $ticketid)
                ->orderByDesc('a.id')
                ->select('a.acknowledged_at', 'a.employeeid',
                    DB::raw("CONCAT(u.first_name, ' ', u.last_name) as ack_name"))
                ->first();
        }

        return view('viewticket', compact('ticketid', 'data', 'triad_exists', 'coaching_exists', 'acknowledgement'));
    }

    /**
     * Acknowledge one of the user's own evaluations (ownership enforced).
     */
    public function acknowledge(Request $request, $auditId)
    {
        $employeeid = auth()->user()->employeeid;

        $audit = DB::table('user_input_audits')->where('audit_id', $auditId)->first();

        if (! $audit || $audit->lda_id !== $employeeid) {
            abort(403, 'You can only acknowledge your own evaluation.');
        }

        // Can't acknowledge while an open dispute is under review
        if (Schema::hasTable('disputes')
            && Dispute::where('audit_id', $auditId)
                ->where('employeeid', $employeeid)
                ->where('status', 'open')
                ->exists()) {
            return redirect()->back()
                ->with('success', 'This evaluation has an open dispute under review, so it can\'t be acknowledged yet.');
        }

        $ack = Acknowledgement::firstOrCreate(
            [
                'reference_type' => 'audit',
                'reference_id'   => $auditId,
                'employeeid'     => $employeeid,
            ],
            [
                'note'            => $request->input('note'),
                'acknowledged_at' => now(),
            ]
        );

        if ($ack->wasRecentlyCreated) {
            AuditTrail::record([
                'event'          => 'acknowledged',
                'description'    => 'Acknowledged own evaluation ' . $auditId,
                'auditable_type' => 'UserInputAudit',
                'auditable_id'   => $auditId,
            ]);
        }

        return redirect()->back()->with('success', 'Evaluation acknowledged. Thank you!');
    }

    /**
     * Raise a dispute / appeal on one of the user's own evaluations (ownership enforced).
     */
    public function dispute(Request $request, $auditId)
    {
        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $employeeid = auth()->user()->employeeid;

        $audit = DB::table('user_input_audits')->where('audit_id', $auditId)->first();
        if (! $audit || $audit->lda_id !== $employeeid) {
            abort(403, 'You can only dispute your own evaluation.');
        }

        // Once acknowledged, the evaluation is accepted and can no longer be disputed
        if (Schema::hasTable('acknowledgements')
            && Acknowledgement::where('reference_type', 'audit')
                ->where('reference_id', $auditId)
                ->where('employeeid', $employeeid)
                ->exists()) {
            return redirect()->back()
                ->with('success', 'You have already acknowledged this evaluation, so it can no longer be disputed.');
        }

        // Prevent multiple open disputes on the same evaluation
        $open = Dispute::where('audit_id', $auditId)
            ->where('employeeid', $employeeid)
            ->where('status', 'open')
            ->exists();

        if ($open) {
            return redirect()->back()->with('success', 'You already have an open dispute for this evaluation.');
        }

        Dispute::create([
            'audit_id'   => $auditId,
            'employeeid' => $employeeid,
            'reason'     => $request->input('reason'),
            'status'     => 'open',
        ]);

        AuditTrail::record([
            'event'          => 'dispute_raised',
            'description'    => 'Raised a dispute on evaluation ' . $auditId,
            'auditable_type' => 'UserInputAudit',
            'auditable_id'   => $auditId,
            'new_values'     => ['reason' => $request->input('reason')],
        ]);

        return redirect()->back()->with('success', 'Dispute submitted. Your supervisor will review it.');
    }
}
