<x-app-layout title="Savings">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Savings']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Savings</h2>
            <div class="flex items-center gap-2">
                @can('view-savings')
                    <a href="{{ route('savings.export') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        Export
                    </a>
                @endcan
                @can('deposit-savings')
                    <a href="{{ route('savings.import') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">upload_file</span>
                        Import
                    </a>
                @endcan
                @can('deposit-savings')
                    <a href="{{ route('savings.deposit') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition">+ Deposit</a>
                @endcan
                @can('withdraw-savings')
                    <a href="{{ route('savings.withdraw') }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm transition">- Withdraw</a>
                @endcan
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-gray-200">
            <a href="{{ route('savings.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px">
                Transactions
            </a>
            <a href="{{ route('savings.accounts') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 -mb-px transition">
                Accounts
            </a>
            @if ($stats['pending_count'] > 0)
                <a href="{{ route('savings.pending-withdrawals') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-amber-600 hover:text-amber-700 hover:border-amber-300 -mb-px transition flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">pending</span>
                    Pending ({{ $stats['pending_count'] }})
                </a>
            @endif
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-green-500 text-lg">savings</span>
                    <p class="text-xs text-gray-500">Total Balance</p>
                </div>
                <p class="text-xl font-bold text-green-700">₦{{ number_format($stats['total_balance'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-blue-500 text-lg">arrow_downward</span>
                    <p class="text-xs text-gray-500">Total Deposits</p>
                </div>
                <p class="text-xl font-bold text-blue-700">₦{{ number_format($stats['total_deposits'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-red-500 text-lg">arrow_upward</span>
                    <p class="text-xs text-gray-500">Total Withdrawals</p>
                </div>
                <p class="text-xl font-bold text-red-600">₦{{ number_format($stats['total_withdrawals'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-purple-500 text-lg">people</span>
                    <p class="text-xs text-gray-500">Accounts</p>
                </div>
                <p class="text-xl font-bold">{{ $stats['total_accounts'] }}</p>
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
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    <option value="interest" {{ request('type') === 'interest' ? 'selected' : '' }}>Interest</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Filter</button>
            @if (request()->hasAny(['search', 'type', 'status']))
                <a href="{{ route('savings.index') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
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
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Balance After</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transactions as $txn)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ $txn->transaction_date?->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $txn->reference }}</td>
                            <td class="px-4 py-3">
                                @if ($txn->savingsAccount?->member)
                                    {{ $txn->savingsAccount->member->first_name }} {{ $txn->savingsAccount->member->last_name }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $txn->type === 'deposit' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $txn->type === 'withdrawal' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $txn->type === 'interest' ? 'bg-blue-100 text-blue-700' : '' }}">
                                    {{ ucfirst($txn->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusStyles = [
                                        'completed' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs rounded-full {{ $statusStyles[$txn->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($txn->status) }}
                                </span>
                                @if ($txn->status === 'rejected' && $txn->rejection_reason)
                                    <span class="text-[11px] text-red-500 ml-1" title="{{ $txn->rejection_reason }}">&#9432;</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium {{ $txn->type === 'deposit' ? 'text-green-700' : ($txn->status === 'pending' ? 'text-gray-500' : 'text-red-700') }}">
                                {{ $txn->type === 'deposit' ? '+' : ($txn->status === 'pending' ? '' : '-') }}₦{{ number_format($txn->amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-xs">
                                @if ($txn->status === 'pending')
                                    <span class="text-gray-400">—</span>
                                @else
                                    ₦{{ number_format($txn->balance_after, 2) }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($txn->type === 'deposit' && $txn->status === 'completed')
                                    <a href="{{ route('receipts.savings-deposit', $txn) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700">
                                        <span class="material-symbols-outlined text-[14px]">print</span>
                                        Receipt
                                    </a>
                                @endif
                                @if ($txn->type === 'withdrawal' && $txn->status === 'pending')
                                    @can('withdraw-savings')
                                        <a href="{{ route('savings.pending-withdrawals') }}" class="inline-flex items-center gap-1 text-xs text-amber-600 hover:text-amber-800 font-medium">
                                            <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                            Review
                                        </a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}

        <x-show-all-toggle />
    </div>
</x-app-layout>
