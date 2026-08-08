<?php

namespace App\Http\Controllers;

use App\Actions\Dividends\ApproveDividend;
use App\Actions\Dividends\CalculateDividend;
use App\Actions\Dividends\DeclareDividend;
use App\Actions\Dividends\DistributeDividend;
use App\Models\Dividend;
use App\Models\DividendDistribution;
use App\Services\ApprovalService;
use App\Services\LedgerService;
use App\Services\ProvisioningService;
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
            $this->assertDividendEligible();

            $dividend = DeclareDividend::run($validated);

            if ((new ApprovalService)->requiresApproval('dividend_declaration')) {
                (new ApprovalService)->request('dividend_declaration', $dividend, auth()->id());
            }
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('dividends.show', $dividend)
            ->with('success', 'Dividend record created. The declaration requires maker-checker approval before distributions can be calculated.');
    }

    public function approveDeclaration(Dividend $dividend): RedirectResponse
    {
        $this->authorize('approve-dividends');

        $approvals = new ApprovalService;

        try {
            if ($approvals->outstanding($dividend, 'dividend_declaration') === 0) {
                return back()->withErrors(['error' => 'No pending dividend-declaration approval.']);
            }

            $slot = $approvals->nextApprovableSlot($dividend, 'dividend_declaration', auth()->user());
            if (! $slot) {
                return back()->withErrors(['error' => 'You are not eligible to approve this declaration (requester and approvers must be distinct).']);
            }

            $approvals->approve($slot, auth()->id());

            if ($approvals->isFullyApproved($dividend, 'dividend_declaration')) {
                return back()->with('success', 'Dividend declaration approved by all required checkers.');
            }

            return back()->with('success', 'Declaration approval recorded. A further senior approval is required.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * CBN compliance gates for declaring a dividend:
     *  - the trial balance must be balanced
     *  - loan loss provision coverage must be >= 100% (provisioning run completed)
     */
    private function assertDividendEligible(): void
    {
        $ledger = new LedgerService;

        if (! $ledger->trialBalanceIsBalanced()) {
            throw new \RuntimeException('Cannot declare a dividend: the trial balance is not balanced.');
        }

        $report = ProvisioningService::agingReport();

        if ($report['total_outstanding'] > 0 && $report['total_provision'] > 0) {
            $provisionHeld = $ledger->getBalance(LedgerService::LOAN_LOSS_PROVISION);
            $coverage = round(($provisionHeld / $report['total_provision']) * 100, 2);

            if ($coverage < 100.0) {
                throw new \RuntimeException(
                    'Cannot declare a dividend: loan loss provision coverage is '.number_format($coverage, 2)
                    .'% (minimum 100%). Run Finance → Loan Aging → Calculate Provision first.'
                );
            }
        }
    }

    public function show(Dividend $dividend): View
    {
        $dividend->load(['distributions.member', 'approvedBy']);

        $approvals = new ApprovalService;
        $declarationApproved = $approvals->isFullyApproved($dividend, 'dividend_declaration');
        $declarationPending = $approvals->outstanding($dividend, 'dividend_declaration');

        return view('dividends.show', compact('dividend', 'declarationApproved', 'declarationPending'));
    }

    public function calculate(Dividend $dividend): RedirectResponse
    {
        $this->authorize('calculate-dividends');

        $approvals = new ApprovalService;

        try {
            if ($approvals->requiresApproval('dividend_declaration') && ! $approvals->isFullyApproved($dividend, 'dividend_declaration')) {
                return back()->withErrors(['error' => 'Cannot calculate distributions: the dividend declaration still awaits maker-checker approval.']);
            }

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
