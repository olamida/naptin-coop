<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;

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

    private const DEFAULTS = [
        self::CASH => ['name' => 'Cash & Bank', 'type' => 'asset', 'normal_side' => 'debit'],
        self::LOANS_RECEIVABLE => ['name' => 'Loans Receivable', 'type' => 'asset', 'normal_side' => 'debit'],
        self::PURCHASE_RECEIVABLES => ['name' => 'Purchase Receivables', 'type' => 'asset', 'normal_side' => 'debit'],
        self::MEMBERS_SAVINGS => ['name' => 'Members Savings Liability', 'type' => 'liability', 'normal_side' => 'credit'],
        self::DIVIDENDS_PAYABLE => ['name' => 'Dividends Payable', 'type' => 'liability', 'normal_side' => 'credit'],
        self::SHARE_CAPITAL => ['name' => 'Share Capital', 'type' => 'equity', 'normal_side' => 'credit'],
        self::RETAINED_EARNINGS => ['name' => 'Retained Earnings', 'type' => 'equity', 'normal_side' => 'credit'],
        self::INTEREST_INCOME => ['name' => 'Interest Income', 'type' => 'income', 'normal_side' => 'credit'],
        self::SALES_REVENUE => ['name' => 'Sales Revenue', 'type' => 'income', 'normal_side' => 'credit'],
    ];

    /**
     * Create a balanced, posted journal entry.
     *
     * @param  string  $description  Human readable narration
     * @param  string|null  $referenceType  e.g. savings, loan
     * @param  int|null  $referenceId  id of the originating record
     * @param  array  $lines  [{account_code, debit, credit, description?}, ...]
     */
    public function post(string $description, ?string $referenceType, ?int $referenceId, array $lines): JournalEntry
    {
        $entry = JournalEntry::create([
            'entry_number' => JournalEntry::generateEntryNumber(),
            'entry_date' => now()->toDateString(),
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'status' => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
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

        return $entry->load('lines');
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
     * Convenience: record the cash deposit of savings (debit cash, credit savings liability).
     */
    public function postSavingsDeposit(int $transactionId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Savings deposit #' . $transactionId,
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
            'Savings withdrawal #' . $transactionId,
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
            'Loan disbursement #' . $loanId,
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
            'Share purchase #' . $transactionId,
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
            'Loan repayment #' . $repaymentId . ' for loan #' . $loanId,
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
            'Cash sale #' . $orderId,
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
            'Hire purchase sale #' . $orderId,
            'purchase',
            $orderId,
            self::PURCHASE_RECEIVABLES,
            self::SALES_REVENUE,
            $amount
        );
    }

    /**
     * Convenience: record a dividend payout (debit retained earnings, credit cash).
     */
    public function postDividendDistribution(int $distributionId, float $amount): JournalEntry
    {
        return $this->postSimple(
            'Dividend distribution #' . $distributionId,
            'dividend',
            $distributionId,
            self::RETAINED_EARNINGS,
            self::CASH,
            $amount
        );
    }
}
