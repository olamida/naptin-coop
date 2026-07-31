<?php

namespace App\Actions\Payroll;

use App\Actions\Action;
use App\Models\Loan;
use App\Models\Member;
use App\Models\MonthlyPayroll;
use App\Models\PayrollArrear;
use App\Models\PayrollDeduction;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompileAndLockPayroll extends Action
{
    public function handle(int $year, int $monthNumber): MonthlyPayroll
    {
        $monthName = Carbon::createFromDate($year, $monthNumber, 1)->format('F');

        $existing = MonthlyPayroll::where('year', $year)->where('month_number', $monthNumber)->first();
        if ($existing) {
            throw new \RuntimeException("Payroll for {$monthName} {$year} already exists.");
        }

        $count = MonthlyPayroll::where('year', $year)->count() + 1;
        $payrollNumber = 'PAY/' . $year . '/' . str_pad($count, 6, '0', STR_PAD_LEFT);

        $members = Member::where('status', 'active')
            ->with('savingsAccount')
            ->get();

        $openArrearsByMember = PayrollArrear::open()
            ->get()
            ->groupBy('member_id')
            ->map(fn ($arrears) => round($arrears->sum('shortfall'), 2));

        return DB::transaction(function () use ($year, $monthNumber, $monthName, $payrollNumber, $members, $openArrearsByMember) {
            $totalSavings = 0;
            $totalLoanRepayments = 0;
            $totalShareContributions = 0;
            $totalPurchases = 0;
            $totalArrears = 0;
            $deductionRows = [];

            foreach ($members as $member) {
                $expectedSavings = $member->monthly_savings ?? round($member->monthly_salary * 0.10, 2);

                $activeLoan = Loan::where('member_id', $member->id)
                    ->whereIn('status', ['disbursed', 'repaying'])
                    ->first();
                $expectedLoanRepayment = $activeLoan ? $activeLoan->monthly_repayment : 0;

                $expectedShareContribution = round($member->monthly_salary * 0.05, 2);

                $activePurchase = PurchaseOrder::where('member_id', $member->id)
                    ->whereIn('status', ['approved', 'active'])
                    ->where('payment_type', 'hire_purchase')
                    ->first();
                $expectedPurchase = $activePurchase ? $activePurchase->monthly_repayment : 0;

                $expectedArrears = $openArrearsByMember[$member->id] ?? 0;

                $totalExpected = $expectedSavings + $expectedLoanRepayment + $expectedShareContribution + $expectedPurchase + $expectedArrears;

                $totalSavings += $expectedSavings;
                $totalLoanRepayments += $expectedLoanRepayment;
                $totalShareContributions += $expectedShareContribution;
                $totalPurchases += $expectedPurchase;
                $totalArrears += $expectedArrears;

                $deductionRows[] = [
                    'member_id' => $member->id,
                    'expected_savings' => $expectedSavings,
                    'expected_loan_repayment' => $expectedLoanRepayment,
                    'expected_share_contribution' => $expectedShareContribution,
                    'expected_purchase' => $expectedPurchase,
                    'expected_arrears' => $expectedArrears,
                    'total_expected' => $totalExpected,
                    'actual_savings' => $expectedSavings,
                    'actual_loan_repayment' => $expectedLoanRepayment,
                    'actual_share_contribution' => $expectedShareContribution,
                    'actual_purchase' => $expectedPurchase,
                    'actual_arrears' => $expectedArrears,
                    'total_actual' => $totalExpected,
                    'status' => 'completed',
                ];
            }

            $payroll = MonthlyPayroll::create([
                'payroll_number' => $payrollNumber,
                'month' => $monthName,
                'year' => $year,
                'month_number' => $monthNumber,
                'total_savings' => $totalSavings,
                'total_loan_repayments' => $totalLoanRepayments,
                'total_share_contributions' => $totalShareContributions,
                'total_purchases' => $totalPurchases,
                'total_arrears' => $totalArrears,
                'grand_total' => $totalSavings + $totalLoanRepayments + $totalShareContributions + $totalPurchases + $totalArrears,
                'member_count' => $members->count(),
                'status' => 'completed',
            ]);

            foreach ($deductionRows as $row) {
                PayrollDeduction::create(array_merge($row, [
                    'monthly_payroll_id' => $payroll->id,
                ]));
            }

            return $payroll;
        });
    }
}
