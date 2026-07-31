<?php

namespace App\Http\Controllers;

use App\Exports\PayrollDeductionExport;
use App\Exports\PayrollUploadTemplateExport;
use App\Imports\PayrollDeductionImport;
use App\Models\ImportLog;
use App\Models\Loan;
use App\Models\Member;
use App\Models\MonthlyPayroll;
use App\Models\PayrollArrear;
use App\Models\PayrollDeduction;
use App\Models\PurchaseOrder;
use App\Actions\Payroll\CompileAndLockPayroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PayrollController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $payrolls = MonthlyPayroll::latest('year')->latest('month_number')->paginate(15);

        $stats = [
            'total_payrolls' => MonthlyPayroll::count(),
            'total_deductions' => MonthlyPayroll::sum('grand_total'),
            'latest_status' => MonthlyPayroll::latest()->value('status'),
            'total_members' => MonthlyPayroll::latest()->value('member_count') ?? 0,
        ];

        return view('payroll.index', ['payrolls' => $payrolls, 'stats' => $stats]);
    }

    public function compile(): \Illuminate\View\View
    {
        return view('payroll.compile');
    }

    public function compilePost(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'month_number' => 'required|integer|between:1,12',
        ]);

        $year = $validated['year'];
        $monthNumber = $validated['month_number'];
        $monthName = Carbon::createFromDate($year, $monthNumber, 1)->format('F');

        $existing = MonthlyPayroll::where('year', $year)->where('month_number', $monthNumber)->first();
        if ($existing) {
            return back()->withErrors(['error' => "Payroll for {$monthName} {$year} already exists."]);
        }

        $count = MonthlyPayroll::where('year', $year)->count() + 1;
        $payrollNumber = 'PAY/' . $year . '/' . str_pad($count, 6, '0', STR_PAD_LEFT);

        $members = Member::where('status', 'active')->get();

        $openArrearsByMember = PayrollArrear::open()
            ->get()
            ->groupBy('member_id')
            ->map(fn ($arrears) => round($arrears->sum('shortfall'), 2));

        $totalSavings = 0;
        $totalLoanRepayments = 0;
        $totalShareContributions = 0;
        $totalPurchases = 0;
        $totalArrears = 0;

        $deductions = $members->map(function ($member) use (&$totalSavings, &$totalLoanRepayments, &$totalShareContributions, &$totalPurchases, &$totalArrears, $openArrearsByMember) {
            $expectedSavings = $member->monthly_savings ?? round($member->monthly_salary * 0.10, 2);

            $activeLoan = Loan::where('member_id', $member->id)
                ->whereIn('status', ['disbursed', 'repaying'])
                ->first();
            $expectedLoanRepayment = $activeLoan ? $activeLoan->monthly_repayment : 0;

            $expectedShareContribution = round($member->monthly_salary * 0.05, 2);

            $activePurchase = PurchaseOrder::where('member_id', $member->id)
                ->whereIn('status', ['approved', 'active'])
                ->where('payment_type', 'hire_purchase')
                ->first();
            $expectedPurchase = $activePurchase ? $activePurchase->monthly_repayment : 0;

            $expectedArrears = $openArrearsByMember[$member->id] ?? 0;

            $totalExpected = $expectedSavings + $expectedLoanRepayment + $expectedShareContribution + $expectedPurchase + $expectedArrears;

            $totalSavings += $expectedSavings;
            $totalLoanRepayments += $expectedLoanRepayment;
            $totalShareContributions += $expectedShareContribution;
            $totalPurchases += $expectedPurchase;
            $totalArrears += $expectedArrears;

            return [
                'member_id' => $member->id,
                'expected_savings' => $expectedSavings,
                'expected_loan_repayment' => $expectedLoanRepayment,
                'expected_share_contribution' => $expectedShareContribution,
                'expected_purchase' => $expectedPurchase,
                'expected_arrears' => $expectedArrears,
                'total_expected' => $totalExpected,
                'actual_savings' => 0,
                'actual_loan_repayment' => 0,
                'actual_share_contribution' => 0,
                'actual_purchase' => 0,
                'actual_arrears' => 0,
                'total_actual' => 0,
                'status' => 'pending',
            ];
        });

        $payroll = DB::transaction(function () use ($payrollNumber, $monthName, $year, $monthNumber, $members, $totalSavings, $totalLoanRepayments, $totalShareContributions, $totalPurchases, $totalArrears, $deductions) {
            $payroll = MonthlyPayroll::create([
                'payroll_number' => $payrollNumber,
                'month' => $monthName,
                'year' => $year,
                'month_number' => $monthNumber,
                'total_savings' => $totalSavings,
                'total_loan_repayments' => $totalLoanRepayments,
                'total_share_contributions' => $totalShareContributions,
                'total_purchases' => $totalPurchases,
                'total_arrears' => $totalArrears,
                'grand_total' => $totalSavings + $totalLoanRepayments + $totalShareContributions + $totalPurchases + $totalArrears,
                'member_count' => $members->count(),
                'status' => 'compiled',
            ]);

            foreach ($deductions as $deduction) {
                PayrollDeduction::create(array_merge($deduction, [
                    'monthly_payroll_id' => $payroll->id,
                ]));
            }

            return $payroll;
        });

        // Notify admins about compiled payroll
        try {
            $adminUsers = \App\Models\User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'treasurer']);
            })->get();
            foreach ($adminUsers as $admin) {
                $admin->notify(new \App\Notifications\PayrollCompiledNotification($payroll));
            }
        } catch (\Exception $e) {
            \Log::error('Payroll notification failed: ' . $e->getMessage());
        }

        return redirect()->route('payroll.show', $payroll)
            ->with('success', "Payroll for {$monthName} {$year} compiled successfully with {$members->count()} members.");
    }

    public function compileAndLock(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'month_number' => 'required|integer|between:1,12',
        ]);

        try {
            $payroll = CompileAndLockPayroll::run($validated['year'], $validated['month_number']);

            \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['super-admin', 'admin', 'treasurer']))
                ->get()->each(fn($user) => $user->notify(new \App\Notifications\PayrollCompiledNotification($payroll)));

            return redirect()->route('payroll.show', $payroll)
                ->with('success', "Payroll for {$payroll->month} {$payroll->year} compiled and locked with {$payroll->member_count} members.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(MonthlyPayroll $monthlyPayroll): \Illuminate\View\View
    {
        $monthlyPayroll->load(['deductions.member.region']);

        return view('payroll.show', ['payroll' => $monthlyPayroll]);
    }

    public function storeArrear(Request $request, MonthlyPayroll $monthlyPayroll): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $deduction = PayrollDeduction::where('monthly_payroll_id', $monthlyPayroll->id)
            ->where('member_id', $validated['member_id'])
            ->first();

        if (! $deduction) {
            return back()->withErrors(['error' => 'Deduction record not found for this member on this payroll.']);
        }

        $shortfall = round((float) $deduction->total_expected - (float) $deduction->total_actual, 2);

        if ($shortfall <= 0) {
            return back()->withErrors(['error' => 'No shortfall exists for this member to flag as arrears.']);
        }

        $exists = PayrollArrear::where('monthly_payroll_id', $monthlyPayroll->id)
            ->where('member_id', $deduction->member_id)
            ->where('status', 'open')
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'An open arrear already exists for this member on this payroll.']);
        }

        PayrollArrear::create([
            'monthly_payroll_id' => $monthlyPayroll->id,
            'member_id' => $deduction->member_id,
            'component' => 'total',
            'expected_amount' => $deduction->total_expected,
            'actual_amount' => $deduction->total_actual,
            'shortfall' => $shortfall,
            'reason' => $validated['reason'] ?? null,
            'status' => 'open',
            'recorded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Arrear recorded for member and will be carried into the next payroll.');
    }

    public function storeAllArrears(Request $request, MonthlyPayroll $monthlyPayroll): \Illuminate\Http\RedirectResponse
    {
        $count = 0;

        $monthlyPayroll->deductions->each(function ($deduction) use ($monthlyPayroll, &$count) {
            $shortfall = round((float) $deduction->total_expected - (float) $deduction->total_actual, 2);

            if ($shortfall <= 0) {
                return;
            }

            $exists = PayrollArrear::where('monthly_payroll_id', $monthlyPayroll->id)
                ->where('member_id', $deduction->member_id)
                ->where('status', 'open')
                ->exists();

            if ($exists) {
                return;
            }

            PayrollArrear::create([
                'monthly_payroll_id' => $monthlyPayroll->id,
                'member_id' => $deduction->member_id,
                'component' => 'total',
                'expected_amount' => $deduction->total_expected,
                'actual_amount' => $deduction->total_actual,
                'shortfall' => $shortfall,
                'status' => 'open',
                'recorded_by' => auth()->id(),
            ]);

            $count++;
        });

        return back()->with('success', "{$count} arrear(s) recorded from shortfalls and will be carried into the next payroll.");
    }

    public function settleArrear(PayrollArrear $payrollArrear): \Illuminate\Http\RedirectResponse
    {
        if ($payrollArrear->status === 'settled') {
            return back()->withErrors(['error' => 'This arrear is already settled.']);
        }

        $payrollArrear->update([
            'status' => 'settled',
            'settled_at' => now(),
        ]);

        return back()->with('success', 'Arrear marked as settled.');
    }

    public function destroyArrear(PayrollArrear $payrollArrear): \Illuminate\Http\RedirectResponse
    {
        $payrollArrear->delete();

        return back()->with('success', 'Arrear removed.');
    }

    public function upload(MonthlyPayroll $monthlyPayroll): \Illuminate\View\View
    {
        $monthlyPayroll->load(['deductions.member']);

        return view('payroll.upload', ['payroll' => $monthlyPayroll]);
    }

    public function downloadTemplate(MonthlyPayroll $monthlyPayroll): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $safe = str_replace('/', '-', $monthlyPayroll->payroll_number);
        $filename = "payroll_upload_template_{$safe}.xlsx";

        return Excel::download(new PayrollUploadTemplateExport($monthlyPayroll->id), $filename);
    }

    public function exportDeductions(MonthlyPayroll $monthlyPayroll): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $safe = str_replace('/', '-', $monthlyPayroll->payroll_number);
        $filename = "payroll_deductions_{$safe}.xlsx";

        return Excel::download(new PayrollDeductionExport($monthlyPayroll->id), $filename);
    }

    public function exportCsv(MonthlyPayroll $monthlyPayroll)
    {
        $monthlyPayroll->load(['deductions.member.region']);
        $safe = str_replace('/', '-', $monthlyPayroll->payroll_number);
        $filename = "payroll_{$safe}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($monthlyPayroll) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, ['Staff ID', 'Member Name', 'Region', 'Monthly Salary', 'Savings', 'Loan Repayment', 'Share Contribution', 'Purchase', 'Arrears', 'Total Expected', 'Total Actual', 'Status']);

            foreach ($monthlyPayroll->deductions as $d) {
                fputcsv($handle, [
                    $d->member->staff_id ?? '',
                    $d->member->full_name ?? '',
                    $d->member->region->name ?? '',
                    number_format($d->member->monthly_salary ?? 0, 2),
                    number_format($d->expected_savings, 2),
                    number_format($d->expected_loan_repayment, 2),
                    number_format($d->expected_share_contribution, 2),
                    number_format($d->expected_purchase, 2),
                    number_format($d->expected_arrears, 2),
                    number_format($d->total_expected, 2),
                    number_format($d->total_actual, 2),
                    $d->status,
                ]);
            }

            // Totals row
            fputcsv($handle, [
                'TOTALS', '', '', '',
                number_format($monthlyPayroll->deductions->sum('expected_savings'), 2),
                number_format($monthlyPayroll->deductions->sum('expected_loan_repayment'), 2),
                number_format($monthlyPayroll->deductions->sum('expected_share_contribution'), 2),
                number_format($monthlyPayroll->deductions->sum('expected_purchase'), 2),
                number_format($monthlyPayroll->deductions->sum('expected_arrears'), 2),
                number_format($monthlyPayroll->deductions->sum('total_expected'), 2),
                number_format($monthlyPayroll->deductions->sum('total_actual'), 2),
                '',
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function uploadDeductions(Request $request, MonthlyPayroll $monthlyPayroll): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'deductions_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        if ($monthlyPayroll->status === 'completed') {
            return back()->withErrors(['error' => 'This payroll is already completed and cannot be updated.']);
        }

        $batchId = (string) Str::uuid();
        $import = new PayrollDeductionImport($monthlyPayroll->id, $batchId);
        $fileName = $request->file('deductions_file')->getClientOriginalName();

        try {
            Excel::import($import, $request->file('deductions_file'));
        } catch (\Exception $e) {
            ImportLog::record($batchId, 'payroll_deductions', $fileName, $import->importStats(), 'failed', $e->getMessage());

            return back()->withErrors(['error' => 'Upload failed: ' . $e->getMessage()]);
        }

        ImportLog::record($batchId, 'payroll_deductions', $fileName, $import->importStats());

        $completedCount = $monthlyPayroll->deductions()->where('status', 'completed')->count();
        $totalDeductions = $monthlyPayroll->deductions()->count();

        if ($completedCount === $totalDeductions) {
            $monthlyPayroll->update(['status' => 'completed']);
        } elseif ($completedCount > 0) {
            $monthlyPayroll->update(['status' => 'deducted']);
        }

        return back()->with('success', "Payroll deductions uploaded. {$completedCount} of {$totalDeductions} members processed.");
    }

    public function savingsReport(MonthlyPayroll $monthlyPayroll): \Illuminate\View\View
    {
        $monthlyPayroll->load(['deductions.member.region']);

        return view('payroll.savings-report', ['payroll' => $monthlyPayroll]);
    }

    public function loansReport(MonthlyPayroll $monthlyPayroll): \Illuminate\View\View
    {
        $monthlyPayroll->load(['deductions.member.region']);

        return view('payroll.loans-report', ['payroll' => $monthlyPayroll]);
    }

    public function purchasesReport(MonthlyPayroll $monthlyPayroll): \Illuminate\View\View
    {
        $monthlyPayroll->load(['deductions.member.region']);

        return view('payroll.purchases-report', ['payroll' => $monthlyPayroll]);
    }

    public function sharesReport(MonthlyPayroll $monthlyPayroll): \Illuminate\View\View
    {
        $monthlyPayroll->load(['deductions.member.region']);

        return view('payroll.shares-report', ['payroll' => $monthlyPayroll]);
    }

    public function summaryReport(MonthlyPayroll $monthlyPayroll): \Illuminate\View\View
    {
        $monthlyPayroll->load(['deductions.member.region']);

        return view('payroll.summary-report', ['payroll' => $monthlyPayroll]);
    }
}
