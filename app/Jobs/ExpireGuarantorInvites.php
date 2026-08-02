<?php

namespace App\Jobs;

use App\Enums\GuarantorStatus;
use App\Models\LoanGuarantor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireGuarantorInvites implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = now();

        LoanGuarantor::where('status', GuarantorStatus::Pending->value)
            ->where('token_expires_at', '<', $now)
            ->update([
                'status' => GuarantorStatus::Declined->value,
                'notes' => 'Invitation expired on '.$now->toDateTimeString().'.',
                'responded_at' => $now,
            ]);
    }
}
