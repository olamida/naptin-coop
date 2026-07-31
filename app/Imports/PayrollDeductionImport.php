<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\PayrollArrear;
use App\Models\PayrollDeduction;
use App\Imports\Concerns\TracksImportStats;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PayrollDeductionImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use TracksImportStats;

    private int $payrollId;

    public function __construct(int $payrollId, public ?string $batchId = null)
    {
        $this->payrollId = $payrollId;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required',
            'actual_savings' => 'required|numeric|min:0',
            'actual_loan_repayment' => 'required|numeric|min:0',
            'actual_share_contribution' => 'required|numeric|min:0',
            'actual_purchase' => 'nullable|numeric|min:0',
            'actual_arrears' => 'nullable|numeric|min:0',
        ];
    }

    public function model(array $row)
    {
        $this->trackRow();

        $member = Member::where('staff_id', $row['staff_id'])->first();

        if (!$member) {
            $this->markFailure('No member found for staff_id ' . $row['staff_id']);

            return null;
        }

        $deduction = PayrollDeduction::where('monthly_payroll_id', $this->payrollId)
            ->where('member_id', $member->id)
            ->first();

        if (!$deduction) {
            $this->markFailure('No payroll deduction row for member ' . $row['staff_id']);

            return null;
        }

        $actualSavings = round($row['actual_savings'], 2);
        $actualLoanRepayment = round($row['actual_loan_repayment'], 2);
        $actualShareContribution = round($row['actual_share_contribution'], 2);
        $actualPurchase = round($row['actual_purchase'] ?? 0, 2);
        $actualArrears = round($row['actual_arrears'] ?? 0, 2);
        $totalActual = $actualSavings + $actualLoanRepayment + $actualShareContribution + $actualPurchase + $actualArrears;

        $deduction->update([
            'actual_savings' => $actualSavings,
            'actual_loan_repayment' => $actualLoanRepayment,
            'actual_share_contribution' => $actualShareContribution,
            'actual_purchase' => $actualPurchase,
            'actual_arrears' => $actualArrears,
            'total_actual' => $totalActual,
            'status' => 'completed',
        ]);

        if ((float) $totalActual >= (float) $deduction->total_expected) {
            PayrollArrear::open()
                ->where('member_id', $member->id)
                ->update([
                    'status' => 'settled',
                    'settled_at' => now(),
                ]);
        }

        $this->markSuccess();

        return null;
    }
}
