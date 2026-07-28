<?php

namespace App\Http\Controllers;

use App\Exports\SavingsExport;
use App\Imports\SavingsImport;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Notifications\WithdrawalStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SavingsController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $query = SavingsTransaction::with('savingsAccount.member');

        if ($search = $request->input('search')) {
            $query->whereHas('savingsAccount.member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            })->orWhere('reference', 'like', "%{$search}%");
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = $request->input('per_page');
        $transactions = $query->latest('transaction_date')->paginate($perPage === 'all' ? 1000 : 15)->withQueryString();

        $pendingCount = SavingsTransaction::where('type', 'withdrawal')
            ->where('status', 'pending')
            ->count();

        $stats = [
            'total_deposits' => SavingsTransaction::where('type', 'deposit')->where('status', 'completed')->sum('amount'),
            'total_withdrawals' => SavingsTransaction::where('type', 'withdrawal')->where('status', 'completed')->sum('amount'),
            'total_balance' => \App\Models\SavingsAccount::sum('balance'),
            'pending_count' => $pendingCount,
            'total_accounts' => \App\Models\SavingsAccount::count(),
            'this_month' => SavingsTransaction::where('transaction_date', '>=', now()->startOfMonth())->sum('amount'),
        ];

        return view('savings.index', compact('transactions', 'pendingCount', 'stats'));
    }

    public function accounts(Request $request): \Illuminate\View\View
    {
        $query = SavingsAccount::with('member');

        if ($search = $request->input('search')) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            })->orWhere('account_number', 'like', "%{$search}%");
        }

        $sort = $request->input('sort', 'latest');
        if ($sort === 'highest') {
            $query->orderByDesc('balance');
        } elseif ($sort === 'lowest') {
            $query->orderBy('balance');
        } else {
            $query->latest();
        }

        $accounts = $query->paginate(20)->withQueryString();

        $totalBalance = SavingsAccount::sum('balance');

        return view('savings.accounts', compact('accounts', 'totalBalance'));
    }

    public function deposit(): \Illuminate\View\View
    {
        $members = Member::where('status', 'active')
            ->with('savingsAccount')
            ->orderBy('first_name')
            ->get();

        return view('savings.deposit', ['members' => $members]);
    }

    public function storeDeposit(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'payment_evidence' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $amount = round($validated['amount'], 2);
        $evidencePath = null;

        if ($request->hasFile('payment_evidence')) {
            $evidencePath = $request->file('payment_evidence')->store('payment-evidence', 'public');
        }

        $savingsService = new \App\Services\SavingsService();
        $txn = $savingsService->recordDeposit($validated['member_id'], $amount, $validated['notes'] ?? null, 'manual');

        if ($evidencePath) {
            $txn->update(['payment_evidence_path' => $evidencePath]);
        }

        // Notify the member
        if ($txn->savingsAccount && $txn->savingsAccount->member && $txn->savingsAccount->member->user) {
            try {
                $txn->savingsAccount->member->user->notify(new \App\Notifications\DepositRecordedNotification($txn));
            } catch (\Exception $e) {
                \Log::error('Deposit notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('savings.accounts')
            ->with('success', 'Deposit of ₦' . number_format($amount, 2) . ' recorded successfully.');
    }

    public function withdraw(): \Illuminate\View\View
    {
        $members = Member::where('status', 'active')
            ->with('savingsAccount')
            ->orderBy('first_name')
            ->get();

        return view('savings.withdraw', ['members' => $members]);
    }

    public function storeWithdrawal(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'payment_evidence' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $amount = round($validated['amount'], 2);

        $savingsService = new \App\Services\SavingsService();
        $transaction = $savingsService->recordWithdrawalRequest($validated['member_id'], $amount, $validated['notes'] ?? null, 'manual');

        if ($request->hasFile('payment_evidence')) {
            $evidencePath = $request->file('payment_evidence')->store('payment-evidence', 'public');
            $transaction->update(['payment_evidence_path' => $evidencePath]);
        }

        // Notify treasurers/admins about pending withdrawal
        try {
            $approverUsers = \App\Models\User::where('id', '!=', auth()->id())->whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'treasurer']);
            })->get();
            foreach ($approverUsers as $user) {
                $user->notify(new \App\Notifications\WithdrawalRequestedNotification($transaction));
            }
        } catch (\Exception $e) {
            \Log::error('Withdrawal notification failed: ' . $e->getMessage());
        }

        return redirect()->route('savings.accounts')
            ->with('success', 'Withdrawal request of ₦' . number_format($amount, 2) . ' submitted for approval. Reference: ' . $transaction->reference);
    }

    public function pendingApprovals(): \Illuminate\View\View
    {
        $withdrawals = SavingsTransaction::where('type', 'withdrawal')
            ->where('status', 'pending')
            ->with('savingsAccount.member', 'approvedBy')
            ->latest('transaction_date')
            ->get();

        $pendingDeposits = SavingsTransaction::where('type', 'deposit')
            ->where('status', 'pending')
            ->with('savingsAccount.member', 'approvedBy')
            ->latest('transaction_date')
            ->get();

        return view('savings.pending-withdrawals', compact('withdrawals', 'pendingDeposits'));
    }

    public function approveDeposit(SavingsTransaction $transaction): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('deposit-savings');

        if ($transaction->type !== 'deposit' || $transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending deposits can be confirmed.']);
        }

        $savingsService = new \App\Services\SavingsService();
        try {
            $transaction = $savingsService->approveDeposit($transaction);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if ($transaction->savingsAccount && $transaction->savingsAccount->member && $transaction->savingsAccount->member->user) {
            try {
                $transaction->savingsAccount->member->user->notify(new \App\Notifications\DepositRecordedNotification($transaction));
            } catch (\Exception $e) {
                \Log::error('Deposit notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Deposit of ₦' . number_format($transaction->amount, 2) . ' confirmed successfully. Balance updated.');
    }

    public function rejectDeposit(Request $request, SavingsTransaction $transaction): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('deposit-savings');

        if ($transaction->type !== 'deposit' || $transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending deposits can be rejected.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $transaction->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Deposit request rejected.');
    }

    public function approveWithdrawal(SavingsTransaction $transaction): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('withdraw-savings');

        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending withdrawals can be approved.']);
        }

        $savingsService = new \App\Services\SavingsService();
        try {
            $transaction = $savingsService->approveWithdrawal($transaction);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if ($transaction->savingsAccount && $transaction->savingsAccount->member && $transaction->savingsAccount->member->user) {
            try {
                $transaction->savingsAccount->member->user->notify(new WithdrawalStatusNotification($transaction, 'pending'));
            } catch (\Exception $e) {
                \Log::error('Withdrawal notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Withdrawal of ₦' . number_format($transaction->amount, 2) . ' approved and processed successfully.');
    }

    public function rejectWithdrawal(Request $request, SavingsTransaction $transaction): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('withdraw-savings');

        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending withdrawals can be rejected.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $transaction->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($transaction->savingsAccount && $transaction->savingsAccount->member && $transaction->savingsAccount->member->user) {
            try {
                $transaction->savingsAccount->member->user->notify(new WithdrawalStatusNotification($transaction, 'pending'));
            } catch (\Exception $e) {
                \Log::error('Withdrawal rejection notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Withdrawal request rejected.');
    }

    public function exportSavings()
    {
        return Excel::download(new SavingsExport, 'savings_export_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function import(): \Illuminate\View\View
    {
        return view('savings.import');
    }

    public function importStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new SavingsImport, $request->file('import_file'));

            return redirect()->route('savings.index')
                ->with('success', 'Savings transactions imported successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['import_file' => 'Import failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="savings_import_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['staff_id', 'amount', 'type', 'transaction_date', 'notes']);
            fputcsv($file, ['STF001', '5000', 'deposit', '2026-01-15', 'January savings deduction']);
            fputcsv($file, ['STF002', '3000', 'deposit', '2026-01-15', 'January savings deduction']);
            fputcsv($file, ['STF001', '2000', 'withdrawal', '2026-02-01', 'February withdrawal']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
