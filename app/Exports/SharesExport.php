<?php

namespace App\Exports;

use App\Models\ShareTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SharesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private int $row = 0;

    public function collection()
    {
        return ShareTransaction::with('shareAccount.member')
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
            'Type',
            'Shares',
            'Amount',
            'Total Shares After',
            'Notes',
        ];
    }

    public function map($txn): array
    {
        $this->row++;

        return [
            $txn->transaction_date?->format('Y-m-d') ?? '',
            $txn->reference,
            $txn->shareAccount->member->staff_id ?? '',
            ($txn->shareAccount->member->first_name ?? '').' '.($txn->shareAccount->member->last_name ?? ''),
            ucfirst($txn->type),
            $txn->shares,
            $txn->amount,
            $txn->balance_after,
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
