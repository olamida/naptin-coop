<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

/**
 * Seeds the full CBN-compliant Chart of Accounts (MFB framework 2020 / Cooperative
 * Societies Act) with codes matching the accounting spec exactly.
 *
 * Idempotent + data-safe: accounts that already exist (e.g. the operational codes
 * 1001 Cash & Bank, 1101 Loans Receivable, 1201 Purchase Receivables, 2001 Members
 * Savings, 2002 Dividends Payable, 2101 Share Capital, 3001 Retained Earnings,
 * 4001/4002, 5001/5004) are kept untouched — only their new CBN columns
 * (subtype, is_control_account, control_module, allow_manual_entry) are synced.
 * Missing accounts are created with the exact spec codes.
 *
 * Documented deviations (existing operational codes win over spec duplicates):
 *   - 1201 stays "Purchase Receivables" (spec: Loan Portfolio - Regular)
 *   - 2002 stays "Dividends Payable" (spec: Members Savings - Target)
 *   - 3001 stays "Retained Earnings" (spec: General Reserve); the CBN statutory
 *     reserve appropriation posts to a dedicated 3003 General Reserve account
 *   - 4002 stays "Sales Revenue" (spec: Interest Income - Emergency)
 *   - 5001 stays "Procurement & General Expenses" (spec: Bank Charges)
 * These avoid two accounts serving one purpose; new features use the spec codes
 * (2201 Dividend Payable, 1301 Inventory, 1501 Payroll Receivable, 4004 Fees,
 * 4005 Sales Margin, 3002 Education Fund, 3003 General Reserve, 1005 Cash
 * Suspense, etc.).
 */
class LedgerAccountsSeeder extends Seeder
{
    private const CHART = [
        // ------------------------------------------------------------- Assets
        ['code' => '1001', 'name' => 'Cash in Hand - HQ', 'type' => 'asset', 'normal_side' => 'debit', 'subtype' => 'current_asset', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],
        ['code' => '1002', 'name' => 'Bank - First Bank', 'type' => 'asset', 'normal_side' => 'debit', 'subtype' => 'current_asset', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '1003', 'name' => 'Bank - UBA', 'type' => 'asset', 'normal_side' => 'debit', 'subtype' => 'current_asset', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '1005', 'name' => 'Cash Suspense - Shortage/Excess', 'type' => 'asset', 'normal_side' => 'debit', 'subtype' => 'current_asset', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '1101', 'name' => 'Loans Receivable', 'type' => 'asset', 'normal_side' => 'debit', 'subtype' => 'current_asset', 'is_control_account' => true, 'control_module' => 'loans', 'allow_manual_entry' => false],
        ['code' => '1201', 'name' => 'Purchase Receivables', 'type' => 'asset', 'normal_side' => 'debit', 'subtype' => 'current_asset', 'is_control_account' => true, 'control_module' => 'purchases', 'allow_manual_entry' => false],
        ['code' => '1205', 'name' => 'Loan Loss Provision', 'type' => 'asset', 'normal_side' => 'credit', 'subtype' => 'contra_asset', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],
        ['code' => '1301', 'name' => 'Inventory - Cooperative Store', 'type' => 'asset', 'normal_side' => 'debit', 'subtype' => 'current_asset', 'is_control_account' => true, 'control_module' => 'inventory', 'allow_manual_entry' => false],
        ['code' => '1401', 'name' => 'Fixed Assets - Furniture', 'type' => 'asset', 'normal_side' => 'debit', 'subtype' => 'non_current_asset', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '1402', 'name' => 'Accumulated Depreciation', 'type' => 'asset', 'normal_side' => 'credit', 'subtype' => 'contra_asset', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '1501', 'name' => 'Receivable - Payroll Deductions Expected', 'type' => 'asset', 'normal_side' => 'debit', 'subtype' => 'current_asset', 'is_control_account' => true, 'control_module' => 'payroll', 'allow_manual_entry' => false],

        // ---------------------------------------------------------- Liabilities
        ['code' => '2001', 'name' => 'Members Savings - Regular', 'type' => 'liability', 'normal_side' => 'credit', 'subtype' => 'current_liability', 'is_control_account' => true, 'control_module' => 'savings', 'allow_manual_entry' => false],
        ['code' => '2002', 'name' => 'Dividends Payable', 'type' => 'liability', 'normal_side' => 'credit', 'subtype' => 'current_liability', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],
        ['code' => '2003', 'name' => 'Members Savings - Fixed Deposit', 'type' => 'liability', 'normal_side' => 'credit', 'subtype' => 'current_liability', 'is_control_account' => true, 'control_module' => 'savings', 'allow_manual_entry' => false],
        ['code' => '2101', 'name' => 'Members Share Capital', 'type' => 'liability', 'normal_side' => 'credit', 'subtype' => 'non_current_liability', 'is_control_account' => true, 'control_module' => 'shares', 'allow_manual_entry' => false],
        ['code' => '2201', 'name' => 'Dividend Payable', 'type' => 'liability', 'normal_side' => 'credit', 'subtype' => 'current_liability', 'is_control_account' => true, 'control_module' => 'dividends', 'allow_manual_entry' => false],
        ['code' => '2301', 'name' => 'Audit Fees Payable', 'type' => 'liability', 'normal_side' => 'credit', 'subtype' => 'current_liability', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '2401', 'name' => 'Loan Collateral / Savings Lien', 'type' => 'liability', 'normal_side' => 'credit', 'subtype' => 'current_liability', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],

        // -------------------------------------------------------------- Equity
        ['code' => '3001', 'name' => 'Retained Earnings', 'type' => 'equity', 'normal_side' => 'credit', 'subtype' => 'reserve', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '3002', 'name' => 'Education Fund', 'type' => 'equity', 'normal_side' => 'credit', 'subtype' => 'reserve', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],
        ['code' => '3003', 'name' => 'General Reserve', 'type' => 'equity', 'normal_side' => 'credit', 'subtype' => 'reserve', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],

        // --------------------------------------------------------------- Income
        ['code' => '4001', 'name' => 'Interest Income - Regular Loans', 'type' => 'income', 'normal_side' => 'credit', 'subtype' => 'operating_income', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],
        ['code' => '4002', 'name' => 'Sales Revenue', 'type' => 'income', 'normal_side' => 'credit', 'subtype' => 'operating_income', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],
        ['code' => '4003', 'name' => 'Interest Income - Educational Loans', 'type' => 'income', 'normal_side' => 'credit', 'subtype' => 'operating_income', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],
        ['code' => '4004', 'name' => 'Processing Fees Income', 'type' => 'income', 'normal_side' => 'credit', 'subtype' => 'operating_income', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],
        ['code' => '4005', 'name' => 'Sales Margin - Store', 'type' => 'income', 'normal_side' => 'credit', 'subtype' => 'operating_income', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],
        ['code' => '4006', 'name' => 'Penalty Income - Late Repayment', 'type' => 'income', 'normal_side' => 'credit', 'subtype' => 'operating_income', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '4007', 'name' => 'Commission Income', 'type' => 'income', 'normal_side' => 'credit', 'subtype' => 'non_operating_income', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],

        // ------------------------------------------------------------- Expenses
        ['code' => '5001', 'name' => 'Procurement & General Expenses', 'type' => 'expense', 'normal_side' => 'debit', 'subtype' => 'operating_expense', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '5002', 'name' => 'Audit Fees', 'type' => 'expense', 'normal_side' => 'debit', 'subtype' => 'operating_expense', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '5003', 'name' => 'Administrative Expenses', 'type' => 'expense', 'normal_side' => 'debit', 'subtype' => 'operating_expense', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '5004', 'name' => 'Loan Loss Expense', 'type' => 'expense', 'normal_side' => 'debit', 'subtype' => 'operating_expense', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => false],
        ['code' => '5005', 'name' => 'Depreciation Expense', 'type' => 'expense', 'normal_side' => 'debit', 'subtype' => 'operating_expense', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '5006', 'name' => 'Dividend Expense', 'type' => 'expense', 'normal_side' => 'debit', 'subtype' => 'operating_expense', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '5007', 'name' => 'Staff Costs', 'type' => 'expense', 'normal_side' => 'debit', 'subtype' => 'operating_expense', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
        ['code' => '5008', 'name' => 'Office Expenses', 'type' => 'expense', 'normal_side' => 'debit', 'subtype' => 'operating_expense', 'is_control_account' => false, 'control_module' => null, 'allow_manual_entry' => true],
    ];

    public function run(): void
    {
        foreach (self::CHART as $account) {
            $existing = ChartOfAccount::firstWhere('code', $account['code']);

            if ($existing) {
                // Preserve the operational name/type/normal_side, but sync the new
                // CBN columns so flags stay correct no matter how the account arrived.
                $existing->update([
                    'subtype' => $account['subtype'],
                    'is_control_account' => $account['is_control_account'],
                    'control_module' => $account['control_module'],
                    'allow_manual_entry' => $account['allow_manual_entry'],
                ]);

                continue;
            }

            ChartOfAccount::create($account);
        }
    }
}
