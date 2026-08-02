<?php

namespace App\Actions\Loans;

use App\Actions\Action;
use App\Models\Loan;
use App\Models\LoanApprovalLog;
use App\Notifications\LoanStatusNotification;
use Illuminate\Support\Facades\DB;

class RejectLoan extends Action
{
    public function handle(Loan $loan, string $reason): Loan
    {
        if ($loan->status !== 'pending') {
            throw new \RuntimeException('Only pending loans can be rejected.');
        }

        return DB::transaction(function () use ($loan, $reason) {
            $oldStatus = $loan->status;

            $loan->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            LoanApprovalLog::record($loan->id, 'rejected', $oldStatus, 'rejected', $reason);

            if ($loan->member && $loan->member->user) {
                try {
                    $loan->member->user->notify(new LoanStatusNotification($loan, $oldStatus, 'rejected'));
                } catch (\Exception $e) {
                }
            }

            return $loan->fresh();
        });
    }
}
