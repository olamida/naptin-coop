<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\ImportLog;
use App\Models\Member;
use App\Models\MonthlyPayroll;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SavingsTransaction;
use Illuminate\Http\Request;

class DataImportController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $imports = [
            [
                'title' => 'Import Members',
                'description' => 'Bulk import member records from Excel. Creates savings and share accounts automatically.',
                'icon' => 'group',
                'color' => 'indigo',
                'route' => route('members.import'),
                'template_route' => route('members.download-template'),
                'columns' => 'staff_id, first_name, last_name, middle_name, region, email, phone, gender, date_of_birth, ...',
                'required' => 'staff_id, first_name, last_name, region',
                'record_count' => Member::count(),
                'record_label' => 'members',
            ],
            [
                'title' => 'Import Savings',
                'description' => 'Bulk import savings deposits or withdrawals for members via salary deduction.',
                'icon' => 'savings',
                'color' => 'emerald',
                'route' => route('savings.import'),
                'template_route' => route('savings.download-template'),
                'columns' => 'staff_id, amount, type, transaction_date, notes',
                'required' => 'staff_id, amount',
                'record_count' => SavingsTransaction::count(),
                'record_label' => 'transactions',
            ],
            [
                'title' => 'Import Loan Repayments',
                'description' => 'Bulk import loan repayment records. System auto-splits into principal and interest.',
                'icon' => 'account_balance',
                'color' => 'amber',
                'route' => route('loans.import'),
                'template_route' => route('loans.download-template'),
                'columns' => 'staff_id, amount, payment_date, notes',
                'required' => 'staff_id, amount',
                'record_count' => Loan::whereIn('status', ['disbursed', 'repaying'])->count(),
                'record_label' => 'active loans',
            ],
            [
                'title' => 'Import Products',
                'description' => 'Bulk import or update the product catalog. Existing products are updated by name.',
                'icon' => 'inventory_2',
                'color' => 'pink',
                'route' => route('products.import'),
                'template_route' => route('products.download-template'),
                'columns' => 'name, description, unit_price, stock_quantity, enabled',
                'required' => 'name, unit_price',
                'record_count' => Product::count(),
                'record_label' => 'products',
            ],
            [
                'title' => 'Import Purchase Orders',
                'description' => 'Bulk import purchase orders for members. Orders start as pending for approval.',
                'icon' => 'shopping_cart',
                'color' => 'cyan',
                'route' => route('purchases.import'),
                'template_route' => route('purchases.download-template'),
                'columns' => 'staff_id, product_name, quantity, unit_price, payment_date, notes',
                'required' => 'staff_id, product_name',
                'record_count' => PurchaseOrder::count(),
                'record_label' => 'orders',
            ],
            [
                'title' => 'Upload Payroll Deductions',
                'description' => 'Upload actual payroll deduction amounts for a compiled payroll run.',
                'icon' => 'payments',
                'color' => 'violet',
                'route' => null,
                'template_route' => null,
                'columns' => 'staff_id, actual_savings, actual_loan_repayment, actual_share_contribution, actual_purchase',
                'required' => 'staff_id, actual amounts',
                'record_count' => MonthlyPayroll::count(),
                'record_label' => 'payroll runs',
                'note' => 'Select a specific payroll run from the Payroll page to upload deductions.',
                'extra_routes' => [
                    ['label' => 'View Payroll Runs', 'route' => route('payroll.index')],
                ],
            ],
        ];

        $recentBatches = ImportLog::with('creator')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.data-import.index', compact('imports', 'recentBatches'));
    }
}
