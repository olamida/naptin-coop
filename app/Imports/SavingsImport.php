<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SavingsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row): ?SavingsTransaction
    {
        $member = Member::where('staff_id', $row['staff_id'])->first();

        if (! $member) {
            return null;
        }

        $account = SavingsAccount::where('member_id', $member->id)->first();

        if (! $account) {
            return null;
        }

        $amount = round((float) $row['amount'], 2);
        $type = $row['type'] ?? 'deposit';
        $balanceBefore = $account->balance;
        $balanceAfter = $type === 'deposit'
            ? $balanceBefore + $amount
            : $balanceBefore - $amount;

        $account->update(['balance' => $balanceAfter]);

        return SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'reference' => 'SAV/IMP/' . strtoupper(Str::random(8)),
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'source' => 'salary_deduction',
            'notes' => $row['notes'] ?? null,
            'transaction_date' => $row['transaction_date'] ?? now()->toDateString(),
        ]);
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|string|exists:members,staff_id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'nullable|in:deposit,withdrawal',
            'transaction_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
