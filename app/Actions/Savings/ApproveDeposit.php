<?php

namespace App\Actions\Savings;

use App\Actions\Action;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Services\LedgerService;
use Illuminate\Support\Facades\DB;

class ApproveDeposit extends Action
{
    public function handle(SavingsTransaction $transaction): SavingsTransaction
    {
        if ($transaction->type !== 'deposit' || $transaction->status !== 'pending') {
            throw new \RuntimeException('Only pending deposits can be confirmed.');
        }

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

            app(LedgerService::class)->postSavingsDeposit($transaction->id, $transaction->amount);

            return $transaction->fresh();
        });
    }
}
