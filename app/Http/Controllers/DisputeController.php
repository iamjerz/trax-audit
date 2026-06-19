<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Dispute;
use App\Models\ScoreCorrection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'open');
        $user = $request->input('user');

        $query = DB::table('disputes as d')
            ->leftJoin('users as u', 'u.employeeid', '=', 'd.employeeid')
            ->leftJoin('user_input_audits as a', 'a.audit_id', '=', 'd.audit_id')
            ->select(
                'd.id', 'd.audit_id', 'd.employeeid', 'd.reason', 'd.status',
                'd.resolution_note', 'd.resolved_by', 'd.resolved_at', 'd.created_at',
                'a.invoice_id',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as raiser_name")
            )
            ->orderByDesc('d.id');

        if ($status !== 'all') {
            $query->where('d.status', $status);
        }
        if ($user) {
            $query->where('d.employeeid', $user);
        }

        $disputes = $query->get();

        $users = DB::table('users')
            ->whereIn('position', ['LDA', 'Logistics Data Analyst'])
            ->orderBy('first_name')
            ->get(['employeeid', 'first_name', 'last_name']);

        // Evaluations with a correction awaiting admin approval
        $pendingCorrections = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('score_corrections')) {
            $pendingCorrections = array_flip(
                DB::table('score_corrections')->where('status', 'pending')->pluck('audit_id')->all()
            );
        }

        $isAdmin = DB::table('extension_access')
            ->where('employeeid', auth()->user()->employeeid)
            ->where('access_type', 'admin')
            ->exists();

        return view('reports.disputes', [
            'disputes' => $disputes,
            'status' => $status,
            'user' => $user,
            'users' => $users,
            'pendingCorrections' => $pendingCorrections,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function resolve(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:resolved,rejected',
            'resolution_note' => 'nullable|string|max:2000',
        ]);

        $dispute = Dispute::findOrFail($id);

        $isAdmin = DB::table('extension_access')
            ->where('employeeid', auth()->user()->employeeid)
            ->where('access_type', 'admin')
            ->exists();

        // Lock the dispute while a score correction is awaiting admin approval —
        // but admins may override and change the status.
        if (! $isAdmin
            && Schema::hasTable('score_corrections')
            && ScoreCorrection::where('audit_id', $dispute->audit_id)->where('status', 'pending')->exists()) {
            return redirect()->back()
                ->with('success', 'This dispute is locked — a score correction is awaiting admin approval.');
        }

        $dispute->update([
            'status'          => $validated['status'],
            'resolution_note' => $validated['resolution_note'] ?? null,
            'resolved_by'     => auth()->user()->employeeid,
            'resolved_at'     => now(),
        ]);

        AuditTrail::record([
            'event'          => 'dispute_resolved',
            'description'    => 'Dispute on evaluation ' . $dispute->audit_id . ' marked ' . $validated['status'],
            'auditable_type' => 'UserInputAudit',
            'auditable_id'   => $dispute->audit_id,
            'new_values'     => ['status' => $validated['status'], 'note' => $validated['resolution_note'] ?? null],
        ]);

        return redirect()->back()->with('success', 'Dispute updated.');
    }
}
