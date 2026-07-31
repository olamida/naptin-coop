<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Submitted = 'submitted';
    case Eligible = 'eligible';
    case GuarantorPending = 'guarantor_pending';
    case CommitteeReview = 'committee_review';
    case Approved = 'approved';
    case Disbursed = 'disbursed';
    case Repaying = 'repaying';
    case Arrears = 'arrears';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Defaulted = 'defaulted';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Pending',
            self::Submitted => 'Submitted',
            self::Eligible => 'Eligible',
            self::GuarantorPending => 'Guarantor Pending',
            self::CommitteeReview => 'Committee Review',
            self::Approved => 'Approved',
            self::Disbursed => 'Disbursed',
            self::Repaying => 'Repaying',
            self::Arrears => 'Arrears',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
            self::Defaulted => 'Defaulted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft, self::Pending, self::Submitted => 'gray',
            self::Eligible, self::Approved, self::Disbursed => 'success',
            self::GuarantorPending, self::CommitteeReview => 'indigo',
            self::Repaying => 'blue',
            self::Arrears => 'warning',
            self::Completed => 'emerald',
            self::Rejected, self::Defaulted => 'danger',
        };
    }

    public static function activeStatuses(): array
    {
        return [self::Approved, self::Disbursed, self::Repaying, self::Arrears];
    }
}
