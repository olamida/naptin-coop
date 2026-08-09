<x-app-layout title="Loans">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Loans']]" />
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Loans</h2>
                <p class="text-xs text-slate-500 mt-1">Manage loan applications, approvals, disbursements and repayments</p>
            </div>
            <div class="flex items-center gap-2">
                @can('view-loans')
                    <a href="{{ route('loans.export') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-[10px] text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        Export
                    </a>
                @endcan
                @can('repay-loans')
                    <a href="{{ route('loans.import') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-[10px] text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">upload_file</span>
                        Import Repayments
                    </a>
                @endcan
                @can('create-loans')
                    <a href="{{ route('loans.create') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-lg">add</span>
                        New Loan
                    </a>
                @endcan
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Loans</p>
                <p class="mt-2 text-2xl font-bold text-[#0F172A]">{{ $stats['total'] }}</p>
                <p class="text-xs text-slate-400 mt-1 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span> All time</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Outstanding</p>
                <p class="mt-2 text-2xl font-mono font-bold text-amber-600 truncate" title="₦{{ number_format($stats['outstanding'], 2) }}">₦{{ number_format($stats['outstanding'], 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Total balance due</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Active Loans</p>
                <p class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['repaying'] }}</p>
                <p class="text-xs text-slate-400 mt-1">In repayment</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Pending</p>
                <p class="mt-2 text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                <p class="text-xs text-slate-400 mt-1">Awaiting approval</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border {{ $stats['defaulted'] > 0 ? 'border-rose-200 bg-rose-50/50' : 'border-slate-200' }} shadow-sm hover:shadow-md transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Defaulted</p>
                <p class="mt-2 text-2xl font-bold {{ $stats['defaulted'] > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $stats['defaulted'] }}</p>
                @if ($stats['defaulted'] > 0)
                    <p class="text-xs text-rose-500 mt-1">₦{{ number_format($stats['defaulted_amount'], 2) }} at risk</p>
                @else
                    <p class="text-xs text-emerald-500 mt-1">No defaults</p>
                @endif
            </div>
        </div>

        {{-- Action Queue --}}
        @if ($stats['pending'] > 0 || $stats['defaulted'] > 0)
            <div class="grid md:grid-cols-2 gap-3">
                @if ($stats['pending'] > 0)
                    <div class="bg-white border border-amber-200 rounded-[16px] p-4 flex justify-between items-center hover:shadow-sm transition">
                        <div>
                            <p class="text-sm font-medium text-[#0F172A]">{{ $stats['pending'] }} Loan{{ $stats['pending'] > 1 ? 's' : '' }} Pending Approval</p>
                            <p class="text-xs text-slate-500">Requires review and guarantor confirmation</p>
                        </div>
                        <a href="{{ route('loans.index', ['status' => 'pending']) }}" class="bg-[#0F172A] text-white text-xs px-3 py-2 rounded-[10px] hover:bg-slate-800 transition">Review</a>
                    </div>
                @endif
                @if ($stats['defaulted'] > 0)
                    <div class="bg-white border border-rose-200 rounded-[16px] p-4 flex justify-between items-center hover:shadow-sm transition">
                        <div>
                            <p class="text-sm font-medium text-[#0F172A]">{{ $stats['defaulted'] }} Defaulted Loan{{ $stats['defaulted'] > 1 ? 's' : '' }}</p>
                            <p class="text-xs text-slate-500">₦{{ number_format($stats['defaulted_amount'], 2) }} total at risk</p>
                        </div>
                        <a href="{{ route('loans.index', ['status' => 'defaulted']) }}" class="bg-rose-600 text-white text-xs px-3 py-2 rounded-[10px] hover:bg-rose-700 transition">Review</a>
                    </div>
                @endif
            </div>
        @endif

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-slate-200 overflow-x-auto">
            <a href="{{ route('loans.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 {{ !request('status') ? 'border-[#0F172A] text-[#0F172A]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'}} -mb-px transition whitespace-nowrap">
                All
            </a>
            @foreach (['pending', 'approved', 'repaying', 'completed'] as $tab)
                @php
                    $countKey = $tab === 'pending' ? $stats['pending'] : ($tab === 'repaying' ? $stats['repaying'] : 0);
                    $colors = [
                        'pending' => 'border-amber-500 text-amber-600',
                        'approved' => 'border-blue-600 text-blue-600',
                        'repaying' => 'border-blue-600 text-blue-600',
                        'completed' => 'border-emerald-600 text-emerald-600',
                    ];
                @endphp
                <a href="{{ route('loans.index', ['status' => $tab]) }}"
                   class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition whitespace-nowrap
                   {{ request('status') === $tab ? $colors[$tab] : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    {{ ucfirst($tab) }}
                    @if ($countKey > 0)
                        <span class="ml-1 text-[10px]">({{ $countKey }})</span>
                    @endif
                </a>
            @endforeach
            @if ($stats['defaulted'] > 0)
                <a href="{{ route('loans.index', ['status' => 'defaulted']) }}"
                   class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition whitespace-nowrap flex items-center gap-1
                   {{ request('status') === 'defaulted' ? 'border-rose-600 text-rose-600' : 'border-transparent text-rose-500 hover:text-rose-700 hover:border-rose-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                    Defaulted ({{ $stats['defaulted'] }})
                </a>
            @endif
        </div>

        @can('manage-loan-products')
            <div class="bg-white border border-slate-200 rounded-[16px] p-5 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-amber-600 text-xl">category</span>
                    </div>
                    <div>
                        <p class="font-semibold text-[#0F172A]">Loan Products</p>
                        <p class="text-xs text-slate-500">Configure loan types, interest rates, and limits</p>
                    </div>
                </div>
                <a href="{{ route('admin.loan-products.index') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                    Manage Products
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </a>
            </div>
        @endcan

        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                <x-search-autocomplete :endpoint="route('loans.search')" name="search" placeholder="Member name, Staff ID, or Loan #..." />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Status</option>
                    @foreach (\App\Enums\LoanStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2 rounded-[10px] text-sm font-medium transition">Filter</button>
            @if (request()->hasAny(['search', 'status']))
                <a href="{{ route('loans.index') }}" class="text-sm text-slate-500 hover:underline py-2">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-[16px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Loan #</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Product</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Outstanding</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($loans as $loan)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $loan->loan_number }}</td>
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('members.show', $loan->member) }}" class="text-slate-800 font-medium hover:text-blue-600 transition">{{ $loan->member->first_name ?? '' }} {{ $loan->member->last_name ?? '' }}</a>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">{{ $loan->loanProduct?->name ?? ucfirst($loan->type) }}</td>
                                <td class="px-5 py-3.5 text-right font-mono text-sm font-medium text-slate-800">₦{{ number_format($loan->amount, 2) }}</td>
                                <td class="px-5 py-3.5 text-right font-mono text-sm font-medium {{ $loan->outstanding > 0 ? 'text-amber-600' : 'text-emerald-600' }}">₦{{ number_format($loan->outstanding, 2) }}</td>
                                <td class="px-5 py-3.5">
                                    <x-status-badge :status="$loan->status" />
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('loans.show', $loan) }}" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 transition">
                                        View
                                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5">
                                    <x-empty-state icon="account_balance" title="No loans found"
                                        message="Loan applications and their approval status will appear here."
                                        actionUrl="{{ route('loans.create') }}" actionLabel="Apply for a loan" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $loans->links() }}

        <x-show-all-toggle />
    </div>
</x-app-layout>