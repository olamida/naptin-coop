<x-app-layout title="{{ $member->first_name }} {{ $member->last_name }} - Savings">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('members.show', $member) }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Savings Records</h2>
                <p class="text-sm text-gray-500">{{ $member->full_name }} &middot; {{ $member->staff_id }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Total Deposits</p>
                <p class="text-xl font-bold text-green-600">₦{{ number_format($totalDeposits, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Total Withdrawals</p>
                <p class="text-xl font-bold text-red-600">₦{{ number_format($totalWithdrawals, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Current Balance</p>
                <p class="text-xl font-bold text-blue-600">₦{{ number_format($member->savingsAccount->balance ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Reference</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Balance After</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transactions as $txn)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ $txn->transaction_date?->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $txn->reference }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $txn->type === 'deposit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($txn->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium {{ $txn->type === 'deposit' ? 'text-green-700' : 'text-red-700' }}">
                                {{ $txn->type === 'deposit' ? '+' : '-' }}₦{{ number_format($txn->amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-xs">₦{{ number_format($txn->balance_after, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($txn->type === 'deposit')
                                    <a href="{{ route('receipts.savings-deposit', $txn) }}" target="_blank" class="text-gray-500 hover:text-gray-700">
                                        <span class="material-symbols-outlined text-[16px]">print</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}
    </div>
</x-app-layout>
