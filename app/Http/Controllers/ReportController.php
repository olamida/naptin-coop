<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Member;
use App\Models\PurchaseOrder;
use App\Models\SavingsTransaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $members = Member::where('status', 'active')->orderBy('first_name')->get();

        return view('reports.index', ['members' => $members]);
    }

    public function memberStatus(Request $request, Member $member): \Illuminate\View\View
    {
        $member->load([
            'region',
            'savingsAccount.transactions' => fn ($q) => $q->latest('transaction_date'),
            'shareAccount',
            'loans' => fn ($q) => $q->latest(),
            'nextOfKins',
        ]);

        $savingsBalance = $member->savingsAccount?->balance ?? 0;
        $sharesCount = $member->shareAccount?->total_shares ?? 0;
        $sharesValue = $member->shareAccount?->total_value ?? 0;

        $activeLoans = $member->loans()->whereIn('status', ['disbursed', 'repaying'])->get();
        $allLoans = $member->loans;
        $totalLoanOutstanding = $activeLoans->sum('outstanding');
        $totalLoanRepaid = $activeLoans->sum('total_repaid');
        $totalLoanAmount = $member->loans->sum('amount');

        $activePurchases = PurchaseOrder::where('member_id', $member->id)
            ->whereIn('status', ['approved', 'active'])
            ->get();
        $allPurchases = PurchaseOrder::where('member_id', $member->id)->latest()->get();
        $totalPurchaseOutstanding = $activePurchases->sum('total_amount') - $activePurchases->sum('amount_paid');
        $totalPurchases = $allPurchases->sum('total_amount');
        $totalPurchasePaid = $allPurchases->sum('amount_paid');

        $expectedMonthlySavings = $member->monthly_savings ?? round($member->monthly_salary * 0.10, 2);
        $expectedMonthlyShares = round($member->monthly_salary * 0.05, 2);
        $expectedLoanRepayment = $activeLoans->sum('monthly_repayment');
        $expectedPurchaseRepayment = $activePurchases->where('payment_type', 'hire_purchase')->sum('monthly_repayment');
        $totalMonthlyDeduction = $expectedMonthlySavings + $expectedMonthlyShares + $expectedLoanRepayment + $expectedPurchaseRepayment;

        $savingsRate = $member->monthly_savings ? round(($member->monthly_savings / max($member->monthly_salary, 1)) * 100, 1) : 10.0;

        return view('reports.member-status', compact(
            'member',
            'savingsBalance',
            'sharesCount',
            'sharesValue',
            'activeLoans',
            'allLoans',
            'totalLoanOutstanding',
            'totalLoanRepaid',
            'totalLoanAmount',
            'activePurchases',
            'allPurchases',
            'totalPurchaseOutstanding',
            'totalPurchases',
            'totalPurchasePaid',
            'expectedMonthlySavings',
            'expectedMonthlyShares',
            'expectedLoanRepayment',
            'expectedPurchaseRepayment',
            'totalMonthlyDeduction',
            'savingsRate',
        ));
    }
}
