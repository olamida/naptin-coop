<?php

use App\Jobs\ExpireGuarantorInvites;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:detect-loan-arrears')->daily();
Schedule::job(new ExpireGuarantorInvites)->daily();
