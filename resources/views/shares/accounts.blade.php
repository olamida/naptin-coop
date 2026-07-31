<x-app-layout title="Share Accounts">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Shares', 'url' => route('shares.index')], ['label' => 'Accounts']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-[#0F172A]">Share Accounts</h2>
            <div class="flex items-center gap-2">
                @can('purchase-shares')
                    <a href="{{ route('shares.purchase') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-3 py-2 rounded-[10px] text-sm transition">+ Purchase</a>
                @endcan
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-slate-200">
            <a href="{{ route('shares.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 -mb-px transition">
                Transactions
            </a>
            <a href="{{ route('shares.accounts') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px">
                Accounts
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <form method="GET" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-slate-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Member name or Staff ID..."
                        class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs text-slate-500 mb-1">Sort by Value</label>
                    <select name="sort" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest First</option>
                        <option value="highest" {{ request('sort') === 'highest' ? 'selected' : '' }}>Highest Value</option>
                        <option value="lowest" {{ request('sort') === 'lowest' ? 'selected' : '' }}>Lowest Value</option>
                    </select>
                </div>
                <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2 rounded-[10px] text-sm font-medium transition">Search</button>
                @if (request()->hasAny(['search', 'sort']))
                    <a href="{{ route('shares.accounts') }}" class="text-sm text-slate-500 hover:underline py-2">Clear</a>
                @endif
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-[16px] shadow-sm p-5 border border-slate-200">
                <p class="text-sm text-slate-500">Total Shares</p>
                <p class="text-3xl font-bold text-purple-700">{{ number_format($totalShares) }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm p-5 border border-slate-200">
                <p class="text-sm text-slate-500">Total Value</p>
                <p class="text-3xl font-bold text-green-700">₦{{ number_format($totalValue, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Shares</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Share Price</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Value</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($accounts as $account)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3.5 font-medium">{{ $account->member->first_name ?? '' }} {{ $account->member->last_name ?? '' }}</td>
                            <td class="px-5 py-3.5 text-right">{{ number_format($account->total_shares) }}</td>
                            <td class="px-5 py-3.5 text-right">₦{{ number_format($account->share_price, 2) }}</td>
                            <td class="px-5 py-3.5 text-right font-medium">₦{{ number_format($account->total_value, 2) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-1 text-xs rounded-full {{ $account->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
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
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">No accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $accounts->links() }}
    </div>
</x-app-layout>
