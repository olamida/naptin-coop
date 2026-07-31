<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanRepaymentSchedule;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanService
{
    /**
     * Calculate monthly repayment including interest.
     * Formula: (amount × (1 + interest_rate / 100)) / tenure_months
     */
    public function calculateMonthlyRepayment(float $amount, float $interestRate, int $tenureMonths): float
    {
        $totalWithInterest = $amount * (1 + $interestRate / 100);

        return round($totalWithInterest / $tenureMonths, 2);
    }

    /**
     * Generate a unique loan number atomically using a database lock.
     */
    public function generateLoanNumber(): string
    {
        $year = date('Y');
        $prefix = 'REG/' . $year . '/';

        return DB::transaction(function () use ($prefix) {
            $last = Loan::withTrashed()
                ->where('loan_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByRaw('CAST(SUBSTRING(loan_number, -6) AS UNSIGNED) DESC')
                ->value('loan_number');

            $next = $last ? ((int) substr($last, -6)) + 1 : 1;

            return $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Validate loan product constraints for a member.
     * Returns null on success, error message string on failure.
     */
    public function validateLoanProduct(LoanProduct $product, int $memberId, float $amount, int $tenureMonths): ?string
    {
        if ($product->max_loans_per_member) {
            $activeCount = Loan::where('member_id', $memberId)
                ->where('loan_product_id', $product->id)
                ->whereIn('status', ['pending', 'approved', 'disbursed', 'repaying'])
                ->count();

            if ($activeCount >= $product->max_loans_per_member) {
                return "This member already has {$activeCount} active loan(s) for {$product->name}. Maximum allowed: {$product->max_loans_per_member}.";
            }
        }

        if ($product->max_total_amount_per_member) {
            $totalOutstanding = Loan::where('member_id', $memberId)
                ->where('loan_product_id', $product->id)
                ->whereIn('status', ['pending', 'approved', 'disbursed', 'repaying'])
                ->sum('outstanding');

            $newTotal = $totalOutstanding + $amount;
            if ($newTotal > $product->max_total_amount_per_member) {
                $remaining = max(0, $product->max_total_amount_per_member - $totalOutstanding);

                return "This member's total outstanding for {$product->name} would be ₦" . number_format($newTotal, 2)
                    . ". Maximum allowed: ₦" . number_format($product->max_total_amount_per_member, 2)
                    . ". Remaining capacity: ₦" . number_format($remaining, 2) . ".";
            }
        }

        if ($amount > $product->max_amount) {
            return "Amount exceeds the maximum of ₦" . number_format($product->max_amount, 2) . " for {$product->name}.";
        }

        if ($amount < $product->min_amount) {
            return "Amount is below the minimum of ₦" . number_format($product->min_amount, 2) . " for {$product->name}.";
        }

        if ($tenureMonths > $product->max_term_months) {
            return "Tenure exceeds the maximum of {$product->max_term_months} months for {$product->name}.";
        }

        return null;
    }

    /**
     * Calculate the interest portion of a repayment using flat-rate split.
     */
    public function splitRepayment(float $amount, float $interestRatePercent): array
    {
        $interestRate = $interestRatePercent / 100;

        if ($interestRate <= 0) {
            return [
                'principal_portion' => $amount,
                'interest_portion' => 0.0,
            ];
        }

        $interestPortion = round($amount * ($interestRate / (1 + $interestRate)), 2);
        $principalPortion = round($amount - $interestPortion, 2);

        return [
            'principal_portion' => $principalPortion,
            'interest_portion' => $interestPortion,
        ];
    }

    /**
     * Generate the amortization schedule for a loan (flat-rate equal installments).
     * Mirrors the wizard calculation; rounding drift is absorbed on the final installment.
     */
    public function generateRepaymentSchedules(Loan $loan): void
    {
        $amount = (float) $loan->amount;
        $rate = (float) $loan->interest_rate;
        $tenure = max(1, (int) $loan->tenure_months);

        $monthlyPrincipal = round($amount / $tenure, 2);
        $monthlyInterest = round(($amount * ($rate / 100)) / $tenure, 2);

        $balance = $amount;
        $rows = [];
        for ($i = 1; $i <= $tenure; $i++) {
            $principal = $monthlyPrincipal;
            $interest = $monthlyInterest;
            if ($i === $tenure) {
                $principal = round($amount - $monthlyPrincipal * ($tenure - 1), 2);
                $interest = round($amount * ($rate / 100) - $monthlyInterest * ($tenure - 1), 2);
            }
            $balance = round($balance - $principal, 2);

            $rows[] = [
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'due_date' => now()->addMonths($i)->startOfMonth()->toDateString(),
                'principal_amount' => $principal,
                'interest_amount' => $interest,
                'total_amount' => round($principal + $interest, 2),
                'balance_after' => $balance,
                'status' => 'pending',
                'amount_paid' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        LoanRepaymentSchedule::where('loan_id', $loan->id)->delete();
        LoanRepaymentSchedule::insert($rows);
    }

    /**
     * Apply a principal payment across the loan's unpaid schedules, marking
     * fully covered installments as paid (in installment order).
     */
    public function applyPrincipalToSchedules(Loan $loan, float $principalPaid, string $paymentDate): void
    {
        $remaining = round($principalPaid, 2);
        $schedules = $loan->schedules()
            ->where('status', '!=', 'paid')
            ->orderBy('installment_number')
            ->lockForUpdate()
            ->get();

        foreach ($schedules as $schedule) {
            if ($remaining <= 0) {
                break;
            }

            $owed = round((float) $schedule->principal_amount - (float) $schedule->amount_paid, 2);
            if ($owed <= 0) {
                continue;
            }

            $apply = min($remaining, $owed);
            $newPaid = round((float) $schedule->amount_paid + $apply, 2);
            $fullyPaid = $newPaid >= (float) $schedule->principal_amount;

            $schedule->update([
                'amount_paid' => $newPaid,
                'status' => $fullyPaid ? 'paid' : $schedule->status,
                'paid_at' => $fullyPaid ? $paymentDate : $schedule->paid_at,
            ]);

            $remaining = round($remaining - $apply, 2);
        }
    }
}
