<x-portal-layout title="My Shares">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'My Shares']]" />

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 text-2xl">trending_up</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-500">Total Share Value</p>
                    <p class="text-3xl font-bold text-gray-900">₦{{ number_format($account ? $account->total_value : 0, 2) }}</p>
                    @if ($account)
                        <p class="text-xs text-gray-400 mt-0.5">{{ number_format($account->total_shares) }} share(s) @ ₦{{ number_format($account->share_price, 2) }} each</p>
                    @endif
                </div>
                @if ($account && $account->total_shares > 0)
                    <a href="{{ route('receipts.share-certificate', $account->id) }}" target="_blank" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2 flex-shrink-0">
                        <span class="material-symbols-outlined text-lg">workspace_premium</span>
                        View Certificate
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Share Transaction History</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-2.5 font-medium text-gray-600 text-xs">Date</th>
                        <th class="text-left px-5 py-2.5 font-medium text-gray-600 text-xs">Type</th>
                        <th class="text-right px-5 py-2.5 font-medium text-gray-600 text-xs">Shares</th>
                        <th class="text-right px-5 py-2.5 font-medium text-gray-600 text-xs">Amount</th>
                        <th class="text-right px-5 py-2.5 font-medium text-gray-600 text-xs">Total After</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transactions as $tx)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $tx->transaction_date?->format('d M Y') ?? $tx->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $tx->type === 'purchase' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">{{ ucfirst($tx->type) }}</span>
                            </td>
                            <td class="px-5 py-3 text-right text-xs text-gray-600">{{ $tx->shares }}</td>
                            <td class="px-5 py-3 text-right font-medium text-xs text-gray-900">₦{{ number_format($tx->amount, 2) }}</td>
                            <td class="px-5 py-3 text-right text-xs font-medium text-blue-600">₦{{ number_format($tx->balance_after, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                                <span class="material-symbols-outlined text-3xl text-gray-300 mb-2 block">trending_up</span>
                                No share transactions yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}
    </div>
</x-portal-layout>
