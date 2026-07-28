<?php

namespace App\Http\Controllers;

use App\Exports\PayrollDeductionExport;
use App\Exports\PayrollUploadTemplateExport;
use App\Imports\PayrollDeductionImport;
use App\Models\Loan;
use App\Models\Member;
use App\Models\MonthlyPayroll;
use App\Models\PayrollDeduction;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $totalSavings = 0;
        $totalLoanRepayments = 0;
        $totalShareContributions = 0;
        $totalPurchases = 0;

        $deductions = $members->map(function ($member) use (&$totalSavings, &$totalLoanRepayments, &$totalShareContributions, &$totalPurchases) {
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

            $totalExpected = $expectedSavings + $expectedLoanRepayment + $expectedShareContribution + $expectedPurchase;

            $totalSavings += $expectedSavings;
            $totalLoanRepayments += $expectedLoanRepayment;
            $totalShareContributions += $expectedShareContribution;
            $totalPurchases += $expectedPurchase;

            return [
                'member_id' => $member->id,
                'expected_savings' => $expectedSavings,
                'expected_loan_repayment' => $expectedLoanRepayment,
                'expected_share_contribution' => $expectedShareContribution,
                'expected_purchase' => $expectedPurchase,
                'total_expected' => $totalExpected,
                'actual_savings' => 0,
                'actual_loan_repayment' => 0,
                'actual_share_contribution' => 0,
                'actual_purchase' => 0,
                'total_actual' => 0,
                'status' => 'pending',
            ];
        });

        $payroll = DB::transaction(function () use ($payrollNumber, $monthName, $year, $monthNumber, $members, $totalSavings, $totalLoanRepayments, $totalShareContributions, $totalPurchases, $deductions) {
            $payroll = MonthlyPayroll::create([
                'payroll_number' => $payrollNumber,
                'month' => $monthName,
                'year' => $year,
                'month_number' => $monthNumber,
                'total_savings' => $totalSavings,
                'total_loan_repayments' => $totalLoanRepayments,
                'total_share_contributions' => $totalShareContributions,
                'total_purchases' => $totalPurchases,
                'grand_total' => $totalSavings + $totalLoanRepayments + $totalShareContributions + $totalPurchases,
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

    public function show(MonthlyPayroll $monthlyPayroll): \Illuminate\View\View
    {
        $monthlyPayroll->load(['deductions.member.region']);

        return view('payroll.show', ['payroll' => $monthlyPayroll]);
    }

    public function upload(MonthlyPayroll $monthlyPayroll): \Illuminate\View\View
    {
        $monthlyPayroll->load(['deductions.member']);

        return view('payroll.upload', ['payroll' => $monthlyPayroll]);
    }

    public function downloadTemplate(MonthlyPayroll $monthlyPayroll): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = "payroll_upload_template_{$monthlyPayroll->payroll_number}.xlsx";

        return Excel::download(new PayrollUploadTemplateExport($monthlyPayroll->id), $filename);
    }

    public function exportDeductions(MonthlyPayroll $monthlyPayroll): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = "payroll_deductions_{$monthlyPayroll->payroll_number}.xlsx";

        return Excel::download(new PayrollDeductionExport($monthlyPayroll->id), $filename);
    }

    public function uploadDeductions(Request $request, MonthlyPayroll $monthlyPayroll): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'deductions_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        if ($monthlyPayroll->status === 'completed') {
            return back()->withErrors(['error' => 'This payroll is already completed and cannot be updated.']);
        }

        Excel::import(new PayrollDeductionImport($monthlyPayroll->id), $request->file('deductions_file'));

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
