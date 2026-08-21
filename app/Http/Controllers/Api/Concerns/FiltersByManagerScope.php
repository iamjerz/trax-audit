<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Shared "Manager / Supervisor" scope resolution, originally built for the QA
 * dashboard (DashboardControllerMain) and reused as-is by the Recon dashboard
 * (DashboardReconController) so both stay in sync going forward.
 *
 * Walks the users.supervisor_id (+ second_supervisor_id) tree to find every
 * LDA beneath a given root, and builds the grouped Managers/SMEs/Supervisors/
 * Other picker options. The two dashboards key their audit rows differently
 * (user_input_audits.lda_id is an employeeid; recon_action_items.lda_email is
 * an email), so resolveScopeLdaIds()/resolveScopeLdaEmails() both derive from
 * the same underlying resolveScopeLdaUsers() walk instead of duplicating it.
 */
trait FiltersByManagerScope
{
    private const LDA_POSITIONS = ['LDA', 'Logistics Data Analyst'];

    /**
     * People who actually have at least one report (directly via
     * supervisor_id OR second_supervisor_id), grouped into Managers / SMEs /
     * Supervisors / Other for a dashboard's "Manager / Supervisor" picker.
     * Only people with a real team show up — nobody with zero reports, so
     * picking a name never silently shows an empty result.
     */
    private function managerPickerOptions(): array
    {
        $reportIds = DB::table('users')
            ->whereNotNull('supervisor_id')->where('supervisor_id', '!=', '')
            ->pluck('supervisor_id')
            ->merge(
                DB::table('users')
                    ->whereNotNull('second_supervisor_id')->where('second_supervisor_id', '!=', '')
                    ->pluck('second_supervisor_id')
            )
            ->unique()
            ->values();

        $people = DB::table('users')
            ->where('position', '!=', 'LDA')
            ->whereIn('employeeid', $reportIds)
            ->orderBy('first_name')
            ->get(['employeeid', 'first_name', 'last_name', 'position']);

        $groups = [
            'Managers'    => [],
            'SMEs'        => [],
            'Supervisors' => [],
            'Other'       => [],
        ];

        foreach ($people as $p) {
            $option = [
                'value' => $p->employeeid,
                'label' => trim("{$p->first_name} {$p->last_name}") . ' (' . $p->position . ')',
            ];

            $positionLower = strtolower((string) $p->position);
            if (str_contains($positionLower, 'manager')) {
                $groups['Managers'][] = $option;
            } elseif (str_contains($positionLower, 'sme')) {
                $groups['SMEs'][] = $option;
            } elseif (str_contains($positionLower, 'supervisor')) {
                $groups['Supervisors'][] = $option;
            } else {
                $groups['Other'][] = $option;
            }
        }

        // Drop empty groups so the frontend doesn't render blank optgroups.
        return array_filter($groups, fn($g) => !empty($g));
    }

    /**
     * Every LDA user record beneath the given scope, or null if scope is
     * empty (no filter at all):
     *  - scope empty ("All"): null.
     *  - scope = "my_team": every LDA beneath the current user in the org
     *    tree (a manager gets their supervisors' LDAs too, all levels down).
     *  - scope = a specific employeeid (picked from the Manager/SME/
     *    Supervisor dropdown): every LDA beneath THAT person instead.
     * Keyed by employeeid so both consumers below can pick whichever field
     * (employeeid or email) their own table filters on.
     */
    private function resolveScopeLdaUsers(?string $scope): ?array
    {
        $scope = trim((string) $scope);

        if ($scope === '') {
            return null;
        }

        if ($scope === 'my_team') {
            $user = Auth::user();
            if (! $user) {
                return [];
            }
            $rootId       = $user->employeeid;
            $rootPosition = $user->position ?? null;
        } else {
            // A specific manager/SME/supervisor employeeid was selected.
            $root = DB::table('users')->where('employeeid', $scope)->first();
            if (! $root) {
                return []; // unknown id — show nothing rather than everything
            }
            $rootId       = $root->employeeid;
            $rootPosition = $root->position ?? null;
        }

        $users    = $this->allUsersMinimal();
        $ldaUsers = [];

        foreach ($this->descendants($rootId, $users) as $eid => $u) {
            if ($this->isLda($u->position)) {
                $ldaUsers[$eid] = $u;
            }
        }

        // If the root themselves is an LDA, include their own records too.
        if ($this->isLda($rootPosition)) {
            $rootUser = $users->firstWhere('employeeid', $rootId);
            if ($rootUser) {
                $ldaUsers[$rootId] = $rootUser;
            }
        }

        return array_values($ldaUsers);
    }

    /**
     * Scope resolved to employeeids — for tables keyed like
     * user_input_audits.lda_id.
     */
    private function resolveScopeLdaIds(?string $scope): ?array
    {
        $ldaUsers = $this->resolveScopeLdaUsers($scope);
        if ($ldaUsers === null) {
            return null;
        }

        return array_values(array_unique(array_map(fn($u) => $u->employeeid, $ldaUsers)));
    }

    /**
     * Scope resolved to emails — for tables keyed like
     * recon_action_items.lda_email.
     */
    private function resolveScopeLdaEmails(?string $scope): ?array
    {
        $ldaUsers = $this->resolveScopeLdaUsers($scope);
        if ($ldaUsers === null) {
            return null;
        }

        return array_values(array_unique(array_filter(array_map(fn($u) => $u->email ?? null, $ldaUsers))));
    }

    private function allUsersMinimal()
    {
        return DB::table('users')
            ->select('employeeid', 'supervisor_id', 'position', 'first_name', 'last_name', 'email')
            ->get();
    }

    /**
     * All employees strictly beneath $root in the supervisor_id tree,
     * keyed by employeeid.
     */
    private function descendants(string $root, $users): array
    {
        $childrenBySup = [];
        foreach ($users as $u) {
            $sup = $u->supervisor_id;
            if ($sup === null || $sup === '') {
                continue;
            }
            $childrenBySup[$sup][] = $u;
        }

        $out   = [];
        $stack = [$root];
        while ($stack) {
            $cur = array_pop($stack);
            foreach ($childrenBySup[$cur] ?? [] as $child) {
                if (!isset($out[$child->employeeid])) {
                    $out[$child->employeeid] = $child;
                    $stack[] = $child->employeeid;
                }
            }
        }

        return $out;
    }

    private function isLda(?string $position): bool
    {
        return in_array($position, self::LDA_POSITIONS, true);
    }
}
