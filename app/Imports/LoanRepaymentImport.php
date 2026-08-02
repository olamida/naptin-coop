<?php

namespace App\Imports;

use App\Imports\Concerns\TracksImportStats;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LoanRepaymentImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use TracksImportStats;

    public function __construct(
        public ?string $batchId = null,
    ) {}

    public function model(array $row): ?LoanRepayment
    {
        $this->trackRow();

        if (! empty($row['external_reference']) && LoanRepayment::where('external_reference', $row['external_reference'])->exists()) {
            $this->markFailure('Duplicate external reference "'.$row['external_reference'].'" — skipped');

            return null;
        }

        $member = Member::where('staff_id', $row['staff_id'])->first();

        if (! $member) {
            $this->markFailure('No member found for staff_id '.$row['staff_id']);

            return null;
        }

        $loan = Loan::where('member_id', $member->id)
            ->whereIn('status', ['disbursed', 'repaying'])
            ->first();

        if (! $loan) {
            $this->markFailure('No active loan found for member '.$row['staff_id']);

            return null;
        }

        $amount = round((float) $row['amount'], 2);

        if ($amount > $loan->outstanding) {
            $this->markFailure('Amount exceeds outstanding balance for member '.$row['staff_id']);

            return null;
        }

        $interestRate = $loan->interest_rate / 100;
        $interestPortion = round($amount * ($interestRate / (1 + $interestRate)), 2);
        $principalPortion = round($amount - $interestPortion, 2);
        $outstandingAfter = round($loan->outstanding - $principalPortion, 2);

        DB::transaction(function () use ($loan, $member, $amount, $principalPortion, $interestPortion, $outstandingAfter, $row) {
            LoanRepayment::create([
                'loan_id' => $loan->id,
                'member_id' => $member->id,
                'reference' => 'LN/IMP/'.strtoupper(Str::random(8)),
                'amount' => $amount,
                'principal_portion' => $principalPortion,
                'interest_portion' => $interestPortion,
                'outstanding_after' => $outstandingAfter,
                'payment_method' => 'salary_deduction',
                'source' => 'salary_deduction',
                'payment_date' => $row['payment_date'] ?? now()->toDateString(),
                'notes' => $row['notes'] ?? null,
                'import_batch_id' => $this->batchId,
                'external_reference' => $row['external_reference'] ?? null,
            ]);

            $loan->update([
                'total_repaid' => $loan->total_repaid + $amount,
                'outstanding' => max(0, $outstandingAfter),
                'status' => $outstandingAfter <= 0 ? 'completed' : 'repaying',
            ]);
        });

        $this->markSuccess();

        return new LoanRepayment;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|exists:members,staff_id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'external_reference' => 'nullable|string|max:100',
        ];
    }
}
