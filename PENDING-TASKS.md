# PENDING-TASKS.md — AI Session Continuity Ledger

> **Purpose:** A single file that lets a future AI session (or a human) pick up exactly where the last one stopped. **Read this file first** when starting work; **update it** every time a task starts, finishes or changes direction.

---

## 1. How to Use This File (Session Protocol)

1. **On session start:** read this file. The `NEXT UP` section below is the authoritative "what to do next".
2. **Before starting any task:** move it to `IN PROGRESS` with today's date.
3. **When finished:** move it to `DONE` in the *Work Log* table (date + commit hash), then pick the next item.
4. **If the session stalls or the app crashes:** the last `IN PROGRESS` item (with its partial notes) is where to resume. Never assume work is finished just because it was started.
5. **Every session must also:** run `composer test` before declaring a feature done, and follow the DoD ritual in `AGENTS.md` (verify → document in `APP-GUIDE.md` + this file → review → commit → push).

---

## 2. Current State (last updated: 2026-08-09)

| Item | Value |
|------|-------|
| APP-GUIDE version | 3.18.0 |
| Last released commit | `844a0c3` — "Command palette keyboard shortcuts and documentation set" |
| Uncommitted WIP | **APP-GUIDE 3.18 refresh (P-01) — pending commit** |
| Test suite | 36 Feature + 2 Unit test files, 176 tests / 710 assertions passing |
| Active branch | `master` (tracking `origin/master`) |

---

## 3. NEXT UP (do this first)

### ✓ Done — Command palette keyboard shortcuts (A / R / N)
**Shipped 2026-08-09.** Global admin shortcuts — `/` opens search, `N` jumps to New Member, `A` approves/confirms, `R` rejects the first visible pending item on approval pages (Loans, Dividends, Period-close reopen, Cash-count verify, Member approve/reject, Product-order approve, Savings pending approvals). `window.commandPalette` gained `shortcuts`/`collectShortcuts()`/`firstShortcut()` (visibility-aware) + `handleGlobalKey`. `triggerShortcut` clicks the form's submit button so the normal `confirm()` dialogs are preserved (no silent approvals on money movements). Verified: `composer test` green. Documented in `APP-GUIDE.md` 3.17 + `USER-GUIDE.md` §3.1.

### ✓ Done — P-01: Full APP-GUIDE refresh
**Status:** DONE (2026-08-09) — commit pending the DoD ritual.

`APP-GUIDE.md` bumped to **3.18.0** and brought back in line with the codebase:
- tech stack corrected (Laravel 13.22, PHP 8.3, Tailwind CSS 4 via Vite, Alpine 3.15 bundled not CDN, Livewire 4 installed);
- architecture tree rewritten (28 Actions, 8 Enums, 8 Exports, 9 Imports, 13 Notifications, 41 controllers, 11 Services, 8 middleware, 9 Form Requests, 3 console commands, 38 Models, 71 migrations / 45 tables, 141 Blade views);
- database schema expanded from 28 → 45 tables (carts, hire_purchase_schedules, payroll_arrears, cash_counts, approval_workflows, pending_approvals, branding_assets, broadcast_messages, import_logs, sessions);
- new Module reference sections K–N (Public Website & Self-Registration, Security & Access, Onboarding & Broadcasts, Admin Management Hub);
- workflows updated (member registration incl. self-registration approve/reject, security & access 2FA/single-session/forced-password);
- notifications rewritten to the actual 13 classes;
- route-summary appendix replaced with verified counts: **267 registered routes / 266 named** (the sole unnamed route is `POST /login`), per-module breakdown table.
- `SYSTEM-DOCUMENTATION.md` synced: route counts (248 declarations → 267 registered / 266 named) and notification count 14 → **13**.

Verified counts via `php artisan route:list --json` (280 total − 13 framework routes = 267 app routes), file listings and grep of `routes/web.php` (248 `Route::…` declarations).

---

## 4. Pending Backlog (not started)

> Tasks below are inferred from the codebase and the audit trail referenced in `APP-GUIDE.md` changelog. They are **not** a formal product spec — re-confirm priority with the user before starting.

| # | Task | Why / Evidence | Effort |
|---|------|----------------|--------|
| P-02 | **Registration/onboarding documentation gap** — the public `register` flow and `admin/onboarding` importer have no user-guide steps beyond "Contact admin". Confirm intended flow (who approves new members? auto-approve settings?) and document in USER-GUIDE. | Newer features, undocumented | Medium |
| P-03 | **Check remaining audit backlog** — changelog references audit findings P1 #1–#20, P2 #4–#18, P3 #20. Completed items are visible in changelog; **find the original audit list** (ask user) to identify any P1/P2 items not yet addressed. | No audit source file in repo | Unknown |
| P-04 | **Member portal keyboard access / accessibility pass** — command palette is admin-only; portal has no `A`/`R` shortcuts. Confirm whether shortcuts should extend to the portal or stay admin-only. | `command-palette` only in `app-layout` | Small |
| P-05 | **Javascript test coverage** — the new `handleGlobalKey`/`firstShortcut` logic has no automated JS tests (no Playwright/Vitest present). Consider a light test harness. | Feature is logic-heavy, untested | Medium |

---

## 5. Work Log (recent completed work)

| Date | Commit | Feature |
|------|--------|---------|
| 2026-08-09 | (pending) | **APP-GUIDE 3.18 documentation refresh (P-01)** — guide brought back in line with the codebase |
| 2026-08-09 | `844a0c3` | Command-palette keyboard shortcuts (A/R/N) + documentation set (APP-GUIDE 3.17) |
| 2026-08-09 | `995f324` | Loan detail lifecycle timeline with avatars (APP-GUIDE 3.16) |
| 2026-08-09 | `7f385fa` | Server-enforced loan wizard rules (3× savings cap, guarantor cap) + amortization chart (3.15) |
| 2026-08-09 | `e2adf48` | Search autocomplete everywhere, sidebar rework, share receipt + member name fixes (3.14) |
| 2026-08-08 | `a605ca6` | bcmath Money precision + transaction traceability (3.13) |
| 2026-08-08 | `9319b1b` | Maker-checker workflows, period-close checks, inventory COGS, payroll + hire-purchase ledger (3.12) |
| 2026-08-08 | `3fffcf4` | CBN compliance: period-close appropriations, dividend gating, single-obligor (3.11) |
| 2026-08-08 | `20b6198` | Finance report exports (Excel + QR-stamped PDF) (3.10) |
| 2026-08-08 | `e02bef2` | Members Savings Control report (3.9.1) |
| 2026-08-08 | `3af575c` | Daily Cash Count (3.9) |
| 2026-08-08 | `f4ff032` | Loan processing fees + dividend accrual (3.8) |
| 2026-08-08 | `6d92959` | Full CBN chart of accounts (3.7) |
| 2026-08-05 | `c256680` | Module toggles, compact sidebar, static login, combobox/TALL hardening (3.5) |

---

## 6. Environment & Commands Reference

```bash
composer test          # config:clear + full test suite (DoD verify step)
vendor/bin/pint        # Laravel Pint style fixes
npm run dev            # Vite (Tailwind/Alpine dev)
composer run dev       # serve + queue + pail + vite together
php artisan migrate:fresh --seed   # rebuild demo DB (destructive — data loss!)
php artisan branding:seed          # re-import branding assets
```

**Key paths:** routes in `routes/web.php` (248 declarations, groups: public → guest → auth → admin → ledger/finance → portal). Views in `resources/views/`. Tests in `tests/Feature` / `tests/Unit`. Business logic split between `app/Services` (financial engine) and `app/Actions` (domain actions).

**Read first:** `AGENTS.md` (DoD ritual) → this file → `APP-GUIDE.md` (features) → `SYSTEM-DOCUMENTATION.md` (how the code works).
