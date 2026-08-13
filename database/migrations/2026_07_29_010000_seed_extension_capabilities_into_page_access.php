<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The 4 raw Chrome-extension capabilities, now assigned per Position via
     * page_access instead of per user via extension_access + role bundles.
     */
    private array $extensionPageKeys = [
        'extension_action_register',
        'extension_monitoring',
        'extension_coaching',
        'extension_triad',
    ];

    /**
     * A frozen snapshot of AccessRoles::$roles as it existed right before the
     * bundle concept was retired in this same change. Deliberately hardcoded
     * here (not read from AccessRoles::class) so this migration keeps seeding
     * correctly forever, even after AccessRoles.php is simplified.
     */
    private array $legacyBundleExpansion = [
        'web_user_manager' => [
            'web_managers', 'web_score_approval',
            'extension_action_register', 'extension_monitoring', 'extension_coaching', 'extension_triad',
        ],
        'web_user_sup' => [
            'web_dashboard', 'web_forms', 'web_report_monitoring', 'web_report_action_register',
            'web_report_coaching', 'web_report_triad',
            'extension_action_register', 'extension_monitoring', 'extension_triad',
        ],
        'web_user_sme' => [
            'web_dashboard', 'web_forms', 'web_report_monitoring', 'web_report_action_register', 'web_report_coaching',
            'extension_action_register', 'extension_monitoring',
        ],
        'web_user_lda' => [
            'extension_action_register',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->seedFromCurrentAccess();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('page_access')->whereIn('page_key', $this->extensionPageKeys)->delete();
    }

    /**
     * Preserve today's effective extension-capability access at cutover: for
     * every Position, if any user in that position currently holds a given
     * extension capability — directly, or via one of the 4 role bundles —
     * grant that Position the equivalent page_access row.
     */
    private function seedFromCurrentAccess(): void
    {
        $rawByPosition = DB::table('users')
            ->join('extension_access', 'users.employeeid', '=', 'extension_access.employeeid')
            ->whereNotNull('users.position')
            ->where('users.position', '!=', '')
            ->select('users.position', 'extension_access.access_type')
            ->get()
            ->groupBy('position')
            ->map(fn ($rows) => $rows->pluck('access_type')->unique()->values()->all());

        $now = now();
        $rows = [];

        foreach ($rawByPosition as $position => $rawTypes) {
            $expanded = $this->expand($rawTypes);

            foreach ($this->extensionPageKeys as $pageKey) {
                if (in_array($pageKey, $expanded, true)) {
                    $rows[] = [
                        'page_key'   => $pageKey,
                        'position'   => $position,
                        'created_by' => 'migration:seed',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (! empty($rows)) {
            // Same table as the original page_access migration, but these
            // rows are new page_keys — insertOrIgnore skips if a row somehow
            // already exists for this (page_key, position) pair.
            DB::table('page_access')->insertOrIgnore($rows);
        }
    }

    /**
     * Same shape as AccessRoles::expand(), applied to the frozen snapshot
     * above rather than the live (already-simplified) AccessRoles::$roles.
     */
    private function expand(array $types): array
    {
        $expanded = $types;

        foreach ($types as $t) {
            if (isset($this->legacyBundleExpansion[$t])) {
                $expanded = array_merge($expanded, $this->legacyBundleExpansion[$t]);
            }
        }

        return array_values(array_unique($expanded));
    }
};
