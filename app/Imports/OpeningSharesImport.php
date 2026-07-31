<?php

namespace App\Imports;

use App\Imports\Concerns\TracksImportStats;
use App\Models\Member;
use App\Models\ShareAccount;
use App\Models\ShareTransaction;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OpeningSharesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use TracksImportStats;

    public function __construct(
        public ?string $batchId = null,
    ) {}

    public function model(array $row): ?ShareTransaction
    {
        $this->trackRow();

        if (!empty($row['external_reference']) && ShareTransaction::where('external_reference', $row['external_reference'])->exists()) {
            $this->markFailure('Duplicate external reference "' . $row['external_reference'] . '" — skipped');

            return null;
        }

        $member = Member::where('staff_id', $row['staff_id'])->first();

        if (! $member) {
            $this->markFailure('No member found for staff_id ' . $row['staff_id']);

            return null;
        }

        $account = ShareAccount::where('member_id', $member->id)->first();

        if (! $account) {
            $this->markFailure('No share account found for member ' . $row['staff_id']);

            return null;
        }

        if (ShareTransaction::where('share_account_id', $account->id)->where('reference', 'like', 'SHR/OPN/%')->exists()) {
            $this->markFailure('Opening share allotment already posted for member ' . $row['staff_id']);

            return null;
        }

        $shares = (int) ($row['shares'] ?? 0);

        if ($shares < 1) {
            $this->markFailure('Share count must be at least 1 for member ' . $row['staff_id']);

            return null;
        }

        $sharePrice = round((float) ($row['share_price'] ?? $account->share_price ?? 100), 2);
        $amount = round($shares * $sharePrice, 2);

        $newTotalShares = $account->total_shares + $shares;
        $newTotalValue = round($newTotalShares * $sharePrice, 2);

        $account->update([
            'total_shares' => $newTotalShares,
            'total_value' => $newTotalValue,
            'share_price' => $sharePrice,
        ]);

        $this->markSuccess();

        return ShareTransaction::create([
            'share_account_id' => $account->id,
            'reference' => 'SHR/OPN/' . strtoupper(Str::random(8)),
            'type' => 'purchase',
            'shares' => $shares,
            'amount' => $amount,
            'balance_after' => $newTotalShares,
            'status' => 'completed',
            'notes' => $row['notes'] ?? 'Opening share allotment',
            'transaction_date' => now(),
            'import_batch_id' => $this->batchId,
            'external_reference' => $row['external_reference'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|exists:members,staff_id',
            'shares' => 'required|integer|min:1',
            'share_price' => 'nullable|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'external_reference' => 'nullable|string|max:100',
        ];
    }
}
