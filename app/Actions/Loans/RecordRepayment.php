<?php

namespace App\Actions\Loans;

use App\Actions\Action;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Notifications\LoanStatusNotification;
use App\Services\LedgerService;
use App\Services\LoanService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecordRepayment extends Action
{
    public function handle(Loan $loan, array $data): array
    {
        $amount = round($data['amount'], 2);

        if ($amount > $loan->outstanding) {
            throw new \RuntimeException(
                'Payment exceeds outstanding amount of ₦'.number_format($loan->outstanding, 2)
            );
        }

        $loanService = app(LoanService::class);
        $split = $loanService->splitRepayment($amount, $loan->interest_rate);

        return DB::transaction(function () use ($loan, $data, $amount, $split) {
            $locked = Loan::whereKey($loan->id)->lockForUpdate()->first();

            if ($amount > $locked->outstanding) {
                throw new \RuntimeException(
                    'Payment exceeds outstanding amount of ₦'.number_format($locked->outstanding, 2)
                );
            }

            $outstandingAfter = round($locked->outstanding - $split['principal_portion'], 2);
            $newStatus = $outstandingAfter <= 0 ? 'completed' : 'repaying';

            $repayment = LoanRepayment::create([
                'loan_id' => $locked->id,
                'member_id' => $locked->member_id,
                'reference' => 'LN/REPAY/'.strtoupper(Str::random(8)),
                'amount' => $amount,
                'principal_portion' => $split['principal_portion'],
                'interest_portion' => $split['interest_portion'],
                'fees_portion' => 0,
                'outstanding_after' => $outstandingAfter,
                'payment_method' => $data['payment_method'],
                'source' => 'manual',
                'payment_date' => $data['payment_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            app(LoanService::class)->applyPrincipalToSchedules(
                $locked,
                $split['principal_portion'],
                $data['payment_date']
            );

            app(LedgerService::class)->postLoanRepayment(
                $locked->id,
                $repayment->id,
                $split['principal_portion'],
                $split['interest_portion']
            );

            $locked->update([
                'total_repaid' => $locked->total_repaid + $amount,
                'outstanding' => max(0, $outstandingAfter),
                'status' => $newStatus,
            ]);

            if ($newStatus === 'completed' && $locked->member && $locked->member->user) {
                try {
                    $locked->member->user->notify(
                        new LoanStatusNotification($locked, 'repaying', 'completed')
                    );
                } catch (\Exception $e) {
                }
            }

            return [
                'loan' => $loan->fresh(),
                'outstanding_after' => $outstandingAfter,
                'is_completed' => $outstandingAfter <= 0,
            ];
        });
    }
}
