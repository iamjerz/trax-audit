<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\CarrierCode;
use App\Models\ClientCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $rawNames = collect($validated['names'])
            ->map(fn ($n) => trim($n))
            ->filter()
            ->values();

        if ($rawNames->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No valid names found.'], 422);
        }

        // Track how many were repeated within THIS SAME paste separately
        // from how many already existed in the table — ->unique() here
        // used to silently collapse the former without counting them,
        // so "added" + "skipped" didn't add up to what was submitted.
        $names = $rawNames->unique()->values();
        $withinPasteDuplicates = $rawNames->count() - $names->count();

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

        $alreadyExisted = $names->count() - count($rows);
        $skipped = $withinPasteDuplicates + $alreadyExisted;
        $detail = $this->skipDetail($withinPasteDuplicates, $alreadyExisted);

        AuditTrail::record([
            'event'          => 'client_codes_bulk_created',
            'description'    => 'Bulk-added ' . count($rows) . ' client code(s)' . ($skipped > 0 ? ", skipped {$skipped} duplicate(s){$detail}" : ''),
            'auditable_type' => 'client_codes',
            'auditable_id'   => null,
            'new_values'     => ['added' => count($rows), 'skipped' => $skipped, 'within_paste_duplicates' => $withinPasteDuplicates, 'already_existed' => $alreadyExisted],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added ' . count($rows) . ' client code(s)' . ($skipped > 0 ? ", skipped {$skipped} duplicate(s){$detail}." : '.'),
            'added'   => count($rows),
            'skipped' => $skipped,
        ]);
    }

    /**
     * "(N repeated in your paste, M already existed)" — or blank if there's
     * nothing to break down. Shared by both bulk methods so the two
     * duplicate categories (within the paste vs. already in the table)
     * are never silently merged into one unexplained number again.
     */
    private function skipDetail(int $withinPaste, int $alreadyExisted): string
    {
        $parts = [];
        if ($withinPaste > 0) {
            $parts[] = "{$withinPaste} repeated in your paste";
        }
        if ($alreadyExisted > 0) {
            $parts[] = "{$alreadyExisted} already existed";
        }

        return $parts ? ' (' . implode(', ', $parts) . ')' : '';
    }

    /**
     * Bulk-add carrier codes from a pasted list: one per line, each either
     * "name" or "name,client_name". A carrier code can legitimately serve
     * more than one client, so "already exists" is judged on the
     * (name, client_name) PAIR, not on the name alone — otherwise a second,
     * distinct client for an already-known carrier code gets wrongly
     * skipped as a duplicate. Same skip-duplicates + single summary
     * audit-trail row approach as bulkStoreClientCodes().
     */
    public function bulkStoreCarrierCodes(Request $request)
    {
        $validated = $request->validate([
            'rows'              => 'required|array|min:1',
            'rows.*.name'       => 'required|string|max:255',
            'rows.*.client_name' => 'nullable|string|max:255',
        ]);

        // Keyed by "name\x1Fclient_name" so two rows only collapse when
        // BOTH the carrier code and the client code match — a repeated
        // carrier code paired with a different client is a distinct,
        // valid combination, not a duplicate. Collapsing still has to be
        // COUNTED (as $withinPasteDuplicates) or "added" + "skipped"
        // silently stop adding up to what was pasted.
        $totalSubmitted = 0;
        $byKey = [];
        foreach ($validated['rows'] as $row) {
            $name = trim($row['name']);
            if ($name === '') {
                continue;
            }
            $totalSubmitted++;
            $clientName = trim((string) ($row['client_name'] ?? ''));
            $clientName = $clientName !== '' ? $clientName : null;
            $key = $name . "\x1F" . ($clientName ?? '');
            $byKey[$key] = ['name' => $name, 'client_name' => $clientName];
        }

        if (empty($byKey)) {
            return response()->json(['success' => false, 'message' => 'No valid rows found.'], 422);
        }

        $withinPasteDuplicates = $totalSubmitted - count($byKey);

        // Pull every existing (name, client_name) pair for the distinct
        // names in this paste, then match on the pair in PHP — a plain
        // whereIn('name') would treat "same carrier, different client" as
        // already existing, which is exactly the bug this fixes.
        $names = collect($byKey)->pluck('name')->unique()->values()->all();
        $existingSet = [];
        foreach (array_chunk($names, 1000) as $chunk) {
            foreach (CarrierCode::whereIn('name', $chunk)->get(['name', 'client_name']) as $row) {
                $existingSet[$row->name . "\x1F" . ($row->client_name ?? '')] = true;
            }
        }

        $addedBy = auth()->user()->email;
        $rows = [];
        foreach ($byKey as $key => $pair) {
            if (isset($existingSet[$key])) {
                continue;
            }
            $rows[] = ['name' => $pair['name'], 'client_name' => $pair['client_name'], 'added_by' => $addedBy];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            CarrierCode::insert($chunk);
        }

        $alreadyExisted = count($byKey) - count($rows);
        $skipped = $withinPasteDuplicates + $alreadyExisted;
        $detail = $this->skipDetail($withinPasteDuplicates, $alreadyExisted);

        AuditTrail::record([
            'event'          => 'carrier_codes_bulk_created',
            'description'    => 'Bulk-added ' . count($rows) . ' carrier code(s)' . ($skipped > 0 ? ", skipped {$skipped} duplicate(s){$detail}" : ''),
            'auditable_type' => 'carrier_codes',
            'auditable_id'   => null,
            'new_values'     => ['added' => count($rows), 'skipped' => $skipped, 'within_paste_duplicates' => $withinPasteDuplicates, 'already_existed' => $alreadyExisted],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added ' . count($rows) . ' carrier code(s)' . ($skipped > 0 ? ", skipped {$skipped} duplicate(s){$detail}." : '.'),
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
        // A carrier code can legitimately serve more than one client, so
        // uniqueness is on the (name, client_name) PAIR, not name alone.
        $clientName = trim((string) $request->input('client_name', ''));
        $clientName = $clientName !== '' ? $clientName : null;

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('carrier_codes', 'name')->where(
                    fn ($query) => $query->where('client_name', $clientName)
                ),
            ],
            'client_name' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'This carrier code already exists for that client code.',
        ]);

        $code = CarrierCode::create([
            'name'        => $validated['name'],
            'client_name' => $clientName,
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

        // Same (name, client_name) pair uniqueness as storeCarrierCode().
        $clientName = trim((string) $request->input('client_name', ''));
        $clientName = $clientName !== '' ? $clientName : null;

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('carrier_codes', 'name')->where(
                    fn ($query) => $query->where('client_name', $clientName)
                )->ignore($code->id),
            ],
            'client_name' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'This carrier code already exists for that client code.',
        ]);

        $old = ['name' => $code->name, 'client_name' => $code->client_name];
        $code->update([
            'name'        => $validated['name'],
            'client_name' => $clientName,
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
