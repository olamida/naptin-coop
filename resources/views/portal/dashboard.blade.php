<x-portal-layout title="My Dashboard">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            @if ($member->photo_url)
                <img src="{{ $member->photo_url }}" alt="" class="w-12 h-12 rounded-full object-cover border-2 border-blue-200">
            @else
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-sm font-bold">{{ $member->initials }}</div>
            @endif
            <div>
                <h2 class="text-xl font-bold text-gray-800">Welcome, {{ $member->first_name }}!</h2>
                <p class="text-sm text-gray-500">{{ $member->staff_id }} &middot; {{ $member->region->name ?? '' }}</p>
            </div>
        </div>

        @if ($pendingGuarantorCount > 0)
            <a href="{{ route('portal.guarantors') }}" class="block bg-amber-50 border border-amber-200 rounded-xl p-4 hover:bg-amber-100 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
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
            <a href="{{ route('portal.savings') }}" class="block bg-yellow-50 border border-yellow-200 rounded-xl p-4 hover:bg-yellow-100 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center">
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('portal.savings') }}" class="stat-card bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-600 text-xl">savings</span>
                    </div>
                    <p class="text-xs text-gray-500 font-medium">Savings Balance</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">₦{{ number_format($totalSavings, 2) }}</p>
                @if ($savingsAccount)
                    <p class="text-[10px] text-gray-400 mt-1">Account: {{ $savingsAccount->account_number }}</p>
                @endif
            </a>

            <a href="{{ route('portal.shares') }}" class="stat-card bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-600 text-xl">trending_up</span>
                    </div>
                    <p class="text-xs text-gray-500 font-medium">Share Value</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">₦{{ number_format($totalShares, 2) }}</p>
                <p class="text-[10px] text-gray-400 mt-1">{{ number_format($shareCount) }} share(s)</p>
            </a>

            <a href="{{ route('portal.loans') }}" class="stat-card bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-amber-600 text-xl">account_balance</span>
                    </div>
                    <p class="text-xs text-gray-500 font-medium">Loan Balance</p>
                </div>
                <p class="text-2xl font-bold {{ $totalLoanBalance > 0 ? 'text-red-600' : 'text-gray-900' }}">₦{{ number_format($totalLoanBalance, 2) }}</p>
                <p class="text-[10px] text-gray-400 mt-1">{{ $activeLoans->count() }} active loan(s)</p>
            </a>

            <a href="{{ route('portal.purchases') }}" class="stat-card bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-purple-600 text-xl">shopping_cart</span>
                    </div>
                    <p class="text-xs text-gray-500 font-medium">My Purchases</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $recentPurchases->count() }}</p>
                <p class="text-[10px] text-gray-400 mt-1">Recent orders</p>
            </a>
        </div>

        @if ($activeLoans->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Active Loans</h3>
                    <a href="{{ route('portal.loans') }}" class="text-xs text-blue-600 hover:underline">View All</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ($activeLoans as $loan)
                        @php
                            $repaidPercent = $loan->amount > 0 ? round(($loan->total_repaid / $loan->amount) * 100, 1) : 0;
                        @endphp
                        <a href="{{ route('portal.loan-detail', $loan) }}" class="block px-5 py-4 hover:bg-gray-50 transition">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <span class="text-sm font-medium text-gray-800">{{ $loan->loan_number }}</span>
                                    <span class="text-xs text-gray-500 ml-2">{{ $loan->loanProduct->name ?? ucfirst($loan->type) }}</span>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                    {{ $loan->status === 'repaying' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ min($repaidPercent, 100) }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Repaid: ₦{{ number_format($loan->total_repaid, 2) }} ({{ $repaidPercent }}%)</span>
                                <span class="font-medium text-orange-600">Outstanding: ₦{{ number_format($loan->outstanding, 2) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Recent Savings</h3>
                    <a href="{{ route('portal.savings') }}" class="text-xs text-blue-600 hover:underline">View All</a>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-50">
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
                                <td class="px-5 py-3 text-xs text-gray-500">{{ $tx->created_at->format('M d, Y') }}</td>
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
                            <tr><td colspan="3" class="px-5 py-6 text-center text-xs text-gray-400">No transactions yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Recent Purchases</h3>
                    <a href="{{ route('portal.purchases') }}" class="text-xs text-blue-600 hover:underline">View All</a>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($recentPurchases as $order)
                            <tr>
                                <td class="px-5 py-3 font-medium text-gray-800 text-xs">{{ $order->product->name ?? 'N/A' }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $order->payment_type === 'cash' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="px-5 py-3 text-right font-medium text-gray-900 text-xs">₦{{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-6 text-center text-xs text-gray-400">No purchases yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('portal.products') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition">
            <span class="material-symbols-outlined text-lg">storefront</span>
            Browse Products & Order
        </a>
    </div>
</x-portal-layout>
