<?php

namespace App\Http\Controllers;

use App\Exports\LoansExport;
use App\Enums\GuarantorStatus;
use App\Imports\LoanRepaymentImport;
use App\Models\Loan;
use App\Models\LoanApprovalLog;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\User;
use App\Notifications\GuarantorRequestNotification;
use App\Notifications\LoanStatusNotification;
use App\Services\LoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class LoanController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
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
            'outstanding' => Loan::whereIn('status', ['disbursed', 'repaying'])->sum('outstanding'),
            'repaying' => Loan::where('status', 'repaying')->count(),
            'pending' => Loan::where('status', 'pending')->count(),
            'defaulted' => Loan::where('status', 'defaulted')->count(),
            'defaulted_amount' => Loan::where('status', 'defaulted')->sum('outstanding'),
        ];

        return view('loans.index', compact('loans', 'stats'));
    }

    public function create(): \Illuminate\View\View
    {
        $members = Member::where('status', 'active')->orderBy('first_name')->get();
        $loanProducts = LoanProduct::where('enabled', true)->orderBy('name')->get();

        return view('loans.create', ['members' => $members, 'loanProducts' => $loanProducts]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
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

        if (!empty($validated['loan_product_id'])) {
            $product = LoanProduct::find($validated['loan_product_id']);

            if ($product) {
                if ($product->max_loans_per_member) {
                    $activeCount = Loan::where('member_id', $validated['member_id'])
                        ->where('loan_product_id', $product->id)
                        ->whereIn('status', ['pending', 'approved', 'disbursed', 'repaying'])
                        ->count();

                    if ($activeCount >= $product->max_loans_per_member) {
                        return back()->withErrors([
                            'loan_product_id' => "This member already has {$activeCount} active loan(s) for {$product->name}. Maximum allowed: {$product->max_loans_per_member}.",
                        ])->withInput();
                    }
                }

                if ($product->max_total_amount_per_member) {
                    $totalOutstanding = Loan::where('member_id', $validated['member_id'])
                        ->where('loan_product_id', $product->id)
                        ->whereIn('status', ['pending', 'approved', 'disbursed', 'repaying'])
                        ->sum('outstanding');

                    $newTotal = $totalOutstanding + $validated['amount'];
                    if ($newTotal > $product->max_total_amount_per_member) {
                        return back()->withErrors([
                            'amount' => "This member's total outstanding for {$product->name} would be ₦" . number_format($newTotal, 2) . ". Maximum allowed: ₦" . number_format($product->max_total_amount_per_member, 2) . ". Remaining capacity: ₦" . number_format(max(0, $product->max_total_amount_per_member - $totalOutstanding), 2) . ".",
                        ])->withInput();
                    }
                }

                if ($validated['amount'] > $product->max_amount) {
                    return back()->withErrors([
                        'amount' => "Amount exceeds the maximum of ₦" . number_format($product->max_amount, 2) . " for {$product->name}.",
                    ])->withInput();
                }
                if ($validated['amount'] < $product->min_amount) {
                    return back()->withErrors([
                        'amount' => "Amount is below the minimum of ₦" . number_format($product->min_amount, 2) . " for {$product->name}.",
                    ])->withInput();
                }
                if ($validated['tenure_months'] > $product->max_term_months) {
                    return back()->withErrors([
                        'tenure_months' => "Tenure exceeds the maximum of {$product->max_term_months} months for {$product->name}.",
                    ])->withInput();
                }
            }
        }

        $loanService = app(LoanService::class);
        $monthlyRepayment = $loanService->calculateMonthlyRepayment(
            $validated['amount'],
            $validated['interest_rate'] ?? 0,
            $validated['tenure_months']
        );
        $loanNumber = $loanService->generateLoanNumber();

        DB::transaction(function () use ($validated, $loanNumber, $monthlyRepayment, &$loan) {
            $loan = Loan::create([
                'member_id' => $validated['member_id'],
                'loan_product_id' => $validated['loan_product_id'] ?? null,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'interest_rate' => $validated['interest_rate'],
                'tenure_months' => $validated['tenure_months'],
                'monthly_repayment' => $monthlyRepayment,
                'outstanding' => $validated['amount'],
                'application_date' => now()->toDateString(),
                'purpose' => $validated['purpose'] ?? null,
                'status' => 'pending',
            ]);

            if (!empty($validated['guarantor_ids'])) {
                foreach ($validated['guarantor_ids'] as $guarantorId) {
                    $guarantor = LoanGuarantor::create([
                        'loan_id' => $loan->id,
                        'member_id' => $guarantorId,
                        'status' => GuarantorStatus::Pending,
                    ]);

                    $guarantorMember = Member::find($guarantorId);
                    if ($guarantorMember && $guarantorMember->user) {
                        try {
                            $guarantorMember->user->notify(new GuarantorRequestNotification($guarantor));
                        } catch (\Exception $e) {
                            // Notification failed — continue
                        }
                    }
                }
            }

            LoanApprovalLog::create([
                'loan_id' => $loan->id,
                'user_id' => auth()->id(),
                'action' => 'submitted',
                'old_status' => null,
                'new_status' => 'pending',
                'notes' => 'Loan application submitted.',
            ]);
        });

        // Notify loan officers/admins about new application
        try {
            $reviewerUsers = User::where('id', '!=', auth()->id())->whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'loan-officer']);
            })->get();
            foreach ($reviewerUsers as $user) {
                $user->notify(new \App\Notifications\LoanAppliedNotification($loan));
            }
        } catch (\Exception $e) {}

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan application submitted successfully. Number: ' . $loanNumber);
    }

    public function show(Loan $loan): \Illuminate\View\View
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

        return view('loans.show', ['loan' => $loan]);
    }

    public function approve(Loan $loan): \Illuminate\Http\RedirectResponse
    {
        if ($loan->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending loans can be approved.']);
        }

        if ($loan->guarantors()->exists()) {
            $acceptedCount = $loan->guarantors()->where('status', 'accepted')->count();
            $totalCount = $loan->guarantors()->count();

            if ($acceptedCount < $totalCount) {
                return back()->withErrors([
                    'error' => "Cannot approve: {$acceptedCount} of {$totalCount} guarantors have accepted. All guarantors must accept before approval.",
                ]);
            }
        }

        $oldStatus = $loan->status;

        $loan->update([
            'status' => 'approved',
            'approval_date' => now()->toDateString(),
            'approved_by' => auth()->id(),
        ]);

        LoanApprovalLog::create([
            'loan_id' => $loan->id,
            'user_id' => auth()->id(),
            'action' => 'approved',
            'old_status' => $oldStatus,
            'new_status' => 'approved',
            'notes' => 'Loan approved.',
        ]);

        if ($loan->member && $loan->member->user) {
            try {
                $loan->member->user->notify(new LoanStatusNotification($loan, $oldStatus, 'approved'));
            } catch (\Exception $e) {
                // Notification failed — continue
            }
        }

        return back()->with('success', 'Loan approved successfully.');
    }

    public function reject(Request $request, Loan $loan): \Illuminate\Http\RedirectResponse
    {
        if ($loan->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending loans can be rejected.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $oldStatus = $loan->status;

        $loan->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        LoanApprovalLog::create([
            'loan_id' => $loan->id,
            'user_id' => auth()->id(),
            'action' => 'rejected',
            'old_status' => $oldStatus,
            'new_status' => 'rejected',
            'notes' => $validated['rejection_reason'],
        ]);

        if ($loan->member && $loan->member->user) {
            try {
                $loan->member->user->notify(new LoanStatusNotification($loan, $oldStatus, 'rejected'));
            } catch (\Exception $e) {
                // Notification failed — continue
            }
        }

        return back()->with('success', 'Loan rejected.');
    }

    public function addNote(Request $request, Loan $loan): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $loan->update(['admin_notes' => $validated['admin_notes']]);

        LoanApprovalLog::create([
            'loan_id' => $loan->id,
            'user_id' => auth()->id(),
            'action' => 'note_added',
            'old_status' => $loan->status,
            'new_status' => $loan->status,
            'notes' => $validated['admin_notes'],
        ]);

        return back()->with('success', 'Note added.');
    }

    public function updateGuarantor(Request $request, Loan $loan, LoanGuarantor $guarantor): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,declined',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($guarantor->loan_id !== $loan->id) {
            return back()->withErrors(['error' => 'This guarantor does not belong to this loan.']);
        }

        $guarantor->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'responded_at' => now(),
        ]);

        $statusText = $validated['status'] === 'accepted' ? 'accepted' : 'declined';
        return back()->with('success', "Guarantor request {$statusText} successfully.");
    }

    public function disburse(Loan $loan): \Illuminate\Http\RedirectResponse
    {
        if ($loan->status !== 'approved') {
            return back()->withErrors(['error' => 'Only approved loans can be disbursed.']);
        }

        $maturityDate = now()->addMonths($loan->tenure_months)->toDateString();
        $oldStatus = $loan->status;

        $loan->update([
            'status' => 'disbursed',
            'disbursement_date' => now()->toDateString(),
            'maturity_date' => $maturityDate,
        ]);

        LoanApprovalLog::create([
            'loan_id' => $loan->id,
            'user_id' => auth()->id(),
            'action' => 'disbursed',
            'old_status' => $oldStatus,
            'new_status' => 'disbursed',
            'notes' => 'Loan disbursed. Maturity: ' . $maturityDate,
        ]);

        if ($loan->member && $loan->member->user) {
            try {
                $loan->member->user->notify(new LoanStatusNotification($loan, $oldStatus, 'disbursed'));
            } catch (\Exception $e) {
                // Notification failed — continue
            }
        }

        return back()->with('success', 'Loan disbursed successfully. Maturity: ' . $maturityDate);
    }

    public function repayment(Loan $loan): \Illuminate\View\View
    {
        $loan->load('member');

        return view('loans.repayment', ['loan' => $loan]);
    }

    public function storeRepayment(Request $request, Loan $loan): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,salary_deduction,savings_deduction',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = round($validated['amount'], 2);

        if ($amount > $loan->outstanding) {
            return back()->withErrors([
                'amount' => 'Payment exceeds outstanding amount of ₦' . number_format($loan->outstanding, 2),
            ])->withInput();
        }

        $interestRate = $loan->interest_rate / 100;
        $interestPortion = round($amount * ($interestRate / (1 + $interestRate)), 2);
        $principalPortion = round($amount - $interestPortion, 2);
        $outstandingAfter = round($loan->outstanding - $principalPortion, 2);
        $newStatus = $outstandingAfter <= 0 ? 'completed' : 'repaying';

        DB::transaction(function () use ($loan, $validated, $amount, $principalPortion, $interestPortion, $outstandingAfter, $newStatus) {
            LoanRepayment::create([
                'loan_id' => $loan->id,
                'member_id' => $loan->member_id,
                'reference' => 'LN/REPAY/' . strtoupper(Str::random(8)),
                'amount' => $amount,
                'principal_portion' => $principalPortion,
                'interest_portion' => $interestPortion,
                'outstanding_after' => $outstandingAfter,
                'payment_method' => $validated['payment_method'],
                'source' => 'manual',
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $loan->update([
                'total_repaid' => $loan->total_repaid + $amount,
                'outstanding' => max(0, $outstandingAfter),
                'status' => $newStatus,
            ]);
        });

        if ($newStatus === 'completed' && $loan->member && $loan->member->user) {
            try {
                $loan->member->user->notify(new LoanStatusNotification($loan, 'repaying', 'completed'));
            } catch (\Exception $e) {
                // Notification failed — continue
            }
        }

        $message = $outstandingAfter <= 0
            ? 'Final repayment recorded. Loan is now completed!'
            : 'Repayment of ₦' . number_format($amount, 2) . ' recorded. Outstanding: ₦' . number_format($outstandingAfter, 2);

        return back()->with('success', $message);
    }

    public function topup(Loan $loan): \Illuminate\View\View
    {
        if (!$loan->canTopup()) {
            return back()->withErrors(['error' => 'This loan is not eligible for top-up.']);
        }

        $loan->load('member', 'loanProduct');
        $members = Member::where('status', 'active')->orderBy('first_name')->get();
        $loanProducts = LoanProduct::where('enabled', true)->orderBy('name')->get();

        return view('loans.topup', compact('loan', 'members', 'loanProducts'));
    }

    public function storeTopup(Request $request, Loan $parentLoan): \Illuminate\Http\RedirectResponse
    {
        if (!$parentLoan->canTopup()) {
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

        if (!empty($validated['loan_product_id'])) {
            $product = LoanProduct::find($validated['loan_product_id']);
            if ($product) {
                if ($validated['amount'] > $product->max_amount) {
                    return back()->withErrors([
                        'amount' => "Amount exceeds the maximum of ₦" . number_format($product->max_amount, 2) . " for {$product->name}.",
                    ])->withInput();
                }
                if ($validated['amount'] < $product->min_amount) {
                    return back()->withErrors([
                        'amount' => "Amount is below the minimum of ₦" . number_format($product->min_amount, 2) . " for {$product->name}.",
                    ])->withInput();
                }
                if ($validated['tenure_months'] > $product->max_term_months) {
                    return back()->withErrors([
                        'tenure_months' => "Tenure exceeds the maximum of {$product->max_term_months} months for {$product->name}.",
                    ])->withInput();
                }
            }
        }

        $loanService = app(LoanService::class);
        $monthlyRepayment = $loanService->calculateMonthlyRepayment(
            $validated['amount'],
            $validated['interest_rate'] ?? 0,
            $validated['tenure_months']
        );
        $loanNumber = $loanService->generateLoanNumber();

        DB::transaction(function () use ($validated, $parentLoan, $loanNumber, $monthlyRepayment, &$topupLoan) {
            $topupLoan = Loan::create([
                'member_id' => $parentLoan->member_id,
                'loan_product_id' => $validated['loan_product_id'] ?? $parentLoan->loan_product_id,
                'parent_loan_id' => $parentLoan->id,
                'loan_number' => $loanNumber,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'interest_rate' => $validated['interest_rate'],
                'tenure_months' => $validated['tenure_months'],
                'monthly_repayment' => $monthlyRepayment,
                'outstanding' => $validated['amount'],
                'application_date' => now()->toDateString(),
                'purpose' => $validated['purpose'] ?? 'Top-up for loan ' . $parentLoan->loan_number,
                'status' => 'pending',
            ]);

            LoanApprovalLog::create([
                'loan_id' => $topupLoan->id,
                'user_id' => auth()->id(),
                'action' => 'submitted',
                'old_status' => null,
                'new_status' => 'pending',
                'notes' => 'Loan top-up application submitted for parent loan: ' . $parentLoan->loan_number,
            ]);
        });

        try {
            $reviewerUsers = User::where('id', '!=', auth()->id())->whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'loan-officer']);
            })->get();
            foreach ($reviewerUsers as $user) {
                $user->notify(new \App\Notifications\LoanAppliedNotification($topupLoan));
            }
        } catch (\Exception $e) {}

        return redirect()->route('loans.show', $topupLoan)
            ->with('success', 'Loan top-up application submitted successfully. Number: ' . $loanNumber);
    }

    public function exportLoans()
    {
        return Excel::download(new LoansExport, 'loans_export_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function import(): \Illuminate\View\View
    {
        return view('loans.import');
    }

    public function importStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new LoanRepaymentImport, $request->file('import_file'));

            return redirect()->route('loans.index')
                ->with('success', 'Loan repayments imported successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['import_file' => 'Import failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="loan_repayment_import_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['staff_id', 'amount', 'payment_date', 'notes']);
            fputcsv($file, ['STF001', '15000', '2026-01-15', 'January salary deduction - loan repayment']);
            fputcsv($file, ['STF002', '10000', '2026-01-15', 'January salary deduction - loan repayment']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
