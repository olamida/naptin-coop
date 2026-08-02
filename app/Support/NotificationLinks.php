<?php

namespace App\Support;

use App\Models\Loan;
use App\Models\Member;
use App\Models\MonthlyPayroll;

class NotificationLinks
{
    public static function actionUrl(array $data, string $context = 'admin'): ?string
    {
        $type = $data['type'] ?? '';

        return match ($type) {
            'loan_applied', 'loan_status' => $context === 'portal'
                ? self::portalLoan($data)
                : self::adminLoan($data),
            'guarantor_request' => $context === 'portal'
                ? route('portal.guarantors')
                : (self::adminLoan($data) ?? route('loans.index')),
            'withdrawal_status', 'withdrawal_requested', 'deposit_recorded', 'deposit_confirmed' => $context === 'portal'
                ? route('portal.savings')
                : route('savings.pending-withdrawals'),
            'share_purchased', 'dividend_declared' => $context === 'portal'
                ? route('portal.shares')
                : route('shares.index'),
            'payroll_compiled' => $context === 'portal'
                ? route('portal.savings')
                : (isset($data['payroll_id']) && MonthlyPayroll::whereKey($data['payroll_id'])->exists()
                    ? route('payroll.show', $data['payroll_id'])
                    : route('payroll.index')),
            'member_registered' => $context === 'portal'
                ? null
                : (isset($data['member_id']) && Member::whereKey($data['member_id'])->exists()
                    ? route('members.show', $data['member_id'])
                    : null),
            default => null,
        };
    }

    protected static function adminLoan(array $data): ?string
    {
        if (! isset($data['loan_id'])) {
            return null;
        }

        return Loan::whereKey($data['loan_id'])->exists()
            ? route('loans.show', $data['loan_id'])
            : null;
    }

    protected static function portalLoan(array $data): ?string
    {
        if (! isset($data['loan_id'])) {
            return route('portal.loans');
        }

        return Loan::whereKey($data['loan_id'])->exists()
            ? route('portal.loan-detail', $data['loan_id'])
            : route('portal.loans');
    }
}
