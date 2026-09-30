# 📢 Audit Ops — Update (v1.0.0.27)

Hi everyone,

A smaller round of updates this time — mostly QA dashboard accuracy, a couple of admin tools, a new way to send us feedback, and one login bug fix.

## 📝 New: Feedback / Feature Request

- A new page for submitting feedback, bug reports, or feature requests — describe what's going on, tag which application (Web App / Chrome Extension) and category (QA Monitoring, Coaching, Triad, Recon Call Register, Others), and it's filed directly as a ticket for the team to triage.

## 🧑‍💼 For Admins

- **Leaver Date is now directly editable** on the Edit User page (same date picker used on the dashboards), instead of only being auto-set when you flip someone's Status to Inactive.
- **New: Export to Excel on /users** — a button next to "Add new user" downloads the full user directory (employee ID, name, email, position, department, role, status, both supervisors, leaver date, created date).
- **Inactive users can no longer log in** — enforced on both the website login and the Chrome extension's Microsoft sign-in, even with a still-valid password or token.

## 📊 QA Dashboard (`/dashboard-qa`)

- **"Total LDA" is now historically accurate.** It reflects who was actually part of the team during the date range you selected, not just who's active today — so a report for a past month still counts someone who has since left.
- **Two new coverage cards:** **Audited LDAs** (how many of that expected team actually have evaluations on file for the period) and **LDAs With No Audits** (the gap between them).
- **Evaluations Trend chart** now has a second, dashed line — **Acknowledged** — showing how many of each period's evaluations have been acknowledged by their LDA.

## 🐛 Fixed

- Chrome extension sign-in could fail in production with "Invalid audience" — a Laravel config-caching issue, not anything wrong with your Microsoft account. Resolved.

## ✅ What you need to do

Nothing required. Admins may want to check out the new Export button on /users.

Thank you!
