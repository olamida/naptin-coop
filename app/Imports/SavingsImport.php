<?php

namespace App\Imports;

use App\Imports\Concerns\TracksImportStats;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SavingsImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use TracksImportStats;

    public function __construct(
        public ?string $batchId = null,
    ) {}

    public function model(array $row): ?SavingsTransaction
    {
        $this->trackRow();

        if (! empty($row['external_reference']) && SavingsTransaction::where('external_reference', $row['external_reference'])->exists()) {
            $this->markFailure('Duplicate external reference "'.$row['external_reference'].'" — skipped');

            return null;
        }

        $member = Member::where('staff_id', $row['staff_id'])->first();

        if (! $member) {
            $this->markFailure('No member found for staff_id '.$row['staff_id']);

            return null;
        }

        $account = SavingsAccount::where('member_id', $member->id)->first();

        if (! $account) {
            $this->markFailure('No savings account found for member '.$row['staff_id']);

            return null;
        }

        $amount = round((float) $row['amount'], 2);
        $type = $row['type'] ?? 'deposit';
        $balanceBefore = $account->balance;
        $balanceAfter = $type === 'deposit'
            ? $balanceBefore + $amount
            : $balanceBefore - $amount;

        $account->update(['balance' => $balanceAfter]);

        $this->markSuccess();

        return SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'reference' => 'SAV/IMP/'.strtoupper(Str::random(8)),
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'source' => 'salary_deduction',
            'notes' => $row['notes'] ?? null,
            'transaction_date' => $row['transaction_date'] ?? now()->toDateString(),
            'import_batch_id' => $this->batchId,
            'external_reference' => $row['external_reference'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|exists:members,staff_id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'nullable|in:deposit,withdrawal',
            'transaction_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'external_reference' => 'nullable|string|max:100',
        ];
    }
}
