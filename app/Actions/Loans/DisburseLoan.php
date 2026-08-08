<?php

namespace App\Actions\Loans;

use App\Actions\Action;
use App\Models\Loan;
use App\Models\LoanApprovalLog;
use App\Notifications\LoanStatusNotification;
use App\Services\LedgerService;
use App\Services\LoanService;
use Illuminate\Support\Facades\DB;

class DisburseLoan extends Action
{
    public function handle(Loan $loan): Loan
    {
        if ($loan->status !== 'approved') {
            throw new \RuntimeException('Only approved loans can be disbursed.');
        }

        return DB::transaction(function () use ($loan) {
            $locked = Loan::whereKey($loan->id)->lockForUpdate()->first();

            if ($locked->status !== 'approved') {
                throw new \RuntimeException('Only approved loans can be disbursed.');
            }

            $maturityDate = now()->addMonths($locked->tenure_months)->toDateString();
            $oldStatus = $locked->status;

            $locked->update([
                'status' => 'disbursed',
                'disbursement_date' => now()->toDateString(),
                'maturity_date' => $maturityDate,
            ]);

            LoanApprovalLog::record($locked->id, 'disbursed', $oldStatus, 'disbursed', 'Loan disbursed. Maturity: '.$maturityDate);

            app(LoanService::class)->generateRepaymentSchedules($locked);

            app(LedgerService::class)->postLoanDisbursement($locked->id, $locked->amount, (float) $locked->processing_fee);

            if ($locked->member && $locked->member->user) {
                try {
                    $locked->member->user->notify(new LoanStatusNotification($locked, $oldStatus, 'disbursed'));
                } catch (\Exception $e) {
                }
            }

            return $locked->fresh();
        });
    }
}
