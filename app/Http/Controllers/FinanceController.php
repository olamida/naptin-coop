<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CashCount;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\PeriodClose;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\ShareAccount;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\LedgerSyncService;
use App\Services\ProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        $period = now()->format('Y-m');
        $closedPeriods = PeriodClose::where('is_closed', true)->pluck('period');

        return view('finance.index', compact('period', 'closedPeriods'));
    }

    // ---------------------------------------------------------------- Period Close

    public function periodCloseIndex()
    {
        $months = collect(range(0, 11))->map(function ($offset) {
            $date = now()->subMonths($offset)->startOfMonth();

            return [
                'period' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
                'close' => PeriodClose::where('period', $date->format('Y-m'))->first(),
            ];
        });

        return view('finance.period-close', compact('months'));
    }

    public function periodCloseStore(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|date_format:Y-m',
            'notes' => 'nullable|string|max:500',
        ]);

        $checks = $this->periodCloseChecks($validated['period']);
        if (! $checks['ok']) {
            return back()->withErrors(['error' => $checks['message']]);
        }

        PeriodClose::updateOrCreate(
            ['period' => $validated['period']],
            [
                'is_closed' => true,
                'closed_at' => now(),
                'closed_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
                'reopened_at' => null,
                'reopened_by' => null,
                'reopen_reason' => null,
            ]
        );

        ActivityLog::log('period.close', "Closed financial period {$validated['period']}.");

        return back()->with('success', "Financial period {$validated['period']} closed.");
    }

    public function periodCloseReopen(Request $request, string $period)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $close = PeriodClose::where('period', $period)->where('is_closed', true)->firstOrFail();

        $close->update([
            'is_closed' => false,
            'reopened_at' => now(),
            'reopened_by' => auth()->id(),
            'reopen_reason' => $validated['reason'],
        ]);

        ActivityLog::log('period.reopen', "Reopened financial period {$period}. Reason: {$validated['reason']}");

        return back()->with('success', "Financial period {$period} reopened.");
    }

    private function periodCloseChecks(string $period): array
    {
        [$year, $month] = array_map('intval', explode('-', $period));
        $start = "{$year}-{$month}-01";
        $end = date('Y-m-t', strtotime($start));

        $entries = JournalEntry::where('status', 'posted')
            ->whereBetween('entry_date', [$start, $end])
            ->count();

        if ($entries > 0) {
            $unbalanced = JournalEntry::where('status', 'posted')
                ->whereBetween('entry_date', [$start, $end])
                ->get()
                ->filter(fn ($entry) => ! $entry->isBalanced())
                ->count();

            if ($unbalanced > 0) {
                return ['ok' => false, 'message' => "Cannot close: {$unbalanced} unbalanced journal entries exist in this period."];
            }
        }

        $pendingWithdrawals = SavingsTransaction::where('status', 'pending')
            ->whereBetween('created_at', ["{$start} 00:00:00", "{$end} 23:59:59"])
            ->count();

        if ($pendingWithdrawals > 0) {
            return ['ok' => false, 'message' => "Cannot close: {$pendingWithdrawals} pending savings transactions exist in this period."];
        }

        return ['ok' => true, 'message' => ''];
    }

    // ---------------------------------------------------------------- Statements

    public function profitLoss(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $validated['from'] ?? now()->startOfYear()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();

        $query = fn ($codes) => DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$from, $to])
            ->whereIn('jel.account_id', function ($q) use ($codes) {
                $q->select('id')->from('chart_of_accounts')->whereIn('code', $codes);
            })
            ->selectRaw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
            ->first();

        $incomeAccounts = ChartOfAccount::where('type', 'income')->orderBy('code')->get();
        $expenseAccounts = ChartOfAccount::where('type', 'expense')->orderBy('code')->get();

        $income = [];
        foreach ($incomeAccounts as $account) {
            $totals = $query([$account->code]);
            $amount = round(($totals->total_credit ?? 0) - ($totals->total_debit ?? 0), 2);
            if (abs($amount) >= 0.01) {
                $income[] = ['account' => $account, 'amount' => $amount];
            }
        }

        $expenses = [];
        foreach ($expenseAccounts as $account) {
            $totals = $query([$account->code]);
            $amount = round(($totals->total_debit ?? 0) - ($totals->total_credit ?? 0), 2);
            if (abs($amount) >= 0.01) {
                $expenses[] = ['account' => $account, 'amount' => $amount];
            }
        }

        $totalIncome = array_sum(array_column($income, 'amount'));
        $totalExpenses = array_sum(array_column($expenses, 'amount'));
        $netProfit = round($totalIncome - $totalExpenses, 2);

        return view('finance.profit-loss', compact('income', 'expenses', 'totalIncome', 'totalExpenses', 'netProfit', 'from', 'to'));
    }

    public function balanceSheet(Request $request)
    {
        $validated = $request->validate([
            'as_of' => 'nullable|date',
        ]);

        $asOf = $validated['as_of'] ?? now()->toDateString();

        $accounts = ChartOfAccount::all()->keyBy('code');

        $balanceOf = function (string $code) use ($asOf, $accounts): float {
            $totals = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->where('je.status', 'posted')
                ->where('je.entry_date', '<=', $asOf)
                ->where('jel.account_id', $accounts->get($code)?->id ?? -1)
                ->selectRaw('SUM(jel.debit) as d, SUM(jel.credit) as c')
                ->first();

            return round((float) ($totals->d ?? 0) - (float) ($totals->c ?? 0), 2);
        };

        $accountBalance = function (ChartOfAccount $account) use ($asOf): float {
            $totals = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->where('je.status', 'posted')
                ->where('je.entry_date', '<=', $asOf)
                ->where('jel.account_id', $account->id)
                ->selectRaw('SUM(jel.debit) as d, SUM(jel.credit) as c')
                ->first();

            return $account->normal_side === 'debit'
                ? round((float) ($totals->d ?? 0) - (float) ($totals->c ?? 0), 2)
                : round((float) ($totals->c ?? 0) - (float) ($totals->d ?? 0), 2);
        };

        $assetAccounts = ChartOfAccount::where('type', 'asset')->where('is_active', true)->orderBy('code')->get();
        $liabilityAccounts = ChartOfAccount::where('type', 'liability')->where('is_active', true)->orderBy('code')->get();
        $equityAccounts = ChartOfAccount::where('type', 'equity')->where('is_active', true)->orderBy('code')->get();

        $provisionAccount = ChartOfAccount::where('code', LedgerService::LOAN_LOSS_PROVISION)->first();
        $provisionBalance = $provisionAccount ? $accountBalance($provisionAccount) : 0.0;

        $assets = $assetAccounts
            ->reject(fn ($a) => $a->code === LedgerService::LOAN_LOSS_PROVISION)
            ->map(fn ($a) => ['account' => $a, 'balance' => $accountBalance($a)])
            ->filter(fn ($r) => abs($r['balance']) >= 0.01)
            ->values();
        $liabilities = $liabilityAccounts->map(fn ($a) => ['account' => $a, 'balance' => $accountBalance($a)])->filter(fn ($r) => abs($r['balance']) >= 0.01)->values();
        $equity = $equityAccounts->map(fn ($a) => ['account' => $a, 'balance' => $accountBalance($a)])->filter(fn ($r) => abs($r['balance']) >= 0.01)->values();

        // Contra asset (loan loss provision) reduces total assets.
        $totalAssets = round($assets->sum('balance') - $provisionBalance, 2);

        $netProfit = $this->netProfitAsOf($asOf);
        $equityRows = $equity->push(['account' => (object) ['name' => 'Net Profit (Current)', 'code' => '—', 'normal_side' => 'credit'], 'balance' => $netProfit]);

        $totalLiabilities = round($liabilities->sum('balance'), 2);
        $totalEquity = round($equityRows->sum('balance'), 2);

        // Assets = Liabilities + Equity must hold.
        $assetsSide = round($totalAssets, 2);
        $liabilitiesSide = round($totalLiabilities + $totalEquity, 2);
        $variance = round($assetsSide - $liabilitiesSide, 2);

        return view('finance.balance-sheet', compact(
            'assets', 'liabilities', 'equityRows', 'totalAssets', 'totalLiabilities', 'totalEquity',
            'assetsSide', 'liabilitiesSide', 'variance', 'asOf', 'netProfit'
        ));
    }

    private function netProfitAsOf(string $asOf): float
    {
        $totals = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jel.account_id')
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<=', $asOf)
            ->whereIn('coa.type', ['income', 'expense'])
            ->selectRaw('SUM(jel.credit) as c, SUM(jel.debit) as d')
            ->first();

        return round((float) ($totals->c ?? 0) - (float) ($totals->d ?? 0), 2);
    }

    public function cashFlow(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $validated['from'] ?? now()->startOfYear()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();

        // Direct method: classify every posted line touching a cash/bank account.
        $cashCodes = ['1001', '1002', '1003'];
        $rows = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jel.account_id')
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$from, $to])
            ->whereIn('jel.account_id', function ($q) use ($cashCodes) {
                $q->select('id')->from('chart_of_accounts')->whereIn('code', $cashCodes);
            })
            ->orderBy('je.entry_date')
            ->select('je.entry_number', 'je.entry_date', 'je.description', 'je.reference_type', 'je.reference_id', 'jel.debit', 'jel.credit', 'coa.code as account_code')
            ->get();

        $inflows = 0.0;
        $outflows = 0.0;
        $entries = [];

        foreach ($rows as $row) {
            $cashDebit = (float) $row->debit;
            $cashCredit = (float) $row->credit;
            $inflows += $cashDebit;
            $outflows += $cashCredit;

            $entries[] = [
                'entry_number' => $row->entry_number,
                'entry_date' => $row->entry_date,
                'description' => $row->description,
                'reference_type' => $row->reference_type,
                'inflow' => $cashDebit,
                'outflow' => $cashCredit,
            ];
        }

        $netCash = round($inflows - $outflows, 2);

        return view('finance.cash-flow', compact('entries', 'inflows', 'outflows', 'netCash', 'from', 'to'));
    }

    // ---------------------------------------------------------------- Loan Aging + Provisioning

    public function loanAging()
    {
        $report = ProvisioningService::agingReport();

        return view('finance.loan-aging', compact('report'));
    }

    public function calculateProvision()
    {
        try {
            $result = ProvisioningService::calculate();
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        ActivityLog::log('provision.calculate', "Calculated loan loss provision for {$result['period']}: ₦{$result['required_provision']}.");

        return back()->with('success', "Provision calculated for {$result['period']} — required ₦".number_format($result['required_provision'], 2).' (delta posted: ₦'.number_format($result['delta'], 2).').');
    }

    // ---------------------------------------------------------------- Control Reconciliation

    public function controlReconciliation()
    {
        $ledger = new LedgerService;

        // 2001 Members Savings vs sum of savings accounts.
        $savingsLedger = $ledger->getBalance(LedgerService::MEMBERS_SAVINGS);
        $savingsSub = (float) SavingsAccount::sum('balance');
        $savings = $ledger->reconcileControlAccount(LedgerService::MEMBERS_SAVINGS, $savingsSub);

        // 1101 Loans Receivable vs sum of outstanding loans.
        $loansLedger = $ledger->getBalance(LedgerService::LOANS_RECEIVABLE);
        $loansSub = (float) Loan::whereIn('status', ['disbursed', 'repaying', 'defaulted'])->sum('outstanding');
        $loans = $ledger->reconcileControlAccount(LedgerService::LOANS_RECEIVABLE, $loansSub);

        // 2101 Share Capital vs sum of share accounts value.
        $sharesLedger = $ledger->getBalance(LedgerService::SHARE_CAPITAL);
        $sharesSub = (float) ShareAccount::sum('total_value');
        $shares = $ledger->reconcileControlAccount(LedgerService::SHARE_CAPITAL, $sharesSub);

        // 1201 Purchase Receivables vs outstanding hire-purchase balances.
        $purchasesLedger = $ledger->getBalance(LedgerService::PURCHASE_RECEIVABLES);
        $purchasesSub = app(LedgerSyncService::class)->subLedgerTargets()['purchases'];
        $purchases = $ledger->reconcileControlAccount(LedgerService::PURCHASE_RECEIVABLES, $purchasesSub);

        $reports = compact('savings', 'loans', 'shares', 'purchases');

        return view('finance.control-reconciliation', compact('reports', 'savingsLedger', 'loansLedger', 'sharesLedger', 'purchasesLedger'));
    }

    // ---------------------------------------------------------------- Cash Count

    public function cashCount()
    {
        $counts = CashCount::with(['countedBy', 'verifiedBy'])->latest('count_date')->paginate(25);
        $systemBalance = (new LedgerService)->getBalance(LedgerService::CASH);

        return view('finance.cash-count', compact('counts', 'systemBalance'));
    }

    public function cashCountStore(Request $request)
    {
        $validated = $request->validate([
            'count_date' => 'required|date|before_or_equal:today',
            'physical_count' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if (CashCount::whereDate('count_date', $validated['count_date'])->exists()) {
            return back()->withErrors(['count_date' => 'A cash count already exists for this date.']);
        }

        $ledger = new LedgerService;
        $systemBalance = $ledger->getBalance(LedgerService::CASH);
        $variance = round((float) $validated['physical_count'] - $systemBalance, 2);

        $status = $variance == 0
            ? CashCount::STATUS_BALANCED
            : ($variance > 0 ? CashCount::STATUS_EXCESS : CashCount::STATUS_SHORTAGE);

        try {
            $count = DB::transaction(function () use ($validated, $systemBalance, $variance, $status) {
                $count = CashCount::create([
                    'count_date' => $validated['count_date'],
                    'system_balance' => $systemBalance,
                    'physical_count' => $validated['physical_count'],
                    'variance' => $variance,
                    'status' => $status,
                    'counted_by' => auth()->id(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                if ($status !== CashCount::STATUS_BALANCED) {
                    app(LedgerService::class)->postCashVariance($count->id, $variance);
                }

                return $count;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        ActivityLog::log('cash-count.store', "Recorded daily cash count for {$count->count_date}: ₦".number_format($count->physical_count, 2).' (variance ₦'.number_format($variance, 2).').');

        return back()->with('success', "Cash count recorded for {$count->count_date} — variance ₦".number_format($variance, 2).'.');
    }

    public function cashCountVerify(CashCount $cashCount)
    {
        if ($cashCount->verified_by) {
            return back()->withErrors(['error' => 'This cash count is already verified.']);
        }

        $cashCount->update(['verified_by' => auth()->id()]);

        ActivityLog::log('cash-count.verify', "Verified daily cash count for {$cashCount->count_date}.");

        return back()->with('success', "Cash count for {$cashCount->count_date} verified.");
    }

    // ---------------------------------------------------------------- Savings Control (Report 6)

    public function savingsControl(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? now()->toDateString();

        $ledger = new LedgerService;
        $subLedgerTotal = (float) SavingsAccount::sum('balance');
        $ledgerBalance = $ledger->getBalance(LedgerService::MEMBERS_SAVINGS);
        $controlVariance = round($subLedgerTotal - $ledgerBalance, 2);

        $rows = SavingsAccount::with('member')->orderBy('account_number')->get()
            ->map(function ($account) use ($from, $to) {
                $query = $account->transactions()
                    ->where('status', 'completed')
                    ->whereDate('transaction_date', '<=', $to);

                if ($from) {
                    $query->whereDate('transaction_date', '>=', $from);
                }

                $transactions = $query->get();

                $closing = (float) $account->balance;
                $netMovement = $transactions->sum(fn ($t) => (float) $t->balance_after - (float) $t->balance_before);
                $opening = round($closing - $netMovement, 2);

                $sumByType = fn (string $type) => round((float) $transactions->where('type', $type)->sum('amount'), 2);

                // All-time check: member balance vs the sum of every completed transaction delta.
                $expected = (float) $account->transactions()
                    ->where('status', 'completed')
                    ->get()
                    ->sum(fn ($t) => (float) $t->balance_after - (float) $t->balance_before);
                $variance = round($closing - $expected, 2);

                return [
                    'member' => $account->member,
                    'account_number' => $account->account_number,
                    'opening' => $opening,
                    'deposits' => $sumByType('deposit'),
                    'withdrawals' => $sumByType('withdrawal'),
                    'interest' => $sumByType('interest'),
                    'transfers' => $sumByType('transfer'),
                    'reversals' => $sumByType('reversal'),
                    'closing' => $closing,
                    'variance' => $variance,
                ];
            });

        return view('finance.savings-control', compact('rows', 'subLedgerTotal', 'ledgerBalance', 'controlVariance', 'from', 'to'));
    }

    // ---------------------------------------------------------------- Ledger Sync

    public function syncOpeningBalances()
    {
        try {
            $result = app(LedgerSyncService::class)->syncOpeningBalances();
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if (! $result['posted']) {
            ActivityLog::log('ledger.sync', 'Ledger sync run — no postings required.');

            return back()->with('info', $result['message']);
        }

        ActivityLog::log('ledger.sync', "Posted opening-balance conversion entry {$result['entry_number']} (₦".number_format($result['total'], 2).').');

        return back()->with('success', "Opening balances posted as {$result['entry_number']} — ₦".number_format($result['total'], 2).' across '.$result['lines'].' lines. Control accounts are now reconciled.');
    }

    // ---------------------------------------------------------------- Audit Trail

    public function auditTrail(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'event' => 'nullable|string|max:100',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $query = ActivityLog::with('user')->latest();

        if (! empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }
        if (! empty($validated['event'])) {
            $query->where('event', 'like', '%'.$validated['event'].'%');
        }
        if (! empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }
        if (! empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        $logs = $query->paginate(25);

        $users = User::orderBy('name')->get();
        $events = ActivityLog::distinct()->pluck('event')->sort()->values();

        $ledger = new LedgerService;
        $hashViolations = $ledger->verifyHashChain();

        return view('finance.audit-trail', compact('logs', 'users', 'events', 'hashViolations'));
    }
}
