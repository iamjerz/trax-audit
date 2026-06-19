<?php

namespace App\Http\Controllers;

use App\Models\Acknowledgement;
use App\Models\AuditTrail;
use Illuminate\Http\Request;

class AcknowledgementController extends Controller
{
    public function store(Request $request, $auditId)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $employeeid = auth()->user()->employeeid;

        // One acknowledgement per user per record.
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
                'description'    => 'Acknowledged evaluation ' . $auditId,
                'auditable_type' => 'UserInputAudit',
                'auditable_id'   => $auditId,
            ]);
        }

        return redirect()->back()->with('success', 'Evaluation acknowledged.');
    }
}
