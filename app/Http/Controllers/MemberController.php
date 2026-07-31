<?php

namespace App\Http\Controllers;

use App\Actions\Membership\ApproveMemberApplication;
use App\Actions\Membership\BulkUpdateStatus;
use App\Actions\Membership\CreateMember;
use App\Actions\Membership\RejectMemberApplication;
use App\Enums\MemberStatus;
use App\Exports\MembersExport;
use App\Imports\MemberImport;
use App\Models\ImportLog;
use App\Models\LoanGuarantor;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\PurchaseOrder;
use App\Models\Region;
use App\Models\SavingsTransaction;
use App\Models\ShareTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $query = Member::with('region');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            });
        }

        if ($regionId = $request->input('region_id')) {
            $query->where('region_id', $regionId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = $request->input('per_page');
        $members = $query->latest()->paginate($perPage === 'all' ? 1000 : 15)->withQueryString();
        $regions = Region::where('enabled', true)->orderBy('name')->get();
        $statuses = MemberStatus::cases();

        return view('members.index', compact('members', 'regions', 'statuses'));
    }

    public function searchJson(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = $request->input('q');

        $members = Member::with('region:id,name')
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            }))
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'staff_id', 'photo_path', 'region_id']);

        return response()->json($members->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->first_name . ' ' . $m->last_name,
            'staff_id' => $m->staff_id_display,
            'photo_url' => $m->photo_url,
            'region' => $m->region?->name,
            'url' => route('members.show', $m),
        ]));
    }

    public function create(): \Illuminate\View\View
    {
        $regions = Region::where('enabled', true)->orderBy('name')->get();

        return view('members.create', ['regions' => $regions]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'staff_id' => 'required|numeric|unique:members,staff_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:members,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'employment_date' => 'nullable|date',
            'retirement_date' => 'nullable|date|after_or_equal:employment_date',
            'address' => 'nullable|string',
            'state_of_origin' => 'nullable|string|max:100',
            'nin' => 'nullable|string|unique:members,nin',
            'grade_level' => 'nullable|string|max:20',
            'monthly_salary' => 'nullable|numeric|min:0',
            'monthly_savings' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', array_column(MemberStatus::cases(), 'value')),
            'photo' => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('member-photos', 'public');
        }

        unset($validated['photo']);

        $member = CreateMember::run(array_merge($validated, ['photo_path' => $photoPath]));

        return redirect()->route('members.show', $member)
            ->with('success', 'Member created successfully with savings and share accounts.' . (!empty($validated['email']) ? ' Login credentials sent to their email.' : ''));
    }

    public function show(Member $member): \Illuminate\View\View
    {
        $member->load([
            'region',
            'positions',
            'savingsAccount.transactions' => fn ($q) => $q->latest('transaction_date'),
            'shareAccount.transactions' => fn ($q) => $q->latest('transaction_date'),
            'loans.loanProduct',
            'loans.guarantors.member',
            'loans.schedules',
            'nextOfKins',
        ]);

        // Savings chart data (last 12 months)
        $savingsChartMonths = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            $deposits = (float) SavingsTransaction::where('savings_account_id', $member->savingsAccount?->id)
                ->where('type', 'deposit')->where('status', 'completed')
                ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
            $withdrawals = (float) SavingsTransaction::where('savings_account_id', $member->savingsAccount?->id)
                ->where('type', 'withdrawal')->where('status', 'completed')
                ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
            $savingsChartMonths->push([
                'label' => $date->format('M Y'),
                'deposits' => $deposits,
                'withdrawals' => $withdrawals,
            ]);
        }

        // Unified ledger: merge savings + loan repayments + share txns + purchases
        $ledger = collect();
        if ($member->savingsAccount) {
            foreach ($member->savingsAccount->transactions as $t) {
                $ledger->push([
                    'date' => $t->created_at,
                    'date_iso' => $t->created_at->toDateString(),
                    'date_display' => $t->created_at->format('d M Y'),
                    'type_label' => $t->type === 'deposit' ? 'Savings Deposit' : 'Savings Withdrawal',
                    'reference' => $t->reference ?? 'SAV/' . $t->id,
                    'amount' => $t->type === 'deposit' ? $t->amount : -$t->amount,
                    'category' => 'savings',
                    'balance_after' => $t->balance_after,
                    'status' => $t->status,
                ]);
            }
        }
        $loanRepayments = LoanRepayment::where('member_id', $member->id)
            ->with('loan')->latest()->limit(20)->get();
        foreach ($loanRepayments as $r) {
            $ledger->push([
                'date' => $r->created_at,
                'date_iso' => $r->created_at->toDateString(),
                'date_display' => $r->created_at->format('d M Y'),
                'type_label' => 'Loan Repayment',
                'reference' => $r->loan?->loan_number ?? 'N/A',
                'amount' => -$r->amount,
                'category' => 'loan_repayment',
                'balance_after' => null,
                'status' => 'completed',
            ]);
        }
        if ($member->shareAccount) {
            foreach ($member->shareAccount->transactions as $t) {
                $ledger->push([
                    'date' => $t->created_at,
                    'date_iso' => $t->created_at->toDateString(),
                    'date_display' => $t->created_at->format('d M Y'),
                    'type_label' => 'Share ' . ucfirst($t->type),
                    'reference' => 'SH/' . $t->id,
                    'amount' => $t->type === 'purchase' ? -$t->amount : $t->amount,
                    'category' => 'shares',
                    'balance_after' => null,
                    'status' => 'completed',
                ]);
            }
        }
        $purchases = PurchaseOrder::where('member_id', $member->id)
            ->with('product')->latest()->limit(20)->get();
        foreach ($purchases as $p) {
            $ledger->push([
                'date' => $p->created_at,
                'date_iso' => $p->created_at->toDateString(),
                'date_display' => $p->created_at->format('d M Y'),
                'type_label' => 'Purchase: ' . ($p->product?->name ?? 'N/A'),
                'reference' => $p->order_number ?? 'ORD/' . $p->id,
                'amount' => -$p->total_amount,
                'category' => 'purchase',
                'balance_after' => null,
                'status' => $p->status,
            ]);
        }
        $ledger = $ledger->sortByDesc('date')->values();

        // Guarantor risk: members this member guarantees
        $guarantorRisk = LoanGuarantor::where('member_id', $member->id)
            ->whereHas('loan', fn($q) => $q->whereIn('status', ['approved', 'disbursed', 'repaying']))
            ->with(['loan.member', 'loan.loanProduct'])
            ->get();

        // Documents: payment evidence from savings transactions
        $documents = $member->savingsAccount
            ? SavingsTransaction::where('savings_account_id', $member->savingsAccount->id)
                ->whereNotNull('payment_evidence_path')
                ->latest()->get()
            : collect();

        // Active loan for timeline
        $activeLoan = $member->loans->whereIn('status', ['disbursed', 'repaying', 'arrears'])->first();
        if ($activeLoan) {
            $totalInstallments = $activeLoan->schedules->count();
            $paidInstallments = $activeLoan->schedules->where('status', 'paid')->count();
        } else {
            $totalInstallments = 0;
            $paidInstallments = 0;
        }

        // Next unpaid installment for the active loan
        $nextDue = $activeLoan
            ? $activeLoan->schedules
                ->where('status', '!=', 'paid')
                ->sortBy('installment_number')
                ->first()
            : null;

        return view('members.show', compact(
            'member', 'savingsChartMonths', 'ledger', 'guarantorRisk',
            'documents', 'activeLoan', 'totalInstallments', 'paidInstallments', 'nextDue'
        ));
    }

    public function edit(Member $member): \Illuminate\View\View
    {
        $regions = Region::where('enabled', true)->orderBy('name')->get();

        return view('members.edit', ['member' => $member, 'regions' => $regions]);
    }

    public function update(Request $request, Member $member): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'staff_id' => 'required|numeric|unique:members,staff_id,' . $member->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:members,email,' . $member->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'employment_date' => 'nullable|date',
            'retirement_date' => 'nullable|date',
            'address' => 'nullable|string',
            'state_of_origin' => 'nullable|string|max:100',
            'nin' => 'nullable|string|unique:members,nin,' . $member->id,
            'grade_level' => 'nullable|string|max:20',
            'monthly_salary' => 'nullable|numeric|min:0',
            'monthly_savings' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', array_column(MemberStatus::cases(), 'value')),
            'is_exco' => 'boolean',
            'photo' => 'nullable|image|max:2048',
            'remove_photo' => 'boolean',
        ]);

        if ($request->boolean('remove_photo') && $member->photo_path) {
            Storage::disk('public')->delete($member->photo_path);
            $validated['photo_path'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($member->photo_path) {
                Storage::disk('public')->delete($member->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('member-photos', 'public');
        }

        unset($validated['photo'], $validated['remove_photo']);

        $member->update($validated);

        return redirect()->route('members.show', $member)
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member): \Illuminate\Http\RedirectResponse
    {
        $hasRelatedData = $member->savingsAccount
            || $member->shareAccount
            || $member->loans()->exists()
            || $member->purchaseOrders()->exists()
            || $member->loanRepayments()->exists()
            || $member->nextOfKins()->exists();

        if ($hasRelatedData) {
            return back()->withErrors([
                'error' => 'Cannot delete member with existing savings, shares, loans, purchases, repayments, or next of kin records. Remove related data first or contact an administrator.',
            ]);
        }

        if ($member->user_id) {
            return back()->withErrors([
                'error' => 'Cannot delete member linked to a user account. Unlink the user first or contact an administrator.',
            ]);
        }

        if ($member->photo_path) {
            Storage::disk('public')->delete($member->photo_path);
        }
        $member->delete();

        return redirect()->route('members.index')
            ->with('success', 'Member deleted successfully.');
    }

    public function import(): \Illuminate\View\View
    {
        return view('members.import');
    }

    public function importStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $batchId = (string) Str::uuid();
        $import = new MemberImport($batchId);
        $fileName = $request->file('import_file')->getClientOriginalName();

        try {
            Excel::import($import, $request->file('import_file'));

            ImportLog::record($batchId, 'members', $fileName, $import->importStats());

            return redirect()->route('members.index')
                ->with('success', 'Members imported successfully. Batch: ' . substr($batchId, 0, 8) . '…');
        } catch (\Exception $e) {
            ImportLog::record($batchId, 'members', $fileName, $import->importStats(), 'failed', $e->getMessage());

            return back()->withErrors(['import_file' => 'Import failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="member_import_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'staff_id', 'first_name', 'last_name', 'middle_name', 'region',
                'email', 'phone', 'gender', 'date_of_birth', 'employment_date',
                'address', 'state_of_origin', 'nin', 'grade_level', 'monthly_salary', 'status', 'external_reference',
            ]);
            fputcsv($file, [
                'STF001', 'John', 'Doe', 'Michael', 'Lagos',
                'john.doe@example.com', '08012345678', 'male', '1990-01-15', '2020-03-01',
                '123 Main Street', 'Lagos', '12345678901', 'Grade 10', '150000', 'active', '',
            ]);
            fputcsv($file, [
                'STF002', 'Jane', 'Smith', '', 'Abuja',
                'jane.smith@example.com', '08087654321', 'female', '1985-06-20', '2019-07-15',
                '456 Second Avenue', 'FCT', '98765432101', 'Grade 8', '120000', 'active', '',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportMembers()
    {
        return Excel::download(new MembersExport, 'members_export_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function savingsDetail(Member $member): \Illuminate\View\View
    {
        $transactions = $member->savingsAccount
            ? $member->savingsAccount->transactions()->latest('transaction_date')->paginate(20)
            : collect();

        $totalDeposits = $member->savingsAccount
            ? $member->savingsAccount->transactions()->where('type', 'deposit')->sum('amount')
            : 0;
        $totalWithdrawals = $member->savingsAccount
            ? $member->savingsAccount->transactions()->where('type', 'withdrawal')->where('status', 'completed')->sum('amount')
            : 0;

        return view('members.savings-detail', compact('member', 'transactions', 'totalDeposits', 'totalWithdrawals'));
    }

    public function loansDetail(Member $member): \Illuminate\View\View
    {
        $loans = $member->loans()->latest()->paginate(20);

        return view('members.loans-detail', compact('member', 'loans'));
    }

    public function purchasesDetail(Member $member): \Illuminate\View\View
    {
        $orders = $member->purchaseOrders()->with('product')->latest()->paginate(20);

        return view('members.purchases-detail', compact('member', 'orders'));
    }

    public function bulkUpdateStatus(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:members,id',
            'status' => 'required|in:' . implode(',', array_column(MemberStatus::cases(), 'value')),
        ]);

        $count = BulkUpdateStatus::run($validated['member_ids'], $validated['status']);

        return back()->with('success', "Successfully updated {$count} member(s) to " . ucfirst($validated['status']) . " status.");
    }

    public function approve(Member $member): \Illuminate\Http\RedirectResponse
    {
        try {
            ApproveMemberApplication::run($member);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Member approved successfully. ' . (!empty($member->email) ? 'Welcome email sent.' : ''));
    }

    public function reject(Member $member): \Illuminate\Http\RedirectResponse
    {
        try {
            RejectMemberApplication::run($member);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Member registration rejected.');
    }
}
