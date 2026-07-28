<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanProduct;
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

        return DB::transaction(function () use ($prefix, $year) {
            $count = Loan::whereYear('created_at', $year)->lockForUpdate()->count() + 1;

            return $prefix . str_pad($count, 6, '0', STR_PAD_LEFT);
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
}
