<x-portal-layout title="My Dashboard">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            @if ($member->photo_url)
                <img src="{{ $member->photo_url }}" alt="" class="w-12 h-12 rounded-full object-cover border-2 border-slate-200">
            @else
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#0F172A] to-slate-700 flex items-center justify-center text-white text-sm font-bold">{{ $member->initials }}</div>
            @endif
            <div>
                <h2 class="text-xl font-bold text-[#0F172A]">Welcome, {{ $member->first_name }}!</h2>
                <p class="text-sm text-slate-500">{{ $member->staff_id_display }} &middot; {{ $member->region->name ?? '' }}</p>
            </div>
        </div>

        {{-- Big Balance Card --}}
        <div x-data="{ showBalance: true }" class="relative overflow-hidden rounded-[20px] p-6 text-white"
             style="background: linear-gradient(135deg, #0F172A 0%, #1e3a5f 55%, #10B981 130%);">
            <div class="absolute -top-10 -right-10 w-44 h-44 rounded-full bg-white/5"></div>
            <div class="absolute top-24 right-24 w-24 h-24 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] text-slate-300 tracking-wider uppercase">Available Savings Balance</p>
                    <button @click="showBalance = !showBalance" type="button"
                            class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                        <span class="material-symbols-outlined text-lg" x-text="showBalance ? 'visibility' : 'visibility_off'">visibility</span>
                    </button>
                </div>
                <p class="mt-2 text-3xl lg:text-4xl font-mono font-bold" x-show="showBalance">₦{{ number_format($totalSavings, 2) }}</p>
                <p class="mt-2 text-3xl lg:text-4xl font-mono font-bold" x-show="!showBalance">₦ &bull;&bull;&bull;&bull;&bull;&bull;</p>
                @if ($savingsAccount)
                    <p class="text-xs text-slate-300 mt-2">Account: {{ $savingsAccount->account_number }}</p>
                @endif
                <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-white/10 rounded-[12px] px-4 py-3">
                        <p class="text-[10px] text-slate-300 uppercase tracking-wider">Share Value</p>
                        <p class="font-mono font-bold mt-0.5">₦{{ number_format($totalShares, 2) }}</p>
                    </div>
                    <div class="bg-white/10 rounded-[12px] px-4 py-3">
                        <p class="text-[10px] text-slate-300 uppercase tracking-wider">Loan Balance</p>
                        <p class="font-mono font-bold mt-0.5 {{ $totalLoanBalance > 0 ? 'text-amber-300' : '' }}">₦{{ number_format($totalLoanBalance, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Next Due Card --}}
        @if ($nextDue)
            <a href="{{ route('portal.loans') }}" class="block bg-white rounded-[16px] shadow-sm border {{ $nextDue->overdue ? 'border-rose-200 bg-rose-50' : 'border-slate-200' }} p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-[16px] {{ $nextDue->overdue ? 'bg-rose-100' : 'bg-amber-100' }} flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined {{ $nextDue->overdue ? 'text-rose-600' : 'text-amber-600' }} text-xl">{{ $nextDue->overdue ? 'error' : 'schedule' }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold {{ $nextDue->overdue ? 'text-rose-800' : 'text-[#0F172A]' }}">
                                @if ($nextDue->overdue)
                                    Loan repayment overdue
                                @else
                                    Loan repayment due in {{ $nextDue->days_until }} day{{ $nextDue->days_until === 1 ? '' : 's' }}
                                @endif
                            </p>
                            <p class="text-xs {{ $nextDue->overdue ? 'text-rose-600' : 'text-slate-500' }} truncate">
                                ₦{{ number_format($nextDue->amount, 2) }} &middot; {{ $nextDue->loan->loan_number }}
                            </p>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 bg-[#0F172A] text-white text-xs font-medium rounded-[10px] flex-shrink-0">Pay Now</span>
                </div>
            </a>
        @else
            <a href="{{ route('portal.loan-apply') }}" class="block bg-white rounded-[16px] shadow-sm border border-slate-200 p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-[16px] bg-emerald-100 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-emerald-600 text-xl">check_circle</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-[#0F172A]">No loan repayment due</p>
                            <p class="text-xs text-slate-500">You're all clear &mdash; need funds? Apply for a loan.</p>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 bg-[#0F172A] text-white text-xs font-medium rounded-[10px] flex-shrink-0">Apply</span>
                </div>
            </a>
        @endif

        {{-- Quick Actions 2x2 --}}
        <div>
            <h3 class="text-sm font-semibold text-[#0F172A] mb-3">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('portal.savings') }}" class="flex items-center gap-3 bg-white rounded-[16px] border border-slate-200 p-4 hover:shadow-sm hover:border-slate-300 transition">
                    <span class="w-9 h-9 rounded-[12px] bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-amber-600 text-lg">money_off</span>
                    </span>
                    <span class="text-sm font-medium text-[#0F172A]">Request Withdrawal</span>
                </a>
                <a href="{{ route('portal.loan-apply') }}" class="flex items-center gap-3 bg-white rounded-[16px] border border-slate-200 p-4 hover:shadow-sm hover:border-slate-300 transition">
                    <span class="w-9 h-9 rounded-[12px] bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-blue-600 text-lg">request_quote</span>
                    </span>
                    <span class="text-sm font-medium text-[#0F172A]">Apply Loan</span>
                </a>
                <a href="{{ route('portal.products') }}" class="flex items-center gap-3 bg-white rounded-[16px] border border-slate-200 p-4 hover:shadow-sm hover:border-slate-300 transition">
                    <span class="w-9 h-9 rounded-[12px] bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-emerald-600 text-lg">storefront</span>
                    </span>
                    <span class="text-sm font-medium text-[#0F172A]">Buy Product</span>
                </a>
                <a href="{{ route('portal.guarantors') }}" class="flex items-center gap-3 bg-white rounded-[16px] border border-slate-200 p-4 hover:shadow-sm hover:border-slate-300 transition">
                    <span class="w-9 h-9 rounded-[12px] bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-purple-600 text-lg">group_add</span>
                    </span>
                    <span class="text-sm font-medium text-[#0F172A]">My Guarantor Requests</span>
                </a>
            </div>
        </div>

        {{-- Gamification Milestone --}}
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-[#0F172A] flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-500 text-lg">flag</span>
                    Next Milestone
                </p>
                @if ($milestoneRemaining > 0)
                    <span class="text-xs text-slate-500 font-mono">₦{{ number_format($totalSavings, 0) }} / ₦{{ number_format($milestoneSavings, 0) }}</span>
                @endif
            </div>
            @if ($milestoneRemaining > 0)
                <div class="w-full bg-slate-100 rounded-full h-2.5 mb-2">
                    <div class="bg-gradient-to-r from-[#0F172A] to-[#10B981] h-2.5 rounded-full transition-all" style="width: {{ $milestonePercent }}%"></div>
                </div>
                <p class="text-xs text-slate-500">Save <strong class="text-[#0F172A]">₦{{ number_format($milestoneRemaining, 0) }}</strong> more to unlock a <strong class="text-emerald-600">₦{{ number_format($milestoneTarget, 0) }} emergency loan</strong>.</p>
            @else
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs text-slate-500 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                        You've unlocked a <strong class="text-emerald-600">₦{{ number_format($milestoneTarget, 0) }} emergency loan</strong> based on your savings.
                    </p>
                    <a href="{{ route('portal.loan-apply') }}" class="flex-shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium px-3 py-2 rounded-[10px] transition">Apply Now</a>
                </div>
            @endif
        </div>

        @if ($pendingGuarantorCount > 0)
            <a href="{{ route('portal.guarantors') }}" class="block bg-amber-50 border border-amber-200 rounded-[16px] p-4 hover:bg-amber-100 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[16px] bg-amber-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-amber-600 text-xl">group_add</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-amber-800">Pending Guarantor Requests</p>
                            <p class="text-xs text-amber-600">You have {{ $pendingGuarantorCount }} guarantor request(s) waiting for your response</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-amber-400">chevron_right</span>
                </div>
            </a>
        @endif

        @if ($pendingWithdrawals > 0)
            <a href="{{ route('portal.savings') }}" class="block bg-yellow-50 border border-yellow-200 rounded-[16px] p-4 hover:bg-yellow-100 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[16px] bg-yellow-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-yellow-600 text-xl">pending</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-yellow-800">Pending Withdrawal Request</p>
                            <p class="text-xs text-yellow-600">{{ $pendingWithdrawals }} withdrawal request(s) totalling ₦{{ number_format($pendingWithdrawalAmount, 2) }} awaiting approval</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-yellow-400">chevron_right</span>
                </div>
            </a>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('portal.savings') }}" class="stat-card bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-[16px] bg-green-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-600 text-xl">savings</span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">Savings Balance</p>
                </div>
                <p class="text-2xl font-bold text-[#0F172A]">₦{{ number_format($totalSavings, 2) }}</p>
                @if ($savingsAccount)
                    <p class="text-[10px] text-slate-400 mt-1">Account: {{ $savingsAccount->account_number }}</p>
                @endif
            </a>

            @if (\App\Models\Company::instance()->moduleEnabled('shares'))
                <a href="{{ route('portal.shares') }}" class="stat-card bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-[16px] bg-blue-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600 text-xl">trending_up</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Share Value</p>
                    </div>
                    <p class="text-2xl font-bold text-[#0F172A]">₦{{ number_format($totalShares, 2) }}</p>
                    <p class="text-[10px] text-slate-400 mt-1">{{ number_format($shareCount) }} share(s)</p>
                </a>
            @endif

            <a href="{{ route('portal.loans') }}" class="stat-card bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-[16px] bg-amber-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-amber-600 text-xl">account_balance</span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">Loan Balance</p>
                </div>
                <p class="text-2xl font-bold {{ $totalLoanBalance > 0 ? 'text-red-600' : 'text-[#0F172A]' }}">₦{{ number_format($totalLoanBalance, 2) }}</p>
                <p class="text-[10px] text-slate-400 mt-1">{{ $activeLoans->count() }} active loan(s)</p>
            </a>

            <a href="{{ route('portal.purchases') }}" class="stat-card bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-[16px] bg-purple-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-purple-600 text-xl">shopping_cart</span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">My Purchases</p>
                </div>
                <p class="text-2xl font-bold text-[#0F172A]">{{ $recentPurchases->count() }}</p>
                <p class="text-[10px] text-slate-400 mt-1">Recent orders</p>
            </a>
        </div>

        @if ($activeLoans->isNotEmpty())
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-semibold text-[#0F172A]">Active Loans</h3>
                    <a href="{{ route('portal.loans') }}" class="text-xs text-blue-600 hover:underline">View All</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach ($activeLoans as $loan)
                        @php
                            $repaidPercent = $loan->amount > 0 ? round(($loan->total_repaid / $loan->amount) * 100, 1) : 0;
                        @endphp
                        <a href="{{ route('portal.loan-detail', $loan) }}" class="block px-5 py-4 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <span class="text-sm font-medium text-[#0F172A]">{{ $loan->loan_number }}</span>
                                    <span class="text-xs text-slate-500 ml-2">{{ $loan->loanProduct->name ?? ucfirst($loan->type) }}</span>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                    {{ $loan->status === 'repaying' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2 mb-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ min($repaidPercent, 100) }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-slate-500">
                                <span>Repaid: ₦{{ number_format($loan->total_repaid, 2) }} ({{ $repaidPercent }}%)</span>
                                <span class="font-medium text-orange-600">Outstanding: ₦{{ number_format($loan->outstanding, 2) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-semibold text-[#0F172A]">Recent Savings</h3>
                    <a href="{{ route('portal.savings') }}" class="text-xs text-blue-600 hover:underline">View All</a>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($recentSavings as $tx)
                            <tr>
                                <td class="px-5 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                        {{ $tx->type === 'deposit' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $tx->type === 'withdrawal' && $tx->status === 'completed' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $tx->type === 'withdrawal' && $tx->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $tx->type === 'interest' ? 'bg-blue-100 text-blue-700' : '' }}">
                                        @if ($tx->type === 'withdrawal' && $tx->status === 'pending')
                                            Pending
                                        @else
                                            {{ ucfirst($tx->type) }}
                                        @endif
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $tx->created_at->format('M d, Y') }}</td>
                                <td class="px-5 py-3 text-right font-medium text-xs
                                    {{ $tx->type === 'deposit' ? 'text-green-600' : ($tx->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                                    @if ($tx->status === 'pending')
                                        -₦{{ number_format($tx->amount, 2) }} (pending)
                                    @else
                                        {{ $tx->type === 'deposit' ? '+' : '-' }}₦{{ number_format(abs($tx->amount), 2) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-6 text-center text-xs text-slate-400">No transactions yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-semibold text-[#0F172A]">Recent Purchases</h3>
                    <a href="{{ route('portal.purchases') }}" class="text-xs text-blue-600 hover:underline">View All</a>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($recentPurchases as $order)
                            <tr>
                                <td class="px-5 py-3 font-medium text-[#0F172A] text-xs">{{ $order->product->name ?? 'N/A' }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $order->payment_type === 'cash' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="px-5 py-3 text-right font-medium text-[#0F172A] text-xs">₦{{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-6 text-center text-xs text-slate-400">No purchases yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('portal.products') }}" class="inline-flex items-center gap-2 bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2.5 rounded-[10px] text-sm font-medium transition">
            <span class="material-symbols-outlined text-lg">storefront</span>
            Browse Products & Order
        </a>
    </div>
</x-portal-layout>
