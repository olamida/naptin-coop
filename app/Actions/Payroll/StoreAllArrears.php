<?php

namespace App\Actions\Payroll;

use App\Actions\Action;
use App\Models\MonthlyPayroll;
use App\Models\PayrollArrear;

class StoreAllArrears extends Action
{
    public function handle(int $monthlyPayrollId): int
    {
        $monthlyPayroll = MonthlyPayroll::with('deductions')->findOrFail($monthlyPayrollId);

        $count = 0;

        foreach ($monthlyPayroll->deductions as $deduction) {
            $shortfall = round((float) $deduction->total_expected - (float) $deduction->total_actual, 2);

            if ($shortfall <= 0) {
                continue;
            }

            $exists = PayrollArrear::where('monthly_payroll_id', $monthlyPayrollId)
                ->where('member_id', $deduction->member_id)
                ->where('status', 'open')
                ->exists();

            if ($exists) {
                continue;
            }

            PayrollArrear::create([
                'monthly_payroll_id' => $monthlyPayrollId,
                'member_id' => $deduction->member_id,
                'component' => 'total',
                'expected_amount' => $deduction->total_expected,
                'actual_amount' => $deduction->total_actual,
                'shortfall' => $shortfall,
                'status' => 'open',
                'recorded_by' => auth()->id(),
            ]);

            $count++;
        }

        return $count;
    }
}
