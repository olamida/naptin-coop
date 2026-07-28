<?php

namespace App\Enums;

enum SavingsTransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Interest = 'interest';
    case Transfer = 'transfer';
    case Reversal = 'reversal';

    public function label(): string
    {
        return match ($this) {
            self::Deposit => 'Deposit',
            self::Withdrawal => 'Withdrawal',
            self::Interest => 'Interest',
            self::Transfer => 'Transfer',
            self::Reversal => 'Reversal',
        };
    }
}
