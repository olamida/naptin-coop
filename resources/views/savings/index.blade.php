<x-app-layout title="Savings">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Savings']]" />
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Savings</h2>
                <p class="text-xs text-slate-500 mt-1">Track member savings deposits, withdrawals and balances</p>
            </div>
            <div class="flex items-center gap-2">
                @can('view-savings')
                    <a href="{{ route('savings.export') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-[10px] text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        Export
                    </a>
                @endcan
                @can('deposit-savings')
                    <a href="{{ route('savings.import') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-[10px] text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">upload_file</span>
                        Import
                    </a>
                    <a href="{{ route('savings.deposit') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-lg">add</span>
                        Deposit
                    </a>
                @endcan
                @can('withdraw-savings')
                    <a href="{{ route('savings.withdraw') }}" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-lg">remove</span>
                        Withdraw
                    </a>
                @endcan
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-slate-200">
            <a href="{{ route('savings.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-[#0F172A] text-[#0F172A] -mb-px">
                Transactions
            </a>
            <a href="{{ route('savings.accounts') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 -mb-px transition">
                Accounts
            </a>
            @if ($stats['pending_count'] > 0)
                <a href="{{ route('savings.pending-withdrawals') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-amber-600 hover:text-amber-700 hover:border-amber-300 -mb-px transition flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse inline-block"></span>
                    Pending ({{ $stats['pending_count'] }})
                </a>
            @endif
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Balance</p>
                <p class="mt-2 text-2xl font-mono font-bold text-emerald-700 truncate" title="₦{{ number_format($stats['total_balance'], 2) }}">₦{{ number_format($stats['total_balance'], 2) }}</p>
                <p class="text-xs text-slate-400 mt-1 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span> Across {{ $stats['total_accounts'] }} accounts</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Deposits</p>
                <p class="mt-2 text-2xl font-mono font-bold text-[#0F172A] truncate" title="₦{{ number_format($stats['total_deposits'], 2) }}">₦{{ number_format($stats['total_deposits'], 2) }}</p>
                <p class="text-xs text-emerald-600 mt-1 flex items-center gap-1">&#8593; Inflow</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Withdrawals</p>
                <p class="mt-2 text-2xl font-mono font-bold text-rose-600 truncate" title="₦{{ number_format($stats['total_withdrawals'], 2) }}">₦{{ number_format($stats['total_withdrawals'], 2) }}</p>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">&#8595; Outflow</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Pending Requests</p>
                <p class="mt-2 text-2xl font-mono font-bold text-amber-600">{{ $stats['pending_count'] }}</p>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">{{ $stats['pending_withdrawal_count'] ?? 0 }} withdrawals &middot; {{ $stats['pending_deposit_count'] ?? 0 }} deposits</p>
            </div>
        </div>

        {{-- Pending Action Queue --}}
        @if (($stats['pending_withdrawal_count'] ?? 0) > 0 || ($stats['pending_deposit_count'] ?? 0) > 0)
            <div class="grid md:grid-cols-2 gap-3">
                @if (($stats['pending_withdrawal_count'] ?? 0) > 0)
                    <div class="bg-white border border-amber-200 rounded-[16px] p-4 flex justify-between items-center hover:shadow-sm transition">
                        <div>
                            <p class="text-sm font-medium text-[#0F172A]">{{ $stats['pending_withdrawal_count'] }} Pending Withdrawals</p>
                            <p class="text-xs text-slate-500">Awaiting approval</p>
                        </div>
                        <a href="{{ route('savings.pending-withdrawals') }}" class="bg-[#0F172A] text-white text-xs px-3 py-2 rounded-[10px] hover:bg-slate-800 transition">Review</a>
                    </div>
                @endif
                @if (($stats['pending_deposit_count'] ?? 0) > 0)
                    <div class="bg-white border border-amber-200 rounded-[16px] p-4 flex justify-between items-center hover:shadow-sm transition">
                        <div>
                            <p class="text-sm font-medium text-[#0F172A]">{{ $stats['pending_deposit_count'] }} Pending Deposits</p>
                            <p class="text-xs text-slate-500">Require confirmation</p>
                        </div>
                        <a href="{{ route('savings.pending-withdrawals') }}" class="bg-[#0F172A] text-white text-xs px-3 py-2 rounded-[10px] hover:bg-slate-800 transition">Review</a>
                    </div>
                @endif
            </div>
        @endif

        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                <x-search-autocomplete :endpoint="route('savings.search')" name="search" placeholder="Member name, Staff ID, or Account #..." />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
                <select name="type" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Types</option>
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    <option value="interest" {{ request('type') === 'interest' ? 'selected' : '' }}>Interest</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2 rounded-[10px] text-sm font-medium transition">Filter</button>
            @if (request()->hasAny(['search', 'type', 'status']))
                <a href="{{ route('savings.index') }}" class="text-sm text-slate-500 hover:underline py-2">Clear</a>
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
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Balance After</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($transactions as $txn)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 text-slate-600 text-xs">{{ $txn->transaction_date?->format('d M Y') }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $txn->reference }}</td>
                                <td class="px-5 py-3.5">
                                    @if ($txn->savingsAccount?->member)
                                        <a href="{{ route('members.show', $txn->savingsAccount->member) }}" class="text-slate-800 font-medium hover:text-blue-600 transition text-sm">{{ $txn->savingsAccount->member->first_name }} {{ $txn->savingsAccount->member->last_name }}</a>
                                    @else
                                        <span class="text-slate-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full
                                        {{ $txn->type === 'deposit' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                        {{ $txn->type === 'withdrawal' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                                        {{ $txn->type === 'interest' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}">
                                        {{ ucfirst($txn->type) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $statusStyles = [
                                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full border {{ $statusStyles[$txn->status] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                        {{ ucfirst($txn->status) }}
                                    </span>
                                    @if ($txn->status === 'rejected' && $txn->rejection_reason)
                                        <span class="text-[10px] text-rose-500 ml-1 cursor-help" title="{{ $txn->rejection_reason }}">&#9432;</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono text-sm font-medium {{ $txn->type === 'deposit' ? 'text-emerald-700' : ($txn->status === 'pending' ? 'text-slate-400' : 'text-rose-700') }}">
                                    {{ $txn->type === 'deposit' ? '+' : ($txn->status === 'pending' ? '' : '-') }}₦{{ number_format($txn->amount, 2) }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono text-xs text-slate-500">
                                    @if ($txn->status === 'pending')
                                        <span class="text-slate-300">—</span>
                                    @else
                                        ₦{{ number_format($txn->balance_after, 2) }}
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if ($txn->type === 'deposit' && $txn->status === 'completed')
                                            <a href="{{ route('receipts.savings-deposit', $txn) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition" title="Print Receipt">
                                                <span class="material-symbols-outlined text-[16px]">print</span>
                                            </a>
                                        @endif
                                        @if ($txn->type === 'withdrawal' && $txn->status === 'pending')
                                            @can('withdraw-savings')
                                                <a href="{{ route('savings.pending-withdrawals') }}" class="text-xs text-amber-600 hover:text-amber-800 font-medium">Review</a>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5">
                                    <x-empty-state icon="savings" title="No transactions found"
                                        message="Deposits, withdrawals and interest movements will appear here once recorded."
                                        actionUrl="{{ route('savings.deposit') }}" actionLabel="Record a deposit" />
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