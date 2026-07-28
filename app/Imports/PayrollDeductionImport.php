<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\PayrollDeduction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PayrollDeductionImport implements ToModel, WithHeadingRow, WithValidation
{
    private int $payrollId;

    public function __construct(int $payrollId)
    {
        $this->payrollId = $payrollId;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|string',
            'actual_savings' => 'required|numeric|min:0',
            'actual_loan_repayment' => 'required|numeric|min:0',
            'actual_share_contribution' => 'required|numeric|min:0',
            'actual_purchase' => 'nullable|numeric|min:0',
        ];
    }

    public function model(array $row)
    {
        $member = Member::where('staff_id', $row['staff_id'])->first();

        if (!$member) {
            return null;
        }

        $deduction = PayrollDeduction::where('monthly_payroll_id', $this->payrollId)
            ->where('member_id', $member->id)
            ->first();

        if (!$deduction) {
            return null;
        }

        $actualSavings = round($row['actual_savings'], 2);
        $actualLoanRepayment = round($row['actual_loan_repayment'], 2);
        $actualShareContribution = round($row['actual_share_contribution'], 2);
        $actualPurchase = round($row['actual_purchase'] ?? 0, 2);
        $totalActual = $actualSavings + $actualLoanRepayment + $actualShareContribution + $actualPurchase;

        $deduction->update([
            'actual_savings' => $actualSavings,
            'actual_loan_repayment' => $actualLoanRepayment,
            'actual_share_contribution' => $actualShareContribution,
            'actual_purchase' => $actualPurchase,
            'total_actual' => $totalActual,
            'status' => 'completed',
        ]);

        return null;
    }
}
