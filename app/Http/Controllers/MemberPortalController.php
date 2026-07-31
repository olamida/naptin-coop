<?php

namespace App\Http\Controllers;

use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\UpdateGuarantor;
use App\Models\Loan;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\SavingsTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\CartService;
use Illuminate\Support\Facades\Log;

class MemberPortalController extends Controller
{
    private function member()
    {
        return Auth::user()->member;
    }

    private function cartService(): CartService
    {
        return new CartService('member', Auth::id(), $this->member()->id);
    }

    public function index(): \Illuminate\View\View
    {
        $member = $this->member();

        $savingsAccount = $member->savingsAccount;
        $shareAccount = $member->shareAccount;
        $activeLoans = $member->loans()
            ->with('loanProduct')
            ->whereIn('status', ['disbursed', 'repaying'])
            ->get();
        $recentSavings = $savingsAccount ? $savingsAccount->transactions()->latest()->take(5)->get() : collect();
        $recentPurchases = $member->purchaseOrders()->with('product')->latest()->take(5)->get();

        $totalSavings = $savingsAccount ? $savingsAccount->balance : 0;
        $totalShares = $shareAccount ? $shareAccount->total_value : 0;
        $shareCount = $shareAccount ? $shareAccount->total_shares : 0;
        $totalLoanBalance = $activeLoans->sum('outstanding');
        $pendingGuarantorCount = $member->guarantorRequests()->where('status', 'pending')->count();
        $pendingWithdrawals = $savingsAccount
            ? $savingsAccount->transactions()->where('type', 'withdrawal')->where('status', 'pending')->count()
            : 0;
        $pendingWithdrawalAmount = $savingsAccount
            ? $savingsAccount->transactions()->where('type', 'withdrawal')->where('status', 'pending')->sum('amount')
            : 0;

        $nextDue = null;
        foreach ($activeLoans as $loan) {
            $schedule = $loan->schedules()->where('status', 'unpaid')->orderBy('due_date')->first();
            $candidate = $schedule
                ? (object) [
                    'loan' => $loan,
                    'amount' => (float) $schedule->principal_amount + (float) $schedule->interest_amount,
                    'due_date' => $schedule->due_date,
                ]
                : (object) [
                    'loan' => $loan,
                    'amount' => (float) $loan->monthly_repayment,
                    'due_date' => now()->addMonth()->startOfMonth(),
                ];

            if (! $nextDue || $candidate->due_date < $nextDue->due_date) {
                $nextDue = $candidate;
            }
        }
        if ($nextDue) {
            $nextDue->days_until = max(now()->startOfDay()->diffInDays($nextDue->due_date->startOfDay(), false), 0);
            $nextDue->overdue = now()->startOfDay()->gt($nextDue->due_date->startOfDay());
        }

        $emergencyProduct = LoanProduct::where('slug', 'emergency')
            ->orWhere('name', 'like', '%emergency%')
            ->first();
        $milestoneTarget = $emergencyProduct ? (float) $emergencyProduct->max_amount : 100000;
        $milestoneSavings = round($milestoneTarget / 3, 2);
        $milestonePercent = $milestoneSavings > 0 ? min(round(($totalSavings / $milestoneSavings) * 100), 100) : 0;
        $milestoneRemaining = max(round($milestoneSavings - $totalSavings, 2), 0);

        return view('portal.dashboard', compact(
            'member', 'savingsAccount', 'shareAccount', 'activeLoans',
            'recentSavings', 'recentPurchases', 'totalSavings', 'totalShares', 'shareCount',
            'totalLoanBalance', 'pendingGuarantorCount', 'pendingWithdrawals', 'pendingWithdrawalAmount',
            'nextDue', 'milestoneTarget', 'milestoneSavings', 'milestonePercent', 'milestoneRemaining'
        ));
    }

    public function savings(): \Illuminate\View\View
    {
        $member = $this->member();
        $account = $member->savingsAccount;
        $transactions = $account ? $account->transactions()->latest()->paginate(15) : collect();
        $totalDeposits = $account ? $account->transactions()->where('type', 'deposit')->where('status', 'completed')->sum('amount') : 0;
        $totalWithdrawals = $account ? $account->transactions()->where('type', 'withdrawal')->where('status', 'completed')->sum('amount') : 0;
        $pendingWithdrawals = $account ? $account->transactions()->where('type', 'withdrawal')->where('status', 'pending')->count() : 0;

        return view('portal.savings', compact('member', 'account', 'transactions', 'totalDeposits', 'totalWithdrawals', 'pendingWithdrawals'));
    }

    public function requestWithdrawal(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $member = $this->member();
        $account = $member->savingsAccount;

        if (!$account) {
            return back()->withErrors(['error' => 'You do not have a savings account.']);
        }

        $amount = round($validated['amount'], 2);

        if ($amount > $account->balance) {
            return back()->withErrors([
                'amount' => 'Insufficient balance. Available: ₦' . number_format($account->balance, 2),
            ])->withInput();
        }

        $transaction = SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'reference' => 'SAV/WTH/' . strtoupper(Str::random(8)),
            'type' => 'withdrawal',
            'amount' => $amount,
            'balance_before' => $account->balance,
            'balance_after' => $account->balance,
            'source' => 'member_request',
            'notes' => $validated['notes'] ?? 'Withdrawal requested by member',
            'transaction_date' => now(),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Withdrawal request of ₦' . number_format($amount, 2) . ' submitted. Reference: ' . $transaction->reference);
    }

    public function loans(): \Illuminate\View\View
    {
        $member = $this->member();
        $loans = $member->loans()->with('loanProduct')->latest()->paginate(15);

        return view('portal.loans', compact('member', 'loans'));
    }

    public function loanDetail(Loan $loan): \Illuminate\View\View
    {
        $member = $this->member();

        if ($loan->member_id !== $member->id) {
            abort(403, 'You can only view your own loans.');
        }

        $loan->load(['repayments', 'schedules', 'loanProduct', 'approvedBy', 'guarantors.member', 'parentLoan', 'topupLoans']);

        return view('portal.loan-detail', compact('member', 'loan'));
    }

    public function loanApplyForm(): \Illuminate\View\View
    {
        $member = $this->member();
        $loanProducts = LoanProduct::where('enabled', true)->orderBy('name')->get();
        $otherMembers = \App\Models\Member::where('id', '!=', $member->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $savingsBalance = (float) ($member->savingsAccount?->balance ?? 0);

        $guarantorExposures = \App\Models\LoanGuarantor::query()
            ->where('status', \App\Enums\GuarantorStatus::Accepted->value)
            ->whereHas('loan', fn($q) => $q->whereIn('status', ['approved', 'disbursed', 'repaying']))
            ->with('loan:id,outstanding')
            ->get()
            ->groupBy('member_id')
            ->map(fn($group) => round((float) $group->sum(fn($g) => (float) $g->loan->outstanding), 2));

        $guarantorLimit = 500000;

        $guarantorList = $otherMembers->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->full_name ?? ($m->first_name . ' ' . $m->last_name),
            'staff' => $m->staff_id_display ?? $m->staff_id,
            'exposure' => round((float) ($guarantorExposures[$m->id] ?? 0), 2),
        ])->values();

        return view('portal.loan-apply', compact(
            'member',
            'loanProducts',
            'otherMembers',
            'savingsBalance',
            'guarantorExposures',
            'guarantorLimit',
            'guarantorList'
        ));
    }

    public function submitLoanApplication(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'loan_product_id' => 'required|exists:loan_products,id',
            'type' => 'required|in:regular,emergency,educational,special',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'tenure_months' => 'required|integer|min:1|max:120',
            'purpose' => 'nullable|string|max:1000',
            'guarantor_ids' => 'nullable|array',
            'guarantor_ids.*' => 'exists:members,id',
        ]);

        $member = $this->member();
        $product = LoanProduct::find($validated['loan_product_id']);

        if ($product) {
            if ($product->max_loans_per_member) {
                $activeCount = Loan::where('member_id', $member->id)
                    ->where('loan_product_id', $product->id)
                    ->whereIn('status', ['pending', 'approved', 'disbursed', 'repaying'])
                    ->count();

                if ($activeCount >= $product->max_loans_per_member) {
                    return back()->withErrors([
                        'loan_product_id' => "You already have {$activeCount} active loan(s) for {$product->name}. Maximum allowed: {$product->max_loans_per_member}.",
                    ])->withInput();
                }
            }

            if ($product->max_total_amount_per_member) {
                $totalOutstanding = Loan::where('member_id', $member->id)
                    ->where('loan_product_id', $product->id)
                    ->whereIn('status', ['pending', 'approved', 'disbursed', 'repaying'])
                    ->sum('outstanding');

                $newTotal = $totalOutstanding + $validated['amount'];
                if ($newTotal > $product->max_total_amount_per_member) {
                    return back()->withErrors([
                        'amount' => "Your total outstanding for {$product->name} would be &#8358;" . number_format($newTotal, 2) . ". Maximum allowed: &#8358;" . number_format($product->max_total_amount_per_member, 2) . ".",
                    ])->withInput();
                }
            }

            if ($validated['amount'] > $product->max_amount) {
                return back()->withErrors([
                    'amount' => "Amount exceeds the maximum of &#8358;" . number_format($product->max_amount, 2) . " for {$product->name}.",
                ])->withInput();
            }
            if ($validated['amount'] < $product->min_amount) {
                return back()->withErrors([
                    'amount' => "Amount is below the minimum of &#8358;" . number_format($product->min_amount, 2) . " for {$product->name}.",
                ])->withInput();
            }
            if ($validated['tenure_months'] > $product->max_term_months) {
                return back()->withErrors([
                    'tenure_months' => "Tenure exceeds the maximum of {$product->max_term_months} months for {$product->name}.",
                ])->withInput();
            }
        }

        $validated['member_id'] = $member->id;

        try {
            $loan = CreateLoan::run($validated);

            try {
                $reviewerUsers = \App\Models\User::where('id', '!=', auth()->id())->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['super-admin', 'admin', 'loan-officer', 'treasurer']);
                })->get();
                foreach ($reviewerUsers as $user) {
                    $user->notify(new \App\Notifications\LoanAppliedNotification($loan));
                }
            } catch (\Exception $e) {
                \Log::error('Notification failed: ' . $e->getMessage());
            }

            return redirect()->route('portal.loan-detail', $loan)
                ->with('success', 'Loan application submitted successfully. Number: ' . $loan->loan_number);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function requestDeposit(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_evidence' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $member = $this->member();
        $account = $member->savingsAccount;

        if (!$account) {
            return back()->withErrors(['error' => 'You do not have a savings account.']);
        }

        $amount = round($validated['amount'], 2);
        $evidencePath = null;

        if ($request->hasFile('payment_evidence')) {
            $evidencePath = $request->file('payment_evidence')->store('payment-evidence', 'public');
        }

        $transaction = app(\App\Services\SavingsService::class)->recordDeposit(
            $member->id, $amount, $validated['notes'] ?? null, 'member_request', $evidencePath
        );

        if ($transaction->status === 'completed') {
            return back()->with('success', 'Deposit of ₦' . number_format($amount, 2) . ' auto-approved and credited.');
        }

        try {
            $approverUsers = \App\Models\User::where('id', '!=', auth()->id())->whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'treasurer']);
            })->get();
            foreach ($approverUsers as $user) {
                $user->notify(new \App\Notifications\DepositRecordedNotification($transaction));
            }
        } catch (\Exception $e) {
            \Log::error('Notification failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Deposit request of &#8358;' . number_format($amount, 2) . ' submitted for confirmation. Reference: ' . $transaction->reference);
    }

    public function shares(): \Illuminate\View\View
    {
        $member = $this->member();
        $account = $member->shareAccount;
        $transactions = $account ? $account->transactions()->latest()->paginate(15) : collect();

        return view('portal.shares', compact('member', 'account', 'transactions'));
    }

    public function purchases(): \Illuminate\View\View
    {
        $member = $this->member();
        $orders = $member->purchaseOrders()
            ->with('product')
            ->selectRaw('order_group, product_id, payment_type, status, MIN(created_at) as created_at, SUM(total_amount) as total_amount, SUM(quantity) as quantity')
            ->groupBy('order_group', 'product_id', 'payment_type', 'status')
            ->latest('created_at')
            ->paginate(15);

        return view('portal.purchases', compact('member', 'orders'));
    }

    public function orderProducts(Request $request): \Illuminate\View\View
    {
        $query = \App\Models\Product::where('enabled', true)->where('stock_quantity', '>', 0);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($minPrice = $request->input('min_price')) {
            $query->where('unit_price', '>=', $minPrice);
        }

        if ($maxPrice = $request->input('max_price')) {
            $query->where('unit_price', '<=', $maxPrice);
        }

        $sort = $request->input('sort', 'newest');
        $query = match($sort) {
            'price_asc' => $query->orderBy('unit_price', 'asc'),
            'price_desc' => $query->orderBy('unit_price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        $products = $query->get();

        $priceRange = [
            'min' => \App\Models\Product::where('enabled', true)->where('stock_quantity', '>', 0)->min('unit_price') ?? 0,
            'max' => \App\Models\Product::where('enabled', true)->where('stock_quantity', '>', 0)->max('unit_price') ?? 0,
        ];

        return view('portal.order-products', compact('products', 'priceRange'));
    }

    public function cart(): \Illuminate\View\View
    {
        ['items' => $items, 'total' => $total] = $this->cartService()->resolveCartItems();

        return view('portal.cart', ['items' => $items, 'total' => $total]);
    }

    public function checkout(): \Illuminate\View\View
    {
        ['items' => $items, 'total' => $total] = $this->cartService()->resolveCartItems();

        $member = $this->member();

        return view('portal.checkout', ['items' => $items, 'total' => $total, 'member' => $member]);
    }

    public function processCheckout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'payment_type' => 'required|in:cash,hire_purchase',
            'monthly_repayment' => 'required_if:payment_type,hire_purchase|nullable|numeric|min:0',
        ]);

        try {
            $orders = $this->cartService()->processCheckout($this->member()->id, $request->payment_type, $request->monthly_repayment);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('portal.purchases')
            ->with('success', count($orders) . ' item(s) ordered successfully.');
    }

    public function guarantors(): \Illuminate\View\View
    {
        $member = $this->member();
        $guarantorRequests = $member->guarantorRequests()
            ->with('loan.member')
            ->latest()
            ->paginate(15);

        return view('portal.guarantors', compact('member', 'guarantorRequests'));
    }

    public function updateGuarantor(\Illuminate\Http\Request $request, LoanGuarantor $guarantor): \Illuminate\Http\RedirectResponse
    {
        $member = $this->member();

        if ($guarantor->member_id !== $member->id) {
            return back()->withErrors(['error' => 'You are not authorized to respond to this request.']);
        }

        if ($guarantor->status->value !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been responded to.']);
        }

        $validated = $request->validate([
            'status' => 'required|in:accepted,declined',
            'notes' => 'nullable|string|max:500',
        ]);

        $loan = $guarantor->loan;
        UpdateGuarantor::run($loan, $guarantor, $validated['status'], $validated['notes'] ?? null, $request->ip(), $request->userAgent());

        $statusText = $validated['status'] === 'accepted' ? 'accepted' : 'declined';
        return back()->with('success', "You have {$statusText} the guarantor request for loan {$loan->loan_number}.");
    }

    public function add_to_cart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $counts = $this->cartService()->add((int) $request->product_id, $request->quantity);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Added to cart.',
                'cart_count' => $counts['cart_count'],
                'cart_quantity' => $counts['cart_quantity'],
            ]);
        }

        return back()->with('success', 'Product added to cart.');
    }

    public function update_cart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $counts = $this->cartService()->update((int) $request->product_id, $request->quantity);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart updated.',
                'cart_count' => $counts['cart_count'],
                'cart_quantity' => $counts['cart_quantity'],
            ]);
        }

        return back()->with('success', 'Cart updated.');
    }

    public function remove_from_cart(Request $request)
    {
        $counts = $this->cartService()->remove((int) $request->product_id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'cart_count' => $counts['cart_count'],
                'cart_quantity' => $counts['cart_quantity'],
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear_cart(Request $request)
    {
        $this->cartService()->clear();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared.',
                'cart_count' => 0,
                'cart_quantity' => 0,
            ]);
        }

        return back()->with('success', 'Cart cleared.');
    }

    public function cart_count()
    {
        return response()->json($this->cartService()->counts());
    }

    public function notifications(): \Illuminate\View\View
    {
        $member = $this->member();
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return view('portal.notifications', compact('member', 'notifications', 'unreadCount'));
    }

    public function markRead($notificationId): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(): \Illuminate\Http\RedirectResponse
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
