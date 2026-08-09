# NAPTIN Staff Thrift Cooperative Society — User Guide

> **Version:** 1.0 (matches APP-GUIDE 3.16+)
> **URL:** `http://localhost/dev-angle/Starter-folder/naptin-coop/public`
> **Companion docs:** `APP-GUIDE.md` (features & changelog), `SYSTEM-DOCUMENTATION.md` (technical), `PENDING-TASKS.md` (AI session ledger).

This guide is written for **people, not programmers** — the society's admin, EXCO members, tellers and the cooperative's everyday staff. It explains how to log in, navigate, and do each job safely.

---

## 1. Getting Started

### 1.1 Logging In

1. Open the application URL above in your browser.
2. Click the **Login** button (top-right of the public homepage).
3. Enter your **email address** and **password**, then click **Sign In**.
4. If you are a new user or your password was just reset by an administrator:
   - You will be asked to **change your password** before you can continue.
   - Pick a strong password you can remember (the system will not let you skip this).
5. If two-factor authentication (2FA) is enabled on your account:
   - Enter the 6-digit code from your authenticator app (Google Authenticator, Microsoft Authenticator, etc.).
   - If you lost your phone, use a **recovery code** (contact the admin if you have none left).

> **Single-session rule:** The system allows only **one active session** per account. If you log in on a second device, the first session is signed out automatically. This is a security feature — do not share your password.

### 1.2 Forgotten Password

1. On the login page, click **Forgot your password?**.
2. Enter your email address and click the submit button.
3. Open the password-reset link sent to your inbox (check spam if needed).
4. Set your new password and log in.

### 1.3 Logging Out

Use the **Logout** option in your profile/user menu (top-right corner). Always log out on shared computers.

---

## 2. Understanding Your Role

The application shows you only what your role allows. The main roles are:

| Role | What they do day-to-day |
|------|--------------------------|
| **super-admin / admin** | Everything: members, money, loans, payroll, dividends, reports, settings, users |
| **secretary** | Mostly viewing + registering/editing members |
| **treasurer** | Savings, shares, loan disbursement/repayment, dividends, payroll, purchases |
| **loan-officer** | Loans end-to-end (apply, approve, disburse, repay, guarantors) |
| **teller** | Savings deposits/withdrawals, share purchases |
| **member** | The self-service **portal** (`/my`) only |

If a button or menu you expect is missing, it is hidden by your role — contact the admin to have your permissions reviewed.

---

## 3. The Admin Interface

After logging in as a staff/EXCO member you land on the **Dashboard**. On the left is the sidebar with the sections below. A section collapses when its arrow is clicked; only one dropdown stays open at a time.

- **Dashboard** — headline stats and charts.
- **Members** — the cooperative's register.
- **Accounts ▸** — Savings, Loans, Purchases, Shares.
- **Reporting & Accounting ▸** — Dividends, Payroll, Reports, Finance, Ledger.
- **Administration ▸** — Settings (users, roles, regions, loan products, stock, products, branding, imports, backup, statistics).

### 3.1 Keyboard Shortcuts

On any admin page you can use the keyboard to work faster (not while typing in a box):

| Key | Action |
|-----|--------|
| `/` | Open the search palette |
| `Ctrl + K` or `Cmd + K` | Open the search palette |
| `N` | Jump straight to "New Member" (where allowed) |
| `A` | **Approve / confirm** the first pending item on the page |
| `R` | **Reject** the first pending item on the page |

The `A` and `R` shortcuts respect the on-page confirmation dialogs — the same safety prompt you would see when clicking the button with your mouse.

---

## 4. Daily Tasks

### 4.1 Members

**Add a member**
1. Sidebar → **Members → + Add Member** (or press `N`).
2. Fill in personal details, employment data and salary.
3. Choose the **region** and, if the member holds an EXCO post, assign the **position**.
4. Enter an **email** if the member should have a portal login — a user account is created for them and a welcome email (with a temporary password) is sent automatically.
5. Save. The system automatically creates the member's **savings account** and **share account** (both starting at ₦0).

> When new staff join, the fastest path is **Administration → Settings → Data Import**, or the dedicated **Onboarding** importer (Members/positions/salaries + opening balances in one upload). A template CSV is provided on those pages.

**Member applications:** Members who register through the public **Register** page arrive with status *pending*. Open their profile to **Approve** or **Reject** them (with a reason). Approval activates their account.

**Search:** On the Members list, start typing in the search box — results appear instantly (name or staff ID). The same autocomplete search exists on Loans, Savings, Shares, Products and Purchases pages.

**Bulk status update:** Tick the checkboxes of several members on the list, then use **Bulk Status** to set them all to e.g. *inactive* or *retired* at once.

### 4.2 Savings

**Record a deposit**
1. Sidebar → **Accounts → Savings → Deposit**.
2. Start typing the member's name/staff ID and pick them from the results.
3. Enter the **amount** and any note. Optionally attach **payment evidence** (a photo of the receipt/transfer).
4. Save. The balance is credited immediately and a receipt can be printed.

**Request a withdrawal**
1. **Savings → Withdraw**, pick the member, enter the amount and submit.
2. The request is now *pending* — money is **not** deducted yet.
3. A **treasurer** (or a second authorised approver) must approve it in **Savings → Pending Approvals**.
4. Withdrawals above ₦100,000 need **two different authorised approvers** (maker-checker).

**Review pending requests**
- **Savings → Pending Approvals** lists both pending deposits (from the member portal) and pending withdrawals.
- Use **Confirm/Approve** to complete, or **Reject** (enter a reason the member will see).
- On this page press `A` to approve or `R` to reject the first pending request.

### 4.3 Loans

**Apply for a loan (on behalf of a member)**
1. **Accounts → Loans → + New Loan**.
2. Pick the member and a **loan product**. The product defines the interest rate, minimum/maximum amount, maximum term and whether guarantors are required.
3. Enter the amount and term. The monthly repayment (principal + interest) is calculated for you and an amortization chart is shown at the review step.
4. Submit. If the product requires guarantors, select them — they are notified and must accept before the loan can be approved.

**Approval → Disbursement → Repayment**
- Open the loan from **Loans →** list.
- The **Loan Lifecycle timeline** shows every step with dates, actors and status (submitted, guarantors, approval, disbursement, repayment, completion/rejection).
- **Approve** the loan (all guarantors must have accepted).
- **Disburse** — requests a maker-checker approval; a second authorised approver confirms the disbursement, after which money is released and the processing fee (if any) is posted.
- **Repayment** — click the loan → **Record Repayment**, enter the amount. The system splits it into principal, interest and fee portions and updates the outstanding balance.
- When the outstanding reaches ₦0 the loan is marked **Completed**.

**Rules enforced automatically** (to protect members and the society):
- A member's total eligible loan is capped at **3× their savings balance** (max ₦5,000,000).
- A person cannot guarantee more than **₦500,000** in total on active loans.
- A member's total exposure cannot exceed **5% of the whole outstanding loan book** (CBN single-obligor rule).

**Loan top-up:** From a disbursed loan's page you can request a **top-up**, which creates a follow-on loan.

### 4.4 Shares

- **Shares → Purchase** — pick a member, enter the number of shares. The account totals update immediately.
- **Shares → Accounts** lists every member's holding; **Shares →** list shows the transaction history.
- If the **Shares module is disabled** in Settings, these menus and pages disappear.

### 4.5 Products, Cart & Purchases

**The shop**
- **Products** lists what the cooperative sells, with prices and stock levels.
- Add items to the **cart** (admin) — the cart badge updates live.
- At **checkout** choose the member and the payment type.

**Payment types**
- **Cash** — order is approved immediately; the member pays and collects.
- **Hire Purchase** — the order is *pending* until approved, then *active*; a monthly repayment schedule is generated. Record each instalment on the order page (Record Repayment) until fully settled, when the order becomes *completed*.

**Standalone purchase orders** (`Accounts → Purchases → Create`) are the same idea without the cart — pick a member, product, quantity and payment type directly.

**Stock:** is reduced automatically when an order is created. Admin can adjust stock in **Administration → Stock Management**.

### 4.6 Payroll

1. **Reporting & Accounting → Payroll → + Compile Payroll**, pick a **year** and **month**.
2. The system calculates each active member's expected deductions automatically:
   - Savings: **10%** of monthly salary
   - Share contribution: **5%** of monthly salary
   - Loan repayment: the member's active loan repayment (if any)
   - Purchase repayment: the member's active hire-purchase instalment (if any)
3. Open the payroll run → **Download Template** (Excel, pre-filled with the expected amounts) and send it to the salary department.
4. When the actual figures come back, fill them in and **Upload** the file (matched by Staff ID).
5. Shortfalls between expected and actual are recorded as **arrears**, which you can enter individually or in bulk and settle later.

### 4.7 Dividends

Only when the **Dividends module** is enabled.

1. **Reporting & Accounting → Dividends → + New Dividend**, enter the **year** and **total profit**.
2. The declaration needs **two distinct approvers** (maker-checker) before you can proceed.
3. **Calculate** per-member amounts (pro-rata by shareholding).
4. **Approve**, then **Distribute** to mark all payouts as paid.

> **Before declaring:** the system checks that the books are balanced and that loan-loss provision coverage is at least 100% whenever loans are outstanding. If it refuses, run **Finance → Loan Aging → Calculate Provision** first.

### 4.8 Reports

- **Reporting & Accounting → Reports** — pick a member and generate a **printable status report** (personal details, savings, shares, full loan history, full purchase history and salary-deduction breakdown with net take-home pay). Use the **Print** button.
- **Reporting & Accounting → Finance** — the finance hub (period close, P&L, balance sheet, cash flow, loan aging, control reconciliation, daily cash count, savings control, audit trail). Each statement has **Excel** and **PDF** download buttons; PDFs carry a QR code you can use to verify the file was not altered.
- **Reporting & Accounting → Ledger** — the raw double-entry books (chart of accounts, journals, trial balance, general ledger). Only for super-admin/admin.

### 4.9 Settings (Administration)

Everything administrative lives behind **Administration → Settings / Manage**:

- **Users** — create/edit user accounts, assign roles, reset passwords.
- **Roles** — manage roles and their 35 permissions.
- **Regions** — the cooperative's regional centres.
- **Loan Products** — interest rates, limits, guarantor requirements, processing fees.
- **Stock** — adjust product stock.
- **Company Settings** — company name/logo, contact info, financial parameters (savings interest, loan interest, max loan multiplier, auto-approve deposit limit), footer note.
- **Branding** — the favicon, hero banners, logo and round sidebar icon.
- **Modules** — switch the **Shares** and **Dividends** modules on/off.
- **Data Import** — the central upload hub (members, savings, loan repayments, products, purchases).
- **Backup** — download a full SQL dump of the database.
- **Statistics** — login activity and system statistics.
- **Broadcasts** — send a notice to all members (appears in their portal notifications).

---

## 5. The Member Portal (`/my`)

Members log in with the same login page and are taken to the **portal** (blue sidebar). This is the member's self-service area.

| I want to... | How |
|--------------|-----|
| See my balances | Portal **Dashboard** — savings, shares, loans and pending items at a glance |
| Deposit money | **My Savings → Request Deposit** — enter amount, optionally attach payment evidence; the treasurer confirms it before it counts |
| Withdraw money | **My Savings → Withdraw** — request goes to the treasurer for approval |
| View savings history | **My Savings** — full transaction list |
| Apply for a loan | **My Loans → Apply** — choose product, amount, term and guarantors; monthly repayment shown live. Rules (3× savings cap, etc.) apply here too. |
| Track a loan | **My Loans → [loan]** — repayment schedule, guarantors and the **Loan Lifecycle timeline** |
| Respond to a guarantor request | **Guarantors** — accept or decline |
| Buy from the shop | **Shop** → browse → **Add to Cart** (the cart badge updates) → **Cart** → **Checkout** (Cash or Hire Purchase) |
| View my orders | **My Purchases** |
| See my shares | **My Shares** (hidden if the module is disabled) |
| Read notices | The **bell icon** (top-right) — recent notifications; **View All** for the full list |
| Update my details | Your **profile** menu — name, email, password, photo |

---

## 6. Receipts & Printing

These open in a new browser tab and have a **Print** button:

| Receipt | Where you get it |
|---------|------------------|
| Savings Deposit Receipt | right after recording a deposit |
| Loan Repayment Receipt | right after recording a repayment |
| Loan Disbursement Receipt | right after disbursing a loan |
| Loan Statement | loan detail page |
| Savings Statement | savings account |
| Share Certificate | share account |
| Purchase Order Receipt | after creating an order |
| Purchase Invoice | order detail page → **Invoice** |

---

## 7. Common Questions & Troubleshooting

**"The page says my session expired (419)."** Your session timed out or the CSRF token was stale. Just sign in again — you'll be returned to the login page.

**"I was logged out on my other device."** The single-session rule kicked in because you (or someone with your password) logged in elsewhere.

**"I can't find a menu item."** It is probably hidden by your role, or the module (Shares/Dividends) is switched off in Settings.

**"The system blocked a loan."** Read the error message — it names the rule (3× savings cap, guarantor cap, or CBN single-obligor). The same limits the member sees in the portal are enforced on the admin side.

**"A dividend won't be declared."** The finance checks are failing (books unbalanced, or loan-loss provision coverage below 100%). Run **Finance → Loan Aging → Calculate Provision**, confirm the period books balance (**Finance → Audit Trail / Ledger → Trial Balance**), then retry.

**"The report PDF looks odd / I want to verify it."** Every exported PDF has a QR code containing the report's SHA-256 hash. Scan it (or compare the printed hash) against the same report generated from the live system to prove it hasn't been tampered with.

**Still stuck?** Ask the **super-admin** — they have access to every module and the ledger.
