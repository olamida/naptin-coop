<?php

namespace App\Exports;

use App\Models\SavingsTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SavingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private int $row = 0;

    public function collection()
    {
        return SavingsTransaction::with('savingsAccount.member')
            ->latest('transaction_date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Reference',
            'Staff ID',
            'Member Name',
            'Account Number',
            'Type',
            'Status',
            'Amount',
            'Balance Before',
            'Balance After',
            'Source',
            'Notes',
        ];
    }

    public function map($txn): array
    {
        $this->row++;

        return [
            $txn->transaction_date?->format('Y-m-d') ?? '',
            $txn->reference,
            $txn->savingsAccount->member->staff_id ?? '',
            ($txn->savingsAccount->member->first_name ?? '').' '.($txn->savingsAccount->member->last_name ?? ''),
            $txn->savingsAccount->account_number ?? '',
            ucfirst($txn->type),
            ucfirst($txn->status),
            $txn->type === 'withdrawal' ? -$txn->amount : $txn->amount,
            $txn->balance_before,
            $txn->balance_after,
            ucfirst(str_replace('_', ' ', $txn->source)),
            $txn->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
