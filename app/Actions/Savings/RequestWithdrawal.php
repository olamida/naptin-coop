<?php

namespace App\Actions\Savings;

use App\Actions\Action;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\User;
use App\Notifications\WithdrawalRequestedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RequestWithdrawal extends Action
{
    public function handle(int $memberId, float $amount, ?string $notes = null, string $source = 'manual', ?string $evidencePath = null, ?int $requestedBy = null): SavingsTransaction
    {
        $transaction = DB::transaction(function () use ($memberId, $amount, $notes, $source, $evidencePath, $requestedBy) {
            $account = SavingsAccount::where('member_id', $memberId)->lockForUpdate()->firstOrFail();
            $balanceBefore = $account->balance;

            $txn = SavingsTransaction::create([
                'savings_account_id' => $account->id,
                'reference' => 'SAV/WTH/'.strtoupper(Str::random(8)),
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore,
                'payment_evidence_path' => $evidencePath,
                'source' => $source,
                'notes' => $notes,
                'requested_by' => $requestedBy,
                'transaction_date' => now(),
                'status' => 'pending',
            ]);

            return $txn;
        });

        try {
            $approverUsers = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin', 'treasurer']))
                ->get();
            foreach ($approverUsers as $user) {
                $user->notify(new WithdrawalRequestedNotification($transaction));
            }
        } catch (\Exception $e) {
        }

        return $transaction;
    }
}
