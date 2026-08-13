<?php

namespace App\Support;

/**
 * The canonical list of independently Position-controlled pages/capabilities.
 *
 * Each entry is one column in the Page Access matrix and one page_key that
 * gets checked against the page_access table — by the `page:` middleware and
 * sidebar for the 19 web pages, and by menu.blade.php for the 4 Chrome
 * extension capabilities. Everything here is Position-based; the only things
 * left outside this system are the admin-only tools (Sign Off, List of
 * Users, Audit Trail, Extension Details, Page Access itself), which stay
 * gated purely by the 'admin' flag on the individual user.
 */
class PageRegistry
{
    public static array $pages = [
        'dashboard-qa'             => 'Dashboard — QA Monitoring',
        'dashboard-recon'          => 'Dashboard — Action Register',
        'dashboard-triad'          => 'Dashboard — Triad',
        'form-builder'             => 'Form Builder',
        'monitoring-form'          => 'QA Monitoring Form',
        'eval-individual'          => 'Evaluations',
        'monitoring-ticket'        => 'QA Monitoring List',
        'lda-scorecard'            => 'LDA Scorecard',
        'pending-acknowledgements' => 'Pending Acknowledgements',
        'auditor-productivity'     => 'Auditor Productivity',
        'root-cause'               => 'Root Cause Analytics',
        'audit-coverage'           => 'Audit Coverage',
        'score-approvals'          => 'Score Approvals',
        'disputes'                 => 'Disputes',
        'recon-ticket'             => 'Action Register Ticket',
        'recon-overdue'            => 'Overdue Items',
        'client-carrier-health'    => 'Client/Carrier Health',
        'coaching-ticket'          => 'Coaching Ticket',
        'triad-ticket'             => 'Triad Ticket',

        // Chrome extension capabilities — which "Audit Ops Forms" buttons
        // menu.blade.php shows. Checked there directly by these same keys.
        // The role-bundle shorthand (web_user_manager/sup/sme/lda) that used
        // to imply these has been retired; a Position now needs each one
        // checked explicitly.
        'extension_action_register' => 'Extension — Recon Call Action Register',
        'extension_monitoring'      => 'Extension — QA Monitoring',
        'extension_coaching'        => 'Extension — Coaching',
        'extension_triad'           => 'Extension — Triad',
    ];

    /**
     * What each page required under the old capability-string system
     * (access:web_dashboard, access:web_managers, etc). Used exactly once,
     * by the create_page_access_table migration, to seed page_access so
     * nobody's effective access changes the moment this ships. Nothing at
     * runtime reads this — the `page:` middleware only reads page_access.
     */
    public static array $legacyRequirements = [
        'dashboard-qa'             => ['web_dashboard', 'web_managers'],
        'dashboard-recon'          => ['web_dashboard', 'web_managers'],
        'dashboard-triad'          => ['web_dashboard', 'web_managers'],
        'form-builder'             => ['web_forms', 'web_managers'],
        'monitoring-form'          => ['web_forms', 'web_managers'],
        'eval-individual'          => ['web_report_monitoring', 'web_managers'],
        'monitoring-ticket'        => ['web_report_monitoring', 'web_managers'],
        'lda-scorecard'            => ['web_managers'],
        'pending-acknowledgements' => ['web_managers'],
        'auditor-productivity'     => ['web_managers'],
        'root-cause'               => ['web_managers'],
        'audit-coverage'           => ['web_managers'],
        'score-approvals'          => ['web_score_approval'],
        'disputes'                 => ['web_managers', 'web_user_sup', 'web_user_sme'],
        'recon-ticket'             => ['web_report_action_register', 'web_managers', 'web_user_lda'],
        'recon-overdue'            => ['web_report_action_register', 'web_managers'],
        'client-carrier-health'    => ['web_report_action_register', 'web_managers'],
        'coaching-ticket'          => ['web_report_coaching', 'web_managers'],
        'triad-ticket'             => ['web_report_triad', 'web_managers'],
    ];
}
