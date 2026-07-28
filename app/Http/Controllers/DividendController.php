<?php

namespace App\Http\Controllers;

use App\Models\Dividend;
use App\Models\DividendDistribution;
use App\Models\ShareAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DividendController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $dividends = Dividend::latest('year')->paginate(15);

        $stats = [
            'total_declared' => Dividend::count(),
            'total_distributed' => Dividend::where('status', 'completed')->sum('total_distributed'),
            'total_profit' => Dividend::sum('total_profit'),
            'pending_distributions' => DividendDistribution::where('status', 'pending')->count(),
        ];

        return view('dividends.index', ['dividends' => $dividends, 'stats' => $stats]);
    }

    public function create(): \Illuminate\View\View
    {
        return view('dividends.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'total_profit' => 'required|numeric|min:0',
        ]);

        $existing = Dividend::where('year', $validated['year'])->first();
        if ($existing) {
            return back()->withErrors(['error' => "Dividend for {$validated['year']} already exists."]);
        }

        $dividendNumber = 'DIV/' . $validated['year'] . '/' . str_pad(
            Dividend::where('year', $validated['year'])->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        $dividend = Dividend::create([
            'dividend_number' => $dividendNumber,
            'year' => $validated['year'],
            'total_profit' => $validated['total_profit'],
            'status' => 'draft',
        ]);

        return redirect()->route('dividends.show', $dividend)
            ->with('success', 'Dividend record created. Now calculate distributions.');
    }

    public function show(Dividend $dividend): \Illuminate\View\View
    {
        $dividend->load(['distributions.member', 'approvedBy']);

        return view('dividends.show', ['dividend' => $dividend]);
    }

    public function calculate(Dividend $dividend): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('calculate-dividends');

        if ($dividend->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft dividends can be calculated.']);
        }

        DB::transaction(function () use ($dividend) {
            $shareAccounts = ShareAccount::where('total_shares', '>', 0)->with('member')->get();
            $totalShares = $shareAccounts->sum('total_shares');

            if ($totalShares <= 0) {
                return;
            }

            $dividend->update([
                'eligible_members' => $shareAccounts->count(),
            ]);

            $perShareDividend = $dividend->total_profit / $totalShares;

            foreach ($shareAccounts as $account) {
                $amount = round($account->total_shares * $perShareDividend, 2);

                DividendDistribution::create([
                    'dividend_id' => $dividend->id,
                    'member_id' => $account->member_id,
                    'share_count' => $account->total_shares,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
            }

            $totalDistributed = DividendDistribution::where('dividend_id', $dividend->id)->sum('amount');

            $dividend->update([
                'total_distributed' => $totalDistributed,
                'status' => 'calculated',
            ]);
        });

        return back()->with('success', 'Dividend distributions calculated successfully.');
    }

    public function approve(Dividend $dividend): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('approve-dividends');

        if ($dividend->status !== 'calculated') {
            return back()->withErrors(['error' => 'Only calculated dividends can be approved.']);
        }

        $dividend->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Dividend approved successfully.');
    }

    public function distribute(Dividend $dividend): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('distribute-dividends');

        if ($dividend->status !== 'approved') {
            return back()->withErrors(['error' => 'Only approved dividends can be distributed.']);
        }

        DividendDistribution::where('dividend_id', $dividend->id)
            ->whereIn('status', ['pending', 'approved'])
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

        $dividend->update(['status' => 'completed']);

        try {
            $distributions = DividendDistribution::where('dividend_id', $dividend->id)
                ->where('status', 'paid')
                ->with('member.user')
                ->get();
            foreach ($distributions as $dist) {
                if ($dist->member && $dist->member->user) {
                    $dist->member->user->notify(new \App\Notifications\WithdrawalStatusNotification($dist, 'completed'));
                }
            }
        } catch (\Exception $e) {
            \Log::error('Dividend distribution notification failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Dividends distributed successfully.');
    }
}
