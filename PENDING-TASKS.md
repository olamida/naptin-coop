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

## 2. Current State (last updated: 2026-08-13)

| Item | Value |
|------|-------|
| APP-GUIDE version | 3.21 |
| Last released commit | `6e8247f` — "JS unit test coverage + command-palette module extraction (P-05)" |
| Uncommitted WIP | **Security hardening + scheduled commands batch** (this session) |
| Test suite | 37 Feature + 2 Unit test files, **201 tests / 784 assertions** passing (PHP) + **36 JS tests** via `npm run test:js` |
| Active branch | `master` (tracking `origin/master`) |

---

## 3. NEXT UP (do this first)

### ✓ Done — P-06: Security hardening + scheduled finance commands
**Status:** DONE (2026-08-13) — this session.

**Completed items:**
- **P6 #33**: Deleted legacy `AdminController.php` (zero refs verified).
- **P4 #24**: `NoBackDating` middleware + alias (`no-back-dating`) wired to all money routes — loan repayment (`loans.repayment.store`), journal store (`ledger.journals.store`), cash count store (`finance.cash-count.store`), plus all `/ledger` and `/finance` route groups.
- **P4 #25**: Migration `2026_08_13_000001_add_money_check_constraints.php` adds MySQL `CHECK` constraints on monetary columns (non-negative balances, positive amounts, valid enum values).
- **P4 #28**: Dedicated `throttle:finance` rate limiter (60 req/min per user) applied to `/ledger/*` and `/finance/*` route groups.
- **P4 #27**: Role-forced 2FA — new `config/security.php` with `enforce_two_factor_roles` (default `['super-admin','admin','treasurer']`), `RequireTwoFactor` middleware enhanced to redirect unenrolled forced roles to `/two-factor/setup` and unverified sessions to `/two-factor/challenge`. `phpunit.xml` sets `SECURITY_ENFORCE_TWO_FACTOR_ROLES=` empty by default so tests opt-in via `config([...])`.
- **P5**: `DatabaseBackupService` + `backup:encrypted` artisan command (AES-256-GCM, streams to avoid memory spikes, configurable retention). Scheduled at `02:00`.
- **P5**: Three new scheduled finance commands with notifications:
  - `verify:ledger-hash-chain` (04:00) — recomputes hash chain, alerts admins via `LedgerTamperNotification` on mismatch.
  - `finance:calculate-provisioning` (month-end 06:00) — posts provision movement, skips if period closed.
  - `finance:reconcile-control-accounts` (23:00) — compares control vs sub-ledger totals, alerts via `ControlVarianceNotification` on variance.
- **Tests**: `FinanceRateLimitTest` (2), `ForcedTwoFactorTest` (6), `NoBackDatingTest` (6), `ScheduledFinanceCommandsTest` (6) — all green.

Verified: `composer test` 201/201 green (784 assertions), `vendor/bin/pint` clean.

### ✓ Done — P-04: Member portal keyboard shortcuts + member-scoped quick search
**Status:** DONE (2026-08-09) — shipped `102c7c9`.

The reusable `<x-command-palette>` is now mounted on the **portal layout** (`portal-layout.blade.php`), so portal pages get:
- `/` (or `Ctrl/Cmd + K`) opening a **member-scoped quick search** — new `GET /my/search` (`portal.search`) JSON endpoint on `MemberPortalController::searchJson()` searches **only the signed-in member's own data**: loans by `loan_number`, savings transactions by `SAV/…` reference, share transactions by `SHR/…` reference, purchase orders by `order_number`/`order_group`. An empty query returns portal quick actions (Dashboard, Savings, Loans, Apply, Shares if module enabled, Purchases, Shop, Cart, Guarantors). `N` stays disabled (no `newMemberUrl`).
- **`A` accepts / `R` declines** the first visible pending guarantor request on **My Guarantor Requests** — the Accept/Decline forms carry `data-shortcut="approve"`/`"reject"` with `(A)`/`(R)` `<kbd>` hints; the decline `confirm()` dialog is preserved (same admin behavior).

New `tests/Feature/PortalSearchTest.php` (4 tests / 19 assertions): guest redirect, quick actions on empty query, loan search scoped to the member's own loans, savings search scoped to the member's own account. Verified: `PortalSearchTest` (4/4), `PortalDashboardVerifyTest` (1/1 — also proves the layout with the mounted palette renders), `SearchAutocompleteTest` (7/7). Documented in `APP-GUIDE.md` **3.19** + `USER-GUIDE.md` **v1.2 §5.1**.

### ✓ Done — Command palette keyboard shortcuts (A / R / N)
**Shipped 2026-08-09.** Global admin shortcuts — `/` opens search, `N` jumps to New Member, `A` approves/confirms, `R` rejects the first visible pending item on approval pages (Loans, Dividends, Period-close reopen, Cash-count verify, Member approve/reject, Product-order approve, Savings pending approvals). `window.commandPalette` gained `shortcuts`/`collectShortcuts()`/`firstShortcut()` (visibility-aware) + `handleGlobalKey`. `triggerShortcut` clicks the form's submit button so the normal `confirm()` dialogs are preserved (no silent approvals on money movements). Verified: `composer test` green. Documented in `APP-GUIDE.md` 3.17 + `USER-GUIDE.md` §3.1.

### ✓ Done — P-01: Full APP-GUIDE refresh
**Status:** DONE — shipped `eedc5f6` (2026-08-09).

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

### ✓ Done — P-02: Registration/onboarding documentation gap
**Status:** DONE (2026-08-09) — commit pending the DoD ritual.

`USER-GUIDE.md` bumped to **v1.1** with:
- a new **§4.1.1 Onboarding Wizard** section — where to find it (Administration → Settings → Data Import → Go to Onboarding), the 3-sheet workbook structure (`members` / `opening_savings` / `shares`), required vs optional columns per sheet, one-transaction all-or-nothing batch behaviour, Import Log batch IDs, and a note that onboarding does **not** create portal logins;
- an accurate **member application** walkthrough — public `/register` creates a *pending* member (no auto-approve exists), Approve activates the member + creates the portal login with a welcome email, Reject sets the member to `inactive` with **no portal login created**;
- the stale "assign the position" add-member step removed (the create form has no position field).

`APP-GUIDE.md` corrected to match the code (3.18.1):
- onboarding importer carries **members + opening_savings + shares** sheets only — position assignments are **not** imported (fixes the 3.18 text that claimed a member-position mapping);
- member reject sets status to `inactive`, not "rejected with a reason";
- Onboard Members admin-guide row points to Management → Data Import → Go to Onboarding;
- Central Import Hub table gained the Onboarding Wizard row.

Verified against `RegistrationController`, `ApproveMemberApplication`, `RejectMemberApplication`, `OnboardingImport` (+ sheet exports) and `OnboardingImportTest`.

### ✓ Done — P-03: Audit backlog reconciliation (FLAW v4.0 audit)
**Status:** DONE (2026-08-09) — audit source found in-repo; all FLAW items already implemented.

The original audit source **was in the repo** (the PENDING-TASKS "No audit source file in repo" note was wrong):
- `NAPTIN_COOP_WORLD_CLASS_AUDIT_AND_PROMPT.txt` and `Full-World-Class-Audit-Prompt.txt` — identical v4.0 "World-Class Audit & AI CLI Prompt" (2026-07-30). They use **FLAW 1–5 / WASTE 1–7 / SCREEN 1–5** numbering, which is **different from** the `P1 #1–#20` / `P2 #4–#18` / `P3 #20` numbering cited in the APP-GUIDE changelog (that numbered list is not present anywhere in the repo).

Reconciliation of every FLAW item against the codebase — **all implemented**:
- **FLAW 1** (god controllers → Actions + DocumentService + split AdminController): done — 28 single-purpose `Actions` (transaction-wrapped), `DocumentService`, controllers split into `User/Stock/Backup/Statistics` + `ReceiptController`.
- **FLAW 2** (loan/payroll state machines): done — `LoanStatus` has all 13 states incl. `guarantor_pending`, `committee_review`, `arrears` with `label()`/`color()`/`activeStatuses()`; `PayrollStatus` has `reconciled`/`variance`.
- **FLAW 3** WASTE 1–7: all done — deposit auto-approve rule (`SavingsService::shouldAutoApprove` + `Company::auto_approve_deposit_limit` + `is_fraud_flagged`), `CompileAndLockPayroll`, Unified Onboarding Wizard (`/admin/onboarding`, 3 sheets), polymorphic DB cart (`carts.actor_type`), `DocumentService`, public guarantor `accept_token` (`/guarantee/{token}` + `ExpireGuarantorInvites` job), import idempotency (`import_batch_id` + `external_reference` + `import_logs`).
- **FLAW 4** (data integrity & security): done — hash-chained immutable ledger + CBN chart (36 accounts), `must_change_password`, TOTP 2FA (setup/challenge/recovery), rate limiting (`throttle:login` 5/min, `throttle:uploads`, `password-reset`, `global`), IP/UA logging on approvals, `lockForUpdate` in `SavingsService`.
- **FLAW 5** (world-class screens): done — Dashboard command center (KPI cards, sparklines, trends, activity feed, `command/search` with reference parsing `REG/`,`SAV/`,`LOAN/`,…), Member 360 (`healthScore()`, loan timeline), smart loan wizard (3× savings cap, guarantor exposure, amortization chart), payroll reconciliation summary + arrears, member portal PWA (bottom nav, `manifest.json` + `sw.js`), loan lifecycle timeline, A/R/N + `/` keyboard shortcuts.

**Conclusion:** the in-repo FLAW-based audit has no remaining unaddressed items. The P1/P2/P3 changelog references correspond to already-shipped features (3.7–3.16 changelog rows); if that numbered list exists elsewhere the user may supply it later, but nothing points to unfinished work.

---

## 4. Pending Backlog (not started)

> Tasks below are inferred from the codebase and the audit trail referenced in `APP-GUIDE.md` changelog. They are **not** a formal product spec — re-confirm priority with the user before starting.

| # | Task | Why / Evidence | Effort |
|---|------|----------------|--------|
| P-06 | *(nothing queued — re-confirm priorities with the user before starting new work)* | — | — |

---

## 5. Work Log (recent completed work)

| Date | Commit | Feature |
|------|--------|---------|
| 2026-08-13 | *(pending)* | **Security hardening + scheduled finance commands (P-06)** — NoBackDating middleware, DB CHECK constraints, finance rate limiting, role-forced 2FA, DatabaseBackupService + encrypted backup command, scheduled hash-chain verification / provisioning / control reconciliation with notifications, full test coverage |
| 2026-08-09 | `6e8247f` | **JS unit test coverage + command-palette module extraction (P-05)** — `command-palette.js` ES module, Vitest + happy-dom harness, 36 JS tests (APP-GUIDE 3.20) |
| 2026-08-09 | `102c7c9` | **Member portal keyboard shortcuts + member-scoped quick search (P-04)** — command palette on portal layout, A/R guarantor actions, `/my/search` endpoint (APP-GUIDE 3.19, USER-GUIDE 1.2) |
| 2026-08-09 | `eedc5f6` | **APP-GUIDE 3.18 documentation refresh (P-01)** — guide brought back in line with the codebase |
| 2026-08-09 | `d037242` | **USER-GUIDE 1.1 + APP-GUIDE 3.18.1 (P-02)** — registration/onboarding documentation corrections |
| 2026-08-09 | `a69b441` | **Audit backlog reconciliation (P-03)** — FLAW v4.0 audit found in-repo; all items already implemented |
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
npm run test:js        # Vitest + happy-dom JS unit tests (DoD verify step for JS changes)
npm run build          # Vite production build (Tailwind/Alpine/JS)
npm run dev            # Vite (Tailwind/Alpine dev)
composer run dev       # serve + queue + pail + vite together
php artisan migrate:fresh --seed   # rebuild demo DB (destructive — data loss!)
php artisan branding:seed          # re-import branding assets
```

**Key paths:** routes in `routes/web.php` (249 declarations, groups: public → guest → auth → admin → ledger/finance → portal). Views in `resources/views/`. Tests in `tests/Feature` / `tests/Unit`. Business logic split between `app/Services` (financial engine) and `app/Actions` (domain actions).

**Read first:** `AGENTS.md` (DoD ritual) → this file → `APP-GUIDE.md` (features) → `SYSTEM-DOCUMENTATION.md` (how the code works).
