<?php

namespace App\Actions\Savings;

use App\Actions\Action;
use App\Models\SavingsTransaction;

class RejectDeposit extends Action
{
    public function handle(SavingsTransaction $transaction, string $reason): SavingsTransaction
    {
        if ($transaction->type !== 'deposit' || $transaction->status !== 'pending') {
            throw new \RuntimeException('Only pending deposits can be rejected.');
        }

        $transaction->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $transaction->fresh();
    }
}
