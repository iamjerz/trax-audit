<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\CarrierCode;
use App\Models\ClientCode;
use Illuminate\Http\Request;

/**
 * Client & Carrier Codes admin page — the canonical lookup lists behind the
 * Client Code / Carrier Code dropdowns fed by ReconFieldController::index()
 * and DefaultFieldApi::index(). Admin-only, like Positions and Page Access.
 *
 * Both tables render via Grid.js, the same library/request contract as the
 * QA Monitoring list (see MonitoringTicket::displayTicket()): the page loads
 * empty grids that pull paged/searched data from clientCodesData()/
 * carrierCodesData() via limit/offset/search query params, since with ~10k
 * rows each these can't be rendered server-side in Blade.
 */
class ClientCarrierCodeController extends Controller
{
    public function index()
    {
        return view('client-carrier-codes');
    }

    public function clientCodesData(Request $request)
    {
        $limit  = (int) $request->input('limit', 10);
        $offset = (int) $request->input('offset', 0);
        $search = $request->input('search');

        $query = ClientCode::query();
        if ($search) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        $total = (clone $query)->count();
        $data = $query->orderBy('name')->offset($offset)->limit($limit)->get();

        return response()->json([
            'data'  => $data,
            'total' => $total,
        ]);
    }

    public function carrierCodesData(Request $request)
    {
        $limit  = (int) $request->input('limit', 10);
        $offset = (int) $request->input('offset', 0);
        $search = $request->input('search');

        $query = CarrierCode::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('client_name', 'ilike', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $data = $query->orderBy('name')->offset($offset)->limit($limit)->get();

        return response()->json([
            'data'  => $data,
            'total' => $total,
        ]);
    }

    /**
     * Remote-search endpoint behind the Client Name picker on the Add/Edit
     * Carrier Code modal. With ~10k client codes, that picker can't preload
     * the full list, so it queries this as the admin types instead of
     * holding every name in a static <option> list.
     */
    public function searchClientCodes(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $query = ClientCode::query();
        if ($term !== '') {
            $query->where('name', 'ilike', "%{$term}%");
        }

        $names = $query->orderBy('name')->limit(20)->pluck('name');

        return response()->json([
            'status' => true,
            'data'   => $names,
        ]);
    }

    /**
     * Bulk-add client codes from a pasted list (one name per line). Existing
     * names are skipped rather than rejected, so a re-paste of a larger list
     * that overlaps a previous one "just works". Uses a raw bulk insert()
     * instead of N calls to create() since this can run into the thousands
     * of rows at once; one summary audit-trail row is written for the whole
     * batch rather than one per row.
     */
    public function bulkStoreClientCodes(Request $request)
    {
        $validated = $request->validate([
            'names'   => 'required|array|min:1',
            'names.*' => 'string|max:255',
        ]);

        $names = collect($validated['names'])
            ->map(fn ($n) => trim($n))
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No valid names found.'], 422);
        }

        $existing = collect();
        foreach ($names->chunk(1000) as $chunk) {
            $existing = $existing->merge(ClientCode::whereIn('name', $chunk->all())->pluck('name'));
        }
        $existingSet = $existing->flip();

        $addedBy = auth()->user()->email;
        $rows = $names->reject(fn ($n) => $existingSet->has($n))
            ->map(fn ($n) => ['name' => $n, 'added_by' => $addedBy])
            ->values()
            ->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            ClientCode::insert($chunk);
        }

        $skipped = $names->count() - count($rows);

        AuditTrail::record([
            'event'          => 'client_codes_bulk_created',
            'description'    => 'Bulk-added ' . count($rows) . ' client code(s)' . ($skipped > 0 ? ", skipped {$skipped} duplicate(s)" : ''),
            'auditable_type' => 'client_codes',
            'auditable_id'   => null,
            'new_values'     => ['added' => count($rows), 'skipped' => $skipped],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added ' . count($rows) . ' client code(s)' . ($skipped > 0 ? ", skipped {$skipped} duplicate(s)." : '.'),
            'added'   => count($rows),
            'skipped' => $skipped,
        ]);
    }

    /**
     * Bulk-add carrier codes from a pasted list: one per line, each either
     * "name" or "name,client_name". Same skip-duplicates + single summary
     * audit-trail row approach as bulkStoreClientCodes().
     */
    public function bulkStoreCarrierCodes(Request $request)
    {
        $validated = $request->validate([
            'rows'              => 'required|array|min:1',
            'rows.*.name'       => 'required|string|max:255',
            'rows.*.client_name' => 'nullable|string|max:255',
        ]);

        // Keyed by name so a repeated name within the same paste just keeps
        // the last client_name seen for it, rather than erroring.
        $byName = [];
        foreach ($validated['rows'] as $row) {
            $name = trim($row['name']);
            if ($name === '') {
                continue;
            }
            $clientName = trim((string) ($row['client_name'] ?? ''));
            $byName[$name] = $clientName !== '' ? $clientName : null;
        }

        if (empty($byName)) {
            return response()->json(['success' => false, 'message' => 'No valid rows found.'], 422);
        }

        $names = array_keys($byName);
        $existing = collect();
        foreach (array_chunk($names, 1000) as $chunk) {
            $existing = $existing->merge(CarrierCode::whereIn('name', $chunk)->pluck('name'));
        }
        $existingSet = $existing->flip();

        $addedBy = auth()->user()->email;
        $rows = [];
        foreach ($byName as $name => $clientName) {
            if ($existingSet->has($name)) {
                continue;
            }
            $rows[] = ['name' => $name, 'client_name' => $clientName, 'added_by' => $addedBy];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            CarrierCode::insert($chunk);
        }

        $skipped = count($byName) - count($rows);

        AuditTrail::record([
            'event'          => 'carrier_codes_bulk_created',
            'description'    => 'Bulk-added ' . count($rows) . ' carrier code(s)' . ($skipped > 0 ? ", skipped {$skipped} duplicate(s)" : ''),
            'auditable_type' => 'carrier_codes',
            'auditable_id'   => null,
            'new_values'     => ['added' => count($rows), 'skipped' => $skipped],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added ' . count($rows) . ' carrier code(s)' . ($skipped > 0 ? ", skipped {$skipped} duplicate(s)." : '.'),
            'added'   => count($rows),
            'skipped' => $skipped,
        ]);
    }

    public function storeClientCode(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:client_codes,name',
        ]);

        $code = ClientCode::create([
            'name'     => $validated['name'],
            'added_by' => auth()->user()->email,
        ]);

        AuditTrail::record([
            'event'          => 'client_code_created',
            'description'    => "Created client code \"{$code->name}\"",
            'auditable_type' => 'client_codes',
            'auditable_id'   => $code->id,
            'new_values'     => ['name' => $code->name],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Client code \"{$code->name}\" added.",
            'code'    => $code,
        ], 201);
    }

    public function updateClientCode(Request $request, $id)
    {
        $code = ClientCode::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:client_codes,name,' . $code->id,
        ]);

        $old = $code->name;
        $code->update(['name' => $validated['name']]);

        AuditTrail::record([
            'event'          => 'client_code_updated',
            'description'    => "Client code \"{$old}\" renamed to \"{$code->name}\"",
            'auditable_type' => 'client_codes',
            'auditable_id'   => $code->id,
            'old_values'     => ['name' => $old],
            'new_values'     => ['name' => $code->name],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client code updated.',
        ]);
    }

    public function destroyClientCode($id)
    {
        $code = ClientCode::findOrFail($id);
        $name = $code->name;
        $code->delete();

        AuditTrail::record([
            'event'          => 'client_code_deleted',
            'description'    => "Deleted client code \"{$name}\"",
            'auditable_type' => 'client_codes',
            'auditable_id'   => $id,
            'old_values'     => ['name' => $name],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Client code \"{$name}\" deleted.",
        ]);
    }

    public function storeCarrierCode(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:carrier_codes,name',
            'client_name' => 'nullable|string|max:255',
        ]);

        $code = CarrierCode::create([
            'name'        => $validated['name'],
            'client_name' => $validated['client_name'] ?: null,
            'added_by'    => auth()->user()->email,
        ]);

        AuditTrail::record([
            'event'          => 'carrier_code_created',
            'description'    => "Created carrier code \"{$code->name}\"",
            'auditable_type' => 'carrier_codes',
            'auditable_id'   => $code->id,
            'new_values'     => ['name' => $code->name, 'client_name' => $code->client_name],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Carrier code \"{$code->name}\" added.",
            'code'    => $code,
        ], 201);
    }

    public function updateCarrierCode(Request $request, $id)
    {
        $code = CarrierCode::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:carrier_codes,name,' . $code->id,
            'client_name' => 'nullable|string|max:255',
        ]);

        $old = ['name' => $code->name, 'client_name' => $code->client_name];
        $code->update([
            'name'        => $validated['name'],
            'client_name' => $validated['client_name'] ?: null,
        ]);

        AuditTrail::record([
            'event'          => 'carrier_code_updated',
            'description'    => "Updated carrier code \"{$old['name']}\"",
            'auditable_type' => 'carrier_codes',
            'auditable_id'   => $code->id,
            'old_values'     => $old,
            'new_values'     => ['name' => $code->name, 'client_name' => $code->client_name],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Carrier code updated.',
        ]);
    }

    public function destroyCarrierCode($id)
    {
        $code = CarrierCode::findOrFail($id);
        $name = $code->name;
        $code->delete();

        AuditTrail::record([
            'event'          => 'carrier_code_deleted',
            'description'    => "Deleted carrier code \"{$name}\"",
            'auditable_type' => 'carrier_codes',
            'auditable_id'   => $id,
            'old_values'     => ['name' => $name],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Carrier code \"{$name}\" deleted.",
        ]);
    }
}
