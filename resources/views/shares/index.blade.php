<x-app-layout title="Shares">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Shares']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Shares</h2>
            <div class="flex items-center gap-2">
                @can('view-shares')
                    <a href="{{ route('shares.export') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        Export
                    </a>
                @endcan
                @can('purchase-shares')
                    <a href="{{ route('shares.purchase') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition">+ Purchase</a>
                @endcan
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-gray-200">
            <a href="{{ route('shares.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px">
                Transactions
            </a>
            <a href="{{ route('shares.accounts') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 -mb-px transition">
                Accounts
            </a>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-purple-500 text-lg">trending_up</span>
                    <p class="text-xs text-gray-500">Total Shares</p>
                </div>
                <p class="text-xl font-bold text-purple-700">{{ number_format($stats['total_shares']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-green-500 text-lg">payments</span>
                    <p class="text-xs text-gray-500">Portfolio Value</p>
                </div>
                <p class="text-xl font-bold text-green-700">₦{{ number_format($stats['total_value'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-blue-500 text-lg">swap_horiz</span>
                    <p class="text-xs text-gray-500">Transactions</p>
                </div>
                <p class="text-xl font-bold">{{ $stats['total_transactions'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-amber-500 text-lg">people</span>
                    <p class="text-xs text-gray-500">Shareholders</p>
                </div>
                <p class="text-xl font-bold">{{ $stats['members_with_shares'] }}</p>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Member name, Staff ID, or Reference..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Type</label>
                <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Types</option>
                    <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Sale</option>
                    <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="dividend" {{ request('type') === 'dividend' ? 'selected' : '' }}>Dividend</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Filter</button>
            @if (request()->hasAny(['search', 'type']))
                <a href="{{ route('shares.index') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
            @endif
        </form>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Reference</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Member</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Shares</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transactions as $txn)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ $txn->transaction_date?->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $txn->reference }}</td>
                            <td class="px-4 py-3">
                                @if ($txn->shareAccount?->member)
                                    {{ $txn->shareAccount->member->first_name }} {{ $txn->shareAccount->member->last_name }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $txn->type === 'purchase' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $txn->type === 'sale' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $txn->type === 'transfer' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $txn->type === 'dividend' ? 'bg-green-100 text-green-700' : '' }}">
                                    {{ ucfirst($txn->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format($txn->shares) }}</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($txn->amount, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($txn->type === 'purchase')
                                    <a href="{{ route('receipts.share-purchase', $txn) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700">
                                        <span class="material-symbols-outlined text-[14px]">print</span>
                                        Receipt
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}

        <x-show-all-toggle />
    </div>
</x-app-layout>
