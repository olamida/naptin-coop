<?php

namespace App\Http\Controllers;

use App\Exports\PayrollDeductionExport;
use App\Exports\PayrollUploadTemplateExport;
use App\Imports\PayrollDeductionImport;
use App\Actions\Payroll\CompileAndLockPayroll;
use App\Actions\Payroll\DestroyArrear;
use App\Actions\Payroll\SettleArrear;
use App\Actions\Payroll\StoreAllArrears;
use App\Actions\Payroll\StoreArrear;
use App\Models\ImportLog;
use App\Models\MonthlyPayroll;
use App\Models\PayrollArrear;
use App\Models\PayrollDeduction;
use Illuminate\Http\Request;
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

        try {
            $payroll = CompileAndLockPayroll::run($validated['year'], $validated['month_number']);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

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
            ->with('success', "Payroll for {$payroll->month} {$payroll->year} compiled and locked with {$payroll->member_count} members.");
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

        try {
            StoreArrear::run($deduction->id, $shortfall, $validated['reason'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Arrear recorded for member and will be carried into the next payroll.');
    }

    public function storeAllArrears(Request $request, MonthlyPayroll $monthlyPayroll): \Illuminate\Http\RedirectResponse
    {
        $count = StoreAllArrears::run($monthlyPayroll->id);

        return back()->with('success', "{$count} arrear(s) recorded from shortfalls and will be carried into the next payroll.");
    }

    public function settleArrear(PayrollArrear $payrollArrear): \Illuminate\Http\RedirectResponse
    {
        try {
            SettleArrear::run($payrollArrear);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Arrear marked as settled.');
    }

    public function destroyArrear(PayrollArrear $payrollArrear): \Illuminate\Http\RedirectResponse
    {
        DestroyArrear::run($payrollArrear);

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
