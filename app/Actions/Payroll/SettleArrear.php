<?php

namespace App\Actions\Payroll;

use App\Actions\Action;
use App\Models\PayrollArrear;

class SettleArrear extends Action
{
    public function handle(PayrollArrear $arrear): PayrollArrear
    {
        if ($arrear->status === 'settled') {
            throw new \RuntimeException('This arrear is already settled.');
        }

        $arrear->update([
            'status' => 'settled',
            'settled_at' => now(),
        ]);

        return $arrear->fresh();
    }
}
