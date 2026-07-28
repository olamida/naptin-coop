<?php

namespace App\Enums;

enum ShareTransactionType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Transfer = 'transfer';
    case Dividend = 'dividend';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Sale => 'Sale',
            self::Transfer => 'Transfer',
            self::Dividend => 'Dividend',
        };
    }
}
