<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Dispute;
use App\Models\Engagement;
use App\Models\ProcessCompliance;
use App\Models\ScoreCorrection;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CorrectionApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $query = DB::table('score_corrections as c')
            ->leftJoin('users as u', 'u.employeeid', '=', 'c.changed_by')
            ->leftJoin('users as ap', 'ap.employeeid', '=', 'c.approved_by')
            ->leftJoin('user_input_audits as a', 'a.audit_id', '=', 'c.audit_id')
            ->select('c.*', 'a.invoice_id',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as requester"),
                DB::raw("CONCAT(ap.first_name, ' ', ap.last_name) as approver"))
            ->orderByDesc('c.id');

        if ($status !== 'all') {
            $query->where('c.status', $status);
        }

        $rows = $query->get()->map(function ($r) {
            $r->old_values = json_decode($r->old_values ?? 'null', true);
            $r->new_values = json_decode($r->new_values ?? 'null', true);
            return $r;
        });

        return view('reports.correction-approvals', ['rows' => $rows, 'status' => $status]);
    }

    public function approve($id)
    {
        $c = ScoreCorrection::findOrFail($id);

        if ($c->status !== 'pending') {
            return redirect()->back()->with('success', 'This correction has already been processed.');
        }

        $new = $c->new_values ?? [];
        $auditId = $c->audit_id;

        // Apply the proposed values to the evaluation (Auditable logs field diffs)
        $ver = Verification::where('audit_id', $auditId)->first();
        if ($ver) {
            $ver->ver_outcome_1 = $new['ver_outcome_1'] ?? $ver->ver_outcome_1;
            $ver->ver_outcome_2 = $new['ver_outcome_2'] ?? $ver->ver_outcome_2;
            $ver->ver_comment_1 = $new['ver_comment_1'] ?? $ver->ver_comment_1;
            $ver->ver_comment_2 = $new['ver_comment_2'] ?? $ver->ver_comment_2;
            $ver->total_score = (float) $ver->ver_outcome_1 + (float) $ver->ver_outcome_2;
            $ver->save();
        }

        $pc = ProcessCompliance::where('audit_id', $auditId)->first();
        if ($pc) {
            foreach ([1, 2, 3, 4] as $i) {
                $pc->{"pc_outcome_$i"} = $new["pc_outcome_$i"] ?? $pc->{"pc_outcome_$i"};
                $pc->{"pc_comment_$i"} = $new["pc_comment_$i"] ?? $pc->{"pc_comment_$i"};
            }
            $pc->total_score = (float) $pc->pc_outcome_1 + (float) $pc->pc_outcome_2
                + (float) $pc->pc_outcome_3 + (float) $pc->pc_outcome_4;
            $pc->save();
        }

        $eng = Engagement::where('audit_id', $auditId)->first();
        if ($eng) {
            foreach ([1, 2, 3, 4] as $i) {
                $eng->{"eng_outcome_$i"} = $new["eng_outcome_$i"] ?? $eng->{"eng_outcome_$i"};
                $eng->{"eng_comment_$i"} = $new["eng_comment_$i"] ?? $eng->{"eng_comment_$i"};
            }
            $eng->total_score = (float) $eng->eng_outcome_1 + (float) $eng->eng_outcome_2
                + (float) $eng->eng_outcome_3 + (float) $eng->eng_outcome_4;
            $eng->save();
        }

        $c->update([
            'status'      => 'approved',
            'approved_by' => auth()->user()->employeeid,
            'approved_at' => now(),
        ]);

        AuditTrail::record([
            'event'          => 'correction_approved',
            'description'    => 'Approved & applied score correction for ' . $auditId,
            'auditable_type' => 'UserInputAudit',
            'auditable_id'   => $auditId,
            'old_values'     => $c->old_values,
            'new_values'     => $c->new_values,
        ]);

        // Resolve the linked dispute now that the correction is applied
        if ($c->dispute_id) {
            $dispute = Dispute::find($c->dispute_id);
            if ($dispute && $dispute->status === 'open') {
                $dispute->update([
                    'status'          => 'resolved',
                    'resolution_note' => 'Scores corrected (approved). ' . $c->reason,
                    'resolved_by'     => auth()->user()->employeeid,
                    'resolved_at'     => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Correction approved and applied to the evaluation.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['decision_note' => 'nullable|string|max:2000']);

        $c = ScoreCorrection::findOrFail($id);

        if ($c->status !== 'pending') {
            return redirect()->back()->with('success', 'This correction has already been processed.');
        }

        $c->update([
            'status'        => 'rejected',
            'approved_by'   => auth()->user()->employeeid,
            'approved_at'   => now(),
            'decision_note' => $request->input('decision_note'),
        ]);

        AuditTrail::record([
            'event'          => 'correction_rejected',
            'description'    => 'Rejected score correction for ' . $c->audit_id . ' (no scores changed)',
            'auditable_type' => 'UserInputAudit',
            'auditable_id'   => $c->audit_id,
        ]);

        return redirect()->back()->with('success', 'Correction rejected. No scores were changed.');
    }
}
