<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OnboardingOpeningSavingsSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            ['STF001', '25000', '2026-01-05', 'Opening balance', ''],
        ];
    }

    public function headings(): array
    {
        return ['staff_id', 'amount', 'transaction_date', 'notes', 'external_reference'];
    }

    public function title(): string
    {
        return 'opening_savings';
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
