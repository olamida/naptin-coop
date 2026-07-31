<?php

namespace App\Actions\Savings;

use App\Actions\Action;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Services\LedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostDeposit extends Action
{
    public function handle(int $memberId, float $amount, ?string $notes = null, string $source = 'manual', ?string $evidencePath = null): SavingsTransaction
    {
        return DB::transaction(function () use ($memberId, $amount, $notes, $source, $evidencePath) {
            $account = SavingsAccount::where('member_id', $memberId)->lockForUpdate()->firstOrFail();
            $balanceBefore = $account->balance;

            $account->increment('balance', $amount);
            $account->refresh();

            $txn = SavingsTransaction::create([
                'savings_account_id' => $account->id,
                'reference' => 'SAV/DEP/' . strtoupper(Str::random(8)),
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $account->balance,
                'source' => $source,
                'notes' => $notes,
                'payment_evidence_path' => $evidencePath,
                'transaction_date' => now(),
                'status' => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            app(LedgerService::class)->postSavingsDeposit($txn->id, $amount);

            return $txn;
        });
    }
}
