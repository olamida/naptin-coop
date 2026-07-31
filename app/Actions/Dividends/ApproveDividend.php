<?php

namespace App\Actions\Dividends;

use App\Actions\Action;
use App\Models\Dividend;

class ApproveDividend extends Action
{
    public function handle(Dividend $dividend): Dividend
    {
        if ($dividend->status !== 'calculated') {
            throw new \RuntimeException('Only calculated dividends can be approved.');
        }

        $dividend->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return $dividend->fresh();
    }
}
