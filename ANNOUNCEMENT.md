# 📢 Audit Ops — Update (v1.0.0.26, includes v1.0.0.25)

Hi everyone,

This release brings a big round of improvements: a self-service experience for analysts, a fair dispute-and-appeal process with admin-approved corrections, a full set of management reports, and simpler role-based access. Here's everything that's new.

> 🚀 **Heads up:** these updates will be deployed to Production **this weekend or early next week**.

## 👤 For Everyone (Analysts)
- **My Evaluations** — a new menu item where you can see every evaluation recorded for you and open the full details (the same view your supervisor sees).
- **Acknowledge** — after reviewing an evaluation, confirm you've seen it. It's recorded with the date.
- **Dispute** — if you disagree with an evaluation, raise a dispute with a reason; your supervisor will review it. You'll see the outcome (and a "Corrected" tag if your scores were changed) right on My Evaluations.
- **One path per evaluation** — you can acknowledge *or* dispute, not both. Once acknowledged it can't be disputed, and while a dispute is open it can't be acknowledged.

## 🧑‍💼 For Supervisors & Managers
- **Disputes review** — review raised disputes and **Resolve** or **Reject** them with a note.
- **Score corrections now require approval** — when you correct a disputed evaluation's scores, it becomes a **pending request**. Scores change only after an approver acts on the new **Manager Tools → Score Approvals** page, which shows exactly **what changed** before approving. The full before/after is always kept on record, and the dispute is locked until the decision is made.
- **New reports & analytics:**
  - **Pending Acknowledgements** — evaluations not yet acknowledged, with how long they've waited.
  - **Overdue Action Items** — open reconciliation tickets 7+ days old.
  - **LDA Scorecard** — per-analyst QA score, pass rate, Triad pass rate, coaching count, open items.
  - **Auditor Productivity** — output, average score, and pass rate per auditor.
  - **Client / Carrier Health** — recon volume / open / overdue by client and carrier.
  - **Root Cause Analytics** — most common issues (Pareto) and a 12-month trend.
  - **Audit Coverage** — % of LDAs evaluated, and who hasn't been audited.
  - **Activity Timeline** — the full history of any single evaluation.
- Report pages now have cleaner filters, including filtering by a specific auditor or LDA.

## 🔐 Simpler Access Roles
Admins can assign a single **role** instead of many individual permissions:
- **Manager** — everything except admin
- **Supervisor** — Dashboards, Forms, all Reports (+ extension tools)
- **SME** — Dashboards, Forms, Reports except Triad (+ extension tools)
- **LDA** — Home and My Evaluations (+ Action Register in the extension)

These apply consistently across the web app and the Chrome extension.

## ✅ What you need to do
- **Analysts:** review and acknowledge anything pending in **My Evaluations**.
- **Supervisors/Managers:** explore the new reports.
- Nothing else is required.

Questions or anything not working as expected? Reach out to your administrator.

Thank you!
