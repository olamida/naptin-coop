<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $totalUsers = User::count();
        $totalMembers = Member::count();
        $activeMembers = Member::where('status', 'active')->count();
        $totalProducts = Product::count();
        $totalLoans = Loan::count();
        $activeLoans = Loan::whereIn('status', ['approved', 'active'])->count();

        $totalSavings = DB::table('savings_transactions')->sum('amount');
        $totalShares = DB::table('share_transactions')->where('type', 'purchase')->sum('amount');
        $totalLoanValue = Loan::whereIn('status', ['approved', 'active'])->sum('amount');

        $loginLogs = ActivityLog::where('event', 'login')->latest()->paginate(15, ['*'], 'logins_page');
        $failedLogins = ActivityLog::where('event', 'login_failed')->latest()->paginate(15, ['*'], 'failed_page');
        $recentActivity = ActivityLog::latest()->paginate(20, ['*'], 'activity_page');

        $loginsByDay = ActivityLog::where('event', 'login')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $loginsByUser = ActivityLog::where('event', 'login')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->selectRaw('users.name, COUNT(*) as count')
            ->groupBy('users.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $recentErrors = ActivityLog::where('event', 'login_failed')
            ->latest()
            ->limit(20)
            ->get();

        $membersByRegion = Member::select('region_id', DB::raw('COUNT(*) as count'))
            ->groupBy('region_id')
            ->with('region')
            ->get();

        $membersByStatus = Member::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $purchaseSummary = PurchaseOrder::selectRaw('status, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return view('admin.statistics', compact(
            'totalUsers', 'totalMembers', 'activeMembers', 'totalProducts',
            'totalLoans', 'activeLoans', 'totalSavings', 'totalShares', 'totalLoanValue',
            'loginLogs', 'failedLogins', 'recentActivity', 'loginsByDay', 'loginsByUser',
            'recentErrors', 'membersByRegion', 'membersByStatus', 'purchaseSummary'
        ));
    }
}
