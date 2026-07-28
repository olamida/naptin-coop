<?php

namespace App\Enums;

enum PayrollStatus: string
{
    case Draft = 'draft';
    case Compiled = 'compiled';
    case Submitted = 'submitted';
    case Deducted = 'deducted';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Compiled => 'Compiled',
            self::Submitted => 'Submitted',
            self::Deducted => 'Deducted',
            self::Completed => 'Completed',
        };
    }
}
