<?php

namespace App\Actions\Loans;

use App\Actions\Action;
use App\Models\Loan;
use App\Models\LoanGuarantor;

class UpdateGuarantor extends Action
{
    public function handle(Loan $loan, LoanGuarantor $guarantor, string $status, ?string $notes = null, ?string $ip = null, ?string $userAgent = null): LoanGuarantor
    {
        if ($guarantor->loan_id !== $loan->id) {
            throw new \RuntimeException('This guarantor does not belong to this loan.');
        }

        $guarantor->update([
            'status' => $status,
            'notes' => $notes,
            'responded_at' => now(),
            'accepted_ip' => $ip,
            'accepted_user_agent' => $userAgent,
        ]);

        return $guarantor->fresh();
    }
}
