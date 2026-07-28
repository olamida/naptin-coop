<x-app-layout title="Savings Accounts">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Savings', 'url' => route('savings.index')], ['label' => 'Accounts']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Savings Accounts</h2>
            <div class="flex items-center gap-2">
                @can('deposit-savings')
                    <a href="{{ route('savings.deposit') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition">+ Deposit</a>
                @endcan
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-gray-200">
            <a href="{{ route('savings.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 -mb-px transition">
                Transactions
            </a>
            <a href="{{ route('savings.accounts') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px">
                Accounts
            </a>
        </div>

        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-gray-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Member name or Staff ID..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs text-gray-500 mb-1">Sort by Balance</label>
                    <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest First</option>
                        <option value="highest" {{ request('sort') === 'highest' ? 'selected' : '' }}>Highest Balance</option>
                        <option value="lowest" {{ request('sort') === 'lowest' ? 'selected' : '' }}>Lowest Balance</option>
                    </select>
                </div>
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Search</button>
                @if (request()->hasAny(['search', 'sort']))
                    <a href="{{ route('savings.accounts') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
                @endif
            </div>
        </form>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <p class="text-sm text-gray-500">Total Savings Balance</p>
            <p class="text-3xl font-bold text-green-700">₦{{ number_format($totalBalance, 2) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Account Number</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Member</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Balance</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($accounts as $account)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs">{{ $account->account_number }}</td>
                            <td class="px-4 py-3 font-medium">{{ $account->member->first_name ?? '' }} {{ $account->member->last_name ?? '' }}</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($account->balance, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $account->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($account->member)
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('receipts.savings-statement', $account) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700">
                                            <span class="material-symbols-outlined text-[14px]">description</span>
                                            Statement
                                        </a>
                                        <a href="{{ route('members.show', $account->member) }}" class="text-blue-600 hover:underline text-xs">View Member</a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $accounts->links() }}
    </div>
</x-app-layout>
