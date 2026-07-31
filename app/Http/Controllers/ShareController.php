<?php

namespace App\Http\Controllers;

use App\Actions\Shares\PurchaseShares;
use App\Exports\SharesExport;
use App\Models\Member;
use App\Models\ShareAccount;
use App\Models\ShareTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ShareController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $query = ShareTransaction::with('shareAccount.member');

        if ($search = $request->input('search')) {
            $query->whereHas('shareAccount.member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            })->orWhere('reference', 'like', "%{$search}%");
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $perPage = $request->input('per_page');
        $transactions = $query->latest('transaction_date')->paginate($perPage === 'all' ? 1000 : 15)->withQueryString();

        $stats = [
            'total_shares' => \App\Models\ShareAccount::sum('total_shares'),
            'total_value' => \App\Models\ShareAccount::sum('total_value'),
            'total_transactions' => ShareTransaction::count(),
            'this_month' => ShareTransaction::where('transaction_date', '>=', now()->startOfMonth())->sum('amount'),
            'members_with_shares' => \App\Models\ShareAccount::where('total_shares', '>', 0)->count(),
        ];

        return view('shares.index', ['transactions' => $transactions, 'stats' => $stats]);
    }

    public function exportShares()
    {
        return Excel::download(new SharesExport, 'shares_export_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function accounts(Request $request): \Illuminate\View\View
    {
        $query = ShareAccount::with('member');

        if ($search = $request->input('search')) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'latest');
        if ($sort === 'highest') {
            $query->orderByDesc('total_value');
        } elseif ($sort === 'lowest') {
            $query->orderBy('total_value');
        } else {
            $query->latest();
        }

        $accounts = $query->paginate(20)->withQueryString();
        $totalShares = ShareAccount::sum('total_shares');
        $totalValue = ShareAccount::sum('total_value');

        return view('shares.accounts', compact('accounts', 'totalShares', 'totalValue'));
    }

    public function purchase(): \Illuminate\View\View
    {
        $members = Member::where('status', 'active')
            ->with('shareAccount')
            ->orderBy('first_name')
            ->get();

        return view('shares.purchase', ['members' => $members]);
    }

    public function storePurchase(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'shares' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $shareTxn = PurchaseShares::run(
                $validated['member_id'],
                $validated['shares'],
                $validated['notes'] ?? null,
            );
        } catch (\Throwable $e) {
            Log::error('Share purchase failed: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Could not record share purchase. Please try again.'])->withInput();
        }

        // Notify the member
        if ($shareTxn->shareAccount->member && $shareTxn->shareAccount->member->user) {
            try {
                $shareTxn->shareAccount->member->user->notify(new \App\Notifications\SharePurchasedNotification($shareTxn));
            } catch (\Exception $e) {
                Log::error('Share notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('shares.accounts')
            ->with('success', "Purchase of {$shareTxn->shares} share(s) for ₦" . number_format($shareTxn->amount, 2) . ' recorded.');
    }
}
