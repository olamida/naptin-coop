<?php

namespace App\Http\Controllers;

use App\Enums\GuarantorStatus;
use App\Models\ActivityLog;
use App\Models\Dividend;
use App\Models\Loan;
use App\Models\LoanGuarantor;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\MonthlyPayroll;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\ShareAccount;
use App\Models\ShareTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
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

        $monthlyLoanRepayments = LoanRepayment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $recentMembers = Member::with('region')->latest()->take(5)->get();
        $recentLoans = Loan::with('member')->latest()->take(5)->get();

        $totalRegions = Region::count();

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
                'loan_repayments' => (float) LoanRepayment::whereMonth('created_at', $date->month)
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

        // Payroll due info - next upcoming or draft payroll
        $nextPayroll = MonthlyPayroll::whereIn('status', ['draft', 'compiled'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();
        $payrollDueDays = $nextPayroll ? (int) round(now()->startOfDay()->diffInDays(Carbon::parse("{$nextPayroll->year}-{$nextPayroll->month}-01")->endOfMonth()->startOfDay(), false)) : null;
        $payrollDueText = $nextPayroll
            ? ($payrollDueDays > 0 ? "In {$payrollDueDays} days" : ($payrollDueDays == 0 ? 'Due today' : 'Overdue'))
            : 'No pending';

        // Sparkline data: daily deposits last 7 days for Savings KPI
        $savingsSparkline = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $daily = (float) SavingsTransaction::where('type', 'deposit')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->sum('amount');
            $savingsSparkline->push(round($daily));
        }
        $savingsSparklineData = $savingsSparkline->toJson();

        // Yesterday's deposits for comparison
        $yesterdayDeposits = (float) SavingsTransaction::where('type', 'deposit')
            ->whereDate('created_at', Carbon::yesterday()->format('Y-m-d'))
            ->sum('amount');
        $savingsChangePercent = $yesterdayDeposits > 0
            ? round((($monthlyDeposits - $yesterdayDeposits) / $yesterdayDeposits) * 100)
            : 0;

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
            'nextPayroll',
            'payrollDueDays',
            'payrollDueText',
            'savingsSparklineData',
            'savingsChangePercent',
            'yesterdayDeposits',
        ));
    }

    public function commandSearchJson(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        $user = auth()->user();

        if ($q === '') {
            $actions = [];
            $addAction = function (string $label, string $url, string $icon) use (&$actions) {
                $actions[] = ['name' => $label, 'sub' => 'Quick action', 'url' => $url, 'icon' => $icon];
            };

            $addAction('Dashboard', route('dashboard'), 'dashboard');
            if ($user->can('view-members')) {
                $addAction('Members', route('members.index'), 'group');
            }
            if ($user->can('create-members')) {
                $addAction('Register New Member', route('members.create'), 'person_add');
            }
            if ($user->can('view-savings')) {
                $addAction('Savings', route('savings.index'), 'savings');
            }
            if ($user->can('deposit-savings')) {
                $addAction('Record Deposit', route('savings.deposit'), 'add_card');
            }
            if ($user->can('withdraw-savings')) {
                $addAction('Request Withdrawal', route('savings.withdraw'), 'money_off');
            }
            if ($user->can('view-loans')) {
                $addAction('Loans', route('loans.index'), 'account_balance');
            }
            if ($user->can('create-loans')) {
                $addAction('New Loan Application', route('loans.create'), 'request_quote');
            }
            if ($user->can('view-shares')) {
                $addAction('Shares', route('shares.index'), 'trending_up');
            }
            if ($user->can('purchase-shares')) {
                $addAction('Purchase Shares', route('shares.purchase'), 'add_card');
            }
            if ($user->can('view-purchase-orders')) {
                $addAction('Purchase Orders', route('products.orders'), 'shopping_cart');
            }
            if ($user->can('view-products')) {
                $addAction('Products', route('products.index'), 'inventory_2');
            }
            if ($user->can('view-payroll')) {
                $addAction('Payroll', route('payroll.index'), 'payments');
            }
            if ($user->can('view-payroll') && $user->can('compile-payroll')) {
                $addAction('Compile Payroll', route('payroll.compile'), 'fact_check');
            }
            if ($user->can('view-dividends')) {
                $addAction('Dividends', route('dividends.index'), 'diversity_3');
            }
            if ($user->can('view-reports')) {
                $addAction('Reports', route('reports.index'), 'description');
            }
            if ($user->can('manage-users')) {
                $addAction('Management', route('admin.manage'), 'settings');
            }

            return response()->json([[
                'key' => 'actions',
                'label' => 'Quick Actions',
                'icon' => 'bolt',
                'items' => $actions,
            ]]);
        }

        $groups = [];

        $qUpper = strtoupper($q);
        $referenceFor = null;
        foreach ([
            'REG/' => 'members',
            'SAV/' => 'savings',
            'LOAN/' => 'loans',
            'PO/' => 'orders',
            'PAY/' => 'payroll',
            'DIV/' => 'dividends',
        ] as $needle => $groupKey) {
            if (str_starts_with($qUpper, $needle)) {
                $referenceFor = $groupKey;
                break;
            }
        }
        $referenceMatched = false;

        $nameCond = function ($x) use ($q) {
            $x->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")
                ->orWhere('staff_id', 'like', "%{$q}%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$q}%"])
                ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE ?", ["%{$q}%"]);
        };

        if ($user->can('view-members')) {
            $referenceItems = [];
            $referenceIds = [];
            if ($referenceFor === 'members') {
                $exactMember = Member::with('region:id,name')->where('staff_id', $q)->first();
                if ($exactMember) {
                    $referenceIds[] = $exactMember->id;
                    $referenceItems[] = [
                        'name' => $exactMember->first_name.' '.$exactMember->last_name,
                        'sub' => ($exactMember->staff_id_display ?? $exactMember->staff_id).($exactMember->region ? ' · '.$exactMember->region->name : ''),
                        'url' => route('members.show', $exactMember),
                        'icon' => 'person',
                        'matched_reference' => true,
                    ];
                }
            }

            $members = Member::with('region:id,name')
                ->where(function ($x) use ($q, $nameCond) {
                    $nameCond($x);
                    $x->orWhere('nin', 'like', "%{$q}%");
                })
                ->limit(5)
                ->get();

            $memberItems = [];
            foreach ($members as $m) {
                if (in_array($m->id, $referenceIds, true)) {
                    continue;
                }
                $memberItems[] = [
                    'name' => $m->first_name.' '.$m->last_name,
                    'sub' => ($m->staff_id_display ?? $m->staff_id).($m->region ? ' · '.$m->region->name : ''),
                    'url' => route('members.show', $m),
                    'icon' => 'person',
                ];
            }

            if ($referenceItems !== []) {
                $referenceMatched = true;
            }
            $items = array_merge($referenceItems, $memberItems);
            if ($items !== []) {
                $groups[] = [
                    'key' => 'members',
                    'label' => 'Members',
                    'icon' => 'group',
                    'items' => $items,
                ];
            }
        }

        if ($user->can('view-loans')) {
            $referenceItems = [];
            $referenceIds = [];
            if ($referenceFor === 'loans') {
                $exactLoan = Loan::with('member:id,first_name,last_name')->where('loan_number', $q)->first();
                if ($exactLoan) {
                    $referenceIds[] = $exactLoan->id;
                    $referenceItems[] = [
                        'name' => $exactLoan->loan_number,
                        'sub' => ($exactLoan->member->first_name.' '.$exactLoan->member->last_name).' · ₦'.number_format($exactLoan->amount, 2),
                        'url' => route('loans.show', $exactLoan),
                        'icon' => 'request_quote',
                        'matched_reference' => true,
                    ];
                }
            }

            $loans = Loan::with('member:id,first_name,last_name')
                ->where(function ($x) use ($q, $nameCond) {
                    $x->where('loan_number', 'like', "%{$q}%")
                        ->orWhereHas('member', $nameCond);
                })
                ->limit(5)
                ->get();

            $loanItems = [];
            foreach ($loans as $l) {
                if (in_array($l->id, $referenceIds, true)) {
                    continue;
                }
                $loanItems[] = [
                    'name' => $l->loan_number,
                    'sub' => ($l->member->first_name.' '.$l->member->last_name).' · ₦'.number_format($l->amount, 2),
                    'url' => route('loans.show', $l),
                    'icon' => 'request_quote',
                ];
            }

            if ($referenceItems !== []) {
                $referenceMatched = true;
            }
            $items = array_merge($referenceItems, $loanItems);
            if ($items !== []) {
                $groups[] = [
                    'key' => 'loans',
                    'label' => 'Loans',
                    'icon' => 'account_balance',
                    'items' => $items,
                ];
            }
        }

        if ($user->can('view-savings')) {
            $savingsItem = function ($t) {
                $member = $t->savingsAccount?->member;

                return [
                    'name' => $t->reference,
                    'sub' => ($member ? $member->first_name.' '.$member->last_name.' · ' : '').'₦'.number_format($t->amount, 2).' · '.ucfirst($t->status),
                    'url' => $member ? route('members.savings-detail', $member) : route('savings.index'),
                    'icon' => 'savings',
                ];
            };

            $referenceItems = [];
            $referenceIds = [];
            if ($referenceFor === 'savings') {
                $exactTransaction = SavingsTransaction::with('savingsAccount.member:id,first_name,last_name')
                    ->where('reference', $q)
                    ->first();
                if ($exactTransaction) {
                    $referenceIds[] = $exactTransaction->id;
                    $referenceItems[] = $savingsItem($exactTransaction) + ['matched_reference' => true];
                }
            }

            $savings = SavingsTransaction::with('savingsAccount.member:id,first_name,last_name')
                ->where(function ($x) use ($q, $nameCond) {
                    $x->where('reference', 'like', "%{$q}%")
                        ->orWhereHas('savingsAccount.member', $nameCond);
                })
                ->latest('id')
                ->limit(5)
                ->get();

            $savingsItems = $savings->filter(fn ($t) => ! in_array($t->id, $referenceIds, true))
                ->map($savingsItem)
                ->values()
                ->all();

            if ($referenceItems !== []) {
                $referenceMatched = true;
            }
            $items = array_merge($referenceItems, $savingsItems);
            if ($items !== []) {
                $groups[] = [
                    'key' => 'savings',
                    'label' => 'Savings',
                    'icon' => 'savings',
                    'items' => $items,
                ];
            }
        }

        if ($user->can('view-shares')) {
            $shares = ShareTransaction::with('shareAccount.member:id,first_name,last_name')
                ->where(function ($x) use ($q, $nameCond) {
                    $x->where('reference', 'like', "%{$q}%")
                        ->orWhereHas('shareAccount.member', $nameCond);
                })
                ->latest('id')
                ->limit(5)
                ->get();

            if ($shares->isNotEmpty()) {
                $groups[] = [
                    'key' => 'shares',
                    'label' => 'Shares',
                    'icon' => 'trending_up',
                    'items' => $shares->map(function ($t) {
                        $member = $t->shareAccount?->member;

                        return [
                            'name' => $t->reference,
                            'sub' => ($member ? $member->first_name.' '.$member->last_name.' · ' : '').number_format($t->shares).' shares · ₦'.number_format($t->amount, 2),
                            'url' => $member ? route('members.show', $member) : route('shares.accounts'),
                            'icon' => 'trending_up',
                        ];
                    })->values()->all(),
                ];
            }
        }

        if ($user->can('view-purchase-orders')) {
            $orderItem = function ($o) {
                return [
                    'name' => $o->order_number,
                    'sub' => ($o->member ? $o->member->first_name.' '.$o->member->last_name.' · ' : '').($o->product?->name ?? '').' · '.ucfirst($o->status),
                    'url' => route('products.orders.show', $o->order_group),
                    'icon' => 'receipt_long',
                ];
            };

            $referenceItems = [];
            $referenceIds = [];
            if ($referenceFor === 'orders') {
                $exactOrder = PurchaseOrder::with(['member:id,first_name,last_name', 'product:id,name'])
                    ->where('order_number', $q)
                    ->orWhere('order_group', $q)
                    ->first();
                if ($exactOrder) {
                    $referenceIds[] = $exactOrder->id;
                    $referenceItems[] = $orderItem($exactOrder) + ['matched_reference' => true];
                }
            }

            $orders = PurchaseOrder::with(['member:id,first_name,last_name', 'product:id,name'])
                ->where(function ($x) use ($q, $nameCond) {
                    $x->where('order_number', 'like', "%{$q}%")
                        ->orWhere('order_group', 'like', "%{$q}%")
                        ->orWhereHas('member', $nameCond);
                })
                ->latest('id')
                ->limit(5)
                ->get();

            $ordersItems = $orders->filter(fn ($o) => ! in_array($o->id, $referenceIds, true))
                ->map($orderItem)
                ->values()
                ->all();

            if ($referenceItems !== []) {
                $referenceMatched = true;
            }
            $items = array_merge($referenceItems, $ordersItems);
            if ($items !== []) {
                $groups[] = [
                    'key' => 'orders',
                    'label' => 'Purchase Orders',
                    'icon' => 'shopping_cart',
                    'items' => $items,
                ];
            }
        }

        if ($user->can('view-payroll')) {
            $payrollItem = fn ($p) => [
                'name' => $p->payroll_number,
                'sub' => $p->month.' '.$p->year.' · ₦'.number_format($p->grand_total ?? 0, 2).' · '.ucfirst($p->status),
                'url' => route('payroll.show', $p),
                'icon' => 'receipt_long',
            ];

            $referenceItems = [];
            $referenceIds = [];
            if ($referenceFor === 'payroll') {
                $exactPayroll = MonthlyPayroll::where('payroll_number', $q)->first();
                if ($exactPayroll) {
                    $referenceIds[] = $exactPayroll->id;
                    $referenceItems[] = $payrollItem($exactPayroll) + ['matched_reference' => true];
                }
            }

            $payrolls = MonthlyPayroll::where('payroll_number', 'like', "%{$q}%")
                ->latest('id')
                ->limit(5)
                ->get();

            $payrollItems = $payrolls->filter(fn ($p) => ! in_array($p->id, $referenceIds, true))
                ->map($payrollItem)
                ->values()
                ->all();

            if ($referenceItems !== []) {
                $referenceMatched = true;
            }
            $items = array_merge($referenceItems, $payrollItems);
            if ($items !== []) {
                $groups[] = [
                    'key' => 'payroll',
                    'label' => 'Payroll',
                    'icon' => 'payments',
                    'items' => $items,
                ];
            }
        }

        if ($user->can('view-dividends') && $referenceFor === 'dividends') {
            $exactDividend = Dividend::where('dividend_number', $q)->first();
            if ($exactDividend) {
                $referenceMatched = true;
                $groups[] = [
                    'key' => 'dividends',
                    'label' => 'Dividends',
                    'icon' => 'diversity_3',
                    'items' => [[
                        'name' => $exactDividend->dividend_number,
                        'sub' => $exactDividend->year.' · ₦'.number_format($exactDividend->total_profit, 2).' · '.ucfirst($exactDividend->status),
                        'url' => route('dividends.show', $exactDividend),
                        'icon' => 'diversity_3',
                        'matched_reference' => true,
                    ]],
                ];
            }
        }

        if ($user->can('view-products')) {
            $products = Product::where('name', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'name', 'unit_price', 'stock_quantity']);

            if ($products->isNotEmpty()) {
                $groups[] = [
                    'key' => 'products',
                    'label' => 'Products',
                    'icon' => 'inventory_2',
                    'items' => $products->map(fn ($p) => [
                        'name' => $p->name,
                        'sub' => '₦'.number_format($p->unit_price, 2).' · '.$p->stock_quantity.' in stock',
                        'url' => route('products.index'),
                        'icon' => 'inventory_2',
                    ])->values()->all(),
                ];
            }
        }

        if ($referenceMatched && $referenceFor !== null) {
            foreach ($groups as $index => $group) {
                if ($group['key'] === $referenceFor) {
                    if ($index > 0) {
                        $group = array_splice($groups, $index, 1)[0];
                        array_unshift($groups, $group);
                    }
                    break;
                }
            }
        }

        return response()->json($groups);
    }
}
