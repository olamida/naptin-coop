<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OnboardingImport implements SkipsUnknownSheets, WithMultipleSheets
{
    public MemberImport $members;

    public OpeningSavingsImport $openingSavings;

    public OpeningSharesImport $openingShares;

    public function __construct(public string $batchId)
    {
        $this->members = new MemberImport($batchId);
        $this->openingSavings = new OpeningSavingsImport($batchId);
        $this->openingShares = new OpeningSharesImport($batchId);
    }

    public function sheets(): array
    {
        return [
            'members' => $this->members,
            'opening_savings' => $this->openingSavings,
            'shares' => $this->openingShares,
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Optional sheets may be omitted from the uploaded workbook.
    }

    public function aggregateStats(): array
    {
        $stats = ['total_rows' => 0, 'success' => 0, 'failed' => 0, 'errors' => []];

        foreach ([$this->members, $this->openingSavings, $this->openingShares] as $sheet) {
            $sheetStats = $sheet->importStats();
            $stats['total_rows'] += $sheetStats['total_rows'];
            $stats['success'] += $sheetStats['success'];
            $stats['failed'] += $sheetStats['failed'];
            $stats['errors'] = array_merge($stats['errors'], $sheetStats['errors']);
        }

        return $stats;
    }
}
