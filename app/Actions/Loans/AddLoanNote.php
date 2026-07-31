<?php

namespace App\Actions\Loans;

use App\Actions\Action;
use App\Models\Loan;
use App\Models\LoanApprovalLog;

class AddLoanNote extends Action
{
    public function handle(Loan $loan, string $notes): Loan
    {
        $loan->update(['admin_notes' => $notes]);

        LoanApprovalLog::record($loan->id, 'note_added', $loan->status, $loan->status, $notes);

        return $loan->fresh();
    }
}
