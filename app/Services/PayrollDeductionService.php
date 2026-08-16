<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Member;
use App\Models\MemberLoanEligibilityOverride;
use App\Models\MonthlyPayroll;
use App\Models\PayrollArrear;
use App\Models\PayrollDeduction;
use App\Models\PayrollDeductionCap;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class PayrollDeductionService
{
    /**
     * Compile deductions for a single member with cap enforcement.
     *
     * @return array{
     *     member_id: int,
     *     net_salary: float,
     *     expected_savings: float,
     *     expected_loan_repayment: float,
     *     expected_share_contribution: float,
     *     expected_purchase: float,
     *     expected_arrears: float,
     *     total_expected: float,
     *     total_actual: float,
     *     total_capped: float,
     *     arrears_carried: float,
     *     deduction_percent: float,
     *     cap_applied: float,
     *     hard_cap: float,
     *     is_retiring_soon: bool,
     *     is_defaulter: bool,
     *     priority_log: array
     * }
     */
    public function compileMemberDeductions(Member $member, string $payrollMonth): array
    {
        $netSalary = $member->monthly_net_salary ?? $member->monthly_salary ?? 0;

        if ($netSalary <= 0) {
            return $this->emptyResult($member->id, 'No salary data');
        }

        // Get deduction caps
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

        // Retirement recovery logic
        $isRetiringSoon = false;
        $retirementOverridePercent = $capConfig['retirement_override_max_percent'] ?? 60.00;
        if ($member->expected_retirement_date) {
            $monthsToRetirement = Carbon::now()->diffInMonths($member->expected_retirement_date, false);
            if ($monthsToRetirement <= 6 && $monthsToRetirement >= 0) {
                $isRetiringSoon = true;
                // Allow up to retirement override percent for loan recovery
                $appliedCap = max($appliedCap, $retirementOverridePercent);
            }
        }

        // Defaulter catch-up logic
        $isDefaulter = (bool) $member->is_defaulter;
        $defaulterOverridePercent = $capConfig['defaulter_override_max_percent'] ?? 50.00;
        if ($isDefaulter) {
            $appliedCap = max($appliedCap, $defaulterOverridePercent);
        }

        // Hard cap protection - never exceed
        $appliedCap = min($appliedCap, $hardCap);

        // Calculate expected deductions
        $expectedSavings = $member->monthly_savings ?? round(($member->monthly_salary ?? 0) * 0.10, 2);

        $activeLoan = Loan::where('member_id', $member->id)
            ->whereIn('status', ['disbursed', 'repaying'])
            ->first();
        $expectedLoanRepayment = $activeLoan ? (float) $activeLoan->monthly_repayment : 0;

        $expectedShareContribution = round(($member->monthly_salary ?? 0) * 0.05, 2);

        $activePurchase = PurchaseOrder::where('member_id', $member->id)
            ->whereIn('status', ['approved', 'active'])
            ->where('payment_type', 'hire_purchase')
            ->first();
        $expectedPurchase = $activePurchase ? (float) $activePurchase->monthly_repayment : 0;

        $expectedArrears = PayrollArrear::open()
            ->where('member_id', $member->id)
            ->sum('shortfall');

        $totalExpected = $expectedSavings + $expectedLoanRepayment + $expectedShareContribution + $expectedPurchase + $expectedArrears;

        // Apply cap
        $maxAllowed = ($netSalary * $appliedCap) / 100;
        $priorityLog = [];

        if ($totalExpected <= $maxAllowed) {
            // Within cap - all deductions taken
            $totalActual = $totalExpected;
            $totalCapped = $totalExpected;
            $arrearsCarried = 0;
            $priorityLog[] = "Within {$appliedCap}% cap - all deductions applied";
        } else {
            // Exceeds cap - prioritize deductions
            $priorityLog[] = "Exceeds {$appliedCap}% cap (₦".number_format($maxAllowed, 2).') - prioritizing deductions';

            // Priority order: 1. Savings (mandatory), 2. Loan repayments (oldest first), 3. Shares, 4. Purchases, 5. Arrears
            $remaining = $maxAllowed;
            $totalActual = 0;

            // 1. Savings (mandatory)
            $savingsApplied = min($expectedSavings, $remaining);
            $remaining -= $savingsApplied;
            $totalActual += $savingsApplied;
            if ($expectedSavings > $savingsApplied) {
                $priorityLog[] = 'Savings capped at ₦'.number_format($savingsApplied, 2).' (expected ₦'.number_format($expectedSavings, 2).')';
            }

            // 2. Loan repayments (oldest first)
            $activeLoans = Loan::where('member_id', $member->id)
                ->whereIn('status', ['disbursed', 'repaying'])
                ->orderBy('disbursement_date')
                ->get();

            foreach ($activeLoans as $loan) {
                $loanAmount = (float) $loan->monthly_repayment;
                if ($remaining <= 0) {
                    $priorityLog[] = "Loan {$loan->loan_number} deferred - cap reached";
                    break;
                }
                $applied = min($loanAmount, $remaining);
                $remaining -= $applied;
                $totalActual += $applied;
                if ($loanAmount > $applied) {
                    $priorityLog[] = "Loan {$loan->loan_number} partial ₦".number_format($applied, 2).' of ₦'.number_format($loanAmount, 2);
                }
            }

            // 3. Share contributions
            if ($remaining > 0) {
                $sharesApplied = min($expectedShareContribution, $remaining);
                $remaining -= $sharesApplied;
                $totalActual += $sharesApplied;
                if ($expectedShareContribution > $sharesApplied) {
                    $priorityLog[] = 'Shares partial ₦'.number_format($sharesApplied, 2);
                }
            } else {
                $priorityLog[] = 'Shares deferred - cap reached';
            }

            // 4. Purchases
            if ($remaining > 0) {
                $purchaseApplied = min($expectedPurchase, $remaining);
                $remaining -= $purchaseApplied;
                $totalActual += $purchaseApplied;
                if ($expectedPurchase > $purchaseApplied) {
                    $priorityLog[] = 'Purchase partial ₦'.number_format($purchaseApplied, 2);
                }
            } else {
                $priorityLog[] = 'Purchases deferred - cap reached';
            }

            // 5. Arrears (catch-up)
            if ($remaining > 0 && $expectedArrears > 0) {
                $arrearsApplied = min($expectedArrears, $remaining);
                $remaining -= $arrearsApplied;
                $totalActual += $arrearsApplied;
                if ($expectedArrears > $arrearsApplied) {
                    $priorityLog[] = 'Arrears partial ₦'.number_format($arrearsApplied, 2);
                }
            } elseif ($expectedArrears > 0) {
                $priorityLog[] = 'Arrears deferred - cap reached';
            }

            $totalCapped = $totalActual;
            $arrearsCarried = $totalExpected - $totalActual;
            $priorityLog[] = 'Total capped at ₦'.number_format($totalCapped, 2).' - ₦'.number_format($arrearsCarried, 2).' carried to arrears';
        }

        $deductionPercent = $netSalary > 0 ? round(($totalActual / $netSalary) * 100, 2) : 0;

        return [
            'member_id' => $member->id,
            'net_salary' => $netSalary,
            'expected_savings' => round($expectedSavings, 2),
            'expected_loan_repayment' => round($expectedLoanRepayment, 2),
            'expected_share_contribution' => round($expectedShareContribution, 2),
            'expected_purchase' => round($expectedPurchase, 2),
            'expected_arrears' => round($expectedArrears, 2),
            'total_expected' => round($totalExpected, 2),
            'total_actual' => round($totalActual, 2),
            'total_capped' => round($totalCapped, 2),
            'arrears_carried' => round($arrearsCarried, 2),
            'deduction_percent' => $deductionPercent,
            'cap_applied' => $appliedCap,
            'hard_cap' => $hardCap,
            'is_retiring_soon' => $isRetiringSoon,
            'is_defaulter' => $isDefaulter,
            'priority_log' => $priorityLog,
        ];
    }

    /**
     * Compile payroll for all active members with cap enforcement.
     *
     * @return array{
     *     payroll: MonthlyPayroll,
     *     summary: array{
     *         total_expected: float,
     *         total_capped: float,
     *         total_arrears: float,
     *         members_within_cap: int,
     *         members_exceeding_cap: int,
     *         members_retiring: int,
     *         members_defaulters: int
     *     }
     * }
     */
    public function compilePayroll(int $year, int $monthNumber): array
    {
        $monthName = Carbon::createFromDate($year, $monthNumber, 1)->format('F');

        $members = Member::where('status', 'active')
            ->with('savingsAccount')
            ->get();

        $results = [];
        $summary = [
            'total_expected' => 0,
            'total_capped' => 0,
            'total_arrears' => 0,
            'members_within_cap' => 0,
            'members_exceeding_cap' => 0,
            'members_retiring' => 0,
            'members_defaulters' => 0,
        ];

        foreach ($members as $member) {
            $result = $this->compileMemberDeductions($member, $monthName);
            $results[] = $result;

            $summary['total_expected'] += $result['total_expected'];
            $summary['total_capped'] += $result['total_capped'];
            $summary['total_arrears'] += $result['arrears_carried'];

            if ($result['deduction_percent'] <= $result['cap_applied']) {
                $summary['members_within_cap']++;
            } else {
                $summary['members_exceeding_cap']++;
            }

            if ($result['is_retiring_soon']) {
                $summary['members_retiring']++;
            }

            if ($result['is_defaulter']) {
                $summary['members_defaulters']++;
            }
        }

        // Create payroll record
        $payroll = $this->createPayrollRecord($year, $monthNumber, $monthName, $results, $summary);

        return [
            'payroll' => $payroll,
            'summary' => $summary,
            'details' => $results,
        ];
    }

    /**
     * Create payroll record in database.
     */
    private function createPayrollRecord(int $year, int $monthNumber, string $monthName, array $results, array $summary): MonthlyPayroll
    {
        $count = MonthlyPayroll::where('year', $year)->count() + 1;
        $payrollNumber = 'PAY/'.$year.'/'.str_pad((string) $count, 6, '0', STR_PAD_LEFT);

        $payroll = MonthlyPayroll::create([
            'payroll_number' => $payrollNumber,
            'month' => $monthName,
            'year' => $year,
            'month_number' => $monthNumber,
            'total_savings' => collect($results)->sum('expected_savings'),
            'total_loan_repayments' => collect($results)->sum('expected_loan_repayment'),
            'total_share_contributions' => collect($results)->sum('expected_share_contribution'),
            'total_purchases' => collect($results)->sum('expected_purchase'),
            'total_arrears' => collect($results)->sum('expected_arrears'),
            'grand_total' => $summary['total_capped'],
            'member_count' => count($results),
            'status' => 'completed',
        ]);

        // Create deduction records
        foreach ($results as $result) {
            PayrollDeduction::create([
                'monthly_payroll_id' => $payroll->id,
                'member_id' => $result['member_id'],
                'expected_savings' => $result['expected_savings'],
                'expected_loan_repayment' => $result['expected_loan_repayment'],
                'expected_share_contribution' => $result['expected_share_contribution'],
                'expected_purchase' => $result['expected_purchase'],
                'expected_arrears' => $result['expected_arrears'],
                'total_expected' => $result['total_expected'],
                'actual_savings' => $result['expected_savings'], // Will be adjusted during upload
                'actual_loan_repayment' => min($result['expected_loan_repayment'], $result['total_capped'] - $result['expected_savings']),
                'actual_share_contribution' => 0, // Will be calculated during upload
                'actual_purchase' => 0,
                'actual_arrears' => 0,
                'total_actual' => $result['total_capped'],
                'status' => 'completed',
            ]);
        }

        // Post to ledger
        app(LedgerService::class)->postPayrollCompilation(
            $payroll->id,
            collect($results)->sum('expected_savings'),
            collect($results)->sum('expected_loan_repayment'),
            collect($results)->sum('expected_share_contribution'),
            collect($results)->sum('expected_purchase'),
            collect($results)->sum('expected_arrears')
        );

        return $payroll;
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
            'retirement_override_max_percent' => config('cooperative.payroll_deduction.retirement_override_max_percent', 60.00),
            'defaulter_override_max_percent' => config('cooperative.payroll_deduction.defaulter_override_max_percent', 50.00),
        ];
    }

    /**
     * Empty result for member with no salary.
     */
    private function emptyResult(int $memberId, string $reason): array
    {
        return [
            'member_id' => $memberId,
            'net_salary' => 0,
            'expected_savings' => 0,
            'expected_loan_repayment' => 0,
            'expected_share_contribution' => 0,
            'expected_purchase' => 0,
            'expected_arrears' => 0,
            'total_expected' => 0,
            'total_actual' => 0,
            'total_capped' => 0,
            'arrears_carried' => 0,
            'deduction_percent' => 0,
            'cap_applied' => 33.33,
            'hard_cap' => 66.67,
            'is_retiring_soon' => false,
            'is_defaulter' => false,
            'priority_log' => [$reason],
        ];
    }
}
