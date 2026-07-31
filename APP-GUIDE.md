# NAPTIN Staff Thrift Cooperative Society — Application Guide

> **Version:** 3.1
> **Platform:** Laravel 13 + Tailwind CSS + MySQL 8
> **URL:** `http://localhost/dev-angle/Starter-folder/naptin-coop/public`
> **Login:** `admin@naptin.coop` / `password`
> **Member Login:** `member@naptin.coop` / `password`

---

## Table of Contents

1. [Application Architecture](#1-application-architecture)
2. [Database Schema](#2-database-schema)
3. [Module Reference](#3-module-reference)
4. [Workflows & Processes](#4-workflows--processes)
5. [User Guide by Role](#5-user-guide-by-role)
6. [Admin Guide](#6-admin-guide)
7. [Receipts & Printing](#7-receipts--printing)
8. [Member Self-Service Portal](#8-member-self-service-portal)
9. [Data Import & Export](#9-data-import--export)
10. [Notifications](#10-notifications)

---

## 1. Application Architecture

### Directory Structure

```
naptin-coop/
├── app/
│   ├── Enums/                          # 8 PHP enums for all statuses
│   │   ├── GuarantorStatus.php         # pending, accepted, declined
│   │   ├── LoanStatus.php              # pending, approved, disbursed, repaying, completed, rejected, defaulted
│   │   ├── LoanType.php                # regular, emergency, educational, special
│   │   ├── MemberStatus.php            # active, inactive, retired, suspended
│   │   ├── PaymentMethod.php           # cash, bank_transfer, salary_deduction, savings_deduction
│   │   ├── PayrollStatus.php           # draft, compiled, deducted, completed
│   │   ├── SavingsTransactionType.php  # deposit, withdrawal, interest, transfer
│   │   └── ShareTransactionType.php    # purchase, sale, transfer, dividend
│   │
│   ├── Exports/                        # 6 Maatwebsite Excel exports
│   │   ├── LoansExport.php
│   │   ├── MembersExport.php
│   │   ├── PayrollDeductionExport.php
│   │   ├── PayrollUploadTemplateExport.php
│   │   ├── SavingsExport.php
│   │   └── SharesExport.php
│   │
│   ├── Http/Controllers/               # 22 controllers
│   │   ├── Auth/
│   │   │   ├── SessionController.php           # Login / Logout
│   │   │   ├── PasswordResetLinkController.php # Forgot password email
│   │   │   └── NewPasswordController.php       # Reset password with token
│   │   ├── AdminController.php         # Users CRUD, stock management, backup, statistics
│   │   ├── CartController.php          # Session-based shopping cart (admin side)
│   │   ├── DashboardController.php     # Dashboard stats + charts + trends
│   │   ├── DataImportController.php    # Central import hub
│   │   ├── DividendController.php      # Declare → calculate → approve → distribute
│   │   ├── InvoiceController.php       # Printable purchase invoice
│   │   ├── LoanController.php          # Loan CRUD + approve + reject + disburse + repayment + import/export
│   │   ├── LoanProductController.php   # Loan product CRUD (admin)
│   │   ├── MemberController.php        # Member CRUD + import/export + detail views
│   │   ├── MemberPortalController.php  # Member self-service portal
│   │   ├── NextOfKinController.php     # Add/remove next of kin
│   │   ├── PayrollController.php       # Compile + upload + export + 5 sub-reports
│   │   ├── ProductController.php       # Product CRUD + purchase orders
│   │   ├── ProfileController.php       # User profile + password + photo (layout switches by role)
│   │   ├── PurchasesController.php     # Purchase order management + import
│   │   ├── ReceiptController.php       # 8 printable receipt/statement types
│   │   ├── RegionController.php        # Regional center CRUD
│   │   ├── ReportController.php        # Printable member status reports
│   │   ├── RoleController.php          # Roles & permissions CRUD
│   │   ├── SavingsController.php       # Deposit + withdraw + approval workflow + import/export
│   │   ├── SettingsController.php      # Company settings (branding, contact, content, financial)
│   │   └── ShareController.php         # Share purchase + accounts + export
│   │
│   ├── Http/Requests/                  # 9 Form Request validation classes
│   │   ├── CompilePayrollRequest.php
│   │   ├── StoreDepositRequest.php
│   │   ├── StoreLoanRequest.php
│   │   ├── StoreMemberRequest.php
│   │   ├── StoreOrderRequest.php
│   │   ├── StoreProductRequest.php
│   │   ├── StoreRepaymentRequest.php
│   │   ├── StoreUserRequest.php
│   │   └── StoreWithdrawalRequest.php
│   │
│   ├── Imports/                        # 6 Maatwebsite Excel imports
│   │   ├── LoanRepaymentImport.php
│   │   ├── MemberImport.php
│   │   ├── PayrollDeductionImport.php
│   │   ├── ProductImport.php
│   │   ├── PurchaseImport.php
│   │   └── SavingsImport.php
│   │
│   ├── Models/                         # 24 Eloquent models
│   │   ├── ActivityLog.php
│   │   ├── Company.php
│   │   ├── Dividend.php
│   │   ├── DividendDistribution.php
│   │   ├── Loan.php
│   │   ├── LoanApprovalLog.php
│   │   ├── LoanGuarantor.php
│   │   ├── LoanProduct.php
│   │   ├── LoanRepayment.php
│   │   ├── LoanRepaymentSchedule.php
│   │   ├── Member.php
│   │   ├── MemberPosition.php
│   │   ├── MonthlyPayroll.php
│   │   ├── NextOfKin.php
│   │   ├── PayrollDeduction.php
│   │   ├── Position.php
│   │   ├── Product.php
│   │   ├── PurchaseOrder.php
│   │   ├── Region.php
│   │   ├── SavingsAccount.php
│   │   ├── SavingsTransaction.php      # includes payment_evidence_path
│   │   ├── ShareAccount.php
│   │   ├── ShareTransaction.php
│   │   └── User.php
│   │
│   ├── Notifications/                  # 7 notification classes
│   │   ├── AdminPasswordResetNotification.php
│   │   ├── GuarantorRequestNotification.php
│   │   ├── LoanAppliedNotification.php
│   │   ├── LoanStatusNotification.php
│   │   ├── PasswordResetNotification.php
│   │   ├── SavingsDepositRequestNotification.php
│   │   └── WithdrawalStatusNotification.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── ViewServiceProvider.php      # View Composers for layout data (regions, notifications)
│   │
│   └── Services/                       # Business logic layer (fat models → thin)
│       ├── CartService.php             # Cart resolution, checkout processing, order numbers
│       ├── LoanService.php             # Interest calculation, loan number generation, product validation
│       └── SavingsService.php          # Atomic deposit/withdrawal with row locking
│
├── database/
│   ├── migrations/                     # 41 migration files
│   └── seeders/
│       ├── DatabaseSeeder.php          # Main seeder: regions, positions, roles, admin, 5 members, loan products, products
│       ├── PermissionsSeeder.php        # 35 permissions across 12 groups, 7 roles
│       └── DemoDataSeeder.php          # Demo loans, savings, payroll data
│
├── resources/views/                    # 90+ Blade templates
│   ├── admin/
│   │   ├── data-import/index.blade.php       # Central import hub view
│   │   ├── loan-products/                    # index, create, edit
│   │   ├── manage.blade.php                  # Admin dashboard (12 tiles, permission-gated)
│   │   ├── regions/                          # index, create, edit
│   │   ├── roles/                            # index, create, edit
│   │   ├── settings/edit.blade.php           # Company settings (tabbed: Branding, Contact, Content, Financial)
│   │   ├── statistics.blade.php              # Login stats, errors, charts
│   │   ├── stock.blade.php                   # Stock management
│   │   └── users/                            # index, create, edit
│   ├── auth/                                 # login, forgot-password, reset-password
│   ├── cart/                                 # index, checkout
│   ├── components/
│   │   ├── app-layout.blade.php              # Admin master layout with sidebar
│   │   ├── breadcrumb.blade.php              # Reusable breadcrumb component
│   │   ├── empty-state.blade.php             # Empty state placeholder
│   │   ├── portal-layout.blade.php           # Member portal layout (with notification dropdown)
│   │   ├── stat-card.blade.php               # Reusable stat card component
│   │   ├── status-badge.blade.php            # Color-coded status badge
│   │   └── stepper.blade.php                 # Multi-step progress indicator
│   ├── dashboard/index.blade.php             # Stats cards + Chart.js bar/doughnut
│   ├── dividends/                            # index, create, show
│   ├── emails/welcome.blade.php              # Welcome email template
│   ├── invoices/purchase.blade.php           # Printable purchase invoice
│   ├── loans/                                # index, create, show, repayment, import
│   ├── members/                              # index, create, show, edit, import, savings-detail, loans-detail, purchases-detail
│   ├── payroll/                              # index, compile, show, upload, 5 sub-reports
│   ├── portal/                               # dashboard, savings, loans, loan-apply, loan-detail, shares, purchases, order-products, cart, checkout, guarantors, notifications
│   ├── products/                             # index, create, edit, import, orders, show-order, create-order
│   ├── profile/
│   │   ├── edit.blade.php                    # Admin profile (app-layout)
│   │   └── member-edit.blade.php             # Member profile (portal-layout)
│   ├── purchases/                            # index, create, import
│   ├── receipts/                             # 8 receipt/statement templates
│   ├── reports/                              # index, member-status
│   ├── savings/                              # index, accounts, deposit, withdraw, pending-withdrawals, import
│   └── shares/                               # index, accounts, purchase
│
└── routes/web.php                      # 100+ routes across all modules
```

### Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Framework | Laravel | 13.x |
| CSS | Tailwind CSS | 3.x |
| UI Theme | Slate (`#0F172A` primary, slate palette, `rounded-[16px]`/`rounded-[10px]`, Material Symbols) | — |
| Charts | Chart.js | 4.4 |
| Fonts | Inter (Google Fonts) | — |
| Icons | Material Symbols (Google) | — |
| Database | MySQL | 8.x |
| Permissions | Spatie Laravel Permission | 8.3 |
| Excel | Maatwebsite Excel | 3.x |
| Auth | Laravel Breeze (session) | — |
| Alpine.js | Via CDN | 3.x |
| Services | Service Layer | 3 classes |

### Sidebar Navigation

```
┌─────────────────────────┐
│ NAPTIN Staff Thrift     │
│ Cooperative Society     │  (dynamic logo + name)
├─────────────────────────┤
│ Dashboard               │
│ Members                 │
│ Savings                 │
│ Loans                   │
│ Shares                  │
│ Purchases               │
│ Dividends               │
│ Payroll                 │
│ Reports                 │
├─────────────────────────┤
│ Member Portal           │  (if user has linked member)
│ My Account              │
├─────────────────────────┤
│ ADMINISTRATION          │  (if can manage-users)
│ Management              │
│   → Company Settings    │
│   → User Management     │
│   → Roles & Permissions │
│   → Regional Centers    │
│   → Loan Products       │
│   → Stock Management    │
│   → Products            │
│   → Data Import & Upload│
│   → Reports             │
│   → Database Backup     │
│   → Statistics          │
└─────────────────────────┘
```

All sidebar items are permission-gated using `@can` directives. The sidebar is dynamic — items appear/disappear based on the logged-in user's role. The sidebar uses the slate dark theme (`bg-[#0F172A]`) with white text icons and `rounded-[10px]` active/hover states.

---

## 2. Database Schema

### Core Tables (25 tables)

#### Membership Domain
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `regions` | 8 regional centers | name, code, state, zone, headquarters, enabled |
| `positions` | 11 positions (9 EXCO + 2 staff) | name, slug, is_executive |
| `members` | 23+ columns per member | staff_id (unique), region_id, first_name, last_name, monthly_salary, status, photo_path, user_id |
| `member_positions` | Position assignments | member_id, position_id, start_date, is_current |
| `next_of_kins` | Emergency contacts | member_id, name, relationship, phone, is_primary |

#### Savings Domain
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `savings_accounts` | One per member | member_id, account_number, balance |
| `savings_transactions` | All savings movements | savings_account_id, type, amount, balance_before, balance_after, reference, status, approved_by, approved_at, payment_evidence_path |

#### Loans Domain
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `loan_products` | Configurable loan types | name, slug, min/max_amount, interest_rate, max_term_months, requires_guarantors, max_loans_per_member, max_total_amount_per_member |
| `loans` | Individual loan records | member_id, loan_product_id, loan_number, type, amount, interest_rate, monthly_repayment, outstanding, status, admin_notes |
| `loan_repayments` | Repayment transactions | loan_id, member_id, amount, principal_portion, interest_portion, payment_method |
| `loan_repayment_schedules` | Amortization schedules | loan_id, installment_number, due_date, principal_amount, interest_amount, status |
| `loan_guarantors` | Guarantor assignments | loan_id, member_id, status (pending/accepted/declined), responded_at |
| `loan_approval_logs` | Full audit trail | loan_id, user_id, action, old_status, new_status, notes |

#### Shares Domain
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `share_accounts` | One per member | member_id, total_shares, total_value, share_price |
| `share_transactions` | Share movements | share_account_id, type, shares, amount, balance_after |

#### Products & Purchases Domain
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `products` | Available products | name, unit_price, stock_quantity, enabled, image_path |
| `purchase_orders` | Member purchase orders | member_id, product_id, order_number, order_group, quantity, total_amount, payment_type, monthly_repayment, status |

#### Dividends Domain
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `dividends` | Annual dividend records | year, total_profit, total_distributed, status |
| `dividend_distributions` | Per-member payout | dividend_id, member_id, share_count, amount, status |

#### Payroll Domain
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `monthly_payrolls` | Monthly payroll run | payroll_number, month, year, grand_total, status |
| `payroll_deductions` | Per-member deduction | monthly_payroll_id, member_id, expected_*, actual_*, total_expected, total_actual |

#### System
| Table | Purpose |
|-------|---------|
| `users` | System user accounts (with profile_photo_path, member_id, last_login_at) |
| `companies` | Singleton company settings (name, logo_path, banner_path, theme_color, secondary_color, description, short_history, social links, thrift_amount, footer_note, etc.) |
| `activity_logs` | Login/audit trail (user_id, event, description, ip_address, user_agent) |
| `roles` | Spatie permission roles |
| `permissions` | Spatie permission permissions (35 permissions) |
| `notifications` | Laravel database notifications |

### All Money Fields
Every monetary column uses `decimal(15,2)` — supports up to 999,999,999,999.99 Naira.

### Performance Indexes
A dedicated migration (`2026_07_26_000001_add_performance_indexes.php`) adds composite and single-column indexes on frequently queried columns:
- `savings_transactions`: composite index on (savings_account_id, type, status)
- `loans`: composite index on (member_id, status) and single index on (status, created_at)
- `loan_repayments`: index on (loan_id, created_at)
- `share_transactions`: index on (share_account_id, created_at)
- `purchase_orders`: composite index on (member_id, status) and index on (product_id, status)
- `payroll_deductions`: composite index on (monthly_payroll_id, member_id)
- `monthly_payrolls`: index on (status, year, month)
- `dividend_distributions`: composite index on (dividend_id, status) and index on (member_id, status)
- `activity_logs`: composite index on (user_id, event, created_at)

---

## 3. Module Reference

### Module A: Members

**Purpose:** Register and manage cooperative members.

| Route | Method | Action |
|-------|--------|--------|
| `/members` | GET | List all members (searchable, filterable by region/status) |
| `/members/create` | GET | Registration form |
| `/members` | POST | Create member + auto-create savings & share accounts + optional user account |
| `/members/{id}` | GET | Member profile (personal, savings, shares, loans, next-of-kin) |
| `/members/{id}/edit` | GET | Edit form |
| `/members/{id}` | PUT | Update member |
| `/members/{id}` | DELETE | Delete member (blocked if related data exists) |
| `/members/{id}/savings-detail` | GET | Full savings transaction history for member |
| `/members/{id}/loans-detail` | GET | Full loan history for member |
| `/members/{id}/purchases-detail` | GET | Full purchase history for member |
| `/members/{id}/next-of-kin` | POST | Add next of kin |
| `/members/{id}/next-of-kin/{kid}` | DELETE | Remove next of kin |
| `/members/import` | GET | Bulk import form |
| `/members/import` | POST | Import members from Excel/CSV |
| `/members/download-template` | GET | Download CSV template |
| `/members/export` | GET | Export members to Excel |
| `/members/bulk-status` | POST | Bulk update member statuses |

**On member creation, the system automatically:**
1. Creates a Savings Account (balance = ₦0)
2. Creates a Share Account (shares = 0, value = ₦0)
3. If email provided: creates User account with `member` role, sends welcome email with temporary password

---

### Module B: Savings

**Purpose:** Track member savings — deposits, withdrawals, and approval workflow.

| Route | Method | Action |
|-------|--------|--------|
| `/savings` | GET | All savings transactions (searchable, filterable by type/status) — **6 stat cards** |
| `/savings/accounts` | GET | All savings accounts with balances (sortable) — **sub-nav tabs** |
| `/savings/deposit` | GET | Deposit form (admin/teller) |
| `/savings/deposit` | POST | Record deposit (updates balance + creates transaction) |
| `/savings/withdraw` | GET | Withdrawal form (admin/teller) |
| `/savings/withdraw` | POST | Record withdrawal (creates pending request) |
| `/savings/pending-withdrawals` | GET | List of pending withdrawal & deposit requests — **sub-nav tabs** |
| `/savings/withdrawals/{id}/approve` | POST | Approve pending withdrawal (deducts balance) |
| `/savings/withdrawals/{id}/reject` | POST | Reject pending withdrawal (with reason) |
| `/savings/deposits/{id}/confirm` | POST | Approve pending deposit (credits balance) |
| `/savings/deposits/{id}/reject` | POST | Reject pending deposit (with reason) |
| `/savings/import` | GET | Bulk import form |
| `/savings/import` | POST | Import savings transactions from Excel |
| `/savings/download-template` | GET | Download CSV template |
| `/savings/export` | GET | Export savings to Excel |

**Transaction Reference Format:** `SAV/DEP/XXXXXXXX` (deposit), `SAV/WTH/XXXXXXXX` (withdrawal)

**Withdrawal Workflow:** Admin/teller withdrawal → status `pending` → treasurer approves → status `completed` + balance deducted. Reject option with reason.

**Deposit Workflow (Admin/Teller):** Record deposit → status `completed` + balance credited immediately. Optional payment evidence uploaded.

**Deposit Workflow (Member Portal):** Member requests deposit → status `pending` → treasurer approves (credits balance) or rejects. Payment evidence viewable by admin during review.

---

### Module C: Loans

**Purpose:** Loan application, approval, disbursement, and repayment with full audit trail.

| Route | Method | Action |
|-------|--------|--------|
| `/loans` | GET | List all loans (searchable, filterable by status) — **6 stat cards + sub-nav tabs** |
| `/loans/create` | GET | Application form (select member, loan product, amount, tenure) |
| `/loans` | POST | Submit application (auto-calculates monthly repayment, creates guarantors) |
| `/loans/{id}` | GET | Loan detail (repayment schedule, repayment history, approval log, guarantors) |
| `/loans/{id}/approve` | POST | Approve pending loan (checks all guarantors accepted) |
| `/loans/{id}/reject` | POST | Reject pending loan (with rejection reason) |
| `/loans/{id}/note` | POST | Add admin notes |
| `/loans/{id}/disburse` | POST | Disburse approved loan (sets maturity date) |
| `/loans/{id}/guarantors/{gid}` | POST | Update guarantor status (admin can accept/decline) |
| `/loans/{id}/repayment` | GET | Repayment form |
| `/loans/{id}/repayment` | POST | Record repayment (splits principal/interest, updates outstanding) |
| `/loans/import/repayments` | GET | Bulk import form |
| `/loans/import/repayments` | POST | Import loan repayments from Excel |
| `/loans/download-template` | GET | Download CSV template |
| `/loans/export` | GET | Export loans to Excel |

**Loan Lifecycle:**
```
pending → approved → disbursed → repaying → completed
    ↓           ↓           ↓
 rejected    rejected    defaulted (via admin status change)
```

**Loan Product Validation on Application (Admin & Portal):**
- Checks `max_loans_per_member` limit
- Checks `max_total_amount_per_member` limit
- Validates amount within min/max range
- Validates tenure within max term

**Monthly Repayment Calculation:**
```
monthly_repayment = (amount × (1 + interest_rate / 100)) / tenure_months
```
Example: ₦100,000 at 5% for 12 months → ₦105,000 / 12 = ₦8,750.00/month

The calculation is centralized in `App\Services\LoanService::calculateMonthlyRepayment()` and called by both `LoanController` and `MemberPortalController`.

**Repayment Split Logic:**
The system automatically splits each repayment into principal and interest portions based on the interest rate using the formula: `interest_portion = amount × (rate / (1 + rate))`.

**Full Audit Trail:** Every status change creates a `LoanApprovalLog` entry with user, action, old/new status, and notes.

---

### Module D: Shares

**Purpose:** Track member share holdings and purchases.

| Route | Method | Action |
|-------|--------|--------|
| `/shares` | GET | All share transactions (searchable, filterable by type) — **5 stat cards** |
| `/shares/accounts` | GET | All share accounts with totals (sortable) — **sub-nav tabs** |
| `/shares/purchase` | GET | Purchase form |
| `/shares/purchase` | POST | Record share purchase (updates account totals) |
| `/shares/export` | GET | Export shares to Excel |

**Share Price:** Configurable per account (default ₦100/share)

---

### Module E: Products & Purchases

**Purpose:** Manage cooperative products, shopping cart, and purchase orders (cash and hire purchase).

| Route | Method | Action |
|-------|--------|--------|
| `/products` | GET | List all products — **sub-nav tabs** |
| `/products/create` | GET | Add product form |
| `/products` | POST | Create product |
| `/products/{id}/edit` | GET | Edit product |
| `/products/{id}` | PUT | Update product |
| `/products/import` | GET | Bulk import form |
| `/products/import` | POST | Import products from Excel |
| `/products/download-template` | GET | Download CSV template |
| `/products/orders` | GET | List all purchase orders — **sub-nav tabs** |
| `/products/orders/create` | GET | New purchase order form |
| `/products/orders` | POST | Create order |
| `/products/orders/{group}` | GET | View order group detail |
| `/products/orders/{id}/approve` | POST | Approve pending order |
| `/products/orders/{id}/collect` | POST | Mark product as collected |
| `/invoices/purchase/{id}` | GET | **Printable invoice** (new window, print button) |

**Shopping Cart (Admin Side):**
| Route | Method | Action |
|-------|--------|--------|
| `/cart` | GET | View cart |
| `/cart/add` | POST | Add product to cart (AJAX supported) |
| `/cart/update` | POST | Update quantity |
| `/cart/remove` | POST | Remove item |
| `/cart/clear` | POST | Clear cart |
| `/cart/checkout` | GET | Checkout form (select member, payment type) |
| `/cart/checkout` | POST | Process checkout (creates orders, deducts stock) |

**Standalone Purchase Orders:**
| Route | Method | Action |
|-------|--------|--------|
| `/purchases` | GET | List all purchase orders (searchable, filterable) |
| `/purchases/create` | GET | Create purchase order (optional member pre-select) |
| `/purchases/import` | GET | Bulk import form |
| `/purchases/import` | POST | Import purchase orders from Excel |
| `/purchases/download-template` | GET | Download CSV template |

**Payment Types:**
- **Cash:** Order auto-approved, member pays and collects immediately
- **Hire Purchase:** Order starts as pending → approved → active → completed (monthly repayment)

**Stock Management:** Stock is automatically decremented when an order is created.

---

### Module F: Dividends

**Purpose:** Annual dividend declaration, calculation, and distribution.

| Route | Method | Action |
|-------|--------|--------|
| `/dividends` | GET | List all dividend records — **4 stat cards** |
| `/dividends/create` | GET | Declare new dividend (year + total profit) |
| `/dividends` | POST | Create dividend record |
| `/dividends/{id}` | GET | View dividend + distribution details |
| `/dividends/{id}/calculate` | POST | Calculate per-member distributions (pro-rata by shares) |
| `/dividends/{id}/approve` | POST | Approve calculated dividend |
| `/dividends/{id}/distribute` | POST | Mark all as paid |

**Dividend Workflow:**
```
draft → calculated → approved → completed
```

**Calculation Logic:**
`per_member_amount = (member_shares / total_shares) × total_profit`

---

### Module G: Payroll

**Purpose:** Monthly payroll deduction compilation, upload, and reporting with 5 sub-reports.

| Route | Method | Action |
|-------|--------|--------|
| `/payroll` | GET | List all payroll runs — **4 stat cards** |
| `/payroll/compile` | GET | Select year + month to compile |
| `/payroll/compile` | POST | Compile payroll |
| `/payroll/{id}` | GET | View payroll detail + navigation to sub-reports |
| `/payroll/{id}/upload` | GET | Upload form for actual deductions |
| `/payroll/{id}/upload` | POST | Upload Excel with actual deducted amounts |
| `/payroll/{id}/export` | GET | Export Excel with full deduction data |
| `/payroll/{id}/download-template` | GET | Download Excel template with expected amounts |
| `/payroll/{id}/report/savings` | GET | Savings-only deduction report |
| `/payroll/{id}/report/loans` | GET | Loan repayment deduction report |
| `/payroll/{id}/report/purchases` | GET | Purchase repayment deduction report |
| `/payroll/{id}/report/shares` | GET | Share contribution deduction report |
| `/payroll/{id}/report/summary` | GET | Full summary report |

**Payroll Compilation Logic (per active member):**
| Deduction | Calculation |
|-----------|-------------|
| Savings | 10% × monthly_salary |
| Share Contribution | 5% × monthly_salary |
| Loan Repayment | Active loan's monthly_repayment (if any) |
| Purchase Repayment | Active hire purchase's monthly_repayment (if any) |

**Payroll Lifecycle:**
```
compiled → deducted → completed
```

---

### Module H: Reports

**Purpose:** Generate printable member status reports.

| Route | Method | Action |
|-------|--------|--------|
| `/reports` | GET | Searchable member list |
| `/reports/member/{id}` | GET | **Printable status report** (new window, print button) |

**Report Includes:**
- Member personal & employment details with photo
- Savings balance with statement link
- Share count & value with certificate link
- **Full Loan History** (all loans, not just active — with color-coded status badges)
- **Full Purchase History** (all orders — with status badges)
- Monthly salary deduction breakdown:
  - Gross Monthly Salary
  - Individual active loan repayments (with loan number + product name)
  - Individual active hire purchase repayments (with order number + product name)
  - Savings deduction (dynamic rate %)
  - Deduction as % of salary
  - Net Take-Home Pay
- Print button + NAPTIN watermark + dynamic company footer

---

## 4. Workflows & Processes

### 4.1 Member Registration

```
Admin creates member
    → System auto-creates Savings Account (balance: ₦0)
    → System auto-creates Share Account (shares: 0, value: ₦0)
    → If email provided:
        → Creates User account with 'member' role
        → Sends WelcomeEmail with temporary password
    → Admin adds Next of Kin
    → Member can access self-service portal
```

### 4.2 Savings Deposit & Withdrawal Approval

```
DEPOSIT (Admin/Teller):
1. Admin/teller records deposit
   → Creates transaction with status: completed
   → Balance credited immediately
   → Optional: payment evidence uploaded

DEPOSIT (Member Portal):
1. Member requests deposit via portal
   → Creates transaction with status: pending
   → Balance NOT yet credited
   → Optional: payment evidence uploaded

2. Treasurer reviews pending deposits
   → Approve: credits balance, marks completed, sends notification
   → Reject: marks rejected with reason, sends notification

WITHDRAWAL:
1. Admin/teller submits withdrawal request
   → Creates transaction with status: pending
   → Balance NOT yet deducted

2. Treasurer reviews pending withdrawals
   → Approve: deducts balance, marks completed, sends notification
   → Reject: marks rejected with reason, sends notification
```

### 4.3 Loan Lifecycle with Guarantors

```
1. APPLICATION (Admin or Member Portal)
   Member applies → selects loan product → enters amount + tenure
   → System validates against loan product limits
   → System calculates monthly repayment WITH INTEREST:
     monthly_repayment = (amount × (1 + rate/100)) / tenure_months
   → If product requires guarantors: creates LoanGuarantor records
   → Sends GuarantorRequestNotification to each guarantor
   → Sends LoanAppliedNotification to treasurers/admins/loan officers
   → Creates LoanApprovalLog entry
   → Status: pending

2. GUARANTOR APPROVAL
   Guarantors accept/decline via:
   - Admin loan detail page (admin can accept on behalf)
   - Member portal guarantors page
   → All must accept before loan can be approved

3. APPROVAL
   Loan officer/EXCO reviews → approves or rejects
   → System checks all guarantors have accepted
   → Creates LoanApprovalLog entry
   → Sends LoanStatusNotification to member
   → Status: approved (or rejected with reason)

4. DISBURSEMENT
   Treasury confirms → disburses funds
   → Maturity date calculated
   → Creates LoanApprovalLog entry
   → Sends LoanStatusNotification to member
   → Status: disbursed

5. REPAYMENT
   Member pays (cash, bank transfer, salary deduction, or savings deduction)
   → System splits into principal + interest
   → Outstanding decreases
   → When outstanding reaches ₦0 → Status: completed

6. COMPLETION
   → Sends LoanStatusNotification (completed)
```

### 4.4 Shopping Cart Flow (Admin & Portal)

```
ADMIN SIDE:
1. Admin adds products to cart (session-based)
2. Admin proceeds to checkout
3. Admin selects member + payment type (cash/hire_purchase)
4. System creates purchase orders for each cart item
5. Stock is decremented for each item
6. Cash orders → auto-approved
   Hire purchase orders → pending for approval
7. Cart is cleared

MEMBER PORTAL:
1. Member browses products (grid view, search, sort, filter)
2. Member clicks "Add to Cart" (AJAX — no page refresh)
3. Live cart badge updates in top menu
4. Member views cart (AJAX-powered: update qty, remove items)
5. Member proceeds to checkout
6. Selects payment type (Cash or Hire Purchase)
7. Confirms order → same processing as admin side
```

### 4.5 Monthly Payroll Process

```
STEP 1: COMPILE (by accounts on 25th of each month)
   → Select year + month
   → System calculates expected deductions for ALL active members

STEP 2: DOWNLOAD TEMPLATE
   → Accounts downloads Excel template pre-filled with expected amounts

STEP 3: SEND TO SALARY DEPARTMENT
   → Accounts sends compiled list to salary department

STEP 4: SALARY DEPARTMENT PROCESSES
   → Salary department deducts amounts from members' salaries

STEP 5: UPLOAD ACTUALS
   → Accounts fills in actual deducted amounts in Excel
   → Uploads Excel back to system
   → System matches by Staff ID, updates actual amounts
   → Payroll status: deducted → completed
```

### 4.6 Dividend Distribution (Annual)

```
1. DECLARE → 2. CALCULATE → 3. APPROVE → 4. DISTRIBUTE
```

---

## 5. Permissions & Roles

### Permission Groups (35 permissions)

| Group | Permissions |
|-------|-------------|
| Dashboard | `view-dashboard` |
| Members | `view-members`, `create-members`, `edit-members`, `delete-members` |
| Next of Kin | `manage-next-of-kin` |
| Savings | `view-savings`, `deposit-savings`, `withdraw-savings` |
| Loans | `view-loans`, `create-loans`, `approve-loans`, `disburse-loans`, `repay-loans`, `delete-loans` |
| Loan Products | `view-loan-products`, `manage-loan-products` |
| Shares | `view-shares`, `purchase-shares` |
| Products | `view-products`, `manage-products`, `view-purchase-orders`, `create-purchase-orders`, `approve-purchase-orders`, `collect-purchase-orders` |
| Dividends | `view-dividends`, `declare-dividends`, `calculate-dividends`, `approve-dividends`, `distribute-dividends` |
| Payroll | `view-payroll`, `compile-payroll`, `upload-payroll`, `export-payroll` |
| Reports | `view-reports` |
| Admin | `manage-users`, `manage-roles` |

### Role Capabilities

| Role | Access Level |
|------|-------------|
| **super-admin** | Full access to all 35 permissions |
| **admin** | Full cooperative access (34 permissions — same as super-admin minus delete-members) |
| **secretary** | View-only across most modules + create/edit members |
| **treasurer** | Savings CRUD, shares, loan disburse/repay, dividends (declare/calculate), payroll (all), purchase orders |
| **loan-officer** | Loan CRUD + approve + disburse + repay |
| **teller** | Savings CRUD + shares |
| **member** | Self-service portal only |

---

## 6. User Guide by Role

### 6.1 Admin / Super Admin

| Task | How To |
|------|--------|
| Login | `/login` → `admin@naptin.coop` / `password` |
| View Dashboard | Click "Dashboard" — stats cards, Chart.js charts, trends, top savers |
| Register Member | Members → "+ Add Member" → fill form → submit |
| Import Members | Members → "Import" → upload Excel/CSV |
| Export Members | Members → "Export" → download Excel |
| Bulk Status Update | Members → select checkboxes → "Bulk Status" |
| Record Savings Deposit | Savings → Deposit → select member → enter amount |
| Approve Withdrawal | Savings → Pending Withdrawals → Approve/Reject |
| Apply for Loan | Loans → "+ New Loan" → select product → enter details |
| Approve/Reject Loan | Loans → click loan → Approve or Reject (with reason) |
| Disburse Loan | Loans → click loan → Disburse |
| Record Repayment | Loans → click loan → Repayment → enter payment |
| Import Loan Repayments | Loans → Import → upload Excel |
| Purchase Shares | Shares → Purchase → select member → enter quantity |
| Shopping Cart | Products → add to cart → checkout → select member + payment type |
| Create Purchase Order | Products → Orders → "+ New Order" |
| Print Invoice | Products → Orders → click order → "Invoice" → print |
| Compile Payroll | Payroll → "+ Compile Payroll" → select year/month |
| Upload Payroll Actuals | Payroll → click payroll → Upload → download template → fill → upload |
| View Sub-Reports | Payroll → click payroll → Savings/Loans/Purchases/Shares/Summary reports |
| Declare Dividend | Dividends → "+ New Dividend" → enter year + profit |
| Calculate/Approve/Distribute | Dividends → click dividend → Calculate → Approve → Distribute |
| Generate Member Report | Reports → select member → printable report → print |
| Manage Users | Management → User Management |
| Manage Roles | Management → Roles & Permissions |
| Manage Regions | Management → Regional Centers |
| Manage Loan Products | Management → Loan Products |
| Stock Management | Management → Stock Management → adjust quantities |
| Company Settings | Management → Company Settings → logo, info, thrift config |
| Data Import | Management → Data Import & Upload → central hub |
| Database Backup | Management → Database Backup → download SQL dump |
| View Statistics | Management → Statistics → login stats, errors, charts |

### 6.2 Treasurer / Financial Secretary

| Task | How To |
|------|--------|
| Record Savings | Savings → Deposit → select member → enter amount |
| Process Withdrawals | Savings → Pending Withdrawals → Approve or Reject |
| Compile Payroll | Payroll → Compile → select period |
| Upload Actuals | Payroll → select run → Upload → download → fill → upload |
| Export Payroll | Payroll → select run → Export Excel |
| Declare Dividend | Dividends → Create → enter year + profit |

### 6.3 Loan Officer

| Task | How To |
|------|--------|
| Create Loan | Loans → "+ New Loan" → select member + product → submit |
| Approve/Reject | Loans → click loan → Approve or Reject |
| Disburse | Loans → click loan → Disburse |
| Record Repayment | Loans → click loan → Repayment → enter payment |
| Accept Guarantor | Loans → click loan → Guarantors section → Accept/Decline |
| Import Repayments | Loans → Import → upload Excel |

### 6.4 Teller

| Task | How To |
|------|--------|
| Record Deposits | Savings → Deposit → select member → enter amount |
| Process Withdrawals | Savings → Withdraw → submit (goes to pending) |
| Purchase Shares | Shares → Purchase → select member → enter quantity |

---

## 7. Receipts & Printing

### 7.1 Available Receipts

| Receipt | Route | When Generated |
|---------|-------|---------------|
| Savings Deposit Receipt | `/receipts/savings/{transaction}` | After recording a deposit |
| Loan Repayment Receipt | `/receipts/loan-repayment/{repayment}` | After recording a repayment |
| Loan Disbursement Receipt | `/receipts/loan-disbursement/{loan}` | After disbursing a loan |
| Loan Statement | `/receipts/loan-statement/{loan}` | Full loan history |
| Savings Statement | `/receipts/savings-statement/{account}` | Full savings transaction history |
| Share Certificate | `/receipts/share-certificate/{account}` | Share holdings certificate |
| Purchase Order Receipt | `/receipts/purchase/{order}` | After creating an order |
| Purchase Invoice | `/invoices/purchase/{order}` | Printable purchase invoice |

### 7.2 Receipt Features

All receipts:
- Open in a new browser tab
- Use company logo from settings (56px, white background, `object-fit: contain`)
- Dynamic company name and footer note from `Company::instance()`
- Include member details (name, staff ID, region, phone)
- Include NAPTIN watermark
- Print button triggers browser print dialog
- Print CSS hides sidebar/header/buttons

---

## 8. Member Self-Service Portal

The portal provides members with a self-service interface accessible at `/my`.

### Portal Layout
- Separate blue-gradient sidebar (`from-[#0F172A] to-[#1e3a5f]`, distinct from admin's dark sidebar)
- Permission-gated: only users with a linked `member_id` can access
- Protected by `portal-member` middleware
- Top menu: Shop (with "New" badge), Cart (live AJAX badge), Notification bell (dropdown with 5 recent + "View All"), User dropdown
- Profile page uses portal layout for members (checked via `hasRole('member')`)

### Portal Pages

| Route | Purpose |
|-------|---------|
| `/my` | Dashboard with stat cards (savings, shares, loans, pending items) |
| `/my/savings` | Savings transaction history + deposit request (with payment evidence) + withdrawal request |
| `/my/loans` | Loan list with status badges |
| `/my/loans/apply` | Loan application form (product select, guarantors, auto-calc with interest) |
| `/my/loans/{id}` | Loan detail (repayment schedule, guarantors, approval log) |
| `/my/shares` | Share account + transaction history |
| `/my/purchases` | Purchase order history |
| `/my/products` | Browse product catalog (grid view, search, sort, "New" badge for recent products) |
| `/my/cart` | AJAX-powered shopping cart (add/update/remove without page refresh) |
| `/my/checkout` | Checkout (cash or hire purchase) |
| `/my/guarantors` | View and respond to guarantor requests |
| `/my/notifications` | Notification center with unread count badge + dropdown |
| `/my/notifications/read-all` | POST: Mark all notifications as read |

### Portal Routes (MemberPortalController)
| Route | Method | Action |
|-------|--------|--------|
| `/my` | GET | Member dashboard |
| `/my/savings` | GET | Savings history + request forms |
| `/my/savings/deposit` | POST | Submit deposit request (pending approval, optional payment evidence) |
| `/my/savings/withdraw` | POST | Submit withdrawal request (pending approval) |
| `/my/loans` | GET | Loan list |
| `/my/loans/apply` | GET | Loan application form |
| `/my/loans/apply` | POST | Submit loan application |
| `/my/loans/{id}` | GET | Loan detail |
| `/my/shares` | GET | Share account + transactions |
| `/my/purchases` | GET | Purchase history |
| `/my/products` | GET | Browse product catalog |
| `/my/cart` | GET | Shopping cart |
| `/my/cart/add` | POST | Add to cart (returns JSON for AJAX) |
| `/my/cart/update` | POST | Update cart quantity (returns JSON for AJAX) |
| `/my/cart/remove` | POST | Remove from cart (returns JSON for AJAX) |
| `/my/cart/clear` | POST | Clear cart (returns JSON for AJAX) |
| `/my/cart/count` | GET | Get cart item count (JSON endpoint) |
| `/my/checkout` | GET | Checkout form |
| `/my/checkout` | POST | Process checkout |
| `/my/guarantors` | GET | View guarantor requests |
| `/my/guarantors/{id}` | POST | Accept/decline guarantor request |
| `/my/notifications` | GET | All notifications |
| `/my/notifications/{id}/read` | POST | Mark notification as read |
| `/my/notifications/read-all` | POST | Mark all notifications as read |

### Portal Features
- **Savings Deposits:** Members request deposits with optional payment evidence → admin pending queue for approval
- **Withdrawal Requests:** Members request withdrawals → go to admin pending queue
- **Loan Applications:** Members apply for loans from portal with product selection, guarantors, auto-calculated repayment including interest
- **Guarantor Responses:** Members accept/decline guarantor requests from the portal
- **Product Ordering:** Browse → AJAX cart (no page refresh, live badge) → checkout
- **Notifications:** Dropdown in top menu (5 recent, mark all read) + full notifications page
- **Profile:** Edit name, email, password, photo (uses portal slate-blue theme)
- **Shop "New" Badge:** Green badge on Shop link if products were added in last 7 days

---

## 9. Data Import & Export

### Central Import Hub

**Path:** Management → Data Import & Upload

Provides a unified interface for all import operations:

| Import Type | Template | Required Columns |
|-------------|----------|-----------------|
| Members | CSV | staff_id, first_name, last_name, region |
| Savings | CSV | staff_id, amount |
| Loan Repayments | CSV | staff_id, amount |
| Products | CSV | name, unit_price |
| Purchase Orders | CSV | staff_id, product_name |

Each import type links directly to its upload form and template download.

### Available Exports

| Module | Export Format | Access Point |
|--------|-------------|--------------|
| Members | Excel (.xlsx) | Members page → "Export" |
| Savings | Excel (.xlsx) | Savings page → "Export" |
| Loans | Excel (.xlsx) | Loans page → "Export" |
| Shares | Excel (.xlsx) | Shares page → "Export" |
| Payroll (full) | Excel (.xlsx) | Payroll show → "Export Excel" |
| Payroll (template) | Excel (.xlsx) | Payroll show → "Download Template" |

---

## 10. Notifications

### Notification Types

| Notification | Trigger | Recipient |
|-------------|---------|-----------|
| `GuarantorRequestNotification` | Loan application with guarantors | Each guarantor member |
| `LoanAppliedNotification` | Loan application submitted | Treasurers, admins, loan officers |
| `LoanStatusNotification` | Loan approved/rejected/disbursed/completed | Loan applicant |
| `WithdrawalStatusNotification` | Withdrawal approved/rejected | Member who requested |
| `SavingsDepositRequestNotification` | Member requests deposit via portal | Treasurers, admins |
| `AdminPasswordResetNotification` | Admin resets user password | Target user (via email) |
| `PasswordResetNotification` | Self-service password reset | Requesting user (via email) |

### Welcome Email
When a member is created with an email address, a `WelcomeEmail` is sent containing:
- Login credentials (email + temporary password)
- Instructions to log in to the portal

---

## Appendix: Route Summary (100+ routes)

| Module | Routes | Prefix |
|--------|--------|--------|
| Auth | 7 | `/login`, `/logout`, `/forgot-password`, `/reset-password` |
| Profile | 2 | `/profile` |
| Dashboard | 1 | `/dashboard` |
| Members | 15 | `/members` |
| Next of Kin | 2 | `/members/{id}/next-of-kin` |
| Savings | 12 | `/savings` |
| Loans | 15 | `/loans` |
| Shares | 5 | `/shares` |
| Products | 11 | `/products` |
| Cart | 7 | `/cart` |
| Purchases | 5 | `/purchases` |
| Dividends | 7 | `/dividends` |
| Payroll | 11 | `/payroll` |
| Receipts | 8 | `/receipts` |
| Invoices | 1 | `/invoices/purchase/{id}` |
| Reports | 2 | `/reports` |
| Admin (Users) | 7 | `/admin/users` |
| Admin (Loan Products) | 6 | `/admin/loan-products` |
| Admin (Roles) | 5 | `/admin/roles` |
| Admin (Regions) | 5 | `/admin/regions` |
| Admin (Settings) | 2 | `/admin/settings` |
| Admin (Stock) | 2 | `/admin/stock` |
| Admin (Data Import) | 1 | `/admin/data-import` |
| Admin (Backup) | 1 | `/admin/backup` |
| Admin (Statistics) | 1 | `/admin/statistics` |
| Admin (Manage) | 1 | `/admin/manage` |
| Member Portal | 24 | `/my` |
