<?php

use App\Jobs\ExpireGuarantorInvites;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:detect-loan-arrears')->daily();
Schedule::job(new ExpireGuarantorInvites)->daily();

// Scheduled finance jobs (audit P5 #29-#32).
Schedule::command('app:backup-encrypted')->dailyAt('02:00');
Schedule::command('app:verify-ledger-hash-chain')->dailyAt('04:00');
Schedule::command('app:reconcile-control-accounts')->dailyAt('23:00');
Schedule::command('app:calculate-provisioning')->lastDayOfMonth('06:00');
