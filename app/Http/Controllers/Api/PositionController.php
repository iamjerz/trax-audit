<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Positions admin page — the canonical list of Positions (replacing the two
 * hardcoded dropdowns that used to live in edituser.blade.php and
 * users.blade.php, which had drifted out of sync with each other) plus each
 * one's data-visibility scope (see App\Support\PositionScope). Admin-only,
 * like Page Access.
 */
class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::orderBy('sort_order')->orderBy('name')->get();

        // Headcount per position — computed from the position string (not
        // the position_id FK) so it stays accurate even if some legacy rows
        // didn't backfill cleanly.
        $totals = DB::table('users')
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->select('position', DB::raw('count(*) as cnt'))
            ->groupBy('position')
            ->pluck('cnt', 'position');

        return view('positions', [
            'positions' => $positions,
            'totals'    => $totals,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255|unique:positions,name',
            'scope' => 'required|string|in:own,team,all',
        ]);

        $maxSort = (int) Position::max('sort_order');

        $position = Position::create([
            'name'       => $validated['name'],
            'scope'      => $validated['scope'],
            'sort_order' => $maxSort + 1,
        ]);

        AuditTrail::record([
            'event'          => 'position_created',
            'description'    => "Created position \"{$position->name}\" with level \"{$position->scope}\"",
            'auditable_type' => 'positions',
            'auditable_id'   => $position->id,
            'new_values'     => ['name' => $position->name, 'scope' => $position->scope],
        ]);

        return response()->json([
            'success'  => true,
            'message'  => "Position \"{$position->name}\" added.",
            'position' => $position,
        ], 201);
    }

    public function updateScope(Request $request, $id)
    {
        $validated = $request->validate([
            'scope' => 'required|string|in:own,team,all',
        ]);

        $position = Position::findOrFail($id);
        $old = $position->scope;

        if ($old === $validated['scope']) {
            return response()->json([
                'success' => true,
                'message' => 'No change.',
            ]);
        }

        $position->update(['scope' => $validated['scope']]);

        AuditTrail::record([
            'event'          => 'position_scope_updated',
            'description'    => "Position \"{$position->name}\" level changed from \"{$old}\" to \"{$validated['scope']}\"",
            'auditable_type' => 'positions',
            'auditable_id'   => $position->id,
            'old_values'     => ['scope' => $old],
            'new_values'     => ['scope' => $validated['scope']],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Updated \"{$position->name}\" to \"{$validated['scope']}\".",
        ]);
    }
}
