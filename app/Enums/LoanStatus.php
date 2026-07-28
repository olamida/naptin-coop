<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Disbursed = 'disbursed';
    case Repaying = 'repaying';
    case Completed = 'completed';
    case Defaulted = 'defaulted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Disbursed => 'Disbursed',
            self::Repaying => 'Repaying',
            self::Completed => 'Completed',
            self::Defaulted => 'Defaulted',
            self::Rejected => 'Rejected',
        };
    }
}
