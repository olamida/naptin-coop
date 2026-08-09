<x-app-layout title="Shares">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Shares']]" />
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Shares</h2>
                <p class="text-xs text-slate-500 mt-1">Track member share holdings, purchases and transactions</p>
            </div>
            <div class="flex items-center gap-2">
                @can('view-shares')
                    <a href="{{ route('shares.export') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-[10px] text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        Export
                    </a>
                @endcan
                @can('purchase-shares')
                    <a href="{{ route('shares.purchase') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-lg">add</span>
                        Purchase
                    </a>
                @endcan
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-slate-200">
            <a href="{{ route('shares.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-[#0F172A] text-[#0F172A] -mb-px">
                Transactions
            </a>
            <a href="{{ route('shares.accounts') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 -mb-px transition">
                Accounts
            </a>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Shares</p>
                <p class="mt-2 text-2xl font-bold text-purple-700">{{ number_format($stats['total_shares']) }}</p>
                <p class="text-xs text-slate-400 mt-1">Units held</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Portfolio Value</p>
                <p class="mt-2 text-2xl font-mono font-bold text-emerald-700 truncate" title="₦{{ number_format($stats['total_value'], 2) }}">₦{{ number_format($stats['total_value'], 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Total worth</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Transactions</p>
                <p class="mt-2 text-2xl font-bold text-[#0F172A]">{{ $stats['total_transactions'] }}</p>
                <p class="text-xs text-slate-400 mt-1">All time volume</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Shareholders</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['members_with_shares'] }}</p>
                <p class="text-xs text-slate-400 mt-1">Active members</p>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                <x-search-autocomplete :endpoint="route('shares.search')" name="search" placeholder="Member name or Staff ID..." />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
                <select name="type" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Types</option>
                    <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Sale</option>
                    <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="dividend" {{ request('type') === 'dividend' ? 'selected' : '' }}>Dividend</option>
                </select>
            </div>
            <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2 rounded-[10px] text-sm font-medium transition">Filter</button>
            @if (request()->hasAny(['search', 'type']))
                <a href="{{ route('shares.index') }}" class="text-sm text-slate-500 hover:underline py-2">Clear</a>
            @endif
        </form>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[12px] text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-[16px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Shares</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($transactions as $txn)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 text-xs text-slate-600">{{ $txn->transaction_date?->format('d M Y') }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $txn->reference }}</td>
                                <td class="px-5 py-3.5">
                                    @if ($txn->shareAccount?->member)
                                        <a href="{{ route('members.show', $txn->shareAccount->member) }}" class="text-slate-800 font-medium hover:text-blue-600 transition">{{ $txn->shareAccount->member->first_name }} {{ $txn->shareAccount->member->last_name }}</a>
                                    @else
                                        <span class="text-slate-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full border
                                        {{ $txn->type === 'purchase' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                        {{ $txn->type === 'sale' ? 'bg-orange-50 text-orange-700 border-orange-200' : '' }}
                                        {{ $txn->type === 'transfer' ? 'bg-purple-50 text-purple-700 border-purple-200' : '' }}
                                        {{ $txn->type === 'dividend' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}">
                                        {{ ucfirst($txn->type) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono text-sm font-medium text-slate-800">{{ number_format($txn->shares) }}</td>
                                <td class="px-5 py-3.5 text-right font-mono text-sm font-medium text-slate-800">₦{{ number_format($txn->amount, 2) }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if ($txn->type === 'purchase')
                                            <a href="{{ route('receipts.share-purchase', $txn) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition" title="Print Receipt">
                                                <span class="material-symbols-outlined text-[16px]">print</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">trending_up</span>
                                    <p class="text-sm">No transactions found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $transactions->links() }}

        <x-show-all-toggle />
    </div>
</x-app-layout>