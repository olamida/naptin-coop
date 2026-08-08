<?php

namespace App\Http\Controllers;

use App\Actions\Loans\AddLoanNote;
use App\Actions\Loans\ApproveLoan;
use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\DisburseLoan;
use App\Actions\Loans\RecordRepayment;
use App\Actions\Loans\RejectLoan;
use App\Actions\Loans\UpdateGuarantor;
use App\Enums\GuarantorStatus;
use App\Exports\LoansExport;
use App\Imports\LoanRepaymentImport;
use App\Models\ImportLog;
use App\Models\Loan;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $query = Loan::with('member');

        if ($search = $request->input('search')) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            })->orWhere('loan_number', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = $request->input('per_page');
        $loans = $query->latest()->paginate($perPage === 'all' ? 1000 : 15)->withQueryString();

        $stats = [
            'total' => Loan::count(),
            'outstanding' => Loan::whereIn('status', ['disbursed', 'repaying', 'arrears'])->sum('outstanding'),
            'repaying' => Loan::where('status', 'repaying')->count(),
            'pending' => Loan::whereIn('status', ['pending', 'guarantor_pending'])->count(),
            'defaulted' => Loan::where('status', 'defaulted')->count(),
            'defaulted_amount' => Loan::where('status', 'defaulted')->sum('outstanding'),
        ];

        return view('loans.index', compact('loans', 'stats'));
    }

    public function create(): View
    {
        $members = Member::with('savingsAccount')->withCount(['loans as active_loans_count' => fn ($q) => $q->whereIn('status', ['disbursed', 'repaying'])])
            ->where('status', 'active')->orderBy('first_name')->get();
        $loanProducts = LoanProduct::where('enabled', true)->orderBy('name')->get();

        $guarantorLimit = 500000;
        $guarantorExposure = LoanGuarantor::query()
            ->where('status', GuarantorStatus::Accepted->value)
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['approved', 'disbursed', 'repaying']))
            ->with('loan:id,outstanding')
            ->get()
            ->groupBy('member_id')
            ->map(fn ($group) => [
                'guaranteeing' => round((float) $group->sum(fn ($g) => (float) $g->loan->outstanding), 2),
                'limit' => $guarantorLimit,
            ]);

        return view('loans.create', compact('members', 'loanProducts', 'guarantorExposure', 'guarantorLimit'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'loan_product_id' => 'nullable|exists:loan_products,id',
            'type' => 'required|in:regular,emergency,educational,special',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'tenure_months' => 'required|integer|min:1|max:120',
            'purpose' => 'nullable|string|max:1000',
            'guarantor_ids' => 'nullable|array',
            'guarantor_ids.*' => 'exists:members,id',
        ]);

        try {
            $loan = CreateLoan::run($validated);
            $loanNumber = $loan->loan_number;

            $createAction = app(CreateLoan::class);
            $createAction->notifyReviewers($loan);

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Loan application submitted successfully. Number: '.$loanNumber);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(Loan $loan): View
    {
        $loan->load([
            'member.region',
            'repayments',
            'schedules',
            'approvedBy',
            'guarantors.member',
            'approvalLogs.user',
            'parentLoan',
            'topupLoans',
        ]);

        $approvals = new ApprovalService;

        return view('loans.show', [
            'loan' => $loan,
            'disbursementPending' => $approvals->outstanding($loan, 'loan_disbursement'),
            'disbursementApproved' => $approvals->isFullyApproved($loan, 'loan_disbursement'),
        ]);
    }

    public function approve(Loan $loan): RedirectResponse
    {
        $this->authorize('approve', $loan);

        try {
            ApproveLoan::run($loan);

            return back()->with('success', 'Loan approved successfully.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, Loan $loan): RedirectResponse
    {
        $this->authorize('reject', $loan);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            RejectLoan::run($loan, $validated['rejection_reason']);

            return back()->with('success', 'Loan rejected.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function addNote(Request $request, Loan $loan): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        AddLoanNote::run($loan, $validated['admin_notes']);

        return back()->with('success', 'Note added.');
    }

    public function updateGuarantor(Request $request, Loan $loan, LoanGuarantor $guarantor): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,declined',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            UpdateGuarantor::run($loan, $guarantor, $validated['status'], $validated['notes'] ?? null, $request->ip(), $request->userAgent());

            $statusText = $validated['status'] === 'accepted' ? 'accepted' : 'declined';

            return back()->with('success', "Guarantor request {$statusText} successfully.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function disburse(Loan $loan): RedirectResponse
    {
        $this->authorize('disburse', $loan);

        $approvals = new ApprovalService;

        try {
            if ($approvals->requiresApproval('loan_disbursement')) {
                if ($approvals->outstanding($loan, 'loan_disbursement') === 0 && ! $approvals->isFullyApproved($loan, 'loan_disbursement')) {
                    $approvals->request('loan_disbursement', $loan, auth()->id());

                    return back()->with('success', 'Disbursement requires maker-checker approval. The request has been logged; a second senior user must approve it before funds are released.');
                }

                if (! $approvals->isFullyApproved($loan, 'loan_disbursement')) {
                    return back()->withErrors(['error' => 'Disbursement awaits maker-checker approval before funds can be released.']);
                }
            }

            DisburseLoan::run($loan);

            return back()->with('success', 'Loan disbursed successfully.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function disburseApprove(Loan $loan): RedirectResponse
    {
        $this->authorize('disburse', $loan);

        $approvals = new ApprovalService;

        try {
            if ($approvals->outstanding($loan, 'loan_disbursement') === 0) {
                return back()->withErrors(['error' => 'No pending disbursement approval for this loan.']);
            }

            $slot = $approvals->nextApprovableSlot($loan, 'loan_disbursement', auth()->user());
            if (! $slot) {
                return back()->withErrors(['error' => 'You are not eligible to approve this disbursement (requester and approvers must be distinct).']);
            }

            $approvals->approve($slot, auth()->id());

            if ($approvals->isFullyApproved($loan, 'loan_disbursement')) {
                DisburseLoan::run($loan);

                return back()->with('success', 'Disbursement approved by checker and loan disbursed.');
            }

            return back()->with('success', 'Disbursement approval recorded. A further senior approval is required before funds are released.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function repayment(Loan $loan): View
    {
        $loan->load('member');

        return view('loans.repayment', ['loan' => $loan]);
    }

    public function storeRepayment(Request $request, Loan $loan): RedirectResponse
    {
        $this->authorize('repay', $loan);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,salary_deduction,savings_deduction',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $result = RecordRepayment::run($loan, $validated);
            $message = $result['is_completed']
                ? 'Final repayment recorded. Loan is now completed!'
                : 'Repayment of ₦'.number_format($validated['amount'], 2).' recorded. Outstanding: ₦'.number_format($result['outstanding_after'], 2);

            return back()->with('success', $message);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }
    }

    public function topup(Loan $loan): View
    {
        if (! $loan->canTopup()) {
            return back()->withErrors(['error' => 'This loan is not eligible for top-up.']);
        }

        $loan->load('member', 'loanProduct');
        $members = Member::where('status', 'active')->orderBy('first_name')->get();
        $loanProducts = LoanProduct::where('enabled', true)->orderBy('name')->get();

        return view('loans.topup', compact('loan', 'members', 'loanProducts'));
    }

    public function storeTopup(Request $request, Loan $parentLoan): RedirectResponse
    {
        if (! $parentLoan->canTopup()) {
            return back()->withErrors(['error' => 'This loan is not eligible for top-up.']);
        }

        $validated = $request->validate([
            'loan_product_id' => 'nullable|exists:loan_products,id',
            'type' => 'required|in:regular,emergency,educational,special',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'tenure_months' => 'required|integer|min:1|max:120',
            'purpose' => 'nullable|string|max:1000',
        ]);

        $validated['member_id'] = $parentLoan->member_id;
        $validated['parent_loan_id'] = $parentLoan->id;

        try {
            $topupLoan = CreateLoan::run($validated);

            return redirect()->route('loans.show', $topupLoan)
                ->with('success', 'Loan top-up application submitted successfully. Number: '.$topupLoan->loan_number);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function exportLoans()
    {
        return Excel::download(new LoansExport, 'loans_export_'.now()->format('Y-m-d').'.xlsx');
    }

    public function import(): View
    {
        return view('loans.import');
    }

    public function importStore(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $batchId = (string) Str::uuid();
        $import = new LoanRepaymentImport($batchId);
        $fileName = $request->file('import_file')->getClientOriginalName();

        try {
            Excel::import($import, $request->file('import_file'));

            ImportLog::record($batchId, 'loan_repayments', $fileName, $import->importStats());

            return redirect()->route('loans.index')
                ->with('success', 'Loan repayments imported successfully. Batch: '.substr($batchId, 0, 8).'…');
        } catch (\Exception $e) {
            ImportLog::record($batchId, 'loan_repayments', $fileName, $import->importStats(), 'failed', $e->getMessage());

            return back()->withErrors(['import_file' => 'Import failed: '.$e->getMessage()])->withInput();
        }
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="loan_repayment_import_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['staff_id', 'amount', 'payment_date', 'notes', 'external_reference']);
            fputcsv($file, ['STF001', '15000', '2026-01-15', 'January salary deduction - loan repayment', '']);
            fputcsv($file, ['STF002', '10000', '2026-01-15', 'January salary deduction - loan repayment', '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
