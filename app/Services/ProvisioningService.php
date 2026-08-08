<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanLossProvision;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * CBN / IFRS 9 Loan Loss Provisioning.
 *
 * Classification buckets (CBN MFB framework):
 *  - Performing       0-30  days past due  -> 1%
 *  - Pass & Watch     31-60 days            -> 25%
 *  - Substandard      61-90 days            -> 50%
 *  - Doubtful         91-180 days           -> 75%
 *  - Lost             >180 days             -> 100%
 */
class ProvisioningService
{
    public const BUCKETS = [
        ['max_days' => 30, 'classification' => 'Performing', 'rate' => 0.01],
        ['max_days' => 60, 'classification' => 'Pass & Watch', 'rate' => 0.25],
        ['max_days' => 90, 'classification' => 'Substandard', 'rate' => 0.50],
        ['max_days' => 180, 'classification' => 'Doubtful', 'rate' => 0.75],
        ['max_days' => PHP_INT_MAX, 'classification' => 'Lost', 'rate' => 1.00],
    ];

    public static function classify(int $daysPastDue): array
    {
        foreach (self::BUCKETS as $bucket) {
            if ($daysPastDue <= $bucket['max_days']) {
                return [
                    'classification' => $bucket['classification'],
                    'rate' => $bucket['rate'],
                ];
            }
        }

        return ['classification' => 'Lost', 'rate' => 1.00];
    }

    public static function daysPastDue(Loan $loan): int
    {
        if ($loan->status === 'defaulted') {
            $base = $loan->maturity_date ?? $loan->application_date ?? $loan->disbursement_date;

            return $base ? (int) max(0, $base->diffInDays(now())) : 0;
        }

        $overdueSchedule = $loan->schedules()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString())
            ->orderBy('due_date')
            ->first();

        if (! $overdueSchedule) {
            return 0;
        }

        return (int) max(0, $overdueSchedule->due_date->diffInDays(now()));
    }

    /**
     * Outstanding principal for a loan (amount minus principal repaid).
     */
    public static function outstandingPrincipal(Loan $loan): float
    {
        $repaidPrincipal = (float) $loan->repayments()->sum('principal_portion');

        return Money::sub((float) $loan->amount, $repaidPrincipal);
    }

    /**
     * Build the loan aging dataset for the current period.
     *
     * @return array{period: string, rows: array, total_outstanding: float, total_provision: float, coverage_ratio: float}
     */
    public static function agingReport(): array
    {
        $period = now()->format('Y-m');
        $rows = [];

        $loans = Loan::with('member')
            ->whereIn('status', ['disbursed', 'repaying', 'defaulted'])
            ->where('outstanding', '>', 0)
            ->orderBy('loan_number')
            ->get();

        $totalOutstanding = 0;
        $totalProvision = 0;

        foreach ($loans as $loan) {
            $outstanding = self::outstandingPrincipal($loan);
            $daysPastDue = self::daysPastDue($loan);
            $bucket = self::classify($daysPastDue);
            $provision = Money::mul($outstanding, $bucket['rate']);

            $totalOutstanding = Money::add($totalOutstanding, $outstanding);
            $totalProvision = Money::add($totalProvision, $provision);

            $rows[] = [
                'loan' => $loan,
                'loan_number' => $loan->loan_number,
                'member' => $loan->member?->full_name ?? $loan->member?->first_name ?? 'N/A',
                'outstanding' => $outstanding,
                'days_past_due' => $daysPastDue,
                'classification' => $bucket['classification'],
                'rate' => $bucket['rate'],
                'provision' => $provision,
            ];
        }

        $coverageRatio = Money::gt($totalOutstanding, 0) ? round(Money::mul(Money::div($totalProvision, $totalOutstanding), 100), 2) : 0.0;

        return [
            'period' => $period,
            'rows' => $rows,
            'total_outstanding' => Money::add($totalOutstanding, 0),
            'total_provision' => Money::add($totalProvision, 0),
            'coverage_ratio' => $coverageRatio,
        ];
    }

    /**
     * Persist provisions for the current period and post the net movement to the ledger.
     * The delta between the current provision balance and the required total is posted,
     * so running it repeatedly converges without double-counting.
     */
    public static function calculate(): array
    {
        $report = self::agingReport();
        $period = $report['period'];
        $requiredProvision = $report['total_provision'];

        $result = DB::transaction(function () use ($report, $period, $requiredProvision) {
            $ledger = new LedgerService;
            $existingProvision = $ledger->getBalance(LedgerService::LOAN_LOSS_PROVISION);
            $delta = Money::sub($requiredProvision, $existingProvision);

            $journalEntry = null;

            if (Money::gte(Money::abs($delta), 0.01)) {
                if (Money::gt($delta, 0)) {
                    $journalEntry = $ledger->post(
                        "Loan loss provision for {$period}",
                        'provision',
                        null,
                        [
                            ['account_code' => LedgerService::LOAN_LOSS_EXPENSE, 'debit' => $delta, 'credit' => 0],
                            ['account_code' => LedgerService::LOAN_LOSS_PROVISION, 'debit' => 0, 'credit' => $delta],
                        ]
                    );
                } else {
                    $journalEntry = $ledger->post(
                        "Loan loss provision release for {$period}",
                        'provision',
                        null,
                        [
                            ['account_code' => LedgerService::LOAN_LOSS_PROVISION, 'debit' => Money::abs($delta), 'credit' => 0],
                            ['account_code' => LedgerService::LOAN_LOSS_EXPENSE, 'debit' => 0, 'credit' => Money::abs($delta)],
                        ]
                    );
                }
            }

            LoanLossProvision::where('period', $period)->delete();

            foreach ($report['rows'] as $row) {
                LoanLossProvision::create([
                    'loan_id' => $row['loan']->id,
                    'period' => $period,
                    'outstanding' => $row['outstanding'],
                    'days_past_due' => $row['days_past_due'],
                    'classification' => $row['classification'],
                    'rate' => $row['rate'],
                    'provision_amount' => $row['provision'],
                    'journal_entry_id' => $journalEntry?->id,
                ]);
            }

            return [
                'period' => $period,
                'required_provision' => $requiredProvision,
                'existing_provision' => $existingProvision,
                'delta' => $delta,
                'journal_entry_id' => $journalEntry?->id,
            ];
        });

        $result['rows'] = $report['rows'];
        $result['total_outstanding'] = $report['total_outstanding'];
        $result['coverage_ratio'] = $report['coverage_ratio'];

        return $result;
    }
}
