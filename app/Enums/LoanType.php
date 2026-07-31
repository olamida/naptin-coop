<?php

namespace App\Enums;

enum LoanType: string
{
    case Regular = 'regular';
    case Emergency = 'emergency';
    case Educational = 'educational';
    case Special = 'special';

    public function label(): string
    {
        return match ($this) {
            self::Regular => 'Regular',
            self::Emergency => 'Emergency',
            self::Educational => 'Educational',
            self::Special => 'Special',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Regular => 'blue',
            self::Emergency => 'danger',
            self::Educational => 'emerald',
            self::Special => 'purple',
        };
    }
}
