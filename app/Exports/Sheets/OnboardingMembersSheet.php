<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OnboardingMembersSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function array(): array
    {
        return [
            [
                'STF001', 'John', 'Doe', 'Michael', 'Lagos',
                'john.doe@example.com', '08012345678', 'male', '1990-01-15', '2020-03-01',
                '123 Main Street', 'Lagos', '12345678901', 'Grade 10', '150000', 'active', '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'staff_id', 'first_name', 'last_name', 'middle_name', 'region',
            'email', 'phone', 'gender', 'date_of_birth', 'employment_date',
            'address', 'state_of_origin', 'nin', 'grade_level', 'monthly_salary', 'status', 'external_reference',
        ];
    }

    public function title(): string
    {
        return 'members';
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
