<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Dispute;
use App\Models\Engagement;
use App\Models\ProcessCompliance;
use App\Models\ScoreCorrection;
use App\Models\UserInputAudit;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ScoreCorrectionController extends Controller
{
    public function edit(Request $request, $auditId)
    {
        $audit = UserInputAudit::where('audit_id', $auditId)->firstOrFail();
        $ver = Verification::where('audit_id', $auditId)->first();
        $pc  = ProcessCompliance::where('audit_id', $auditId)->first();
        $eng = Engagement::where('audit_id', $auditId)->first();

        $disputeId = $request->input('dispute_id');

        $history = collect();
        if (Schema::hasTable('score_corrections')) {
            $history = ScoreCorrection::where('audit_id', $auditId)->orderByDesc('id')->get();
        }

        return view('scorecorrection', compact('audit', 'ver', 'pc', 'eng', 'disputeId', 'history'));
    }

    public function update(Request $request, $auditId)
    {
        $validated = $request->validate([
            'ver_outcome_1' => 'nullable|numeric',
            'ver_outcome_2' => 'nullable|numeric',
            'pc_outcome_1'  => 'nullable|numeric',
            'pc_outcome_2'  => 'nullable|numeric',
            'pc_outcome_3'  => 'nullable|numeric',
            'pc_outcome_4'  => 'nullable|numeric',
            'eng_outcome_1' => 'nullable|numeric',
            'eng_outcome_2' => 'nullable|numeric',
            'eng_outcome_3' => 'nullable|numeric',
            'eng_outcome_4' => 'nullable|numeric',
            'ver_comment_1' => 'nullable|string|max:2000',
            'ver_comment_2' => 'nullable|string|max:2000',
            'pc_comment_1'  => 'nullable|string|max:2000',
            'pc_comment_2'  => 'nullable|string|max:2000',
            'pc_comment_3'  => 'nullable|string|max:2000',
            'pc_comment_4'  => 'nullable|string|max:2000',
            'eng_comment_1' => 'nullable|string|max:2000',
            'eng_comment_2' => 'nullable|string|max:2000',
            'eng_comment_3' => 'nullable|string|max:2000',
            'eng_comment_4' => 'nullable|string|max:2000',
            'reason'        => 'required|string|max:2000',
            'dispute_id'    => 'nullable|integer',
        ]);

        UserInputAudit::where('audit_id', $auditId)->firstOrFail();

        $ver = Verification::where('audit_id', $auditId)->first();
        $pc  = ProcessCompliance::where('audit_id', $auditId)->first();
        $eng = Engagement::where('audit_id', $auditId)->first();

        // Don't allow a second pending request for the same evaluation
        if (ScoreCorrection::where('audit_id', $auditId)->where('status', 'pending')->exists()) {
            return redirect()->route('reports.disputes')
                ->with('success', 'A score correction for this evaluation is already awaiting admin approval.');
        }

        // Snapshot current values and the proposed values (NOT applied yet)
        $old = $this->snapshot($ver, $pc, $eng);
        $new = $this->proposedSnapshot($request, $ver, $pc, $eng);

        // Stage the correction for admin approval — scores are unchanged for now
        ScoreCorrection::create([
            'audit_id'   => $auditId,
            'dispute_id' => $validated['dispute_id'] ?? null,
            'changed_by' => auth()->user()->employeeid,
            'reason'     => $validated['reason'],
            'old_values' => $old,
            'new_values' => $new,
            'status'     => 'pending',
        ]);

        AuditTrail::record([
            'event'          => 'correction_requested',
            'description'    => 'Requested score correction for ' . $auditId . ' (awaiting admin approval)',
            'auditable_type' => 'UserInputAudit',
            'auditable_id'   => $auditId,
            'old_values'     => $old,
            'new_values'     => $new,
        ]);

        return redirect()->route('reports.disputes')
            ->with('success', 'Correction submitted for admin approval. Scores will change only once approved.');
    }

    /**
     * Build the proposed snapshot from the submitted form (without saving anything).
     */
    private function proposedSnapshot(Request $request, $ver, $pc, $eng): array
    {
        $vo1 = $request->input('ver_outcome_1') ?? optional($ver)->ver_outcome_1;
        $vo2 = $request->input('ver_outcome_2') ?? optional($ver)->ver_outcome_2;
        $verTotal = (float) $vo1 + (float) $vo2;

        $po = [];
        for ($i = 1; $i <= 4; $i++) {
            $po[$i] = $request->input("pc_outcome_$i") ?? optional($pc)->{"pc_outcome_$i"};
        }
        $pcTotal = (float) $po[1] + (float) $po[2] + (float) $po[3] + (float) $po[4];

        $eo = [];
        for ($i = 1; $i <= 4; $i++) {
            $eo[$i] = $request->input("eng_outcome_$i") ?? optional($eng)->{"eng_outcome_$i"};
        }
        $engTotal = (float) $eo[1] + (float) $eo[2] + (float) $eo[3] + (float) $eo[4];

        $overall = ($verTotal >= 200) ? ($pcTotal + $engTotal) : 0;

        return [
            'ver_outcome_1' => $vo1,
            'ver_outcome_2' => $vo2,
            'ver_comment_1' => $request->input('ver_comment_1'),
            'ver_comment_2' => $request->input('ver_comment_2'),
            'ver_total'     => $verTotal,
            'pc_outcome_1'  => $po[1],
            'pc_outcome_2'  => $po[2],
            'pc_outcome_3'  => $po[3],
            'pc_outcome_4'  => $po[4],
            'pc_comment_1'  => $request->input('pc_comment_1'),
            'pc_comment_2'  => $request->input('pc_comment_2'),
            'pc_comment_3'  => $request->input('pc_comment_3'),
            'pc_comment_4'  => $request->input('pc_comment_4'),
            'pc_total'      => $pcTotal,
            'eng_outcome_1' => $eo[1],
            'eng_outcome_2' => $eo[2],
            'eng_outcome_3' => $eo[3],
            'eng_outcome_4' => $eo[4],
            'eng_comment_1' => $request->input('eng_comment_1'),
            'eng_comment_2' => $request->input('eng_comment_2'),
            'eng_comment_3' => $request->input('eng_comment_3'),
            'eng_comment_4' => $request->input('eng_comment_4'),
            'eng_total'     => $engTotal,
            'overall'       => $overall,
        ];
    }

    private function snapshot($ver, $pc, $eng): array
    {
        $verTotal = (float) optional($ver)->total_score;
        $pcTotal  = (float) optional($pc)->total_score;
        $engTotal = (float) optional($eng)->total_score;
        $overall  = ($verTotal >= 200) ? ($pcTotal + $engTotal) : 0;

        return [
            'ver_outcome_1' => optional($ver)->ver_outcome_1,
            'ver_outcome_2' => optional($ver)->ver_outcome_2,
            'ver_comment_1' => optional($ver)->ver_comment_1,
            'ver_comment_2' => optional($ver)->ver_comment_2,
            'ver_total'     => $verTotal,
            'pc_outcome_1'  => optional($pc)->pc_outcome_1,
            'pc_outcome_2'  => optional($pc)->pc_outcome_2,
            'pc_outcome_3'  => optional($pc)->pc_outcome_3,
            'pc_outcome_4'  => optional($pc)->pc_outcome_4,
            'pc_comment_1'  => optional($pc)->pc_comment_1,
            'pc_comment_2'  => optional($pc)->pc_comment_2,
            'pc_comment_3'  => optional($pc)->pc_comment_3,
            'pc_comment_4'  => optional($pc)->pc_comment_4,
            'pc_total'      => $pcTotal,
            'eng_outcome_1' => optional($eng)->eng_outcome_1,
            'eng_outcome_2' => optional($eng)->eng_outcome_2,
            'eng_outcome_3' => optional($eng)->eng_outcome_3,
            'eng_outcome_4' => optional($eng)->eng_outcome_4,
            'eng_comment_1' => optional($eng)->eng_comment_1,
            'eng_comment_2' => optional($eng)->eng_comment_2,
            'eng_comment_3' => optional($eng)->eng_comment_3,
            'eng_comment_4' => optional($eng)->eng_comment_4,
            'eng_total'     => $engTotal,
            'overall'       => $overall,
        ];
    }
}
