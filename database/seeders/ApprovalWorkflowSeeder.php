<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use Illuminate\Database\Seeder;

class ApprovalWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $workflows = [
            [
                'key' => 'period_reopen',
                'name' => 'Period Reopen',
                'required_permission' => 'manage-users',
                'required_roles' => ['president', 'auditor'],
                'threshold_amount' => null,
                'enabled' => true,
            ],
            [
                'key' => 'loan_disbursement',
                'name' => 'Loan Disbursement',
                'required_permission' => 'disburse-loans',
                'required_roles' => ['treasurer', 'auditor'],
                'threshold_amount' => null,
                'enabled' => true,
            ],
            [
                'key' => 'dividend_declaration',
                'name' => 'Dividend Declaration',
                'required_permission' => 'approve-dividends',
                'required_roles' => ['president', 'auditor'],
                'threshold_amount' => null,
                'enabled' => true,
            ],
            [
                'key' => 'savings_withdrawal',
                'name' => 'High-Value Savings Withdrawal',
                'required_permission' => 'withdraw-savings',
                'required_roles' => ['treasurer'],
                'threshold_amount' => 100000.00,
                'enabled' => true,
            ],
        ];

        foreach ($workflows as $workflow) {
            ApprovalWorkflow::updateOrCreate(['key' => $workflow['key']], $workflow);
        }
    }
}
