<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MembersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private int $row = 0;

    public function collection()
    {
        return Member::with('region')->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Staff ID',
            'First Name',
            'Last Name',
            'Middle Name',
            'Region',
            'Email',
            'Phone',
            'Gender',
            'Date of Birth',
            'Employment Date',
            'Grade Level',
            'Monthly Salary',
            'Monthly Savings',
            'Status',
            'Address',
            'State of Origin',
            'NIN',
        ];
    }

    public function map($member): array
    {
        $this->row++;

        return [
            $member->staff_id,
            $member->first_name,
            $member->last_name,
            $member->middle_name ?? '',
            $member->region->name ?? '',
            $member->email ?? '',
            $member->phone ?? '',
            ucfirst($member->gender ?? ''),
            $member->date_of_birth?->format('Y-m-d') ?? '',
            $member->employment_date?->format('Y-m-d') ?? '',
            $member->grade_level ?? '',
            $member->monthly_salary ?? 0,
            $member->monthly_savings ?? 0,
            ucfirst($member->status),
            $member->address ?? '',
            $member->state_of_origin ?? '',
            $member->nin ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
