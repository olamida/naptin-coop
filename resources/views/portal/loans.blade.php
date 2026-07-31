<x-portal-layout title="My Loans">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'My Loans']]" />

        @php
            $activeLoans = $loans->filter(fn($l) => in_array($l->status, ['disbursed', 'repaying']));
            $totalOutstanding = $activeLoans->sum('outstanding');
        @endphp

        @if ($totalOutstanding > 0)
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-[16px] bg-orange-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-orange-600 text-xl">account_balance</span>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Outstanding</p>
                        <p class="text-2xl font-bold text-red-600">₦{{ number_format($totalOutstanding, 2) }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-[#0F172A]">My Loans</h2>
            <a href="{{ route('portal.loan-apply') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Apply for Loan
            </a>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h3 class="font-semibold text-[#0F172A]">Loan History</h3>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse ($loans as $loan)
                    @php
                        $repaidPercent = $loan->amount > 0 ? round(($loan->total_repaid / $loan->amount) * 100, 1) : 0;
                        $isActive = in_array($loan->status, ['disbursed', 'repaying']);
                        $isOverdue = $loan->maturity_date && $loan->maturity_date->isPast() && $loan->outstanding > 0;
                    @endphp
                    <a href="{{ $isActive ? route('portal.loan-detail', $loan) : '#' }}" class="block px-5 py-4 {{ $isActive ? 'hover:bg-slate-50 transition cursor-pointer' : '' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-mono font-medium text-[#0F172A]">{{ $loan->loan_number }}</span>
                                    <span class="text-xs text-slate-500">{{ $loan->loanProduct->name ?? ucfirst($loan->type) }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                        {{ $loan->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $loan->status === 'approved' ? 'bg-purple-100 text-purple-700' : '' }}
                                        {{ $loan->status === 'disbursed' ? 'bg-orange-100 text-orange-700' : '' }}
                                        {{ $loan->status === 'repaying' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $loan->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $loan->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $loan->status === 'defaulted' ? 'bg-red-100 text-red-700' : '' }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                    @if ($isOverdue)
                                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-red-100 text-red-700">
                                            Overdue
                                        </span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3 text-xs">
                                    <div>
                                        <p class="text-slate-500">Amount</p>
                                        <p class="font-medium text-[#0F172A]">₦{{ number_format($loan->amount, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-500">Monthly Repayment</p>
                                        <p class="font-medium text-[#0F172A]">₦{{ number_format($loan->monthly_repayment, 2) }}</p>
                                    </div>
                                    @if ($isActive)
                                        <div>
                                            <p class="text-slate-500">Repaid</p>
                                            <p class="font-medium text-green-600">₦{{ number_format($loan->total_repaid, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500">Outstanding</p>
                                            <p class="font-medium text-orange-600">₦{{ number_format($loan->outstanding, 2) }}</p>
                                        </div>
                                    @else
                                        <div>
                                            <p class="text-slate-500">Status</p>
                                            <p class="font-medium text-[#0F172A]">{{ ucfirst($loan->status) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500">Applied</p>
                                            <p class="font-medium text-[#0F172A]">{{ $loan->application_date?->format('d M Y') ?? 'N/A' }}</p>
                                        </div>
                                    @endif
                                </div>

                                @if ($isActive && $loan->amount > 0)
                                    <div class="mt-3">
                                        <div class="w-full bg-slate-200 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ min($repaidPercent, 100) }}%"></div>
                                        </div>
                                        <p class="text-[11px] text-slate-400 mt-1">{{ $repaidPercent }}% repaid</p>
                                    </div>
                                @endif

                                @if ($loan->maturity_date && $isActive)
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        Maturity: {{ $loan->maturity_date->format('d M Y') }}
                                        @if ($isOverdue)
                                            <span class="text-red-500 font-medium">({{ $loan->maturity_date->diffForHumans() }})</span>
                                        @endif
                                    </p>
                                @endif
                            </div>

                            @if ($isActive)
                                <span class="material-symbols-outlined text-slate-300 text-xl shrink-0 mt-1">chevron_right</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <x-empty-state icon="account_balance" title="No loans found"
                        message="You have not applied for any loans yet."
                        actionUrl="{{ route('portal.loan-apply') }}" actionLabel="Apply for Loan" />
                @endforelse
            </div>
        </div>

        {{ $loans->links() }}
    </div>
</x-portal-layout>
