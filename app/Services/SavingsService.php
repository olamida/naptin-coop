<?php

namespace App\Services;

use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SavingsService
{
    /**
     * Record a deposit atomically using increment to prevent race conditions.
     */
    public function recordDeposit(int $memberId, float $amount, ?string $notes = null, string $source = 'manual'): SavingsTransaction
    {
        $account = SavingsAccount::where('member_id', $memberId)->lockForUpdate()->firstOrFail();
        $balanceBefore = $account->balance;

        $account->increment('balance', $amount);
        $account->refresh();

        return SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'reference' => 'SAV/DEP/' . strtoupper(Str::random(8)),
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $account->balance,
            'source' => $source,
            'notes' => $notes,
            'transaction_date' => now(),
            'status' => 'completed',
        ]);
    }

    /**
     * Record a withdrawal request (pending approval).
     */
    public function recordWithdrawalRequest(int $memberId, float $amount, ?string $notes = null, string $source = 'manual'): SavingsTransaction
    {
        $account = SavingsAccount::where('member_id', $memberId)->lockForUpdate()->firstOrFail();
        $balanceBefore = $account->balance;

        return SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'reference' => 'SAV/WTH/' . strtoupper(Str::random(8)),
            'type' => 'withdrawal',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore,
            'source' => $source,
            'notes' => $notes,
            'transaction_date' => now(),
            'status' => 'pending',
        ]);
    }

    /**
     * Approve a pending withdrawal atomically.
     */
    public function approveWithdrawal(SavingsTransaction $transaction): SavingsTransaction
    {
        return DB::transaction(function () use ($transaction) {
            $account = SavingsAccount::where('id', $transaction->savings_account_id)->lockForUpdate()->firstOrFail();

            if ($transaction->amount > $account->balance) {
                throw new \RuntimeException('Insufficient balance for withdrawal.');
            }

            $balanceBefore = $account->balance;
            $account->decrement('balance', $transaction->amount);
            $account->refresh();

            $transaction->update([
                'status' => 'completed',
                'balance_after' => $account->balance,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return $transaction->fresh();
        });
    }

    /**
     * Approve a pending deposit atomically.
     */
    public function approveDeposit(SavingsTransaction $transaction): SavingsTransaction
    {
        return DB::transaction(function () use ($transaction) {
            $account = SavingsAccount::where('id', $transaction->savings_account_id)->lockForUpdate()->firstOrFail();

            $balanceBefore = $account->balance;
            $account->increment('balance', $transaction->amount);
            $account->refresh();

            $transaction->update([
                'status' => 'completed',
                'balance_after' => $account->balance,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return $transaction->fresh();
        });
    }
}
