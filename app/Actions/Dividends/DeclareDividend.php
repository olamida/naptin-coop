<?php

namespace App\Actions\Dividends;

use App\Actions\Action;
use App\Models\Dividend;

class DeclareDividend extends Action
{
    public function handle(array $validated): Dividend
    {
        $existing = Dividend::where('year', $validated['year'])->first();
        if ($existing) {
            throw new \RuntimeException("Dividend for {$validated['year']} already exists.");
        }

        $dividendNumber = 'DIV/' . $validated['year'] . '/' . str_pad(
            Dividend::where('year', $validated['year'])->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        return Dividend::create([
            'dividend_number' => $dividendNumber,
            'year' => $validated['year'],
            'total_profit' => $validated['total_profit'],
            'status' => 'draft',
        ]);
    }
}
