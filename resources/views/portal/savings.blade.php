<x-portal-layout title="My Savings">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'My Savings']]" />

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-green-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-600 text-2xl">savings</span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Savings Balance</p>
                        <p class="text-3xl font-bold text-gray-900">₦{{ number_format($account ? $account->balance : 0, 2) }}</p>
                        @if ($account)
                            <p class="text-xs text-gray-400 mt-0.5">Account: {{ $account->account_number }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if ($pendingWithdrawals > 0)
                        <span class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg text-xs font-medium flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">pending</span>
                            {{ $pendingWithdrawals }} pending withdrawal(s)
                        </span>
                    @endif
                </div>
            </div>
            @if ($account)
                <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-xs text-gray-500">Total Deposits</p>
                        <p class="text-sm font-semibold text-green-600">₦{{ number_format($totalDeposits, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total Withdrawals</p>
                        <p class="text-sm font-semibold text-red-600">₦{{ number_format($totalWithdrawals, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Net Savings</p>
                        <p class="text-sm font-semibold text-blue-600">₦{{ number_format($totalDeposits - $totalWithdrawals, 2) }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Deposit Request --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="{ showForm: false }">
            <div class="flex items-center justify-between cursor-pointer" @click="showForm = !showForm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-600 text-xl">add_circle</span>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Request Deposit</p>
                        <p class="text-xs text-gray-500">Submit a deposit request with optional payment evidence</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-gray-400 transition-transform" :class="showForm ? 'rotate-180' : ''">expand_more</span>
            </div>

            <div x-show="showForm" x-transition class="mt-4 pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('portal.savings.deposit') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (&#8358;) *</label>
                            <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required min="0.01"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 outline-none"
                                placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Evidence (optional)</label>
                            <input type="file" name="payment_evidence" accept="image/*"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 outline-none file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                            <p class="text-[11px] text-gray-400 mt-1">Upload payment screenshot/receipt (jpg, png, max 5MB)</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 outline-none"
                            placeholder="E.g. Transfer to GTBank, Ref: 1234567890">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            Submit Deposit Request
                        </button>
                        <span class="text-xs text-gray-400">Requires admin confirmation</span>
                    </div>
                </form>
            </div>
        </div>

        {{-- Withdrawal Request --}}
        @if ($account && $account->balance > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="{ showForm: false }">
                <div class="flex items-center justify-between cursor-pointer" @click="showForm = !showForm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-600 text-xl">account_balance_wallet</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Request Withdrawal</p>
                            <p class="text-xs text-gray-500">Submit a withdrawal request for admin approval</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-gray-400 transition-transform" :class="showForm ? 'rotate-180' : ''">expand_more</span>
                </div>

                <div x-show="showForm" x-transition class="mt-4 pt-4 border-t border-gray-100">
                    <form method="POST" action="{{ route('portal.savings.withdraw') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₦)</label>
                                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required min="0.01" max="{{ $account->balance }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 outline-none"
                                    placeholder="0.00">
                                <p class="text-xs text-gray-400 mt-1">Available: ₦{{ number_format($account->balance, 2) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
                                <input type="text" name="notes" value="{{ old('notes') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 outline-none"
                                    placeholder="Purpose of withdrawal">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Submit Withdrawal Request
                            </button>
                            <span class="text-xs text-gray-400">Requires admin approval</span>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Savings History</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-2.5 font-medium text-gray-600 text-xs">Date</th>
                        <th class="text-left px-5 py-2.5 font-medium text-gray-600 text-xs">Type</th>
                        <th class="text-left px-5 py-2.5 font-medium text-gray-600 text-xs">Notes</th>
                        <th class="text-left px-5 py-2.5 font-medium text-gray-600 text-xs">Status</th>
                        <th class="text-right px-5 py-2.5 font-medium text-gray-600 text-xs">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transactions as $tx)
                        @php
                            $statusStyles = [
                                'completed' => 'bg-green-100 text-green-700',
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'rejected' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $tx->transaction_date?->format('d M Y') ?? $tx->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $tx->type === 'deposit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($tx->type) }}</span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-600 max-w-[200px] truncate">{{ $tx->notes ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $statusStyles[$tx->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($tx->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-xs
                                {{ $tx->type === 'deposit' ? 'text-green-600' : ($tx->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $tx->type === 'deposit' ? '+' : '-' }}₦{{ number_format(abs($tx->amount), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                                <span class="material-symbols-outlined text-3xl text-gray-300 mb-2 block">savings</span>
                                No savings transactions yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}
    </div>
</x-portal-layout>
