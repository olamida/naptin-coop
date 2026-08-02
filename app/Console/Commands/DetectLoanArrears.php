<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;

class DetectLoanArrears extends Command
{
    protected $signature = 'app:detect-loan-arrears';

    protected $description = 'Mark disbursed loans past maturity date as defaulted';

    public function handle(): int
    {
        $overdueLoans = Loan::overdue()->get();

        if ($overdueLoans->isEmpty()) {
            $this->info('No overdue loans found.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($overdueLoans as $loan) {
            $loan->update(['status' => 'defaulted']);
            $this->line("  Loan {$loan->loan_number} — {$loan->member->first_name} {$loan->member->last_name} — ₦".number_format($loan->outstanding, 2)." outstanding — {$loan->daysOverdue()} days overdue");
            $count++;
        }

        $totalOutstanding = $overdueLoans->sum('outstanding');
        $this->info("Done. {$count} loan(s) marked as defaulted. Total outstanding: ₦".number_format($totalOutstanding, 2));

        return self::SUCCESS;
    }
}
