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
| APP-GUIDE version | 3.17.0 |
| Last released commit | *(shipped with the shortcuts + docs commit — see Work Log)* |
| Uncommitted WIP | **None** (as of this ledger update) |
| Test suite | 36 Feature + 2 Unit test files, 176 tests / 710 assertions passing |
| Active branch | `master` (tracking `origin/master`) |

---

## 3. NEXT UP (do this first)

### ✓ Done — Command palette keyboard shortcuts (A / R / N)
**Shipped 2026-08-09.** Global admin shortcuts — `/` opens search, `N` jumps to New Member, `A` approves/confirms, `R` rejects the first visible pending item on approval pages (Loans, Dividends, Period-close reopen, Cash-count verify, Member approve/reject, Product-order approve, Savings pending approvals). `window.commandPalette` gained `shortcuts`/`collectShortcuts()`/`firstShortcut()` (visibility-aware) + `handleGlobalKey`. `triggerShortcut` clicks the form's submit button so the normal `confirm()` dialogs are preserved (no silent approvals on money movements). Verified: `composer test` green. Documented in `APP-GUIDE.md` 3.17 + `USER-GUIDE.md` §3.1.

### ▶ Next in line — P-01: Full APP-GUIDE refresh
**Status:** queued (not started).

`APP-GUIDE.md` is significantly stale vs the codebase:
- says 52 migrations → actual **71**; "100+ routes" → actual **248 declarations / 258 named**; 40 controllers → actual **41**; misses newer features (public registration + member applications, TOTP 2FA, single-session enforcement, force-password-change, onboarding importer, broadcasts, loan top-ups, public guarantee token, `DocumentService`, 9 Imports / 8 Exports / 14 Notifications, Tailwind 4 / Alpine 3.15 / Livewire present).
- Update the architecture tree, module reference, route summary and tech-stack table to reality (ground truth is in `SYSTEM-DOCUMENTATION.md`).

---

## 4. Pending Backlog (not started)

> Tasks below are inferred from the codebase and the audit trail referenced in `APP-GUIDE.md` changelog. They are **not** a formal product spec — re-confirm priority with the user before starting.

| # | Task | Why / Evidence | Effort |
|---|------|----------------|--------|
| P-01 | **Full APP-GUIDE refresh** — APP-GUIDE is significantly stale: says 52 migrations (actual **71**), 100+ routes (actual **248**), 40 controllers (actual **41**), and does **not** document public registration, member applications (approve/reject), TOTP 2FA, single-session enforcement, force-password-change, onboarding importer, broadcasts, loan top-ups, public guarantee token, `DocumentService`, 9 Imports / 8 Exports / 14 Notifications. Update architecture tree, module reference and route summary to reality. | Observed drift on 2026-08-09 | Large |
| P-02 | **Registration/onboarding documentation gap** — the public `register` flow and `admin/onboarding` importer have no user-guide steps beyond "Contact admin". Confirm intended flow (who approves new members? auto-approve settings?) and document in USER-GUIDE. | Newer features, undocumented | Medium |
| P-03 | **Check remaining audit backlog** — changelog references audit findings P1 #1–#20, P2 #4–#18, P3 #20. Completed items are visible in changelog; **find the original audit list** (ask user) to identify any P1/P2 items not yet addressed. | No audit source file in repo | Unknown |
| P-04 | **Member portal keyboard access / accessibility pass** — command palette is admin-only; portal has no `A`/`R` shortcuts. Confirm whether shortcuts should extend to the portal or stay admin-only. | `command-palette` only in `app-layout` | Small |
| P-05 | **Javascript test coverage** — the new `handleGlobalKey`/`firstShortcut` logic has no automated JS tests (no Playwright/Vitest present). Consider a light test harness. | Feature is logic-heavy, untested | Medium |

---

## 5. Work Log (recent completed work)

| Date | Commit | Feature |
|------|--------|---------|
| 2026-08-09 | *(filled after commit)* | Command-palette keyboard shortcuts (A/R/N) + documentation set (APP-GUIDE 3.17) |
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
