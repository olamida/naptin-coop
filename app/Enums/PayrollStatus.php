<?php

namespace App\Enums;

enum PayrollStatus: string
{
    case Draft = 'draft';
    case Compiled = 'compiled';
    case Submitted = 'submitted';
    case Deducted = 'deducted';
    case Reconciled = 'reconciled';
    case Variance = 'variance';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Compiled => 'Compiled',
            self::Submitted => 'Submitted',
            self::Deducted => 'Deducted',
            self::Reconciled => 'Reconciled',
            self::Variance => 'Variance',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Compiled, self::Submitted => 'indigo',
            self::Deducted => 'blue',
            self::Reconciled => 'success',
            self::Variance => 'warning',
            self::Completed => 'emerald',
        };
    }
}
