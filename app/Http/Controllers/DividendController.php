<?php

namespace App\Http\Controllers;

use App\Actions\Dividends\ApproveDividend;
use App\Actions\Dividends\CalculateDividend;
use App\Actions\Dividends\DeclareDividend;
use App\Actions\Dividends\DistributeDividend;
use App\Models\Dividend;
use App\Models\DividendDistribution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DividendController extends Controller
{
    public function index(): View
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

    public function create(): View
    {
        return view('dividends.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:'.(date('Y') + 1),
            'total_profit' => 'required|numeric|min:0',
        ]);

        try {
            $dividend = DeclareDividend::run($validated);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('dividends.show', $dividend)
            ->with('success', 'Dividend record created. Now calculate distributions.');
    }

    public function show(Dividend $dividend): View
    {
        $dividend->load(['distributions.member', 'approvedBy']);

        return view('dividends.show', ['dividend' => $dividend]);
    }

    public function calculate(Dividend $dividend): RedirectResponse
    {
        $this->authorize('calculate-dividends');

        try {
            CalculateDividend::run($dividend);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Dividend distributions calculated successfully.');
    }

    public function approve(Dividend $dividend): RedirectResponse
    {
        $this->authorize('approve-dividends');

        try {
            ApproveDividend::run($dividend);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Dividend approved successfully.');
    }

    public function distribute(Dividend $dividend): RedirectResponse
    {
        $this->authorize('distribute-dividends');

        try {
            DistributeDividend::run($dividend);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Dividends distributed successfully.');
    }
}
