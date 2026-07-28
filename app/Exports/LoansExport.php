<?php

namespace App\Exports;

use App\Models\Loan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoansExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private int $row = 0;

    public function collection()
    {
        return Loan::with('member.region', 'loanProduct')
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Loan Number',
            'Staff ID',
            'Member Name',
            'Region',
            'Loan Product',
            'Type',
            'Amount',
            'Interest Rate',
            'Tenure (Months)',
            'Monthly Repayment',
            'Total Repaid',
            'Outstanding',
            'Application Date',
            'Disbursement Date',
            'Maturity Date',
            'Status',
        ];
    }

    public function map($loan): array
    {
        $this->row++;

        return [
            $loan->loan_number,
            $loan->member->staff_id ?? '',
            ($loan->member->first_name ?? '') . ' ' . ($loan->member->last_name ?? ''),
            $loan->member->region->name ?? '',
            $loan->loanProduct?->name ?? ucfirst($loan->type),
            ucfirst($loan->type),
            $loan->amount,
            $loan->interest_rate . '%',
            $loan->tenure_months,
            $loan->monthly_repayment,
            $loan->total_repaid,
            $loan->outstanding,
            $loan->application_date?->format('Y-m-d') ?? '',
            $loan->disbursement_date?->format('Y-m-d') ?? '',
            $loan->maturity_date?->format('Y-m-d') ?? '',
            ucfirst($loan->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
