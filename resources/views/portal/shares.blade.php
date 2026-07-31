<x-portal-layout title="My Shares">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'My Shares']]" />

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-[16px] bg-slate-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 text-2xl">trending_up</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-slate-500">Total Share Value</p>
                    <p class="text-3xl font-bold text-[#0F172A]">₦{{ number_format($account ? $account->total_value : 0, 2) }}</p>
                    @if ($account)
                        <p class="text-xs text-slate-400 mt-0.5">{{ number_format($account->total_shares) }} share(s) @ ₦{{ number_format($account->share_price, 2) }} each</p>
                    @endif
                </div>
                @if ($account && $account->total_shares > 0)
                    <a href="{{ route('receipts.share-certificate', $account->id) }}" target="_blank" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2 flex-shrink-0">
                        <span class="material-symbols-outlined text-lg">workspace_premium</span>
                        View Certificate
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h3 class="font-semibold text-[#0F172A]">Share Transaction History</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-2.5 font-medium text-slate-600 text-xs">Date</th>
                        <th class="text-left px-5 py-2.5 font-medium text-slate-600 text-xs">Type</th>
                        <th class="text-right px-5 py-2.5 font-medium text-slate-600 text-xs">Shares</th>
                        <th class="text-right px-5 py-2.5 font-medium text-slate-600 text-xs">Amount</th>
                        <th class="text-right px-5 py-2.5 font-medium text-slate-600 text-xs">Total After</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($transactions as $tx)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-xs text-slate-500">{{ $tx->transaction_date?->format('d M Y') ?? $tx->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $tx->type === 'purchase' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">{{ ucfirst($tx->type) }}</span>
                            </td>
                            <td class="px-5 py-3 text-right text-xs text-slate-600">{{ $tx->shares }}</td>
                            <td class="px-5 py-3 text-right font-medium text-xs text-[#0F172A]">₦{{ number_format($tx->amount, 2) }}</td>
                            <td class="px-5 py-3 text-right text-xs font-medium text-blue-600">₦{{ number_format($tx->balance_after, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-4">
                                <x-empty-state icon="trending_up" title="No share transactions yet"
                                    message="Your share purchases will appear here once recorded." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}
    </div>
</x-portal-layout>
