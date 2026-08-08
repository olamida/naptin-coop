<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PeriodClose;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Double-entry bookkeeping helper. Must be called inside an existing DB transaction
 * so the journal entry commits atomically with the underlying financial operation.
 */
class LedgerService
{
    public const CASH = '1001';                  // Cash & Bank (asset, debit normal)

    public const BANK = '1002';                  // Bank - First Bank (asset, debit normal)

    public const CASH_SUSPENSE = '1005';         // Cash Suspense - shortage/excess (asset, debit normal)

    public const LOANS_RECEIVABLE = '1101';      // Loans Receivable (asset, debit normal)

    public const PURCHASE_RECEIVABLES = '1201';  // Purchase Receivables (asset, debit normal)

    public const LOAN_LOSS_PROVISION = '1205';   // Loan Loss Provision (contra asset, credit normal)

    public const INVENTORY = '1301';             // Inventory - Cooperative Store (asset, debit normal)

    public const FIXED_ASSETS = '1401';          // Fixed Assets - Furniture (asset, debit normal)

    public const ACCUMULATED_DEPRECIATION = '1402'; // Accumulated Depreciation (contra asset, credit normal)

    public const PAYROLL_RECEIVABLE = '1501';    // Receivable - Payroll Deductions Expected (asset, debit normal)

    public const MEMBERS_SAVINGS = '2001';       // Members Savings Liability (liability, credit normal)

    public const DIVIDENDS_PAYABLE = '2201';     // Dividend Payable (liability, credit normal)

    public const SHARE_CAPITAL = '2101';         // Share Capital (equity, credit normal)

    public const RETAINED_EARNINGS = '3001';     // Retained Earnings (equity, credit normal)

    public const EDUCATION_FUND = '3002';        // Education Fund (equity, credit normal)

    public const GENERAL_RESERVE = '3003';       // General Reserve / Statutory Reserve (equity, credit normal)

    public const INTEREST_INCOME = '4001';       // Interest Income (income, credit normal)

    public const SALES_REVENUE = '4002';         // Sales Revenue (income, credit normal)

    public const PROCESSING_FEES_INCOME = '4004'; // Processing Fees Income (income, credit normal)

    public const SALES_MARGIN = '4005';          // Sales Margin - Store (income, credit normal)

    public const PROCUREMENT_EXPENSE = '5001';   // Procurement / General Expenses (expense, debit normal)

    public const AUDIT_FEES_EXPENSE = '5002';    // Audit Fees (expense, debit normal)

    public const ADMIN_EXPENSE = '5003';         // Administrative Expenses (expense, debit normal)

    public const LOAN_LOSS_EXPENSE = '5004';     // Loan Loss Expense / Provisioning (expense, debit normal)

    public const DEPRECIATION_EXPENSE = '5005';  // Depreciation Expense (expense, debit normal)

    public const STAFF_COSTS = '5007';           // Staff Costs (expense, debit normal)

    /**
     * Full CBN chart the LedgerService can create on demand. Codes match the
     * accounting spec exactly (LedgerAccountsSeeder seeds the full chart); this
     * list guarantees ensureAccount() never fails for any supported code.
     */
    private const DEFAULTS = [
        self::CASH => ['name' => 'Cash & Bank', 'type' => 'asset', 'normal_side' => 'debit'],
        self::BANK => ['name' => 'Bank - First Bank', 'type' => 'asset', 'normal_side' => 'debit'],
        self::CASH_SUSPENSE => ['name' => 'Cash Suspense - Shortage/Excess', 'type' => 'asset', 'normal_side' => 'debit'],
        self::LOANS_RECEIVABLE => ['name' => 'Loans Receivable', 'type' => 'asset', 'normal_side' => 'debit'],
        self::PURCHASE_RECEIVABLES => ['name' => 'Purchase Receivables', 'type' => 'asset', 'normal_side' => 'debit'],
        self::LOAN_LOSS_PROVISION => ['name' => 'Loan Loss Provision', 'type' => 'asset', 'normal_side' => 'credit'],
        self::INVENTORY => ['name' => 'Inventory - Cooperative Store', 'type' => 'asset', 'normal_side' => 'debit'],
        self::FIXED_ASSETS => ['name' => 'Fixed Assets - Furniture', 'type' => 'asset', 'normal_side' => 'debit'],
        self::ACCUMULATED_DEPRECIATION => ['name' => 'Accumulated Depreciation', 'type' => 'asset', 'normal_side' => 'credit'],
        self::PAYROLL_RECEIVABLE => ['name' => 'Receivable - Payroll Deductions Expected', 'type' => 'asset', 'normal_side' => 'debit'],
        self::MEMBERS_SAVINGS => ['name' => 'Members Savings Liability', 'type' => 'liability', 'normal_side' => 'credit'],
        self::DIVIDENDS_PAYABLE => ['name' => 'Dividend Payable', 'type' => 'liability', 'normal_side' => 'credit'],
        self::SHARE_CAPITAL => ['name' => 'Share Capital', 'type' => 'equity', 'normal_side' => 'credit'],
        self::RETAINED_EARNINGS => ['name' => 'Retained Earnings', 'type' => 'equity', 'normal_side' => 'credit'],
        self::EDUCATION_FUND => ['name' => 'Education Fund', 'type' => 'equity', 'normal_side' => 'credit'],
        self::GENERAL_RESERVE => ['name' => 'General Reserve', 'type' => 'equity', 'normal_side' => 'credit', 'subtype' => 'reserve', 'allow_manual_entry' => false],
        self::INTEREST_INCOME => ['name' => 'Interest Income', 'type' => 'income', 'normal_side' => 'credit'],
        self::SALES_REVENUE => ['name' => 'Sales Revenue', 'type' => 'income', 'normal_side' => 'credit'],
        self::PROCESSING_FEES_INCOME => ['name' => 'Processing Fees Income', 'type' => 'income', 'normal_side' => 'credit'],
        self::SALES_MARGIN => ['name' => 'Sales Margin - Store', 'type' => 'income', 'normal_side' => 'credit'],
        self::PROCUREMENT_EXPENSE => ['name' => 'Procurement & General Expenses', 'type' => 'expense', 'normal_side' => 'debit'],
        self::AUDIT_FEES_EXPENSE => ['name' => 'Audit Fees', 'type' => 'expense', 'normal_side' => 'debit'],
        self::ADMIN_EXPENSE => ['name' => 'Administrative Expenses', 'type' => 'expense', 'normal_side' => 'debit'],
        self::LOAN_LOSS_EXPENSE => ['name' => 'Loan Loss Expense', 'type' => 'expense', 'normal_side' => 'debit'],
        self::DEPRECIATION_EXPENSE => ['name' => 'Depreciation Expense', 'type' => 'expense', 'normal_side' => 'debit'],
        self::STAFF_COSTS => ['name' => 'Staff Costs', 'type' => 'expense', 'normal_side' => 'debit'],
    ];

    /**
     * Create a balanced, posted journal entry.
     *
     * @param  string  $description  Human readable narration
     * @param  string|null  $referenceType  e.g. savings, loan
     * @param  int|null  $referenceId  id of the originating record
     * @param  array  $lines  [{account_code, debit, credit, description?}, ...]
     * @param  string|null  $entryDate  optional entry date (defaults to today); the period is derived from it
     */
    public function post(
        string $description,
        ?string $referenceType,
        ?int $referenceId,
        array $lines,
        ?int $reversalOfId = null,
        ?string $reversalReason = null,
        ?string $entryDate = null
    ): JournalEntry {
        $entryDate = $entryDate ?? now()->toDateString();
        $period = substr($entryDate, 0, 7);

        if (PeriodClose::isClosed($period)) {
            throw new \RuntimeException("Financial period {$period} is closed. New postings are not allowed.");
        }

        $entry = JournalEntry::create([
            'entry_number' => JournalEntry::generateEntryNumber(),
            'entry_date' => $entryDate,
            'period' => $period,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'status' => 'draft',
            'uuid' => (string) Str::uuid(),
            'reversal_of_id' => $reversalOfId,
            'reversal_reason' => $reversalReason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $line) {
            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);
            $totalDebit += $debit;
            $totalCredit += $credit;

            $account = $this->ensureAccount($line['account_code']);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $account->id,
                'debit' => $debit,
                'credit' => $credit,
                'description' => $line['description'] ?? null,
            ]);
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new \RuntimeException(
                "Unbalanced journal entry: debit {$totalDebit} does not equal credit {$totalCredit}."
            );
        }

        // Hash-chain the entry and flip status in a single draft->posted update.
        // The model's post() derives prev_hash from the prior hashed entry.
        $entry->post();

        return $entry->fresh()->load('lines');
    }

    /**
     * Create a two-line journal entry (one debit, one credit).
     */
    public function postSimple(
        string $description,
        ?string $referenceType,
        ?int $referenceId,
        string $debitAccount,
        string $creditAccount,
        float $amount
    ): JournalEntry {
        return $this->post($description, $referenceType, $referenceId, [
            ['account_code' => $debitAccount, 'debit' => $amount, 'credit' => 0],
            ['account_code' => $creditAccount, 'debit' => 0, 'credit' => $amount],
        ]);
    }

    /**
     * Resolve a chart of accounts entry by code, creating it on demand if missing.
     */
    public function ensureAccount(string $code): ChartOfAccount
    {
        $account = ChartOfAccount::firstWhere('code', $code);

        if ($account) {
            return $account;
        }

        $default = self::DEFAULTS[$code] ?? null;
        if (! $default) {
            throw new \RuntimeException("Unknown ledger account code: {$code}");
        }

        $isControl = $default['is_control_account'] ?? false;
        $allowManual = $default['allow_manual_entry'] ?? false;

        return ChartOfAccount::create([
            'code' => $code,
            'name' => $default['name'],
            'type' => $default['type'],
            'subtype' => $default['subtype'] ?? null,
            'normal_side' => $default['normal_side'],
            'is_control_account' => $isControl,
            'control_module' => $default['control_module'] ?? ($isControl ? $code : null),
            'allow_manual_entry' => $allowManual,
            'description' => 'Auto-created by LedgerService.',
        ]);
    }

    /**
     * Reverse a posted journal entry by creating an opposite entry that references it.
     * The original entry is never mutated (immutable by design).
     */
    public function reverse(JournalEntry $entry, string $reason, ?Model $reference = null): JournalEntry
    {
        if ($entry->status !== 'posted') {
            throw new \RuntimeException('Only posted journal entries can be reversed.');
        }

        if ($entry->isReversal()) {
            throw new \RuntimeException('Reversal entries cannot be reversed.');
        }

        $entry->load('lines');

        $lines = $entry->lines->map(function (JournalEntryLine $line) {
            return [
                'account_code' => $line->account->code,
                'debit' => $line->credit,
                'credit' => $line->debit,
                'description' => $line->description,
            ];
        })->all();

        $reversal = $this->post(
            "REVERSAL of {$entry->entry_number}: {$reason}",
            $reference ? $reference->getMorphClass() : $entry->reference_type,
            $reference ? $reference->id : $entry->reference_id,
            $lines,
            $entry->id,
            $reason
        );

        return $reversal;
    }

    /**
     * Net balance for a single ledger account. Positive value is on the account's normal side.
     * Only posted entries are counted — draft entries are never final.
     * Optionally restrict the window with inclusive fromDate/toDate (Y-m-d).
     */
    public function getBalance(string $code, ?string $fromDate = null, ?string $toDate = null): float
    {
        $account = ChartOfAccount::where('code', $code)->first();

        if (! $account) {
            return 0.0;
        }

        $query = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entry_lines.account_id', $account->id);

        if ($fromDate) {
            $query->whereDate('journal_entries.entry_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('journal_entries.entry_date', '<=', $toDate);
        }

        $totals = $query
            ->selectRaw('COALESCE(SUM(journal_entry_lines.debit), 0) as d, COALESCE(SUM(journal_entry_lines.credit), 0) as c')
            ->first();

        $debit = (float) $totals->d;
        $credit = (float) $totals->c;

        return $account->normal_side === 'debit' ? $debit - $credit : $credit - $debit;
    }

    /**
     * Is the given financial period (Y-m) locked against new postings?
     */
    public function isPeriodClosed(string $period): bool
    {
        return PeriodClose::isClosed($period);
    }

    /**
     * Net profit realised in a single financial period (income credits minus expense
     * debits on posted entries dated within the period).
     */
    public function periodNetProfit(string $period): float
    {
        $start = $period.'-01';
        $end = date('Y-m-t', strtotime($start));

        $totals = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jel.account_id')
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$start, $end])
            ->whereIn('coa.type', ['income', 'expense'])
            ->selectRaw('COALESCE(SUM(jel.credit), 0) as c, COALESCE(SUM(jel.debit), 0) as d')
            ->first();

        return round((float) ($totals->c ?? 0) - (float) ($totals->d ?? 0), 2);
    }

    /**
     * Post the CBN statutory reserve (25%) and education fund (2.5%) appropriations of a
     * period's net profit, debiting retained earnings (3001) into the reserve accounts.
     * Posts at the period's month-end so the appropriation lands in the closed period.
     * Caller is responsible for idempotency (only appropriate on the first close of a period).
     *
     * @return array{statutory: ?JournalEntry, education: ?JournalEntry, net_profit: float}
     */
    public function postPeriodAppropriations(string $period, float $netProfit): array
    {
        $netProfit = round($netProfit, 2);

        if ($netProfit <= 0) {
            return ['statutory' => null, 'education' => null, 'net_profit' => 0.0];
        }

        $entryDate = date('Y-m-t', strtotime($period.'-01'));

        $statutory = round($netProfit * 0.25, 2);
        $education = round($netProfit * 0.025, 2);

        $statutoryEntry = $this->post(
            "Statutory reserve appropriation for {$period}: 25% of net profit (₦".number_format($statutory, 2).')',
            'period_close',
            null,
            [
                ['account_code' => self::RETAINED_EARNINGS, 'debit' => $statutory, 'credit' => 0],
                ['account_code' => self::GENERAL_RESERVE, 'debit' => 0, 'credit' => $statutory],
            ],
            null,
            null,
            $entryDate
        );

        $educationEntry = $this->post(
            "Education fund appropriation for {$period}: 2.5% of net profit (₦".number_format($education, 2).')',
            'period_close',
            null,
            [
                ['account_code' => self::RETAINED_EARNINGS, 'debit' => $education, 'credit' => 0],
                ['account_code' => self::EDUCATION_FUND, 'debit' => 0, 'credit' => $education],
            ],
            null,
            null,
            $entryDate
        );

        return ['statutory' => $statutoryEntry, 'education' => $educationEntry, 'net_profit' => $netProfit];
    }

    /**
     * Is the trial balance balanced (total posted debits equal total posted credits)?
     * Used as a dividend-declaration gate and general ledger sanity check.
     */
    public function trialBalanceIsBalanced(): bool
    {
        $totals = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->where('je.status', 'posted')
            ->selectRaw('COALESCE(SUM(jel.debit), 0) as d, COALESCE(SUM(jel.credit), 0) as c')
            ->first();

        return round((float) $totals->d, 2) === round((float) $totals->c, 2);
    }

    /**
     * Validate every control account against its sub-ledger total.
     *
     * @return array<int, array{code: string, name: string, ledger_balance: float, sub_ledger_balance: float, variance: float, reconciled: bool}>
     */
    public function validateControlAccounts(): array
    {
        $targets = app(LedgerSyncService::class)->subLedgerTargets();

        $map = [
            self::MEMBERS_SAVINGS => ['name' => 'Members Savings', 'sub' => $targets['savings']],
            self::LOANS_RECEIVABLE => ['name' => 'Loans Receivable', 'sub' => $targets['loans']],
            self::SHARE_CAPITAL => ['name' => 'Share Capital', 'sub' => $targets['shares']],
            self::PURCHASE_RECEIVABLES => ['name' => 'Purchase Receivables', 'sub' => $targets['purchases']],
        ];

        $rows = [];

        foreach ($map as $code => $meta) {
            $account = ChartOfAccount::firstWhere('code', $code);
            $rows[] = [
                'code' => $code,
                'name' => $account?->name ?? $meta['name'],
                ...$this->reconcileControlAccount($code, $meta['sub']),
            ];
        }

        return $rows;
    }

    /**
     * Verify the integrity of the entire ledger hash chain.
     * Returns a list of entries whose stored hash does not match their recomputed hash.
     */
    public function verifyHashChain(): array
    {
        $broken = [];
        $entries = JournalEntry::whereNotNull('hash')->orderBy('id')->get();
        $expectedPrev = 'GENESIS';

        foreach ($entries as $entry) {
            $valid = $entry->prev_hash === $expectedPrev
                && $entry->verifyHash($expectedPrev);

            if (! $valid) {
                $broken[] = [
                    'id' => $entry->id,
                    'entry_number' => $entry->entry_number,
                    'expected_prev_hash' => $expectedPrev,
                    'stored_prev_hash' => $entry->prev_hash,
                    'stored_hash' => $entry->hash,
                ];
            }

            $expectedPrev = (string) $entry->hash;
        }

        return $broken;
    }

    /**
     * Reconcile a control account (e.g. 2001 Members Savings) against its sub-ledger total.
     */
    public function reconcileControlAccount(string $code, float $subLedgerBalance): array
    {
        $ledgerBalance = $this->getBalance($code);

        return [
            'code' => $code,
            'ledger_balance' => $ledgerBalance,
            'sub_ledger_balance' => $subLedgerBalance,
            'variance' => round($ledgerBalance - $subLedgerBalance, 2),
            'reconciled' => abs($ledgerBalance - $subLedgerBalance) < 0.01,
        ];
    }

    /**
     * Convenience: record the cash deposit of savings (debit cash, credit savings liability).
     */
    public function postSavingsDeposit(int $transactionId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Savings deposit #'.$transactionId,
            'savings',
            $transactionId,
            self::CASH,
            self::MEMBERS_SAVINGS,
            $amount
        );
    }

    /**
     * Convenience: record a savings withdrawal payout (debit savings liability, credit cash).
     */
    public function postSavingsWithdrawal(int $transactionId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Savings withdrawal #'.$transactionId,
            'savings',
            $transactionId,
            self::MEMBERS_SAVINGS,
            self::CASH,
            $amount
        );
    }

    /**
     * Convenience: record a loan disbursement (debit loans receivable, credit cash).
     * When a processing fee is charged, the payout account is credited with the
     * net amount (principal − fee) and the fee is recognised as Processing Fees
     * Income (4004) — net disbursement = principal − fee, per CBN spec.
     */
    public function postLoanDisbursement(int $loanId, float $amount, float $processingFee = 0.0): JournalEntry
    {
        $lines = [
            ['account_code' => self::LOANS_RECEIVABLE, 'debit' => $amount, 'credit' => 0],
        ];

        if ($processingFee > 0) {
            $lines[] = ['account_code' => self::CASH, 'debit' => 0, 'credit' => round($amount - $processingFee, 2)];
            $lines[] = ['account_code' => self::PROCESSING_FEES_INCOME, 'debit' => 0, 'credit' => $processingFee];
        } else {
            $lines[] = ['account_code' => self::CASH, 'debit' => 0, 'credit' => $amount];
        }

        return $this->post(
            'Loan disbursement #'.$loanId,
            'loan',
            $loanId,
            $lines
        );
    }

    /**
     * Convenience: record a share purchase (debit cash, credit share capital).
     */
    public function postSharePurchase(int $transactionId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Share purchase #'.$transactionId,
            'share',
            $transactionId,
            self::CASH,
            self::SHARE_CAPITAL,
            $amount
        );
    }

    /**
     * Convenience: record a loan repayment split between principal and interest.
     */
    public function postLoanRepayment(int $loanId, int $repaymentId, float $principal, float $interest): JournalEntry
    {
        return $this->post(
            'Loan repayment #'.$repaymentId.' for loan #'.$loanId,
            'loan',
            $repaymentId,
            [
                ['account_code' => self::CASH, 'debit' => $principal + $interest, 'credit' => 0],
                ['account_code' => self::LOANS_RECEIVABLE, 'debit' => 0, 'credit' => $principal],
                ['account_code' => self::INTEREST_INCOME, 'debit' => 0, 'credit' => $interest],
            ]
        );
    }

    /**
     * Convenience: record a cash product sale (debit cash, credit sales revenue).
     */
    public function postCashSale(int $orderId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Cash sale #'.$orderId,
            'purchase',
            $orderId,
            self::CASH,
            self::SALES_REVENUE,
            $amount
        );
    }

    /**
     * Convenience: record a hire-purchase sale (debit purchase receivables, credit sales revenue).
     */
    public function postHirePurchaseSale(int $orderId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Hire purchase sale #'.$orderId,
            'purchase',
            $orderId,
            self::PURCHASE_RECEIVABLES,
            self::SALES_REVENUE,
            $amount
        );
    }

    /**
     * Convenience: record an executive/society procurement expense (debit expense, credit cash).
     */
    public function postSocietyExpense(int $orderId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Society procurement expense #'.$orderId,
            'purchase',
            $orderId,
            self::PROCUREMENT_EXPENSE,
            self::CASH,
            $amount
        );
    }

    /**
     * Convenience: accrue a declared dividend (debit retained earnings, credit dividend payable).
     * Posted once per dividend at calculation time so the payable mirrors the distribution.
     */
    public function postDividendAccrual(int $dividendId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Dividend accrual #'.$dividendId,
            'dividend',
            $dividendId,
            self::RETAINED_EARNINGS,
            self::DIVIDENDS_PAYABLE,
            $amount
        );
    }

    /**
     * Convenience: record a dividend payout (debit dividend payable, credit cash).
     * Clears the payable accrued at declaration/calculation time.
     */
    public function postDividendDistribution(int $distributionId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Dividend distribution #'.$distributionId,
            'dividend',
            $distributionId,
            self::DIVIDENDS_PAYABLE,
            self::CASH,
            $amount
        );
    }

    /**
     * Convenience: post a daily cash-count variance against Cash Suspense (1005).
     * Excess (physical > system): debit Cash, credit Cash Suspense.
     * Shortage (physical < system): debit Cash Suspense, credit Cash.
     */
    public function postCashVariance(int $cashCountId, float $variance): JournalEntry
    {
        if (abs($variance) < 0.005) {
            throw new \RuntimeException('No variance to post for a balanced cash count.');
        }

        $isExcess = $variance > 0;

        return $this->postSimple(
            'Daily cash count #'.$cashCountId.($isExcess ? ' — excess' : ' — shortage'),
            'cash_count',
            $cashCountId,
            $isExcess ? self::CASH : self::CASH_SUSPENSE,
            $isExcess ? self::CASH_SUSPENSE : self::CASH,
            abs($variance)
        );
    }
}
