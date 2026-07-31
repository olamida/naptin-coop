<?php

namespace App\Actions\Payroll;

use App\Actions\Action;
use App\Models\PayrollArrear;
use App\Models\PayrollDeduction;

class StoreArrear extends Action
{
    public function handle(int $payrollDeductionId, float $amount, ?string $reason = null): PayrollArrear
    {
        $deduction = PayrollDeduction::find($payrollDeductionId);

        if (! $deduction) {
            throw new \RuntimeException('Deduction record not found for this member on this payroll.');
        }

        if ($amount <= 0) {
            throw new \RuntimeException('No shortfall exists for this member to flag as arrears.');
        }

        $exists = PayrollArrear::where('monthly_payroll_id', $deduction->monthly_payroll_id)
            ->where('member_id', $deduction->member_id)
            ->where('status', 'open')
            ->exists();

        if ($exists) {
            throw new \RuntimeException('An open arrear already exists for this member on this payroll.');
        }

        return PayrollArrear::create([
            'monthly_payroll_id' => $deduction->monthly_payroll_id,
            'member_id' => $deduction->member_id,
            'component' => 'total',
            'expected_amount' => $deduction->total_expected,
            'actual_amount' => $deduction->total_actual,
            'shortfall' => $amount,
            'reason' => $reason,
            'status' => 'open',
            'recorded_by' => auth()->id(),
        ]);
    }
}
