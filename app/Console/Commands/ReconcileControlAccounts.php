<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\ControlVarianceNotification;
use App\Services\LedgerService;
use App\Support\Money;
use Illuminate\Console\Command;

class ReconcileControlAccounts extends Command
{
    protected $signature = 'app:reconcile-control-accounts';

    protected $description = 'Compare ledger control accounts to their sub-ledger totals and alert admins on variance';

    public function handle(LedgerService $ledger): int
    {
        $rows = $ledger->validateControlAccounts();

        $variances = array_values(array_filter(
            $rows,
            fn (array $row) => ! $row['reconciled'] && Money::gte(Money::abs($row['variance']), 0.01)
        ));

        if (empty($variances)) {
            $this->info('Control accounts reconciled — all sub-ledgers match the ledger.');

            return self::SUCCESS;
        }

        $summary = collect($variances)
            ->map(fn (array $row) => "{$row['code']} {$row['name']}: variance {$row['variance']}")
            ->implode('; ');

        ActivityLog::log('control_reconciliation_variance', 'Scheduled reconciliation found variance: '.$summary);

        User::whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin']))
            ->get()->each(fn ($user) => $user->notify(new ControlVarianceNotification($variances)));

        $this->error(count($variances).' control account(s) out of balance: '.$summary);

        return self::SUCCESS;
    }
}
