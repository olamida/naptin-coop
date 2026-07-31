<?php

namespace App\Support;

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
                : (isset($data['payroll_id']) ? route('payroll.show', $data['payroll_id']) : route('payroll.index')),
            'member_registered' => $context === 'portal'
                ? null
                : (isset($data['member_id']) ? route('members.show', $data['member_id']) : null),
            default => null,
        };
    }

    protected static function adminLoan(array $data): ?string
    {
        return isset($data['loan_id']) ? route('loans.show', $data['loan_id']) : null;
    }

    protected static function portalLoan(array $data): ?string
    {
        return isset($data['loan_id']) ? route('portal.loan-detail', $data['loan_id']) : route('portal.loans');
    }
}
