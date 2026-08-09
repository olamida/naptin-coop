# NAPTIN Staff Thrift Cooperative Society — System Documentation

> **For programmers.** This document describes how the application actually works internally — the skeleton, request flow, layers, routing, database, ledger internals and conventions — so a developer can understand the system and replicate it.
>
> **Stack (verified 2026-08-09):** PHP 8.3 · Laravel **13.22** · MySQL 8 · Tailwind CSS **4** (`@tailwindcss/vite`) · Alpine.js 3.15 (CDN in Blade, plus bundled JS in `resources/js/app.js`) · Chart.js 4.4 · Spatie Permission 8.3 · Maatwebsite Excel · barryvdh/laravel-dompdf · bacon/bacon-qr-code · Livewire 4 (present but the app is Blade+Alpine) · PHPUnit 12.
>
> **Sister docs:** `APP-GUIDE.md` (features/roles/changelog), `USER-GUIDE.md` (end users), `PENDING-TASKS.md` (AI session ledger).

---

## 1. High-Level Architecture

```
Browser (Tailwind UI + Alpine.js)
        │  HTTPS requests
        ▼
routes/web.php  ── 248 route declarations, 258 named routes
        │
        ▼
Middleware pipeline  (bootstrap/app.php aliases)
        │
        ▼
Controllers  (41)  ──  thin: validate (Form Requests) + orchestrate
        │                        │
        │   write ops            │   reads
        ▼                        ▼
app/Actions  (28 domain actions)   Eloquent Models (38)
app/Services (11 business engine)         │
        │                                  │
        ▼                                  ▼
        └──────────────►  MySQL (71 migrations, 45 tables)
```

**Design principles in this codebase:**

1. **Thin controllers.** Controllers validate input and call one collaborator. Write-side business rules live in `app/Actions` (single-responsibility "use cases") and in `app/Services` (the financial/ledger engine). Fat Eloquent models are avoided for money logic.
2. **Double-entry ledger is the source of truth for money.** Sub-ledgers (`savings_accounts`, `loans`, `share_accounts`, `purchase_orders`) drive day-to-day screens, but every movement that matters is also **posted** to `journal_entries` through `LedgerService`. Control-account reconciliation (`LedgerService::validateControlAccounts`) and the period-close pre-checks verify the two agree.
3. **Immutability is enforced at two levels:** application (posted entries are never updated — only reversed) and database (MySQL triggers reject UPDATE/DELETE on posted rows).
4. **Money arithmetic goes through `App\Support\Money`** (bcmath) — never raw floats — to keep ledgers exact to the kobo.

---

## 2. Directory Tree (authoritative)

```
app/
├── Actions/                          # 28 single-purpose use-cases
│   ├── Dividends/    ApproveDividend, CalculateDividend, DeclareDividend, DistributeDividend
│   ├── Loans/        AddLoanNote, ApproveLoan, CreateLoan, DisburseLoan, RecordRepayment,
│   │                 RejectLoan, UpdateGuarantor
│   ├── Membership/   ApproveMemberApplication, BulkUpdateStatus, CreateMember, RejectMemberApplication
│   ├── Payroll/      CompileAndLockPayroll, DestroyArrear, SettleArrear, StoreAllArrears, StoreArrear
│   ├── Savings/      ApproveDeposit, ApproveWithdrawal, PostDeposit, RejectDeposit,
│   │                 RejectWithdrawal, RequestWithdrawal
│   ├── Shares/       PurchaseShares
│   └── Action.php                    # abstract base (transactional run())
├── Console/Commands/                 # DetectLoanArrears, GenerateBrandingVariants, SeedBrandingAssets
├── Enums/                            # 8 PHP enums: GuarantorStatus, LoanStatus, LoanType,
│                                     # MemberStatus, PaymentMethod, PayrollStatus,
│                                     # SavingsTransactionType, ShareTransactionType
├── Exports/                          # 8 Maatwebsite Excel exports (incl. FinanceReportExport
│                                     # generic array export, OnboardingTemplateExport)
├── Http/
│   ├── Controllers/                  # 41 (incl. base Controller, StreamsReportExports trait)
│   │   ├── Auth/            SessionController, PasswordResetLinkController, NewPasswordController
│   │   ├── Admin/           BackupController, NotificationController, StatisticsController,
│   │   │                    StockController, UserController
│   │   └── (root)           Member, Savings, Loan, Share, Product, Purchases, Payroll, Dividend,
│   │                        Finance, Ledger, Report, Receipt, Invoice, Cart, Settings, Region, Role,
│   │                        LoanProduct, Dashboard, DataImport, BrandingAsset, Broadcast,
│   │                        Guarantee, Home, MemberPortal, NextOfKin, Onboarding, Profile,
│   │                        Registration, TwoFactor
│   ├── Middleware/                   # 8, aliased in bootstrap/app.php (see §4)
│   └── Requests/                     # 9 Form Request classes (StoreMemberRequest, StoreLoanRequest, …)
├── Imports/                          # 9 Maatwebsite imports (Member, Savings, LoanRepayment,
│                                     # Product, Purchase, PayrollDeduction, Onboarding,
│                                     # OpeningSavings, OpeningShares)
├── Mail/                             # WelcomeEmail
├── Models/                           # 38 Eloquent models (list in §6)
├── Notifications/                    # 14 classes (see APP-GUIDE §10 for the 7 core ones; plus
│                                     # BroadcastNotification, DepositRecordedNotification,
│                                     # DividendDeclaredNotification, MemberRegisteredNotification,
│                                     # PayrollCompiledNotification, SharePurchasedNotification,
│                                     # WithdrawalRequestedNotification)
├── Policies/                         # 1 (loan policy)
├── Providers/                        # AppServiceProvider, ViewServiceProvider
├── Services/                         # 11
│   ├── ApprovalService.php           # maker-checker workflow engine
│   ├── BrandingService.php           # assets, GD variants, cache, favicon/PWA sync
│   ├── CartService.php               # DB-backed carts (polymorphic actor)
│   ├── DocumentService.php           # 9 printable receipt/statement builders
│   ├── HirePurchaseService.php       # flat-principal HP schedules + instalment payments
│   ├── LedgerService.php             # double-entry engine (24 public methods)
│   ├── LedgerSyncService.php         # opening-balance conversion
│   ├── LoanService.php               # repayment calc, number gen, product validation
│   ├── ProvisioningService.php       # IFRS 9 / CBN provisioning buckets
│   ├── ReportExportService.php       # canonical SHA-256 + GD QR for exports
│   └── SavingsService.php            # atomic deposit/withdrawal with row locking
├── Support/
│   ├── Money.php                     # bcmath money arithmetic
│   ├── BrandingImage.php             # GD image helpers
│   └── NotificationLinks.php         # safe action URLs for notifications

bootstrap/app.php                     # Application bootstrap + middleware aliases + exception render
database/
├── migrations/                       # 71 files (in chronological batches)
└── seeders/                          # DatabaseSeeder, PermissionsSeeder (35 perms, 7 roles),
                                     # LedgerAccountsSeeder (36-account CBN chart, idempotent),
                                     # ApprovalWorkflowSeeder, BrandingAssetSeeder, DemoDataSeeder
public/
resources/
├── css/app.css                       # Tailwind 4 CSS-first config (no tailwind.config.js)
├── js/app.js                         # Alpine: memberFormSearch, searchAutocomplete,
│                                     # window.commandPalette, toast, cart AJAX, money formatters
└── views/                            # 141 Blade templates
    ├── components/                   # 14 reusable components (app-layout, portal-layout,
    │                                 # public-layout, command-palette, member-combobox,
    │                                 # member-search, search-autocomplete, stat-card,
    │                                 # status-badge, breadcrumb, stepper, empty-state,
    │                                 # report-export-buttons, show-all-toggle)
    └── (module dirs)                 # admin, auth, cart, dashboard, dividends, finance,
                                      # invoices, loans, members, payroll, portal, products,
                                      # profile, purchases, receipts, reports, savings, shares, emails
routes/web.php                        # 248 routes (all web traffic)
tests/                                # 36 Feature + 2 Unit files (PHPUnit)
```

---

## 3. Request Lifecycle (a worked example)

Take **"record a savings deposit"** (`POST /savings/deposit`):

1. `routes/web.php` — the route sits inside the `auth` + `enforce-single-session` + `throttle:global` group, and inside the inner `admin-only` + `must-change-password` + `two-factor` group (§4). It maps to `SavingsController@storeDeposit`.
2. Middleware chain runs in order: session/auth → single-session → throttle → admin-only (redirects portal-only users) → must-change-password → 2FA challenge.
3. `SavingsController::storeDeposit()`:
   - Validates with `StoreDepositRequest` (rules: member, amount, evidence file).
   - Delegates to `App\Actions\Savings\PostDeposit` (the write path) → `SavingsService::recordDeposit()`.
   - `SavingsService` locks the member's `savings_accounts` row (`lockForUpdate`), computes `balance_after` via `Money`, writes the transaction, and calls `LedgerService::postSavingsDeposit()` to post **Dr Cash / Cr Members Savings**.
   - Controller redirects with a flash message + points to the deposit receipt (`DocumentService::savingsDepositReceipt`).
4. Blade renders the target page inside `app-layout` (admin) with the sidebar, flash, and toast.

**The two orchestration styles:**

- **Actions** (`app/Actions/*`): used for compound use-cases (loan approval, payroll compile, dividend calculate) — each extends the abstract `Action` base which wraps `run()` in a DB transaction. Call as `ApproveLoan::run($loan, $user, $notes)`.
- **Services**: used for engine-like logic that is called from many places (Savings/Loan/Ledger/Approval services) — pure classes resolved via the container.

---

## 4. Middleware Pipeline

Aliases registered in `bootstrap/app.php` (Laravel 13 convention):

| Alias | Class | Purpose |
|-------|-------|---------|
| `admin-only` | `MemberGuard` | Blocks portal-only members from admin routes |
| `portal-member` | `PortalMember` | Requires the user to have a linked `member_id` (portal) |
| `enforce-single-session` | `EnforceSingleSession` | One active session per account (`users.active_session_token`) |
| `must-change-password` | `MustChangePassword` | Forces password reset for accounts created/reset by admin |
| `two-factor` | `RequireTwoFactor` | TOTP challenge gate when enabled |
| `module.enabled:shares\|dividends` | `ModuleEnabled` | Blocks access to disabled modules (Settings → Modules) |
| `prevent-cache` | `PreventCache` | No-store headers on auth pages |
| `log-login` | `LogLogin` | Writes `activity_logs` rows + `last_login_at` |
| `role` / `permission` / `role_or_permission` | Spatie | RBAC middleware |

**Route group nesting** (the security skeleton):

```
public (home, shop, about, health, guarantee token, guest auth pages)
 └─ guest group            → login, register, forgot/reset password
 auth + enforce-single-session + throttle:global
 ├─ profile / two-factor / force-password routes
 ├─ admin-only + must-change-password + two-factor
 │   ├─ dashboard + command/search
 │   ├─ members, savings, loans, shares(module), purchases, payroll,
 │   │  cart, products, dividends(module), next-of-kin, invoices,
 │   │  reports, admin/* (manage, onboarding, settings, branding, users,
 │   │  loan-products, roles, regions, stock, backup, statistics,
 │   │  broadcasts, notifications)
 │   ├─ ledger/*        (can:manage-users)
 │   ├─ finance/*       (can:manage-users)
 │   └─ receipts/*
 └─ portal-member + must-change-password + two-factor
     └─ my/*            (member portal)
```

Routes are **permission-gated** via `@can` in Blade and `middleware('can:...')` / Spatie on the routes. The `manage-users` permission gates all `/ledger` and `/finance` access.

---

## 5. Layering & Conventions

### 5.1 Controller → Action → Service → Model

- **Controller:** `validate()` (Form Request), then a single call to an Action or Service, then redirect/JSON. Returns JSON for the `*SearchJson` autocomplete endpoints.
- **Action:** a single public static `run(...)`; wraps business logic in a transaction (via the abstract `Action` base). One file per use-case keeps git history readable.
- **Service:** stateless, container-resolved engine logic. All money math routes through `App\Support\Money`.
- **Model:** Eloquent for persistence + relationships. No money arithmetic. Accessors for display (e.g. `Member::getFullNameAttribute()` collapses whitespace).

### 5.2 Form Requests

9 validation classes in `app/Http/Requests` (`StoreMemberRequest`, `StoreLoanRequest`, `StoreDepositRequest`, `StoreWithdrawalRequest`, `StoreRepaymentRequest`, `StoreProductRequest`, `StoreOrderRequest`, `StoreUserRequest`, `CompilePayrollRequest`). Authorization is role/permission-based in controllers (via `Gate`/`@can`), not inside the requests.

### 5.3 Blade composition

- **Layouts:** `components/app-layout` (admin, dark slate sidebar + accordion state via root `x-data`), `components/portal-layout` (member, blue-gradient sidebar + notification dropdown), `components/public-layout` (homepage/guest).
- **Partial composition:** `@extends`/`@section` via layouts; `@include` for fragments; Blade **components** (`<x-stat-card>`, `<x-status-badge>`, `<x-breadcrumb>`, `<x-member-combobox>`, `<x-search-autocomplete>`, `<x-report-export-buttons>`) for reusable UI.
- **Data for the layout** (regions, cart badge, notifications, command palette flags) is injected by `ViewServiceProvider` view composers and by `app-layout`'s root Alpine component.
- **JS behaviours** are attached through Alpine `x-data` calling globals defined in `resources/js/app.js` (`window.memberFormSearch`, `window.searchAutocomplete`, `window.commandPalette`, `window.toast`, cart helpers). The TALL CDN bundle is not the only source — `app.js` is compiled by Vite and is authoritative.

### 5.4 The command palette & search

- `window.commandPalette` (in `app.js`) is instantiated in `components/command-palette.blade.php`, included once in `app-layout`. `/` and `Ctrl/Cmd+K` open it; it fetches `/command/search` (JSON from `DashboardController@commandSearchJson`).
- Global keyboard shortcuts: `N` (new member, when permitted), `A` (approve), `R` (reject) — gated by `firstShortcut()` which only "sees" **visible** `[data-shortcut]` elements, so shortcuts never fire on hidden tabs.
- The reusable `<x-search-autocomplete>` combobox backs the server-side `search` JSON endpoints on Members, Loans, Savings, Shares, Products/Orders, Purchases, the public Shop and the portal catalog.

---

## 6. Data Model (45 tables, 71 migrations)

### 6.1 Model inventory (38)

```
Membership:  Member, Region, Position, MemberPosition, NextOfKin
Savings:     SavingsAccount, SavingsTransaction
Loans:       Loan, LoanProduct, LoanGuarantor, LoanRepayment,
             LoanRepaymentSchedule, LoanApprovalLog, LoanLossProvision
Shares:      ShareAccount, ShareTransaction
Commerce:    Product, PurchaseOrder, HirePurchaseSchedule, Cart
Dividends:   Dividend, DividendDistribution
Payroll:     MonthlyPayroll, PayrollDeduction, PayrollArrear
Ledger:      ChartOfAccount, JournalEntry, JournalEntryLine, PeriodClose,
             ApprovalWorkflow, PendingApproval, CashCount
System:      User, Company, BrandingAsset, BroadcastMessage, ImportLog, ActivityLog
```

### 6.2 Money

Every monetary column is `decimal(15,2)`. **All** arithmetic uses `App\Support\Money` (bcmath): `add/sub/mul/div/percent`, compare helpers, `min/max/sum`. Operands are normalised to two-decimal strings; multiplications/divisions run at 10-decimal precision to avoid float drift.

### 6.3 The Ledger (the part to study before extending)

- **Accounts:** `chart_of_accounts` (code, name, type, subtype, normal_side, `is_control_account`/`control_module`, `allow_manual_entry`, parent). The full **36-account CBN MFB chart** is seeded by `LedgerAccountsSeeder` (idempotent, data-safe) and `LedgerService::ensureAccount` auto-creates any code on demand. Codes: 1001 Cash … 1501 Payroll Deductions Receivable; 2001 Members Savings, 2101 Share Capital, 2201 Dividend Payable, 3001 Retained Earnings, 3002 Education Fund, 3003 General Reserve; 4001–4007 income (4004 Processing Fees, 4005 Sales Margin); 5001–5008 expenses (5004 Loan Loss Expense).
- **Posting:** `LedgerService::post($date, $description, [[code, debit], …], $reference)` validates debit=credit, resolves/creates accounts, posts header + lines, computes the hash chain. Domain post helpers (`postSavingsDeposit`, `postLoanDisbursement`, `postPayrollCompilation`, `postHirePurchaseInstalment`, `postCashSale`, `postDividendAccrual`, …) wrap the generic `post()` with the right accounts.
- **Hash chain:** each posted `journal_entries` row stores `uuid`, `prev_hash`, `hash = SHA-256(uuid|entry_number|period|prev_hash|id)`. `verifyHashChain()` recomputes every hash; the Audit Trail page flags tampering.
- **DB triggers:** `prevent_journal_entry_update` / `prevent_journal_entry_delete` reject UPDATE/DELETE on posted rows at the database level (irreversible safety net).
- **Reversal:** never mutates the original — posts an opposite entry linked via `reversal_of_id`; reversals cannot themselves be reversed.
- **Period close:** `period_closes` per `Y-m`. Postings to a closed period are rejected. First close posts CBN appropriations once (25% net profit → 3003, 2.5% → 3002). Reopen requires a reason and a `period_reopen` maker-checker approval (two distinct approvers).
- **Maker-checker:** `approval_workflows` (key, required_permission, required_roles, threshold_amount) + `pending_approvals`. `ApprovalService::requiresApproval()/request()/approve()` enforces distinct approvers and the permission gate. Workflows seeded: `loan_disbursement`, `dividend_declaration`, `period_reopen`, `savings_withdrawal` (high-value > ₦100,000).
- **Control reconciliation:** `validateControlAccounts()` compares each control account balance against its sub-ledger (`2001` vs `Σ savings_accounts.balance`, `1101` vs `Σ loans.outstanding`, `2101` vs shares, `1201` vs hire-purchase outstanding). `LedgerSyncService` posts only the **delta** as an opening-balance entry (plugged to `3001`) so re-running is a no-op.
- **Provisioning:** `ProvisioningService` ages loans (0–30 → 1%, 31–60 → 25%, 61–90 → 50%, 91–180 → 75%, >180 → 100%) and posts the **net movement** (Dr 5004 / Cr 1205) so repeated runs converge. Per-loan snapshots keyed `(loan_id, period)`.
- **Traceability:** `savings_transactions.journal_entry_id`, `loan_repayments.fees_portion`, and `import_batch_id`/`external_reference` on loans/purchase_orders/dividends give full audit trails back to the source.

### 6.4 Key business rules (server-side, enforced — do not soften)

| Rule | Where |
|------|-------|
| Loan eligibility ceiling = `min(savings balance × 3, ₦5,000,000)` | `LoanService::validateLoanProduct()` |
| Guarantor exposure cap ₦500,000 (aggregate accepted guarantees) | `LoanService::validateLoanProduct()` |
| CBN single-obligor ≤ 5% of outstanding portfolio | `LoanService::validateLoanProduct()` |
| Dividend declaration requires balanced trial balance + provision coverage ≥ 100% | `DividendController::assertDividendEligible()` |
| High-value withdrawal (> ₦100,000) needs maker-checker | `SavingsService`/`ApprovalService` |

---

## 7. Frontend Build

- **Tailwind CSS 4** configured CSS-first in `resources/css/app.css` (`@import "tailwindcss"` + theme vars) and compiled by `@tailwindcss/vite` — there is **no** `tailwind.config.js`.
- **Vite:** `vite.config.js` with the `laravel-vite-plugin`. Dev: `npm run dev` / `composer run dev`. Build: `npm run build`.
- **Alpine.js 3.15** loaded via NPM packages (`alpinejs`, `@alpinejs/focus`) and started by `resources/js/app.js` (`Alpine.start()`). `@alpinejs/focus` powers the `x-trap` modal focus-lock in the command palette.
- **Fonts/icons:** `@fontsource/inter`, `@fontsource/jetbrains-mono`, `material-symbols` (all bundled via Vite/Google fonts).
- **Charts:** Chart.js 4.4 via CDN on dashboard/loan-wizard pages.
- **Receipts/PDFs:** receipts are self-contained Blade (own inline CSS — no Tailwind dependency); financial-report PDFs are DomPDF (`ReportExportService` builds a canonical JSON → SHA-256 hash → GD-rendered PNG QR embedded via bacon-qr-code).

---

## 8. Tests (PHPUnit 12)

`composer test` = `php artisan config:clear` + `php artisan test`.

- **`tests/Feature` (36 files)** — behaviour/regression tests by domain: `MakerCheckerTest`, `PeriodCloseChecksTest`, `PeriodReopenApprovalTest`, `CbnComplianceTest`, `LoanWizardRulesTest`, `LoanLifecycleTimelineTest`, `ProvisioningTest`, `LedgerImmutabilityTest`, `LedgerSyncTest`, `LedgerChartOfAccountsTest`, `TraceabilityTest`, `PayrollLedgerPostingTest`, `HirePurchaseScheduleTest`, `CashCountTest`, `SavingsControlReportTest`, `ReportExportTest`, `SearchAutocompleteTest`, `ModuleToggleTest`, `DynamicCartTest`, `OnboardingImportTest`, `MemberFormSearchTest`, `MemberPhotoUploadTest`, `TermiiSmsChannelTest`, `SocietyExpenseTest`, `StockAdjustmentTest`, `SavingsServiceTest`, `ShareReceiptTest`, `PortalDashboardVerifyTest`, `LoanShowPageGuarantorStatusTest`, `LoanProcessingFeeAndDividendAccrualTest`, etc.
- **`tests/Unit` (2 files)** — `MoneyTest` (bcmath arithmetic), `ExampleTest`.
- Style: `vendor/bin/pint`.

---

## 9. Adding a Feature (the house style)

1. **Route** in `routes/web.php` inside the correct permission group; name it (`->name('module.action')`).
2. **Controller** method → validate via a Form Request → call an `Action` or `Service` → redirect/flash or JSON.
3. **Write logic** in `app/Actions/<Domain>/` for compound use-cases (wrap in the `Action` transaction base) or a `Service` for reusable engine logic. Use `App\Support\Money` for money.
4. **If money moves:** post a balanced journal through `LedgerService` (reuse a `post*` helper or add one). Consider maker-checker via `ApprovalService` if it is high-value or governance-sensitive.
5. **Blade:** a page under `resources/views/<module>/`, wrapped in `app-layout` (admin) or `portal-layout` (portal). Use the shared components. Add `data-shortcut` attributes for approve/reject actions so keyboard shortcuts work.
6. **Tests:** add a `tests/Feature/<Feature>Test.php`. Run `composer test` + `vendor/bin/pint`.
7. **Document:** update `APP-GUIDE.md` (changelog row + module reference) and `USER-GUIDE.md`/`PENDING-TASKS.md` as relevant, then DoD commit per `AGENTS.md`.

---

## 10. Replication Checklist (build a sibling cooperative system)

1. `composer create-project laravel/laravel` + this stack (see header).
2. Apply the 71 migrations in dependency order (auth → config → regions/positions → members → savings/shares/loans/products → ledger → compliance → traceability).
3. Seed: `PermissionsSeeder` → `LedgerAccountsSeeder` → `ApprovalWorkflowSeeder` → `BrandingAssetSeeder` → `DatabaseSeeder`/`DemoDataSeeder`.
4. Copy the `app/Services` layer verbatim — it is the financial engine (Money + LedgerService + SavingsService + LoanService + ProvisioningService + ApprovalService are portable).
5. Port `app/Actions`, `app/Http/Controllers`, `app/Support`, `resources/views/components` and `resources/js/app.js`.
6. Wire middleware aliases in `bootstrap/app.php` exactly as in §4.
7. Configure the MySQL `prevent_journal_entry_*` triggers for ledger immutability.
8. Run `composer test`; then replace branding/seed data with the new society's identity.
