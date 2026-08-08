<?php

namespace App\Actions\Dividends;

use App\Actions\Action;
use App\Models\Dividend;
use App\Models\DividendDistribution;
use App\Models\ShareAccount;
use App\Services\LedgerService;
use Illuminate\Support\Facades\DB;

class CalculateDividend extends Action
{
    public function handle(Dividend $dividend): Dividend
    {
        if ($dividend->status !== 'draft') {
            throw new \RuntimeException('Only draft dividends can be calculated.');
        }

        DB::transaction(function () use ($dividend) {
            $shareAccounts = ShareAccount::where('total_shares', '>', 0)->with('member')->get();
            $totalShares = $shareAccounts->sum('total_shares');

            if ($totalShares <= 0) {
                return;
            }

            $dividend->update([
                'eligible_members' => $shareAccounts->count(),
            ]);

            $perShareDividend = $dividend->total_profit / $totalShares;

            foreach ($shareAccounts as $account) {
                $amount = round($account->total_shares * $perShareDividend, 2);

                DividendDistribution::create([
                    'dividend_id' => $dividend->id,
                    'member_id' => $account->member_id,
                    'share_count' => $account->total_shares,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
            }

            $totalDistributed = DividendDistribution::where('dividend_id', $dividend->id)->sum('amount');

            $dividend->update([
                'total_distributed' => $totalDistributed,
                'status' => 'calculated',
            ]);

            // Accrue the liability: Dr Retained Earnings / Cr Dividend Payable (2201).
            app(LedgerService::class)->postDividendAccrual($dividend->id, (float) $totalDistributed);
        });

        return $dividend->fresh();
    }
}
