<?php

namespace App\Actions\Loans;

use App\Actions\Action;
use App\Models\Loan;
use App\Models\LoanApprovalLog;
use App\Notifications\LoanStatusNotification;
use Illuminate\Support\Facades\DB;

class ApproveLoan extends Action
{
    public function handle(Loan $loan): Loan
    {
        if ($loan->status !== 'pending') {
            throw new \RuntimeException('Only pending loans can be approved.');
        }

        if ($loan->guarantors()->exists()) {
            $acceptedCount = $loan->guarantors()->where('status', 'accepted')->count();
            $totalCount = $loan->guarantors()->count();

            if ($acceptedCount < $totalCount) {
                throw new \RuntimeException(
                    "Cannot approve: {$acceptedCount} of {$totalCount} guarantors have accepted. All guarantors must accept before approval."
                );
            }
        }

        return DB::transaction(function () use ($loan) {
            $locked = Loan::whereKey($loan->id)->lockForUpdate()->first();

            if ($locked->status !== 'pending') {
                throw new \RuntimeException('Only pending loans can be approved.');
            }

            if ($locked->guarantors()->exists()) {
                $acceptedCount = $locked->guarantors()->where('status', 'accepted')->count();
                $totalCount = $locked->guarantors()->count();

                if ($acceptedCount < $totalCount) {
                    throw new \RuntimeException(
                        "Cannot approve: {$acceptedCount} of {$totalCount} guarantors have accepted. All guarantors must accept before approval."
                    );
                }
            }

            $oldStatus = $locked->status;

            $locked->update([
                'status' => 'approved',
                'approval_date' => now()->toDateString(),
                'approved_by' => auth()->id(),
            ]);

            LoanApprovalLog::record($locked->id, 'approved', $oldStatus, 'approved', 'Loan approved.');

            if ($locked->member && $locked->member->user) {
                try {
                    $locked->member->user->notify(new LoanStatusNotification($locked, $oldStatus, 'approved'));
                } catch (\Exception $e) {}
            }

            return $locked->fresh();
        });
    }
}
