<x-portal-layout title="Loan {{ $loan->loan_number }}">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'My Loans', 'url' => route('portal.loans')], ['label' => $loan->loan_number]]" />

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.loans') }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
                <h2 class="text-2xl font-bold text-gray-800">{{ $loan->loan_number }}</h2>
                @php
                    $statusStyles = [
                        'pending' => 'bg-yellow-100 text-yellow-700',
                        'approved' => 'bg-purple-100 text-purple-700',
                        'disbursed' => 'bg-orange-100 text-orange-700',
                        'repaying' => 'bg-blue-100 text-blue-700',
                        'completed' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        'defaulted' => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <span class="px-2 py-1 text-xs rounded-full {{ $statusStyles[$loan->status] ?? '' }}">{{ ucfirst($loan->status) }}</span>
            </div>
            @if (in_array($loan->status, ['disbursed', 'repaying', 'completed', 'defaulted']))
                <a href="{{ route('receipts.loan-statement', $loan) }}" target="_blank" class="inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg text-sm transition">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    Print Statement
                </a>
            @endif
        </div>

        @if ($loan->isTopup() && $loan->parentLoan)
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-indigo-600 text-xl mt-0.5">link</span>
                <div>
                    <p class="font-semibold text-indigo-800 text-sm">Top-up Loan</p>
                    <p class="text-sm text-indigo-700 mt-1">
                        This is a top-up for <a href="{{ route('portal.loan-detail', $loan->parentLoan) }}" class="underline font-medium hover:text-indigo-900">{{ $loan->parentLoan->loan_number }}</a>
                    </p>
                </div>
            </div>
        @endif

        @if ($loan->topupLoans->isNotEmpty())
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                <p class="font-semibold text-indigo-800 text-sm mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600 text-lg">add_circle</span>
                    Linked Top-up Loans ({{ $loan->topupLoans->count() }})
                </p>
                <div class="space-y-2">
                    @foreach ($loan->topupLoans as $topup)
                        <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-indigo-100">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('portal.loan-detail', $topup) }}" class="font-mono text-sm font-medium text-indigo-700 hover:text-indigo-900 underline">{{ $topup->loan_number }}</a>
                                <span class="px-2 py-0.5 text-[10px] rounded-full
                                    {{ $topup->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $topup->status === 'repaying' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $topup->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $topup->status === 'approved' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $topup->status === 'disbursed' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $topup->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $topup->status === 'defaulted' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ ucfirst($topup->status) }}
                                </span>
                            </div>
                            <span class="text-sm font-medium">₦{{ number_format($topup->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($loan->status === 'rejected' && $loan->rejection_reason)
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-red-600 text-xl mt-0.5">cancel</span>
                <div>
                    <p class="font-semibold text-red-800 text-sm">Loan Rejected</p>
                    <p class="text-sm text-red-700 mt-1">{{ $loan->rejection_reason }}</p>
                </div>
            </div>
        @endif

        @if ($loan->isOverdue())
            <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl p-4 flex items-center gap-3 text-white">
                <span class="material-symbols-outlined text-xl">warning</span>
                <div>
                    <p class="font-bold text-sm">This loan is {{ $loan->daysOverdue() }} days overdue</p>
                    <p class="text-white/80 text-xs">Maturity date was {{ $loan->maturity_date->format('d M Y') }}</p>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Loan Details</h3>
                    <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500 text-xs">Loan Number</dt>
                            <dd class="font-mono font-medium">{{ $loan->loan_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Loan Product</dt>
                            <dd class="font-medium">{{ $loan->loanProduct->name ?? ucfirst($loan->type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Loan Amount</dt>
                            <dd class="font-bold text-gray-900">₦{{ number_format($loan->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Interest Rate</dt>
                            <dd class="font-medium">{{ $loan->interest_rate }}%</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Tenure</dt>
                            <dd class="font-medium">{{ $loan->tenure_months }} months</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Monthly Repayment</dt>
                            <dd class="font-medium">₦{{ number_format($loan->monthly_repayment, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Total Repaid</dt>
                            <dd class="font-medium text-green-600">₦{{ number_format($loan->total_repaid, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Outstanding</dt>
                            <dd class="font-bold text-orange-600">₦{{ number_format($loan->outstanding, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Application Date</dt>
                            <dd class="font-medium">{{ $loan->application_date?->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Approval Date</dt>
                            <dd class="font-medium">{{ $loan->approval_date?->format('d M Y') ?? 'Pending' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Disbursement Date</dt>
                            <dd class="font-medium">{{ $loan->disbursement_date?->format('d M Y') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Maturity Date</dt>
                            <dd class="font-medium {{ $loan->isOverdue() ? 'text-red-600 font-bold' : '' }}">
                                {{ $loan->maturity_date?->format('d M Y') ?? 'N/A' }}
                                @if ($loan->isOverdue())
                                    <span class="text-xs text-red-500 ml-1">({{ $loan->daysOverdue() }}d overdue)</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                    @if ($loan->purpose)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Purpose</p>
                            <p class="text-sm text-gray-700">{{ $loan->purpose }}</p>
                        </div>
                    @endif
                </div>

                @if ($loan->repayments->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Repayment History</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-200">
                                    <tr>
                                        <th class="text-left px-3 py-2 font-medium text-gray-600 text-xs">Date</th>
                                        <th class="text-left px-3 py-2 font-medium text-gray-600 text-xs">Reference</th>
                                        <th class="text-right px-3 py-2 font-medium text-gray-600 text-xs">Amount</th>
                                        <th class="text-right px-3 py-2 font-medium text-gray-600 text-xs">Principal</th>
                                        <th class="text-right px-3 py-2 font-medium text-gray-600 text-xs">Interest</th>
                                        <th class="text-right px-3 py-2 font-medium text-gray-600 text-xs">Balance After</th>
                                        <th class="text-right px-3 py-2 font-medium text-gray-600 text-xs">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($loan->repayments as $repayment)
                                        <tr>
                                            <td class="px-3 py-2 text-xs text-gray-600">{{ $repayment->payment_date->format('d M Y') }}</td>
                                            <td class="px-3 py-2 font-mono text-[11px] text-gray-500">{{ $repayment->reference }}</td>
                                            <td class="px-3 py-2 text-right text-xs font-medium text-green-600">₦{{ number_format($repayment->amount, 2) }}</td>
                                            <td class="px-3 py-2 text-right text-xs text-gray-600">₦{{ number_format($repayment->principal_portion, 2) }}</td>
                                            <td class="px-3 py-2 text-right text-xs text-gray-600">₦{{ number_format($repayment->interest_portion, 2) }}</td>
                                            <td class="px-3 py-2 text-right text-xs font-medium text-orange-600">₦{{ number_format($repayment->outstanding_after, 2) }}</td>
                                            <td class="px-3 py-2 text-right">
                                                <a href="{{ route('receipts.loan-repayment', $repayment) }}" target="_blank" class="text-[11px] text-gray-500 hover:text-gray-700">
                                                    <span class="material-symbols-outlined text-[14px]">print</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($loan->schedules->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Repayment Schedule</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-200">
                                    <tr>
                                        <th class="text-left px-3 py-2 font-medium text-gray-600 text-xs">#</th>
                                        <th class="text-left px-3 py-2 font-medium text-gray-600 text-xs">Due Date</th>
                                        <th class="text-right px-3 py-2 font-medium text-gray-600 text-xs">Principal</th>
                                        <th class="text-right px-3 py-2 font-medium text-gray-600 text-xs">Interest</th>
                                        <th class="text-right px-3 py-2 font-medium text-gray-600 text-xs">Total</th>
                                        <th class="text-left px-3 py-2 font-medium text-gray-600 text-xs">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($loan->schedules as $schedule)
                                        <tr>
                                            <td class="px-3 py-2 text-xs text-gray-500">{{ $schedule->installment_number }}</td>
                                            <td class="px-3 py-2 text-xs text-gray-600">{{ $schedule->due_date->format('d M Y') }}</td>
                                            <td class="px-3 py-2 text-right text-xs">₦{{ number_format($schedule->principal_amount, 2) }}</td>
                                            <td class="px-3 py-2 text-right text-xs">₦{{ number_format($schedule->interest_amount, 2) }}</td>
                                            <td class="px-3 py-2 text-right text-xs font-medium">₦{{ number_format($schedule->principal_amount + $schedule->interest_amount, 2) }}</td>
                                            <td class="px-3 py-2">
                                                @php
                                                    $schStatus = match($schedule->status) {
                                                        'paid' => 'bg-green-100 text-green-700',
                                                        'overdue' => 'bg-red-100 text-red-700',
                                                        default => 'bg-yellow-100 text-yellow-700',
                                                    };
                                                @endphp
                                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $schStatus }}">{{ ucfirst($schedule->status) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                {{-- Progress --}}
                @php
                    $repaidPercent = $loan->amount > 0 ? round(($loan->total_repaid / $loan->amount) * 100, 1) : 0;
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Repayment Progress</h3>
                    <div class="w-full bg-gray-200 rounded-full h-4 mb-3">
                        <div class="bg-green-500 h-4 rounded-full transition-all" style="width: {{ min($repaidPercent, 100) }}%"></div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Repaid</span>
                            <span class="font-medium">{{ $repaidPercent }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Repaid</span>
                            <span class="font-medium text-green-600">₦{{ number_format($loan->total_repaid, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Outstanding</span>
                            <span class="font-medium text-orange-600">₦{{ number_format($loan->outstanding, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Monthly</span>
                            <span class="font-medium">₦{{ number_format($loan->monthly_repayment, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Guarantors --}}
                @if ($loan->guarantors->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-500 text-xl">group_add</span>
                            Guarantors
                        </h3>
                        <div class="space-y-2">
                            @foreach ($loan->guarantors as $guarantor)
                                @php
                                    $gStatusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'accepted' => 'bg-green-100 text-green-700',
                                        'declined' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600">
                                            {{ strtoupper(substr($guarantor->member->first_name ?? '?', 0, 1) . substr($guarantor->member->last_name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-800">{{ $guarantor->member->first_name ?? 'N/A' }} {{ $guarantor->member->last_name ?? '' }}</p>
                                            <p class="text-[10px] text-gray-500">{{ $guarantor->member->staff_id ?? '' }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $gStatusColors[$guarantor->status->value] ?? '' }}">{{ $guarantor->status->label() }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-portal-layout>
