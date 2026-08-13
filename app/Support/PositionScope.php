<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Looks up a Position's data-visibility scope from the positions table,
 * replacing the string-matching heuristic (strtolower/trim + str_contains)
 * that used to be duplicated across MonitoringTicket, ReconTiketController,
 * CoachingTicket, and TriadTicket — with the same values in one place,
 * editable from the /positions admin page instead of a code change.
 *
 * Values: 'own' (just this person's records), 'team' (this person's plus
 * everyone whose supervisor_id points back to them), 'all' (unrestricted).
 */
class PositionScope
{
    public static function forPosition(?string $position): string
    {
        if (!$position) {
            return 'all';
        }

        $scope = DB::table('positions')->where('name', $position)->value('scope');

        // Unknown/unseeded position -> permissive default, matching the
        // fallback behavior that already existed before this table.
        return $scope ?: 'all';
    }
}
