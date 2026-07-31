<?php

namespace App\Exports;

use App\Models\PayrollDeduction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollDeductionExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private int $payrollId;
    private int $row = 0;

    public function __construct(int $payrollId)
    {
        $this->payrollId = $payrollId;
    }

    public function collection()
    {
        return PayrollDeduction::where('monthly_payroll_id', $this->payrollId)
            ->with('member')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Staff ID',
            'Member Name',
            'Expected Savings',
            'Expected Loan Repayment',
            'Expected Share Contribution',
            'Expected Purchase',
            'Expected Arrears',
            'Total Expected',
            'Actual Savings',
            'Actual Loan Repayment',
            'Actual Share Contribution',
            'Actual Purchase',
            'Actual Arrears',
            'Total Actual',
            'Status',
        ];
    }

    public function map($deduction): array
    {
        $this->row++;

        return [
            $deduction->member->staff_id ?? '',
            ($deduction->member->first_name ?? '') . ' ' . ($deduction->member->last_name ?? ''),
            $deduction->expected_savings,
            $deduction->expected_loan_repayment,
            $deduction->expected_share_contribution,
            $deduction->expected_purchase ?? 0,
            $deduction->expected_arrears ?? 0,
            $deduction->total_expected,
            $deduction->actual_savings,
            $deduction->actual_loan_repayment,
            $deduction->actual_share_contribution,
            $deduction->actual_purchase ?? 0,
            $deduction->actual_arrears ?? 0,
            $deduction->total_actual,
            ucfirst($deduction->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
