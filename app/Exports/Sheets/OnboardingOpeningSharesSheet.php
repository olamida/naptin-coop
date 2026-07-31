<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OnboardingOpeningSharesSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            ['STF001', '10', '100', 'Opening share allotment', ''],
        ];
    }

    public function headings(): array
    {
        return ['staff_id', 'shares', 'share_price', 'notes', 'external_reference'];
    }

    public function title(): string
    {
        return 'shares';
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
