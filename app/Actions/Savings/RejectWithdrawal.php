<?php

namespace App\Actions\Savings;

use App\Actions\Action;
use App\Models\SavingsTransaction;
use App\Notifications\WithdrawalStatusNotification;

class RejectWithdrawal extends Action
{
    public function handle(SavingsTransaction $transaction, string $reason): SavingsTransaction
    {
        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
            throw new \RuntimeException('Only pending withdrawals can be rejected.');
        }

        $transaction->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($transaction->savingsAccount && $transaction->savingsAccount->member && $transaction->savingsAccount->member->user) {
            try {
                $transaction->savingsAccount->member->user->notify(
                    new WithdrawalStatusNotification($transaction, 'pending')
                );
            } catch (\Exception $e) {
            }
        }

        return $transaction->fresh();
    }
}
