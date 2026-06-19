<?php

namespace App\Support;

/**
 * Role bundles → underlying capability access types.
 *
 * A user can be assigned a single role (e.g. web_user_sup) instead of many
 * individual capabilities; expand() turns the role into the capabilities the
 * rest of the app already checks for (CheckAccess middleware, sidebar, extension).
 */
class AccessRoles
{
    public static array $roles = [
        // Everything except admin (web + all extension capabilities)
        'web_user_manager' => [
            'web_managers',        // grants dashboards, forms, all reports, management reports, evaluation viewing
            'web_score_approval',  // Manager Tools → Score Approvals
            'extension_action_register',
            'extension_monitoring',
            'extension_coaching',
            'extension_triad',
        ],

        // Supervisor: Dashboards, Forms, all Reports, + 3 extension capabilities
        'web_user_sup' => [
            'web_dashboard',
            'web_forms',
            'web_report_monitoring',
            'web_report_action_register',
            'web_report_coaching',
            'web_report_triad',
            'extension_action_register',
            'extension_monitoring',
            'extension_triad',
        ],

        // SME: Dashboard, Forms, Reports (except Triad Ticket), + 2 extension capabilities
        'web_user_sme' => [
            'web_dashboard',
            'web_forms',
            'web_report_monitoring',
            'web_report_action_register',
            'web_report_coaching',
            'extension_action_register',
            'extension_monitoring',
        ],

        // LDA: Main + My Evaluations are open to all authenticated users already;
        // this role only adds the Action Register extension capability.
        'web_user_lda' => [
            'extension_action_register',
        ],
    ];

    /**
     * Expand a set of assigned access types to include any capabilities
     * implied by role bundles. Returns a de-duplicated list.
     *
     * @param  iterable<string>  $types
     * @return array<int, string>
     */
    public static function expand($types): array
    {
        $types = is_array($types) ? $types : iterator_to_array($types);
        $expanded = $types;

        foreach ($types as $t) {
            if (isset(self::$roles[$t])) {
                $expanded = array_merge($expanded, self::$roles[$t]);
            }
        }

        return array_values(array_unique($expanded));
    }
}
