<?php

namespace App\Actions\Loans;

use App\Actions\Action;
use App\Enums\GuarantorStatus;
use App\Models\Loan;
use App\Models\LoanApprovalLog;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\User;
use App\Notifications\GuarantorRequestNotification;
use App\Notifications\LoanAppliedNotification;
use App\Services\LoanService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateLoan extends Action
{
    public function handle(array $data): Loan
    {
        $loanService = app(LoanService::class);

        if (! empty($data['loan_product_id'])) {
            $product = LoanProduct::find($data['loan_product_id']);
            if ($product) {
                $error = $loanService->validateLoanProduct(
                    $product,
                    $data['member_id'],
                    $data['amount'],
                    $data['tenure_months']
                );
                if ($error) {
                    throw new \RuntimeException($error);
                }
            }
        }

        $monthlyRepayment = $loanService->calculateMonthlyRepayment(
            $data['amount'],
            $data['interest_rate'] ?? 0,
            $data['tenure_months']
        );

        $loanNumber = $loanService->generateLoanNumber();

        return DB::transaction(function () use ($data, $loanNumber, $monthlyRepayment) {
            $loan = Loan::create([
                'member_id' => $data['member_id'],
                'loan_product_id' => $data['loan_product_id'] ?? null,
                'loan_number' => $loanNumber,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'interest_rate' => $data['interest_rate'],
                'tenure_months' => $data['tenure_months'],
                'monthly_repayment' => $monthlyRepayment,
                'outstanding' => $data['amount'],
                'application_date' => now()->toDateString(),
                'purpose' => $data['purpose'] ?? null,
                'status' => 'pending',
            ]);

            if (! empty($data['guarantor_ids'])) {
                foreach ($data['guarantor_ids'] as $guarantorId) {
                    $guarantor = LoanGuarantor::create([
                        'loan_id' => $loan->id,
                        'member_id' => $guarantorId,
                        'status' => GuarantorStatus::Pending,
                        'accept_token' => Str::random(64),
                        'token_expires_at' => now()->addHours(72),
                    ]);

                    $guarantorMember = Member::find($guarantorId);
                    if ($guarantorMember && $guarantorMember->user) {
                        try {
                            $guarantorMember->user->notify(new GuarantorRequestNotification($guarantor));
                        } catch (\Exception $e) {
                        }
                    }
                }
            }

            LoanApprovalLog::record($loan->id, 'submitted', null, 'pending', 'Loan application submitted.');

            return $loan;
        });
    }

    public function notifyReviewers(Loan $loan): void
    {
        try {
            $reviewerUsers = User::where('id', '!=', auth()->id())
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin', 'loan-officer']))
                ->get();
            foreach ($reviewerUsers as $user) {
                $user->notify(new LoanAppliedNotification($loan));
            }
        } catch (\Exception $e) {
        }
    }
}
