# 📢 Audit Ops — Update (v1.0.0.28)

Hi everyone,

This release is all about login — a new way to sign in, plus more control over how each account is allowed to sign in. **Effective Monday, October 5, 2026.**

## 🔐 New: Sign in with Microsoft

- The login page now has a **Sign in with Microsoft** button alongside the usual email/password form — the same Microsoft account you already use elsewhere at Trax, one click, no extra password to remember.
- If your account isn't already set up with an email matching your Microsoft account, you'll get a clear message to contact your admin — nothing is created automatically.

## 🧑‍💼 For Admins: Login Method control

- New **Login Method** setting on each user (Add User / Edit User on `/users`) — choose whether an account can sign in with **Password only**, **Microsoft only**, or **Both**.
- Existing accounts have been switched to **Microsoft only** by default. Admin accounts are kept on **Both**, so there's always a way in.
- New users are created as **Microsoft only** by default — change it on the Add User form for anyone who needs password access instead.

## ✅ What you need to do

Starting **Monday, October 5**, if your account is now Microsoft-only, signing in with your old password will no longer work — use the **Sign in with Microsoft** button instead. If you're not sure your Microsoft sign-in works with this app, try it or check with your admin before then.

Thank you!
