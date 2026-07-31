<x-app-layout title="Savings Accounts">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Savings', 'url' => route('savings.index')], ['label' => 'Accounts']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-[#0F172A]">Savings Accounts</h2>
            <div class="flex items-center gap-2">
                @can('deposit-savings')
                    <a href="{{ route('savings.deposit') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-[10px] text-sm transition">+ Deposit</a>
                @endcan
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-slate-200">
            <a href="{{ route('savings.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 -mb-px transition">
                Transactions
            </a>
            <a href="{{ route('savings.accounts') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-[#0F172A] text-[#0F172A] -mb-px">
                Accounts
            </a>
        </div>

        <form method="GET" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Member name or Staff ID..."
                        class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Sort by Balance</label>
                    <select name="sort" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest First</option>
                        <option value="highest" {{ request('sort') === 'highest' ? 'selected' : '' }}>Highest Balance</option>
                        <option value="lowest" {{ request('sort') === 'lowest' ? 'selected' : '' }}>Lowest Balance</option>
                    </select>
                </div>
                <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2 rounded-[10px] text-sm font-medium transition">Search</button>
                @if (request()->hasAny(['search', 'sort']))
                    <a href="{{ route('savings.accounts') }}" class="text-sm text-slate-500 hover:underline py-2">Clear</a>
                @endif
            </div>
        </form>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[12px] text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-[16px] shadow-sm p-5 border border-slate-200">
            <p class="text-sm text-slate-500">Total Savings Balance</p>
            <p class="text-3xl font-bold text-green-700">₦{{ number_format($totalBalance, 2) }}</p>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Account Number</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Balance</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($accounts as $account)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3.5 font-mono text-xs">{{ $account->account_number }}</td>
                            <td class="px-5 py-3.5 font-medium">{{ $account->member->first_name ?? '' }} {{ $account->member->last_name ?? '' }}</td>
                            <td class="px-5 py-3.5 text-right font-medium">₦{{ number_format($account->balance, 2) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-1 text-xs rounded-full {{ $account->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if ($account->member)
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('receipts.savings-statement', $account) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-700">
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
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">No accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $accounts->links() }}
    </div>
</x-app-layout>
