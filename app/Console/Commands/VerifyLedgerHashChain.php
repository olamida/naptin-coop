<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\LedgerTamperNotification;
use App\Services\LedgerService;
use Illuminate\Console\Command;

class VerifyLedgerHashChain extends Command
{
    protected $signature = 'app:verify-ledger-hash-chain';

    protected $description = 'Recompute the ledger hash chain and alert admins if any entry was tampered with';

    public function handle(LedgerService $ledger): int
    {
        $broken = $ledger->verifyHashChain();

        if (empty($broken)) {
            $this->info('Ledger hash chain verified — all entries intact.');

            return self::SUCCESS;
        }

        $ids = collect($broken)->pluck('entry_number')->implode(', ');

        ActivityLog::log('ledger_tamper_detected', 'Hash-chain verification found '.count($broken).' tampered journal entries: '.$ids);

        User::whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin']))
            ->get()->each(fn ($user) => $user->notify(new LedgerTamperNotification($broken)));

        $this->error(count($broken).' tampered journal entr'.(count($broken) === 1 ? 'y' : 'ies').' detected: '.$ids);

        return self::SUCCESS;
    }
}
