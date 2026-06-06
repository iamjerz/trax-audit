# Changelog

All notable changes to the Trax Audit Ops platform are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [1.0.0.24] - 2026-06-06

### Added
- **Audit Trail.** New `audit_trails` table, `AuditTrail` model, and an `Auditable` trait applied to the core Eloquent models so create/update/delete actions are logged automatically with before/after field diffs. Authentication events (login, logout, failed login) are captured via event listeners, and query-builder actions (recon create/update/delete, ticket assignment, status changes, comments, access changes, password resets) are logged explicitly. Includes an admin page with search, event, and date filters.
- **Triad Dashboard.** New `DashboardTriadController` with summary cards, a pass/fail-by-criterion chart across all 10 triad criteria, a criterion breakdown table, and a per-evaluator breakdown, backed by new routes and `dashboard-triad.js`.
- **Home page.** The previously empty `/homepage` now shows a welcome banner, access-aware at-a-glance stat cards, quick-access module links, and a getting-started guide.
- **Server-side access control.** New `CheckAccess` middleware (alias `access`) enforces `extension_access` permissions on every authenticated route (admins bypass). Routes are now grouped by required permission.
- **Forced password change.** New `ForcePasswordChange` middleware and `ChangePasswordController`/view require any user still on the default password to set a new one before using the app.
- **Reset password (admin).** The previously non-functional "Reset" button on the user edit page now resets a user's password to the default via a new `resetPassword` endpoint (logged to the audit trail).
- **Extension Details page (admin).** New `ExtensionDetail` model, controller, and page to list, add, and edit `extension_details` (Version / Item ID / Status), with a per-entry change-history modal sourced from the audit trail.
- **Chrome extension login auditing.** Microsoft SSO sign-ins via `/api/login/verify` are now recorded in the audit trail.
- **Documentation.** Added a project `README.md`.

### Changed
- **QA Monitoring dashboard cards** now compute and display **Above/Below 75%** counts using the same scoring rule as the ticket view (verification gate ≥ 200, otherwise process + engagement).
- **Total LDAs** count now matches the actual data (`position = 'LDA'`, with the legacy `'Logistics Data Analyst'` label also accepted).
- Auth-event descriptions now note the channel (e.g. "logged in on the website" vs "on the Chrome extension").
- Pagination now renders with Bootstrap 5 styling (`Paginator::useBootstrapFive()`).
- Cleaned up stray/unclosed markup in the QA dashboard stat cards.
- Triad dashboard scope dropdown removed per request (now shows all triads).

### Fixed
- **QA dashboard cards all showing 0.** A `COALESCE(total_score, 0)` mixing a `varchar` column with an integer threw a type error on PostgreSQL and broke the whole `/dashboard/cards` response. Values are now selected raw and cast in PHP.
- **Show/Hide password toggle** on the login and change-password screens (the shared script targeted a non-existent element id). Replaced with a robust per-field toggle; added a toggle to the confirm-password field.
- **Login error message** is now shown when credentials are incorrect.
- **Extension Details action button** was being absolutely positioned/hidden by a theme CSS class collision (`.edit-btn`); renamed to unique classes (`ext-edit-btn` / `ext-history-btn`) and moved the actions into a dropdown.
- **`LoginVerifyController`** was catching `ExpiredException` without importing it; added the missing import.

### Security
- Enforced authorization server-side so pages and data endpoints can no longer be reached by URL without the required `extension_access` permission (previously the permissions only hid menu items).
- Default-password accounts are now forced to set a new password at next login.
- Sensitive fields (`password`, `remember_token`) are excluded from audit-trail diffs.

### Notes / Follow-ups
- `/import-users` remains outside authentication and should be secured (or removed) before production.
- The application requires PostgreSQL (uses `ILIKE`, `||`, and `jsonb`).
- Automated test coverage is still minimal (default Laravel stubs); feature tests for scoring, access control, and the audit trail are recommended.

---

> Earlier history predates this changelog; see the Git commit log for prior changes.
