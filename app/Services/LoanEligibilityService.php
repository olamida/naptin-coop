<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\MemberLoanEligibilityOverride;
use App\Models\PayrollArrear;
use App\Models\PayrollDeductionCap;
use App\Models\PurchaseOrder;

class LoanEligibilityService
{
    /**
     * Calculate maximum eligible loan amount for a member based on savings and overrides.
     *
     * @return array{
     *     savings_balance: float,
     *     default_multiplier: float,
     *     applied_multiplier: float,
     *     max_eligible: float,
     *     is_override: bool,
     *     override_details: MemberLoanEligibilityOverride|null,
     *     formula: string
     * }
     */
    public function calculateMaxEligibleAmount(Member $member, LoanProduct $product): array
    {
        $savingsBalance = (float) ($member->savingsAccount?->balance ?? 0);

        // Check for active override
        $override = $this->getActiveOverride($member, $product);

        if ($override && $override->custom_multiplier) {
            $appliedMultiplier = (float) $override->custom_multiplier;
            $isOverride = true;
        } else {
            $appliedMultiplier = (float) $product->default_multiplier;
            $isOverride = false;
        }

        // Cap by product max_multiplier
        $appliedMultiplier = min($appliedMultiplier, (float) $product->max_multiplier);

        // Calculate max eligible
        $maxEligible = $savingsBalance * $appliedMultiplier;

        // Cap by product max_amount if set
        if ($product->max_amount) {
            $maxEligible = min($maxEligible, (float) $product->max_amount);
        }

        // Cap by product max_total_amount_per_member if set (considering existing loans)
        if ($product->max_total_amount_per_member) {
            $existingTotal = Loan::where('member_id', $member->id)
                ->where('loan_product_id', $product->id)
                ->whereIn('status', ['pending', 'approved', 'disbursed', 'repaying'])
                ->sum('outstanding');
            $remaining = (float) $product->max_total_amount_per_member - $existingTotal;
            $maxEligible = min($maxEligible, max(0, $remaining));
        }

        $formula = '₦'.number_format($savingsBalance, 2).' x '.$appliedMultiplier.' = ₦'.number_format($maxEligible, 2);

        return [
            'savings_balance' => $savingsBalance,
            'default_multiplier' => (float) $product->default_multiplier,
            'applied_multiplier' => $appliedMultiplier,
            'max_eligible' => round($maxEligible, 2),
            'is_override' => $isOverride,
            'override_details' => $override,
            'formula' => $formula,
        ];
    }

    /**
     * Calculate total deductions as percentage of net salary.
     *
     * @return array{
     *     net_salary: float,
     *     current_deductions: float,
     *     current_percent: float,
     *     new_loan_repayment: float,
     *     projected_total: float,
     *     projected_percent: float,
     *     default_cap: float,
     *     applied_cap: float,
     *     hard_cap: float,
     *     is_within_default: bool,
     *     is_within_override: bool,
     *     is_exceeds_hard: bool,
     *     requires_override: bool
     * }
     */
    public function calculateTotalDeductionsPercent(Member $member, ?float $newLoanMonthlyRepayment = null): array
    {
        // Get net salary (use monthly_net_salary if set, otherwise monthly_salary)
        $netSalary = $member->monthly_net_salary ?? $member->monthly_salary ?? 0;

        if ($netSalary <= 0) {
            return $this->emptyDeductionResult();
        }

        // Calculate current deductions
        $currentDeductions = $this->calculateCurrentDeductions($member);

        // Get caps
        $capConfig = $this->getDeductionCapConfig();
        $defaultCap = $capConfig['default_max_percent'];
        $hardCap = $capConfig['hard_max_percent'];

        // Check for member-specific override
        $appliedCap = $defaultCap;
        $override = MemberLoanEligibilityOverride::where('member_id', $member->id)
            ->where('is_active', true)
            ->where('valid_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString());
            })
            ->whereNotNull('custom_max_deduction_percent')
            ->first();

        if ($override) {
            $appliedCap = (float) $override->custom_max_deduction_percent;
        }

        $currentPercent = ($currentDeductions / $netSalary) * 100;
        $newRepayment = $newLoanMonthlyRepayment ?? 0;
        $projectedTotal = $currentDeductions + $newRepayment;
        $projectedPercent = ($projectedTotal / $netSalary) * 100;

        $isWithinDefault = $projectedPercent <= $defaultCap;
        $isWithinOverride = $projectedPercent <= $appliedCap;
        $isExceedsHard = $projectedPercent > $hardCap;
        $requiresOverride = ! $isWithinDefault && ! $isExceedsHard;

        return [
            'net_salary' => $netSalary,
            'current_deductions' => round($currentDeductions, 2),
            'current_percent' => round($currentPercent, 2),
            'new_loan_repayment' => round($newRepayment, 2),
            'projected_total' => round($projectedTotal, 2),
            'projected_percent' => round($projectedPercent, 2),
            'default_cap' => $defaultCap,
            'applied_cap' => $appliedCap,
            'hard_cap' => $hardCap,
            'is_within_default' => $isWithinDefault,
            'is_within_override' => $isWithinOverride,
            'is_exceeds_hard' => $isExceedsHard,
            'requires_override' => $requiresOverride,
        ];
    }

    /**
     * Validate a loan application against all eligibility rules.
     *
     * @return array{
     *     is_eligible: bool,
     *     requires_multiplier_override: bool,
     *     requires_deduction_override: bool,
     *     monthly_repayment: float,
     *     total_percent: float,
     *     errors: array,
     *     warnings: array,
     *     eligibility: array,
     *     deduction_analysis: array
     * }
     */
    public function validateLoanApplication(Member $member, LoanProduct $product, float $amount, int $tenure, array $guarantorIds = []): array
    {
        $errors = [];
        $warnings = [];

        // 1. Check max eligible amount (with multiplier logic)
        $eligibility = $this->calculateMaxEligibleAmount($member, $product);
        if ($amount > $eligibility['max_eligible']) {
            $errors[] = 'Amount exceeds eligible ₦'.number_format($eligibility['max_eligible'], 2)." ({$eligibility['applied_multiplier']}x savings).";
        }

        if ($eligibility['is_override']) {
            $warnings[] = "Using EXCO-approved multiplier override: {$eligibility['applied_multiplier']}x (default: {$eligibility['default_multiplier']}x). Reason: {$eligibility['override_details']->reason_category}";
        }

        // 2. Calculate monthly repayment
        $monthlyRate = (float) $product->interest_rate_monthly / 100;
        $monthlyRepayment = $this->calculateMonthlyRepayment($amount, $monthlyRate, $tenure);

        // 3. Check salary deduction caps
        $deductionAnalysis = $this->calculateTotalDeductionsPercent($member, $monthlyRepayment);

        if ($deductionAnalysis['is_exceeds_hard']) {
            $errors[] = "Cannot approve - Even with maximum override {$deductionAnalysis['hard_cap']}%, salary insufficient. Projected deduction: {$deductionAnalysis['projected_percent']}%. Reduce amount or increase tenure.";
        } elseif ($deductionAnalysis['requires_override']) {
            $warnings[] = "Exceeds default 1/3 salary rule ({$deductionAnalysis['default_cap']}%). Projected: {$deductionAnalysis['projected_percent']}%. Requires EXCO approval for deduction cap override.";
        }

        // 4. Validate tenure
        if ($tenure < $product->min_tenure_months) {
            $errors[] = "Tenure below minimum of {$product->min_tenure_months} months for {$product->name}.";
        }
        if ($tenure > $product->max_term_months) {
            $errors[] = "Tenure exceeds maximum of {$product->max_term_months} months for {$product->name}.";
        }

        // 5. Validate guarantors if required
        if ($product->requires_guarantor) {
            $guarantorCount = count($guarantorIds);
            if ($guarantorCount < $product->min_guarantors) {
                $errors[] = "This loan requires at least {$product->min_guarantors} guarantor(s).";
            }
            if ($guarantorCount > $product->max_guarantors) {
                $errors[] = "Maximum {$product->max_guarantors} guarantors allowed.";
            }
        }

        // 6. Check max loans per member
        if ($product->max_loans_per_member) {
            $activeCount = Loan::where('member_id', $member->id)
                ->where('loan_product_id', $product->id)
                ->whereIn('status', ['pending', 'approved', 'disbursed', 'repaying'])
                ->count();

            if ($activeCount >= $product->max_loans_per_member) {
                $errors[] = "Member already has {$activeCount} active loan(s) for {$product->name}. Maximum allowed: {$product->max_loans_per_member}.";
            }
        }

        // 7. Check guarantor exposure cap (existing logic from LoanService)
        foreach ($guarantorIds as $guarantorId) {
            $guarantor = Member::find($guarantorId);
            if ($guarantor) {
                $exposure = $this->calculateGuarantorExposure($guarantor);
                $newExposure = $exposure + $amount;
                if ($newExposure > config('cooperative.guarantor_cap', 500000)) {
                    $errors[] = "Guarantor {$guarantor->full_name} exposure cap exceeded: currently guarantees ₦".number_format($exposure, 2).', would exceed ₦'.number_format(config('cooperative.guarantor_cap', 500000), 2).' limit.';
                }
            }
        }

        $isEligible = empty($errors);
        $requiresMultiplierOverride = $eligibility['is_override'];
        $requiresDeductionOverride = $deductionAnalysis['requires_override'];

        return [
            'is_eligible' => $isEligible,
            'requires_multiplier_override' => $requiresMultiplierOverride,
            'requires_deduction_override' => $requiresDeductionOverride,
            'monthly_repayment' => round($monthlyRepayment, 2),
            'total_percent' => $deductionAnalysis['projected_percent'],
            'errors' => $errors,
            'warnings' => $warnings,
            'eligibility' => $eligibility,
            'deduction_analysis' => $deductionAnalysis,
        ];
    }

    /**
     * Get active override for member + product combination.
     */
    private function getActiveOverride(Member $member, LoanProduct $product): ?MemberLoanEligibilityOverride
    {
        return MemberLoanEligibilityOverride::where('member_id', $member->id)
            ->where('loan_product_id', $product->id)
            ->where('is_active', true)
            ->where('valid_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString());
            })
            ->first();
    }

    /**
     * Get deduction cap configuration.
     */
    private function getDeductionCapConfig(): array
    {
        $cap = PayrollDeductionCap::where('is_active', true)->first();

        return [
            'default_max_percent' => $cap?->default_max_percent ?? config('cooperative.payroll_deduction.default_max_percent', 33.33),
            'hard_max_percent' => $cap?->hard_max_percent ?? config('cooperative.payroll_deduction.hard_max_percent', 66.67),
        ];
    }

    /**
     * Calculate current monthly deductions for a member.
     */
    private function calculateCurrentDeductions(Member $member): float
    {
        $total = 0;

        // Savings contribution (10% of monthly salary or monthly_savings)
        $total += $member->monthly_savings ?? round(($member->monthly_salary ?? 0) * 0.10, 2);

        // Active loan repayments
        $activeLoans = Loan::where('member_id', $member->id)
            ->whereIn('status', ['disbursed', 'repaying'])
            ->get();

        foreach ($activeLoans as $loan) {
            $total += (float) $loan->monthly_repayment;
        }

        // Share contributions (5% of monthly salary)
        $total += round(($member->monthly_salary ?? 0) * 0.05, 2);

        // Active hire purchase installments
        $activePurchases = PurchaseOrder::where('member_id', $member->id)
            ->whereIn('status', ['approved', 'active'])
            ->where('payment_type', 'hire_purchase')
            ->get();

        foreach ($activePurchases as $purchase) {
            $total += (float) $purchase->monthly_repayment;
        }

        // Open payroll arrears
        $openArrears = PayrollArrear::open()
            ->where('member_id', $member->id)
            ->sum('shortfall');

        $total += $openArrears;

        return round($total, 2);
    }

    /**
     * Calculate guarantor's current exposure.
     */
    private function calculateGuarantorExposure(Member $guarantor): float
    {
        return (float) LoanGuarantor::query()
            ->where('member_id', $guarantor->id)
            ->where('status', 'accepted')
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['approved', 'disbursed', 'repaying']))
            ->with('loan:id,outstanding')
            ->get()
            ->sum(fn ($g) => (float) $g->loan->outstanding);
    }

    /**
     * Calculate monthly repayment using amortization formula.
     */
    private function calculateMonthlyRepayment(float $principal, float $monthlyRate, int $tenure): float
    {
        if ($monthlyRate <= 0) {
            return round($principal / $tenure, 2);
        }

        $factor = pow(1 + $monthlyRate, $tenure);
        $monthly = $principal * ($monthlyRate * $factor) / ($factor - 1);

        return round($monthly, 2);
    }

    /**
     * Empty deduction result for zero salary.
     */
    private function emptyDeductionResult(): array
    {
        return [
            'net_salary' => 0,
            'current_deductions' => 0,
            'current_percent' => 0,
            'new_loan_repayment' => 0,
            'projected_total' => 0,
            'projected_percent' => 0,
            'default_cap' => 33.33,
            'applied_cap' => 33.33,
            'hard_cap' => 66.67,
            'is_within_default' => true,
            'is_within_override' => true,
            'is_exceeds_hard' => false,
            'requires_override' => false,
        ];
    }
}
