<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReconActionItemController extends Controller
{
    // ✅ GET ALL
    public function index()
    {
        $data = DB::table('recon_action_items')->get();

        return response()->json($data);
    }

    // ✅ GET SINGLE
    public function show($id)
    {
        $data = DB::table('recon_action_items')
            ->where('id', $id)
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($data);
    }

    // ✅ CREATE
    public function store(Request $request)
    {
        // Basic validation
        $validated = $request->validate([
            'submission_id' => 'nullable|unique:recon_action_items,submission_id',
            'recon_call_date' => 'nullable|date',
            'lda_email' => 'nullable|email',
            'audit_sup_email' => 'nullable|email',
            'client_code' => 'nullable|string',
            'carrier_code' => 'nullable|string',
            'region' => 'nullable|string',
            'action_item_summary' => 'nullable|string',
            'action_item_details' => 'nullable|string',
            'jira_ticket' => 'nullable|string',
            'status' => 'nullable|string',
            'raw_data' => 'nullable|array',
        ]);

        // Insert
        $id = DB::table('recon_action_items')->insertGetId([
            'submission_id' => $validated['submission_id'] ?? null,
            'recon_call_date' => $validated['recon_call_date'] ?? null,
            'lda_email' => $validated['lda_email'] ?? null,
            'audit_sup_email' => $validated['audit_sup_email'] ?? null,
            'client_code' => $validated['client_code'] ?? null,
            'carrier_code' => $validated['carrier_code'] ?? null,
            'region' => $validated['region'] ?? null,
            'action_item_summary' => $validated['action_item_summary'] ?? null,
            'action_item_details' => $validated['action_item_details'] ?? null,
            'jira_ticket' => $validated['jira_ticket'] ?? null,
            'status' => $validated['status'] ?? null,
            'raw_data' => isset($validated['raw_data']) ? json_encode($validated['raw_data']) : null,
            'created_at' => now(),
        ]);

        $data = DB::table('recon_action_items')->where('id', $id)->first();

        AuditTrail::record([
            'event'          => 'created',
            'description'    => 'Created recon action item ' . ($data->submission_id ?? '#' . $id),
            'auditable_type' => 'recon_action_items',
            'auditable_id'   => $id,
            'new_values'     => (array) $data,
        ]);

        return response()->json($data, 201);
    }

    // ✅ UPDATE
    public function update(Request $request, $id)
    {
        $exists = DB::table('recon_action_items')->where('id', $id)->exists();

        if (!$exists) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $old = DB::table('recon_action_items')->where('id', $id)->first();

        $validated = $request->validate([
            'submission_id' => 'nullable|unique:recon_action_items,submission_id,' . $id,
            'recon_call_date' => 'nullable|date',
            'lda_email' => 'nullable|email',
            'audit_sup_email' => 'nullable|email',
            'client_code' => 'nullable|string',
            'carrier_code' => 'nullable|string',
            'region' => 'nullable|string',
            'action_item_summary' => 'nullable|string',
            'action_item_details' => 'nullable|string',
            'jira_ticket' => 'nullable|string',
            'status' => 'nullable|string',
            'raw_data' => 'nullable|array',
        ]);

        DB::table('recon_action_items')
            ->where('id', $id)
            ->update([
                'submission_id' => $validated['submission_id'] ?? null,
                'recon_call_date' => $validated['recon_call_date'] ?? null,
                'lda_email' => $validated['lda_email'] ?? null,
                'audit_sup_email' => $validated['audit_sup_email'] ?? null,
                'client_code' => $validated['client_code'] ?? null,
                'carrier_code' => $validated['carrier_code'] ?? null,
                'region' => $validated['region'] ?? null,
                'action_item_summary' => $validated['action_item_summary'] ?? null,
                'action_item_details' => $validated['action_item_details'] ?? null,
                'jira_ticket' => $validated['jira_ticket'] ?? null,
                'status' => $validated['status'] ?? null,
                'raw_data' => isset($validated['raw_data']) ? json_encode($validated['raw_data']) : null,
            ]);

        $data = DB::table('recon_action_items')->where('id', $id)->first();

        AuditTrail::record([
            'event'          => 'updated',
            'description'    => 'Updated recon action item ' . ($data->submission_id ?? '#' . $id),
            'auditable_type' => 'recon_action_items',
            'auditable_id'   => $id,
            'old_values'     => (array) $old,
            'new_values'     => (array) $data,
        ]);

        return response()->json($data);
    }

    // ✅ DELETE
    public function destroy($id)
    {
        $old = DB::table('recon_action_items')->where('id', $id)->first();

        $deleted = DB::table('recon_action_items')
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Not found'], 404);
        }

        AuditTrail::record([
            'event'          => 'deleted',
            'description'    => 'Deleted recon action item ' . ($old->submission_id ?? '#' . $id),
            'auditable_type' => 'recon_action_items',
            'auditable_id'   => $id,
            'old_values'     => (array) $old,
        ]);

        return response()->json(['message' => 'Deleted successfully']);
    }
}