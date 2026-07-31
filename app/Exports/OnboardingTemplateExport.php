<?php

namespace App\Exports;

use App\Exports\Sheets\OnboardingMembersSheet;
use App\Exports\Sheets\OnboardingOpeningSavingsSheet;
use App\Exports\Sheets\OnboardingOpeningSharesSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OnboardingTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new OnboardingMembersSheet,
            new OnboardingOpeningSavingsSheet,
            new OnboardingOpeningSharesSheet,
        ];
    }
}
