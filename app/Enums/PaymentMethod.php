<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case SalaryDeduction = 'salary_deduction';
    case SavingsDeduction = 'savings_deduction';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::BankTransfer => 'Bank Transfer',
            self::SalaryDeduction => 'Salary Deduction',
            self::SavingsDeduction => 'Savings Deduction',
        };
    }
}
