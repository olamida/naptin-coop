<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PeriodClose;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Double-entry bookkeeping helper. Must be called inside an existing DB transaction
 * so the journal entry commits atomically with the underlying financial operation.
 */
class LedgerService
{
    public const CASH = '1001';                  // Cash & Bank (asset, debit normal)

    public const LOANS_RECEIVABLE = '1101';      // Loans Receivable (asset, debit normal)

    public const PURCHASE_RECEIVABLES = '1201';  // Purchase Receivables (asset, debit normal)

    public const MEMBERS_SAVINGS = '2001';       // Members Savings Liability (liability, credit normal)

    public const DIVIDENDS_PAYABLE = '2002';     // Dividends Payable (liability, credit normal)

    public const SHARE_CAPITAL = '2101';         // Share Capital (equity, credit normal)

    public const RETAINED_EARNINGS = '3001';     // Retained Earnings (equity, credit normal)

    public const INTEREST_INCOME = '4001';       // Interest Income (income, credit normal)

    public const SALES_REVENUE = '4002';         // Sales Revenue (income, credit normal)

    public const PROCUREMENT_EXPENSE = '5001';   // Procurement / General Expenses (expense, debit normal)

    public const LOAN_LOSS_PROVISION = '1205';   // Loan Loss Provision (contra asset, credit normal)

    public const LOAN_LOSS_EXPENSE = '5004';     // Loan Loss Expense / Provisioning (expense, debit normal)

    private const DEFAULTS = [
        self::CASH => ['name' => 'Cash & Bank', 'type' => 'asset', 'normal_side' => 'debit'],
        self::LOANS_RECEIVABLE => ['name' => 'Loans Receivable', 'type' => 'asset', 'normal_side' => 'debit'],
        self::PURCHASE_RECEIVABLES => ['name' => 'Purchase Receivables', 'type' => 'asset', 'normal_side' => 'debit'],
        self::LOAN_LOSS_PROVISION => ['name' => 'Loan Loss Provision', 'type' => 'asset', 'normal_side' => 'credit'],
        self::MEMBERS_SAVINGS => ['name' => 'Members Savings Liability', 'type' => 'liability', 'normal_side' => 'credit'],
        self::DIVIDENDS_PAYABLE => ['name' => 'Dividends Payable', 'type' => 'liability', 'normal_side' => 'credit'],
        self::SHARE_CAPITAL => ['name' => 'Share Capital', 'type' => 'equity', 'normal_side' => 'credit'],
        self::RETAINED_EARNINGS => ['name' => 'Retained Earnings', 'type' => 'equity', 'normal_side' => 'credit'],
        self::INTEREST_INCOME => ['name' => 'Interest Income', 'type' => 'income', 'normal_side' => 'credit'],
        self::SALES_REVENUE => ['name' => 'Sales Revenue', 'type' => 'income', 'normal_side' => 'credit'],
        self::PROCUREMENT_EXPENSE => ['name' => 'Procurement & General Expenses', 'type' => 'expense', 'normal_side' => 'debit'],
        self::LOAN_LOSS_EXPENSE => ['name' => 'Loan Loss Expense', 'type' => 'expense', 'normal_side' => 'debit'],
    ];

    /**
     * Create a balanced, posted journal entry.
     *
     * @param  string  $description  Human readable narration
     * @param  string|null  $referenceType  e.g. savings, loan
     * @param  int|null  $referenceId  id of the originating record
     * @param  array  $lines  [{account_code, debit, credit, description?}, ...]
     */
    public function post(
        string $description,
        ?string $referenceType,
        ?int $referenceId,
        array $lines,
        ?int $reversalOfId = null,
        ?string $reversalReason = null
    ): JournalEntry {
        $entryDate = now()->toDateString();
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

        return ChartOfAccount::create([
            'code' => $code,
            'name' => $default['name'],
            'type' => $default['type'],
            'normal_side' => $default['normal_side'],
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
     */
    public function getBalance(string $code): float
    {
        $account = ChartOfAccount::where('code', $code)->first();

        if (! $account) {
            return 0.0;
        }

        $debit = (float) $account->journalLines()->sum('debit');
        $credit = (float) $account->journalLines()->sum('credit');

        return $account->normal_side === 'debit' ? $debit - $credit : $credit - $debit;
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
     */
    public function postLoanDisbursement(int $loanId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Loan disbursement #'.$loanId,
            'loan',
            $loanId,
            self::LOANS_RECEIVABLE,
            self::CASH,
            $amount
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
     * Convenience: record a dividend payout (debit retained earnings, credit cash).
     */
    public function postDividendDistribution(int $distributionId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Dividend distribution #'.$distributionId,
            'dividend',
            $distributionId,
            self::RETAINED_EARNINGS,
            self::CASH,
            $amount
        );
    }
}
