<?php

namespace App\Actions\Dividends;

use App\Actions\Action;
use App\Models\Dividend;
use App\Models\DividendDistribution;
use App\Notifications\WithdrawalStatusNotification;
use App\Services\LedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DistributeDividend extends Action
{
    public function handle(Dividend $dividend): Dividend
    {
        if ($dividend->status !== 'approved') {
            throw new \RuntimeException('Only approved dividends can be distributed.');
        }

        DB::transaction(function () use ($dividend) {
            $distributions = DividendDistribution::where('dividend_id', $dividend->id)
                ->whereIn('status', ['pending', 'approved'])
                ->get();

            foreach ($distributions as $dist) {
                $dist->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                app(LedgerService::class)
                    ->postDividendDistribution($dist->id, (float) $dist->amount);
            }

            $dividend->update(['status' => 'completed']);
        });

        try {
            $distributions = DividendDistribution::where('dividend_id', $dividend->id)
                ->where('status', 'paid')
                ->with('member.user')
                ->get();
            foreach ($distributions as $dist) {
                if ($dist->member && $dist->member->user) {
                    $dist->member->user->notify(new WithdrawalStatusNotification($dist, 'completed'));
                }
            }
        } catch (\Exception $e) {
            Log::error('Dividend distribution notification failed: ' . $e->getMessage());
        }

        return $dividend->fresh();
    }
}
