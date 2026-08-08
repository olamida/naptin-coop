<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanRepaymentSchedule;
use App\Models\Member;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * Calculate monthly repayment including interest.
     * Formula: (amount × (1 + interest_rate / 100)) / tenure_months
     */
    public function calculateMonthlyRepayment(float $amount, float $interestRate, int $tenureMonths): float
    {
        $totalWithInterest = Money::mul($amount, Money::add(1, Money::div($interestRate, 100)));

        return round(Money::div($totalWithInterest, $tenureMonths), 2);
    }

    /**
     * Generate a unique loan number atomically using a database lock.
     */
    public function generateLoanNumber(): string
    {
        $year = date('Y');
        $prefix = 'REG/'.$year.'/';

        return DB::transaction(function () use ($prefix) {
            $last = Loan::withTrashed()
                ->where('loan_number', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByRaw('CAST(SUBSTRING(loan_number, -6) AS UNSIGNED) DESC')
                ->value('loan_number');

            $next = $last ? ((int) substr($last, -6)) + 1 : 1;

            return $prefix.str_pad($next, 6, '0', STR_PAD_LEFT);
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

            $newTotal = Money::add($totalOutstanding, $amount);
            if (Money::gt($newTotal, $product->max_total_amount_per_member)) {
                $remaining = Money::max(0, Money::sub($product->max_total_amount_per_member, $totalOutstanding));

                return "This member's total outstanding for {$product->name} would be ₦".number_format($newTotal, 2)
                    .'. Maximum allowed: ₦'.number_format($product->max_total_amount_per_member, 2)
                    .'. Remaining capacity: ₦'.number_format($remaining, 2).'.';
            }
        }

        if ($amount > $product->max_amount) {
            return 'Amount exceeds the maximum of ₦'.number_format($product->max_amount, 2)." for {$product->name}.";
        }

        if ($amount < $product->min_amount) {
            return 'Amount is below the minimum of ₦'.number_format($product->min_amount, 2)." for {$product->name}.";
        }

        if ($tenureMonths > $product->max_term_months) {
            return "Tenure exceeds the maximum of {$product->max_term_months} months for {$product->name}.";
        }

        // CBN single obligor limit: a member's total loan exposure may not exceed 5% of
        // the current loan portfolio. Skipped while the portfolio is empty so the very
        // first loans in a greenfield cooperative are not blocked.
        $portfolio = Loan::whereIn('status', ['disbursed', 'repaying', 'defaulted'])
            ->where('outstanding', '>', 0)
            ->sum('outstanding');

        if ($portfolio > 0) {
            $limit = Money::percent($portfolio, 5);
            $memberExposure = Money::add(
                Loan::where('member_id', $memberId)
                    ->whereIn('status', ['pending', 'approved', 'disbursed', 'repaying'])
                    ->sum('outstanding'),
                $amount
            );

            if (Money::gt($memberExposure, $limit)) {
                return 'CBN single obligor limit exceeded: this member\'s exposure of ₦'
                    .number_format($memberExposure, 2).' would exceed 5% of the loan portfolio (₦'
                    .number_format($limit, 2).').';
            }
        }

        return null;
    }

    /**
     * Calculate the interest portion of a repayment using flat-rate split.
     */
    public function splitRepayment(float $amount, float $interestRatePercent): array
    {
        $interestRate = Money::div($interestRatePercent, 100);

        if (Money::lte($interestRate, 0)) {
            return [
                'principal_portion' => $amount,
                'interest_portion' => 0.0,
            ];
        }

        $interestPortion = Money::mul($amount, Money::div($interestRate, Money::add(1, $interestRate)));
        $principalPortion = Money::sub($amount, $interestPortion);

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

        $monthlyPrincipal = round(Money::div($amount, $tenure), 2);
        $monthlyInterest = round(Money::div(Money::mul($amount, Money::div($rate, 100)), $tenure), 2);

        $balance = $amount;
        $rows = [];
        for ($i = 1; $i <= $tenure; $i++) {
            $principal = $monthlyPrincipal;
            $interest = $monthlyInterest;
            if ($i === $tenure) {
                $principal = round(Money::sub($amount, Money::mul($monthlyPrincipal, $tenure - 1)), 2);
                $interest = round(Money::sub(Money::mul($amount, Money::div($rate, 100)), Money::mul($monthlyInterest, $tenure - 1)), 2);
            }
            $balance = Money::sub($balance, $principal);

            $rows[] = [
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'due_date' => now()->addMonths($i)->startOfMonth()->toDateString(),
                'principal_amount' => $principal,
                'interest_amount' => $interest,
                'total_amount' => Money::add($principal, $interest),
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
        $remaining = Money::add($principalPaid, 0);
        $schedules = $loan->schedules()
            ->where('status', '!=', 'paid')
            ->orderBy('installment_number')
            ->lockForUpdate()
            ->get();

        foreach ($schedules as $schedule) {
            if (Money::lte($remaining, 0)) {
                break;
            }

            $owed = Money::sub((float) $schedule->principal_amount, (float) $schedule->amount_paid);
            if (Money::lte($owed, 0)) {
                continue;
            }

            $apply = Money::min($remaining, $owed);
            $newPaid = Money::add((float) $schedule->amount_paid, $apply);
            $fullyPaid = Money::gte($newPaid, (float) $schedule->principal_amount);

            $schedule->update([
                'amount_paid' => $newPaid,
                'status' => $fullyPaid ? 'paid' : $schedule->status,
                'paid_at' => $fullyPaid ? $paymentDate : $schedule->paid_at,
            ]);

            $remaining = Money::sub($remaining, $apply);
        }
    }
}
