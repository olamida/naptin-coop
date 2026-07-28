<?php

namespace App\Enums;

enum MemberStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Inactive = 'inactive';
    case Retired = 'retired';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Approval',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Retired => 'Retired',
            self::Suspended => 'Suspended',
        };
    }
}
