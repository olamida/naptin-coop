<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    public function accounts()
    {
        $accounts = ChartOfAccount::with('children')->whereNull('parent_id')->orderBy('code')->get();
        $types = ['asset', 'liability', 'equity', 'income', 'expense'];

        return view('ledger.accounts', compact('accounts', 'types'));
    }

    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'normal_side' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string|max:1000',
        ]);

        ChartOfAccount::create($validated);

        return redirect()->route('ledger.accounts')
            ->with('success', 'Account created successfully.');
    }

    public function updateAccount(Request $request, ChartOfAccount $account)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:chart_of_accounts,code,'.$account->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'normal_side' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $account->update($validated);

        return redirect()->route('ledger.accounts')
            ->with('success', 'Account updated successfully.');
    }

    public function journals()
    {
        $entries = JournalEntry::with('postedBy')->orderBy('entry_date', 'desc')->orderBy('id', 'desc')->paginate(20);

        return view('ledger.journals', compact('entries'));
    }

    public function createJournal()
    {
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        $nextNumber = JournalEntry::generateEntryNumber();

        return view('ledger.journal-create', compact('accounts', 'nextNumber'));
    }

    public function storeJournal(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:1000',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'required_without:lines.*.credit|numeric|min:0',
            'lines.*.credit' => 'required_without:lines.*.debit|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:500',
        ]);

        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['lines' => 'Total debits (₦'.number_format($totalDebit, 2).') must equal total credits (₦'.number_format($totalCredit, 2).').'])
                ->withInput();
        }

        DB::transaction(function () use ($validated) {
            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'],
                'status' => 'draft',
            ]);

            foreach ($validated['lines'] as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? null,
                ]);
            }
        });

        return redirect()->route('ledger.journals')
            ->with('success', 'Journal entry created successfully.');
    }

    public function showJournal(JournalEntry $journalEntry)
    {
        $journalEntry->load(['lines.account', 'postedBy']);

        return view('ledger.journal-show', ['entry' => $journalEntry]);
    }

    public function postJournal(JournalEntry $journalEntry)
    {
        try {
            $journalEntry->post();
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('ledger.journals.show', $journalEntry)
            ->with('success', 'Journal entry posted successfully.');
    }

    public function reverseJournal(Request $request, JournalEntry $journalEntry)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $reversal = (new LedgerService)->reverse($journalEntry, $validated['reason']);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('ledger.journals.show', $reversal)
            ->with('success', 'Journal entry reversed. The reversal was posted and linked to the original entry.');
    }

    public function trialBalance()
    {
        $accounts = ChartOfAccount::withSum('journalLines', 'debit')
            ->withSum('journalLines', 'credit')
            ->active()
            ->orderBy('code')
            ->get();

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $balance = $account->normal_side === 'debit'
                ? ($account->journal_lines_sum_debit ?? 0) - ($account->journal_lines_sum_credit ?? 0)
                : ($account->journal_lines_sum_credit ?? 0) - ($account->journal_lines_sum_debit ?? 0);

            $account->balance_value = abs($balance);
            $account->balance_side = $balance >= 0 ? $account->normal_side : ($account->normal_side === 'debit' ? 'credit' : 'debit');

            if ($account->balance_side === 'debit') {
                $totalDebit += $account->balance_value;
            } else {
                $totalCredit += $account->balance_value;
            }
        }

        return view('ledger.trial-balance', compact('accounts', 'totalDebit', 'totalCredit'));
    }

    public function generalLedger(Request $request)
    {
        $accountId = $request->get('account_id');
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        $account = null;
        $lines = collect();

        if ($accountId) {
            $account = ChartOfAccount::findOrFail($accountId);
            $lines = JournalEntryLine::with('journalEntry')
                ->where('account_id', $accountId)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
                ->orderBy('id')
                ->get();
        }

        return view('ledger.general-ledger', compact('accounts', 'account', 'lines'));
    }
}
