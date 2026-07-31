<?php

namespace App\Actions\Shares;

use App\Actions\Action;
use App\Models\ShareAccount;
use App\Models\ShareTransaction;
use App\Services\LedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseShares extends Action
{
    public function handle(int $memberId, int $shares, ?string $notes = null): ShareTransaction
    {
        if ($shares <= 0) {
            throw new \RuntimeException('Number of shares must be at least 1.');
        }

        return DB::transaction(function () use ($memberId, $shares, $notes) {
            $account = ShareAccount::where('member_id', $memberId)
                ->lockForUpdate()
                ->firstOrFail();

            $sharePrice = $account->share_price;
            $amount = round($shares * $sharePrice, 2);

            $newTotalShares = $account->total_shares + $shares;
            $newTotalValue = $newTotalShares * $sharePrice;

            $account->update([
                'total_shares' => $newTotalShares,
                'total_value' => $newTotalValue,
            ]);

            $shareTxn = ShareTransaction::create([
                'share_account_id' => $account->id,
                'reference' => 'SHR/PUR/' . strtoupper(Str::random(8)),
                'type' => 'purchase',
                'shares' => $shares,
                'amount' => $amount,
                'balance_after' => $newTotalShares,
                'notes' => $notes,
                'transaction_date' => now(),
            ]);

            app(LedgerService::class)->postSharePurchase($shareTxn->id, $amount);

            return $shareTxn;
        });
    }
}
