<?php

namespace App\Http\Controllers;

use App\Enums\GuarantorStatus;
use App\Models\ActivityLog;
use App\Models\Dividend;
use App\Models\Loan;
use App\Models\LoanGuarantor;
use App\Models\Member;
use App\Models\MonthlyPayroll;
use App\Models\PurchaseOrder;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\ShareAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $totalMembers = Member::count();
        $activeMembers = Member::where('status', 'active')->count();
        $inactiveMembers = Member::whereIn('status', ['inactive', 'retired', 'suspended'])->count();
        $totalSavings = SavingsAccount::sum('balance');
        $totalShares = ShareAccount::sum('total_value');

        $totalLoansDisbursed = Loan::whereIn('status', ['disbursed', 'repaying'])->sum('amount');
        $totalLoansOutstanding = Loan::whereIn('status', ['disbursed', 'repaying'])->sum('outstanding');
        $totalLoansRepaid = $totalLoansDisbursed - $totalLoansOutstanding;
        $pendingLoans = Loan::where('status', 'pending')->count();
        $pendingLoanAmount = Loan::where('status', 'pending')->sum('amount');
        $completedLoans = Loan::where('status', 'completed')->count();

        $defaultedLoans = Loan::defaulted()->count();
        $defaultedAmount = Loan::defaulted()->sum('outstanding');
        $overdueCount = Loan::overdue()->count();
        $overdueAmount = Loan::overdue()->sum('outstanding');
        $arrearsCount = $defaultedLoans + $overdueCount;
        $arrearsAmount = $defaultedAmount + $overdueAmount;

        $totalPurchases = PurchaseOrder::whereIn('status', ['approved', 'active', 'completed'])->sum('total_amount');
        $activePurchases = PurchaseOrder::where('status', 'active')->count();
        $pendingOrders = PurchaseOrder::where('status', 'pending')->count();

        $monthlyDeposits = SavingsTransaction::where('type', 'deposit')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        $monthlyWithdrawals = SavingsTransaction::where('type', 'withdrawal')
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $monthlyLoanRepayments = \App\Models\LoanRepayment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $recentMembers = Member::with('region')->latest()->take(5)->get();
        $recentLoans = Loan::with('member')->latest()->take(5)->get();

        $totalRegions = \App\Models\Region::count();

        $regionStats = Member::select('region_id', DB::raw('count(*) as total'))
            ->groupBy('region_id')
            ->with('region')
            ->get();

        $loanStatusCounts = Loan::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Pending withdrawals
        $pendingWithdrawals = SavingsTransaction::where('type', 'withdrawal')
            ->where('status', 'pending')
            ->count();
        $pendingWithdrawalAmount = SavingsTransaction::where('type', 'withdrawal')
            ->where('status', 'pending')
            ->sum('amount');

        // Pending guarantor requests
        $pendingGuarantors = LoanGuarantor::where('status', GuarantorStatus::Pending)->count();

        // Monthly trends (last 6 months)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyTrends[] = [
                'label' => $date->format('M Y'),
                'deposits' => (float) SavingsTransaction::where('type', 'deposit')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount'),
                'withdrawals' => (float) SavingsTransaction::where('type', 'withdrawal')
                    ->where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount'),
                'loan_disbursed' => (float) Loan::where('status', 'disbursed')
                    ->whereMonth('disbursement_date', $date->month)
                    ->whereYear('disbursement_date', $date->year)
                    ->sum('amount'),
                'loan_repayments' => (float) \App\Models\LoanRepayment::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount'),
                'new_members' => Member::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ];
        }

        // Top 5 savers
        $topSavers = SavingsAccount::with('member')
            ->where('balance', '>', 0)
            ->orderByDesc('balance')
            ->take(5)
            ->get();

        // Recent activity feed
        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        // Latest dividend
        $latestDividend = Dividend::latest()->first();

        // Repayment rate
        $repaymentRate = $totalLoansDisbursed > 0 ? round(($totalLoansRepaid / $totalLoansDisbursed) * 100) : 0;

        // Net monthly flow
        $netMonthlyFlow = $monthlyDeposits - $monthlyWithdrawals;

        return view('dashboard.index', compact(
            'totalMembers',
            'activeMembers',
            'inactiveMembers',
            'totalSavings',
            'totalShares',
            'totalLoansDisbursed',
            'totalLoansOutstanding',
            'totalLoansRepaid',
            'pendingLoans',
            'pendingLoanAmount',
            'completedLoans',
            'totalPurchases',
            'activePurchases',
            'pendingOrders',
            'monthlyDeposits',
            'monthlyWithdrawals',
            'monthlyLoanRepayments',
            'recentMembers',
            'recentLoans',
            'totalRegions',
            'regionStats',
            'loanStatusCounts',
            'arrearsCount',
            'arrearsAmount',
            'overdueCount',
            'overdueAmount',
            'defaultedLoans',
            'defaultedAmount',
            'pendingWithdrawals',
            'pendingWithdrawalAmount',
            'pendingGuarantors',
            'monthlyTrends',
            'topSavers',
            'recentActivity',
            'latestDividend',
            'repaymentRate',
            'netMonthlyFlow',
        ));
    }
}
