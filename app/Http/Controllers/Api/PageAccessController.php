<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\User;
use App\Support\PageRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Page Access — one row per Position, one column per web page (see
 * PageRegistry). Unlike the Access Matrix (which reflects per-user
 * extension_access rows aggregated up to Position), page_access is stored
 * directly at the Position level, so a cell is a plain checked/unchecked —
 * no "mixed" state. Changes are staged client-side and only take effect on
 * Save. Admin-only tools and the Chrome extension are not part of this —
 * see PageRegistry's docblock.
 */
class PageAccessController extends Controller
{
    public function index()
    {
        $pages = PageRegistry::$pages;

        $positions = User::whereNotNull('position')
            ->where('position', '!=', '')
            ->distinct()
            ->orderBy('position')
            ->pluck('position');

        $totals = User::whereNotNull('position')
            ->where('position', '!=', '')
            ->select('position', DB::raw('count(*) as cnt'))
            ->groupBy('position')
            ->pluck('cnt', 'position');

        $grantedLookup = DB::table('page_access')
            ->get(['position', 'page_key'])
            ->groupBy('position')
            ->map(fn ($rows) => $rows->pluck('page_key')->all());

        $matrix = [];
        foreach ($positions as $position) {
            $granted = $grantedLookup->get($position, []);
            foreach ($pages as $pageKey => $label) {
                $matrix[$position][$pageKey] = in_array($pageKey, $granted, true);
            }
        }

        return view('page-access', [
            'positions' => $positions,
            'pages'     => $pages,
            'totals'    => $totals,
            'matrix'    => $matrix,
        ]);
    }

    public function save(Request $request)
    {
        $validPageKeys = array_keys(PageRegistry::$pages);

        $validated = $request->validate([
            'changes'             => 'required|array|min:1',
            'changes.*.position'  => 'required|string',
            'changes.*.page_key'  => 'required|string|in:' . implode(',', $validPageKeys),
            'changes.*.grant'     => 'required|boolean',
        ]);

        $actorId = auth()->user()->employeeid ?? 'system';
        $grantedTotal = 0;
        $revokedTotal = 0;

        foreach ($validated['changes'] as $change) {
            $position = $change['position'];
            $pageKey = $change['page_key'];
            $grant = (bool) $change['grant'];

            if ($grant) {
                $inserted = DB::table('page_access')->insertOrIgnore([
                    'page_key'   => $pageKey,
                    'position'   => $position,
                    'created_by' => $actorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($inserted) {
                    $grantedTotal++;
                    AuditTrail::record([
                        'event'          => 'page_access_granted',
                        'description'    => "Page access: granted \"{$pageKey}\" to position \"{$position}\"",
                        'auditable_type' => 'page_access',
                        'auditable_id'   => $position,
                        'new_values'     => ['page_key' => $pageKey, 'position' => $position],
                    ]);
                }
            } else {
                $deleted = DB::table('page_access')
                    ->where('page_key', $pageKey)
                    ->where('position', $position)
                    ->delete();

                if ($deleted) {
                    $revokedTotal++;
                    AuditTrail::record([
                        'event'          => 'page_access_revoked',
                        'description'    => "Page access: revoked \"{$pageKey}\" from position \"{$position}\"",
                        'auditable_type' => 'page_access',
                        'auditable_id'   => $position,
                        'old_values'     => ['page_key' => $pageKey, 'position' => $position],
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Saved — {$grantedTotal} page grant(s) added, {$revokedTotal} revoked.",
            'granted' => $grantedTotal,
            'revoked' => $revokedTotal,
        ]);
    }
}
