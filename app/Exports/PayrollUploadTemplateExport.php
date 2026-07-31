<?php

namespace App\Exports;

use App\Models\PayrollDeduction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PayrollUploadTemplateExport implements FromCollection, WithHeadings
{
    private int $payrollId;

    public function __construct(int $payrollId)
    {
        $this->payrollId = $payrollId;
    }

    public function collection()
    {
        return PayrollDeduction::where('monthly_payroll_id', $this->payrollId)
            ->with('member')
            ->get()
            ->map(function ($deduction) {
                return [
                    'staff_id' => $deduction->member->staff_id ?? '',
                    'member_name' => trim(($deduction->member->first_name ?? '') . ' ' . ($deduction->member->last_name ?? '')),
                    'actual_savings' => $deduction->expected_savings,
                    'actual_loan_repayment' => $deduction->expected_loan_repayment,
                    'actual_share_contribution' => $deduction->expected_share_contribution,
                    'actual_purchase' => $deduction->expected_purchase ?? 0,
                    'actual_arrears' => $deduction->expected_arrears ?? 0,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Staff ID',
            'Member Name',
            'Actual Savings',
            'Actual Loan Repayment',
            'Actual Share Contribution',
            'Actual Purchase',
            'Actual Arrears',
        ];
    }
}
