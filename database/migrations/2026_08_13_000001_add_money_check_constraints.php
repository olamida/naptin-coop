<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add DB-level CHECK constraints on the money columns (audit P4 #25).
 *
 * Defence-in-depth on top of the business logic: amounts must be positive,
 * schedule balances non-negative, and every journal line must be single-sided
 * (either a debit or a credit, never both and never neither).
 *
 * These constraints are enforced by MySQL 8 / MariaDB. The test suite runs on
 * SQLite (in-memory) which cannot ALTER TABLE to add CHECK constraints, so the
 * migration is a no-op for every driver other than MySQL-family databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        $constraints = [
            // Amounts must be strictly positive.
            'savings_transactions' => [
                ['chk_savings_amount_positive', 'amount > 0'],
            ],
            'share_transactions' => [
                ['chk_share_amount_positive', 'amount > 0'],
            ],
            'loan_repayments' => [
                ['chk_loan_repayment_amount_positive', 'amount > 0'],
                ['chk_loan_repayment_principal_positive', 'principal_portion >= 0'],
                ['chk_loan_repayment_interest_positive', 'interest_portion >= 0'],
                ['chk_loan_repayment_fees_positive', 'fees_portion >= 0'],
            ],
            'purchase_orders' => [
                ['chk_purchase_order_total_positive', 'total_amount > 0'],
                ['chk_purchase_order_monthly_positive', 'monthly_repayment >= 0'],
            ],
            'dividend_distributions' => [
                ['chk_dividend_amount_positive', 'amount > 0'],
                ['chk_dividend_share_count_positive', 'share_count > 0'],
            ],
            // Schedules must never carry negative balances/amounts.
            'loan_repayment_schedules' => [
                ['chk_loan_schedule_principal_positive', 'principal_amount >= 0'],
                ['chk_loan_schedule_interest_positive', 'interest_amount >= 0'],
                ['chk_loan_schedule_total_positive', 'total_amount >= 0'],
            ],
            'hire_purchase_schedules' => [
                ['chk_hp_schedule_principal_positive', 'principal_amount >= 0'],
                ['chk_hp_schedule_total_positive', 'total_amount >= 0'],
            ],
            'cash_counts' => [
                ['chk_cash_counts_non_negative', 'system_balance >= 0 AND physical_count >= 0'],
            ],
            // Journal lines must be single-sided.
            'journal_entry_lines' => [
                ['chk_journal_line_single_side', '(debit > 0 AND credit = 0) OR (credit > 0 AND debit = 0)'],
            ],
        ];

        foreach ($constraints as $table => $rules) {
            foreach ($rules as [$name, $expression]) {
                DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$name}` CHECK ({$expression})");
            }
        }
    }

    public function down(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        $constraints = [
            'savings_transactions' => ['chk_savings_amount_positive'],
            'share_transactions' => ['chk_share_amount_positive'],
            'loan_repayments' => [
                'chk_loan_repayment_amount_positive',
                'chk_loan_repayment_principal_positive',
                'chk_loan_repayment_interest_positive',
                'chk_loan_repayment_fees_positive',
            ],
            'purchase_orders' => [
                'chk_purchase_order_total_positive',
                'chk_purchase_order_monthly_positive',
            ],
            'dividend_distributions' => [
                'chk_dividend_amount_positive',
                'chk_dividend_share_count_positive',
            ],
            'loan_repayment_schedules' => [
                'chk_loan_schedule_principal_positive',
                'chk_loan_schedule_interest_positive',
                'chk_loan_schedule_total_positive',
            ],
            'hire_purchase_schedules' => [
                'chk_hp_schedule_principal_positive',
                'chk_hp_schedule_total_positive',
            ],
            'cash_counts' => ['chk_cash_counts_non_negative'],
            'journal_entry_lines' => ['chk_journal_line_single_side'],
        ];

        foreach ($constraints as $table => $names) {
            foreach ($names as $name) {
                DB::statement("ALTER TABLE `{$table}` DROP CHECK `{$name}`");
            }
        }
    }

    private function isMysql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
