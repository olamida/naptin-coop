<?php

namespace App\Http\Controllers;

use App\Actions\Savings\ApproveDeposit;
use App\Actions\Savings\ApproveWithdrawal;
use App\Actions\Savings\PostDeposit;
use App\Actions\Savings\RejectDeposit;
use App\Actions\Savings\RejectWithdrawal;
use App\Actions\Savings\RequestWithdrawal;
use App\Exports\SavingsExport;
use App\Imports\SavingsImport;
use App\Models\ImportLog;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use Illuminate\Http\Request;
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
            'total_balance' => SavingsAccount::sum('balance'),
            'pending_count' => $pendingCount,
            'total_accounts' => SavingsAccount::count(),
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

        return view('savings.deposit', compact('members'));
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

        $txn = PostDeposit::run(
            $validated['member_id'],
            $amount,
            $validated['notes'] ?? null,
            'manual',
            $evidencePath
        );

        if ($txn->savingsAccount && $txn->savingsAccount->member && $txn->savingsAccount->member->user) {
            try {
                $txn->savingsAccount->member->user->notify(new \App\Notifications\DepositRecordedNotification($txn));
            } catch (\Exception $e) {}
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

        return view('savings.withdraw', compact('members'));
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
        $evidencePath = null;

        if ($request->hasFile('payment_evidence')) {
            $evidencePath = $request->file('payment_evidence')->store('payment-evidence', 'public');
        }

        $transaction = RequestWithdrawal::run(
            $validated['member_id'],
            $amount,
            $validated['notes'] ?? null,
            'manual',
            $evidencePath
        );

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
        try {
            $transaction = ApproveDeposit::run($transaction);
            return back()->with('success', 'Deposit of ₦' . number_format($transaction->amount, 2) . ' confirmed successfully. Balance updated.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function rejectDeposit(Request $request, SavingsTransaction $transaction): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            RejectDeposit::run($transaction, $validated['rejection_reason']);

            return back()->with('success', 'Deposit request rejected.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approveWithdrawal(SavingsTransaction $transaction): \Illuminate\Http\RedirectResponse
    {
        try {
            $transaction = ApproveWithdrawal::run($transaction);
            return back()->with('success', 'Withdrawal of ₦' . number_format($transaction->amount, 2) . ' approved and processed successfully.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function rejectWithdrawal(Request $request, SavingsTransaction $transaction): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            RejectWithdrawal::run($transaction, $validated['rejection_reason']);

            return back()->with('success', 'Withdrawal request rejected.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
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

        $batchId = (string) Str::uuid();
        $import = new SavingsImport($batchId);
        $fileName = $request->file('import_file')->getClientOriginalName();

        try {
            Excel::import($import, $request->file('import_file'));

            ImportLog::record($batchId, 'savings', $fileName, $import->importStats());

            return redirect()->route('savings.index')
                ->with('success', 'Savings transactions imported successfully. Batch: ' . substr($batchId, 0, 8) . '…');
        } catch (\Exception $e) {
            ImportLog::record($batchId, 'savings', $fileName, $import->importStats(), 'failed', $e->getMessage());

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
            fputcsv($file, ['staff_id', 'amount', 'type', 'transaction_date', 'notes', 'external_reference']);
            fputcsv($file, ['STF001', '5000', 'deposit', '2026-01-15', 'January savings deduction', '']);
            fputcsv($file, ['STF002', '3000', 'deposit', '2026-01-15', 'January savings deduction', '']);
            fputcsv($file, ['STF001', '2000', 'withdrawal', '2026-02-01', 'February withdrawal', '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
