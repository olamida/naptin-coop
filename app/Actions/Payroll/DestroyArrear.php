<?php

namespace App\Actions\Payroll;

use App\Actions\Action;
use App\Models\PayrollArrear;

class DestroyArrear extends Action
{
    public function handle(PayrollArrear $arrear): void
    {
        $arrear->delete();
    }
}
