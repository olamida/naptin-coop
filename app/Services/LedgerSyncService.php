<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\PurchaseOrder;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use Illuminate\Support\Facades\DB;

/**
 * Brings the double-entry ledger up to date with the sub-ledgers.
 *
 * Cooperative transactions created before the ledger module (or via bulk
 * imports / seeders) never posted journal entries, leaving the balance sheet,
 * P&L and control reconciliation empty. This service posts a single balanced
 * "opening balance" conversion entry that reconciles every control account to
 * its sub-ledger total. Historical income/expense is NOT re-created here — it is
 * closed into Retained Earnings, which is the correct conversion practice.
 *
 * The sync is idempotent: it only posts the delta between each control account's
 * current ledger balance and its sub-ledger target, so re-running converges and
 * never double-counts (same design as ProvisioningService).
 */
class LedgerSyncService
{
    /**
     * Compute the current sub-ledger target for each balance-sheet control account.
     *
     * @return array<string, float> keyed by semantic name
     */
    public function subLedgerTargets(): array
    {
        return [
            'savings' => (float) SavingsAccount::sum('balance'),
            'loans' => (float) Loan::whereIn('status', ['disbursed', 'repaying', 'defaulted'])->sum('outstanding'),
            'shares' => (float) ShareAccount::sum('total_value'),
            'purchases' => (float) PurchaseOrder::where('payment_type', 'hire_purchase')
                ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
                ->sum(DB::raw('total_amount - COALESCE(amount_paid, 0)')),
        ];
    }

    /**
     * Post the opening-balance conversion entry (if anything is out of balance).
     *
     * @return array{posted: bool, entry_number?: string, lines?: int, total?: float, message?: string}
     */
    public function syncOpeningBalances(): array
    {
        return DB::transaction(function () {
            $ledger = new LedgerService;
            $targets = $this->subLedgerTargets();

            $lines = [];

            // Liability & equity accounts are credit-normal: a positive delta is credited.
            foreach ([LedgerService::MEMBERS_SAVINGS => 'savings', LedgerService::SHARE_CAPITAL => 'shares'] as $code => $key) {
                $this->pushLine($lines, $code, $key, $targets[$key], $ledger->getBalance($code), 'credit');
            }

            // Asset accounts are debit-normal: a positive delta is debited.
            foreach ([LedgerService::LOANS_RECEIVABLE => 'loans', LedgerService::PURCHASE_RECEIVABLES => 'purchases'] as $code => $key) {
                $this->pushLine($lines, $code, $key, $targets[$key], $ledger->getBalance($code), 'debit');
            }

            if (empty($lines)) {
                return ['posted' => false, 'message' => 'The ledger is already in sync with the sub-ledger. No entry was posted.'];
            }

            // Balance the entry against Retained Earnings (conversion equity).
            $totalDebit = round(array_sum(array_column($lines, 'debit')), 2);
            $totalCredit = round(array_sum(array_column($lines, 'credit')), 2);
            $net = round($totalDebit - $totalCredit, 2);

            if (abs($net) >= 0.01) {
                $lines[] = $net > 0
                    ? ['account_code' => LedgerService::RETAINED_EARNINGS, 'debit' => 0, 'credit' => $net, 'description' => 'Opening balance equity (balancing figure)']
                    : ['account_code' => LedgerService::RETAINED_EARNINGS, 'debit' => abs($net), 'credit' => 0, 'description' => 'Opening balance equity (balancing figure)'];
            }

            $entry = $ledger->post(
                'Opening balance sync: sub-ledger to general ledger',
                'conversion',
                null,
                $lines
            );

            return [
                'posted' => true,
                'entry_number' => $entry->entry_number,
                'lines' => count($lines),
                'total' => round(array_sum(array_column($lines, 'debit')), 2),
            ];
        });
    }

    private function pushLine(array &$lines, string $code, string $key, float $target, float $ledgerBalance, string $normalSide): void
    {
        $delta = round($target - $ledgerBalance, 2);

        if (abs($delta) < 0.01) {
            return;
        }

        // On its normal side a positive delta increases the account balance.
        $increase = $delta > 0;

        if ($normalSide === 'credit') {
            $lines[] = $increase
                ? ['account_code' => $code, 'debit' => 0, 'credit' => $delta, 'description' => "Opening balance: {$key}"]
                : ['account_code' => $code, 'debit' => abs($delta), 'credit' => 0, 'description' => "Opening balance adjustment: {$key}"];
        } else {
            $lines[] = $increase
                ? ['account_code' => $code, 'debit' => $delta, 'credit' => 0, 'description' => "Opening balance: {$key}"]
                : ['account_code' => $code, 'debit' => 0, 'credit' => abs($delta), 'description' => "Opening balance adjustment: {$key}"];
        }
    }
}
