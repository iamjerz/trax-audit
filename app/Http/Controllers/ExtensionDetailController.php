<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\ExtensionDetail;
use Illuminate\Http\Request;

class ExtensionDetailController extends Controller
{
    public function index()
    {
        $details = ExtensionDetail::orderByDesc('id')->get();

        return view('extensiondetails', compact('details'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'version' => 'required|string|max:255',
            'item_id' => 'required|string|max:255',
            'status'  => 'required|string|in:active,inactive',
        ]);

        $detail = ExtensionDetail::create([
            'version'    => $validated['version'],
            'item_id'    => $validated['item_id'],
            'status'     => $validated['status'],
            'created_by' => auth()->user()->employeeid ?? 'system',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Extension detail added successfully.',
            'data'    => $detail,
        ]);
    }

    public function update(Request $request, $id)
    {
        $detail = ExtensionDetail::findOrFail($id);

        $validated = $request->validate([
            'version' => 'required|string|max:255',
            'item_id' => 'required|string|max:255',
            'status'  => 'required|string|in:active,inactive',
        ]);

        $detail->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Extension detail updated successfully.',
        ]);
    }

    /**
     * Change history for a single entry, drawn from the audit trail.
     */
    public function history($id)
    {
        $detail = ExtensionDetail::findOrFail($id);

        $logs = AuditTrail::where('auditable_type', 'ExtensionDetail')
            ->where('auditable_id', (string) $detail->id)
            ->orderByDesc('id')
            ->get(['event', 'actor_name', 'employeeid', 'old_values', 'new_values', 'created_at']);

        return response()->json([
            'item_id' => $detail->item_id,
            'data'    => $logs,
        ]);
    }
}
