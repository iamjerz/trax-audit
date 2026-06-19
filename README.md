# Trax Audit Ops

An internal QA, audit, and reconciliation platform for Trax Technologies' logistics operation. It lets audit supervisors evaluate Logistics Data Analysts (LDAs), track reconciliation action items, run Triad and Coaching follow-ups, and review performance across the team — from both a web dashboard and a companion Chrome extension.

---

## Tech Stack

- **Framework:** Laravel 12 (PHP 8.2+)
- **Database:** PostgreSQL (the app relies on Postgres features such as `jsonb`, `ILIKE`, and `||` string concatenation)
- **Frontend:** Blade templates with a Bootstrap 5 admin theme (`public/assets`); Vite + Tailwind are wired up for `resources/` assets
- **Auth:** Session-based login for the web app; Microsoft Entra (Azure AD) JWT verification for the Chrome extension
- **Notable packages:** `laravel/sanctum`, `laravel/socialite` + `socialiteproviders/microsoft`, `maatwebsite/excel`, `firebase/php-jwt`, `doctrine/dbal`

---

## Features

### QA Monitoring
Create evaluations of an LDA across four scored sections — **Verification**, **Process Compliance**, **Engagement**, and **Business Analytics** — each stored in its own table and written together in a single transaction. The dashboard shows totals, an evaluations breakdown, and **Above/Below 75%** score cards.

> **Scoring rule:** Verification acts as a gate. If `verification.total_score < 200` the overall score is **0%**; otherwise the score is `process_compliance + engagement` (each 0–50, summing to 0–100). An audit scoring **≥ 75** counts as "Above Average."

### Action Register (Reconciliation)
Track recon tickets (`recon_action_items`) with a status workflow (To Do / In Progress / Pending / Closed), assignment to LDAs, threaded comments, and a dashboard with status counts and top client/carrier breakdowns.

### Triad
Evaluations scoring **10 call-handling criteria** as Pass/Fail (body language, clearing the mind, permission to take notes, word choices, SME trust/buy-in, recap, the 80/20 rule, SMART goal definition, RCA documentation, and actions in line with the situation). Includes a dedicated dashboard with per-criterion pass/fail charts and per-evaluator breakdowns.

### Coaching
Capture SMART goals and GROW plans linked to an evaluation.

### Users & Access Management
Admin CRUD for users, CSV/Excel import, per-user permission management via the `extension_access` table, and a **Reset password** action that returns a user to the default password.

### Audit Trail
A system-wide activity log (`audit_trails`) recording authentication events (web and Chrome-extension logins, logouts, failed logins), record create/update/delete with before/after field diffs, status/assignment changes, access changes, and password resets. Viewable on an admin page with search, event, and date filters.

### Extension Details
Admin page to manage the Chrome extension's version registry (`extension_details`): list Version / Item ID / Status, add and edit entries, and view a per-entry change history (drawn from the audit trail).

### My Evaluations & Acknowledgement
Every user has a **My Evaluations** page listing the evaluations recorded for them. They can open the full read-only detail (same layout as the supervisor ticket view) and **acknowledge** that they've reviewed it — recorded with who/when and logged to the audit trail. Acknowledgement is ownership-enforced (you can only acknowledge your own).

### Disputes / Appeals
From My Evaluations, an LDA can **dispute** an evaluation they disagree with (with a reason). Supervisors review on a **Disputes** report and **Resolve** or **Reject** each, with a note. Optionally they can **correct the scores** as part of resolving (see below). All actions are audit-logged.

### Score Corrections — maker / checker (with history)
From a dispute, a supervisor proposes a score correction via dropdowns matching the QA form (Pass/Fail and Met/Coached/Not Met). This creates a **pending request** — scores do **not** change yet. A **Score Approvals** screen (Manager Tools) lets an approver **Approve** (applies the values, recalculates totals, resolves the dispute) or **Reject** (no change), showing a field-level "what changed" diff. Every correction keeps a full **before/after snapshot** (`score_corrections`) plus field diffs in the audit trail — nothing changes without a record. While a correction is pending, the dispute is **locked** (admins can override).

### Reports & Analytics
- **Pending Acknowledgements** — evaluations not yet acknowledged by their LDA, with days-waiting.
- **Overdue Action Items** — open recon tickets aged 7+ days.
- **LDA Scorecard** — per-analyst QA score, pass rate, Triad pass rate, coaching count, open recon items.
- **Auditor Productivity** — per-auditor output, average score given, pass rate.
- **Client / Carrier Health** — recon volume / open / overdue by client and carrier.
- **Root-Cause Analytics** — Pareto of cause-of-issue + 12-month trend.
- **Audit Coverage** — % of LDAs evaluated in a period.
- **Per-record Activity Timeline** — full chronological history of a single evaluation.
- **Excel exports** for evaluations, recon, triad, and the audit trail.

Report pages share a consistent filter bar (Choices.js dropdowns, result counts) including a user (auditor/LDA) filter.

### Security & Workflow Rules
- **Forced password change:** users on the default password must set a new one before using the app.
- **Server-side access control:** an `access` middleware enforces `extension_access` permissions on every protected route (admins bypass), so hidden menu items can't be reached by URL. Role bundles (below) expand to capabilities in the middleware, the sidebar, and the Chrome-extension menu.
- **Acknowledge vs Dispute** are mutually exclusive, and disputes lock while a correction awaits approval — enforced in the UI and on the server.

---

## Requirements

- PHP **8.2+** with the usual Laravel extensions, plus `pdo_pgsql`
- Composer
- Node.js + npm
- PostgreSQL

---

## Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Create your environment file
cp .env.example .env

# 3. Generate the app key
php artisan key:generate

# 4. Configure your database and Microsoft settings in .env (see below)

# 5. Run migrations
php artisan migrate

# 6. Install and build front-end assets
npm install
npm run build
```

A convenience script is also defined in `composer.json`:

```bash
composer setup   # install, copy .env, key:generate, migrate --force, npm install, npm run build
```

---

## Environment Configuration

Set the database connection to PostgreSQL in `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=trax_audit
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

For the Chrome extension's Microsoft SSO verification, configure the Azure AD application (client) ID. Tokens are validated against Microsoft's public JWKS (`https://login.microsoftonline.com/common/discovery/v2.0/keys`):

```env
MICROSOFT_CLIENT_ID=your-azure-ad-application-id
```

---

## Running the App

```bash
# Serve the application (plus queue, logs, and Vite) in one command:
composer dev

# …or individually:
php artisan serve
npm run dev
```

Then open `http://localhost:8000` and log in.

---

## Default Credentials

Users created via the import or the "Add User" form are seeded with the default password:

```
password123
```

On first login with the default password, the user is **forced to set a new password** before they can access any page.

> **Note:** `/import-users` performs a CSV import. Review and secure this route before deploying to production.

---

## Access Control

Permissions are stored per user in the `extension_access` table. The `access` route middleware enforces them server-side (an `admin` entry bypasses all checks). Role bundles expand to capabilities via `App\Support\AccessRoles` (in the middleware, sidebar, and extension menu).

**Capabilities:**

| Access type | Grants |
|---|---|
| `admin` | Full access — users, audit trail, extension details, everything |
| `web_dashboard` | QA, Action Register, and Triad dashboards |
| `web_forms` | QA monitoring form and form builder |
| `web_report_monitoring` | Individual evaluation reports + viewing any evaluation |
| `web_report_action_register` | Action Register tickets, overdue list, client/carrier health |
| `web_report_coaching` | Coaching tickets |
| `web_report_triad` | Triad tickets |
| `web_managers` | Everything except admin — dashboards, forms, all reports, Management Reports (scorecard, disputes, analytics), view any evaluation |
| `web_score_approval` | Manager Tools → Score Approvals (approve/reject corrections) |
| `extension_*` | Chrome-extension capabilities (`extension_action_register`, `extension_monitoring`, `extension_coaching`, `extension_triad`) |

**Role bundles** (assign one; it expands to the capabilities below):

| Role | Effective access |
|---|---|
| `web_user_manager` | Everything except admin (web + all extension capabilities) |
| `web_user_sup` | Dashboards, Forms, all Reports; extension: action register, monitoring, triad |
| `web_user_sme` | Dashboards, Forms, Reports **except** Triad Ticket; extension: action register, monitoring |
| `web_user_lda` | Main + My Evaluations; extension: action register |

> Main and My Evaluations are open to any authenticated user. Admin-only areas (Users, Audit Trail, Extension Details) are never granted by these roles.

---

## Project Structure

```
app/
  Http/
    Controllers/        Page + API controllers (QA, recon, triad, coaching, users, audit trail, …)
    Middleware/
      VerifyMicrosoftToken.php   Validates the extension's Microsoft JWT (alias: ms.jwt)
      ForcePasswordChange.php    Redirects default-password users to reset
      CheckAccess.php            Enforces extension_access (alias: access)
  Models/               Eloquent models (UserInputAudit, Verification, Coaching, TriadItems, ExtensionDetail, AuditTrail, …)
  Traits/Auditable.php  Auto-logs model create/update/delete to the audit trail
  Services/             DropdownService (CSV-backed client/carrier/audit dropdowns)
routes/
  web.php               Session-authenticated routes, grouped by access permission
  api.php               Extension API routes (ms.jwt protected)
resources/views/        Blade pages (dashboards, forms, tickets, homepage, admin pages)
public/assets/          Bootstrap admin theme + page JS (dashboard-*.js, user-edit.js, …)
database/migrations/    Schema
```

---

## Key Database Tables

- `users` — accounts; `position`, `role`, `status`, `supervisor_id`, `extension_access` link
- `user_input_audits` + `verifications`, `process_compliances`, `engagements`, `business_analytics` — QA evaluations
- `recon_action_items` + `recon_item_comments` — reconciliation tickets and their history
- `triad_items` — Triad evaluations (`jsonb` criteria)
- `coachings` — Coaching sessions (SMART/GROW `jsonb`)
- `extension_access` — per-user permissions
- `extension_details` — Chrome extension version registry
- `audit_trails` — system-wide activity log
- `acknowledgements` — LDA sign-offs on their evaluations
- `disputes` — evaluation disputes/appeals and their resolution
- `score_corrections` — before/after snapshots of score corrections
- Reference tables: `client_codes`, `carrier_codes`, `region`, `status`, `form_list`

---

## Chrome Extension Integration

The extension authenticates users via Microsoft SSO. It sends the Microsoft access token as a Bearer token; the app validates it against Microsoft's JWKS and checks the audience against `MICROSOFT_CLIENT_ID`.

- `GET  /api/login/verify` — validates the Microsoft token and logs the sign-in to the audit trail
- Routes under the `ms.jwt` middleware (`api.php`) expose form data, dropdowns, and submission endpoints for the extension (QA form, recon, triad, coaching)
- `GET  /api/extension/connector/check` and `POST /api/extension/details/check` — connector and version validation against `extension_details`

---

## Testing

```bash
composer test        # php artisan test
# or
php artisan test
```

> The repository currently contains the default Laravel example tests only. Adding feature tests around scoring, access control, and the audit trail is recommended.

---

## Notes & Conventions

- **Database engine:** several queries use PostgreSQL-specific syntax (`ILIKE`, `||`, `jsonb`). Run the app on PostgreSQL.
- **Position labels:** LDA users are stored with `position = 'LDA'`. A few older code paths reference the longer `'Logistics Data Analyst'`; prefer `'LDA'` for consistency.
- **Audit trail:** sensitive fields (`password`, `remember_token`) are excluded from logged diffs.
- **Compiled views:** if a Blade change doesn't appear, run `php artisan view:clear`.
