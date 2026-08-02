<?php

namespace App\Actions\Savings;

use App\Actions\Action;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Notifications\WithdrawalStatusNotification;
use App\Services\LedgerService;
use Illuminate\Support\Facades\DB;

class ApproveWithdrawal extends Action
{
    public function handle(SavingsTransaction $transaction): SavingsTransaction
    {
        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
            throw new \RuntimeException('Only pending withdrawals can be approved.');
        }

        $transaction = DB::transaction(function () use ($transaction) {
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

            app(LedgerService::class)->postSavingsWithdrawal($transaction->id, $transaction->amount);

            return $transaction->fresh();
        });

        if ($transaction->savingsAccount && $transaction->savingsAccount->member && $transaction->savingsAccount->member->user) {
            try {
                $transaction->savingsAccount->member->user->notify(
                    new WithdrawalStatusNotification($transaction, 'pending')
                );
            } catch (\Exception $e) {
            }
        }

        return $transaction;
    }
}
