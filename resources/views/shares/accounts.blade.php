<x-app-layout title="Share Accounts">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Shares', 'url' => route('shares.index')], ['label' => 'Accounts']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Share Accounts</h2>
            <div class="flex items-center gap-2">
                @can('purchase-shares')
                    <a href="{{ route('shares.purchase') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition">+ Purchase</a>
                @endcan
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-gray-200">
            <a href="{{ route('shares.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 -mb-px transition">
                Transactions
            </a>
            <a href="{{ route('shares.accounts') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px">
                Accounts
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-gray-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Member name or Staff ID..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs text-gray-500 mb-1">Sort by Value</label>
                    <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest First</option>
                        <option value="highest" {{ request('sort') === 'highest' ? 'selected' : '' }}>Highest Value</option>
                        <option value="lowest" {{ request('sort') === 'lowest' ? 'selected' : '' }}>Lowest Value</option>
                    </select>
                </div>
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Search</button>
                @if (request()->hasAny(['search', 'sort']))
                    <a href="{{ route('shares.accounts') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
                @endif
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <p class="text-sm text-gray-500">Total Shares</p>
                <p class="text-3xl font-bold text-purple-700">{{ number_format($totalShares) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <p class="text-sm text-gray-500">Total Value</p>
                <p class="text-3xl font-bold text-green-700">₦{{ number_format($totalValue, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Member</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Total Shares</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Share Price</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Total Value</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($accounts as $account)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $account->member->first_name ?? '' }} {{ $account->member->last_name ?? '' }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($account->total_shares) }}</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($account->share_price, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($account->total_value, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $account->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($account->total_shares > 0)
                                    <a href="{{ route('receipts.share-certificate', $account->id) }}" target="_blank" class="text-purple-600 hover:underline text-xs">Certificate</a>
                                @endif
                                @if ($account->member)
                                    <a href="{{ route('members.show', $account->member) }}" class="text-blue-600 hover:underline text-xs ml-2">View Member</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $accounts->links() }}
    </div>
</x-app-layout>
