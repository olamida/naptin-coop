<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SavingsTransaction;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\AdminPasswordResetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function users(): \Illuminate\View\View
    {
        $users = User::with('roles')->latest()->paginate(15);

        return view('admin.users.index', ['users' => $users]);
    }

    public function createUser(): \Illuminate\View\View
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', ['roles' => $roles]);
    }

    public function storeUser(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function editUser(User $user): \Illuminate\View\View
    {
        $roles = Role::orderBy('name')->get();
        $currentRole = $user->roles->first();

        return view('admin.users.edit', compact('user', 'roles', 'currentRole'));
    }

    public function updateUser(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroyUser(User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('manage-users');

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function resetUserPassword(User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('manage-users');

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot reset your own password from here. Use profile settings instead.']);
        }

        $tempPassword = Str::random(14);
        $user->update(['password' => Hash::make($tempPassword)]);

        try {
            $user->notify(new AdminPasswordResetNotification($tempPassword));
        } catch (\Exception $e) {
            \Log::error('Password reset notification failed for user ' . $user->id . ': ' . $e->getMessage());
        }

        return back()->with('success', "Password reset for {$user->name}. New temporary password has been sent to their email: {$user->email}");
    }

    public function stock(): \Illuminate\View\View
    {
        $products = \App\Models\Product::orderBy('name')->get();

        return view('admin.stock', ['products' => $products]);
    }

    public function updateStock(Request $request, \App\Models\Product $product): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('manage-users');

        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $newQuantity = $product->stock_quantity + $validated['adjustment'];
        if ($newQuantity < 0) {
            return back()->withErrors(['error' => 'Stock cannot go below zero. Current stock: ' . $product->stock_quantity]);
        }

        $product->update(['stock_quantity' => $newQuantity]);

        return back()->with('success', "Stock for {$product->name} updated to {$newQuantity}.");
    }

    public function backup(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('manage-users');

        $filename = 'naptin_coop_backup_' . date('Y-m-d_His') . '.sql';

        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database)
        );

        $process = popen($command, 'r');

        return response()->stream(function () use ($process) {
            while ($line = fgets($process)) {
                echo $line;
            }
            pclose($process);
        }, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function statistics(): \Illuminate\View\View
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

    public function notifications(): \Illuminate\View\View
    {
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return view('admin.notifications', compact('notifications', 'unreadCount'));
    }

    public function markNotificationRead($notificationId): \Illuminate\Http\RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllNotificationsRead(): \Illuminate\Http\RedirectResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
