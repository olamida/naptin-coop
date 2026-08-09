<x-app-layout title="Loan {{ $loan->loan_number }}">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Loans', 'url' => route('loans.index')], ['label' => 'Loan Details']]" />
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('loans.index') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
                <h2 class="text-2xl font-bold text-[#0F172A]">Loan {{ $loan->loan_number }}</h2>
                <span class="px-2 py-1 text-xs rounded-full
                    {{ $loan->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $loan->status === 'repaying' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $loan->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $loan->status === 'approved' ? 'bg-purple-100 text-purple-700' : '' }}
                    {{ $loan->status === 'disbursed' ? 'bg-orange-100 text-orange-700' : '' }}
                    {{ $loan->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $loan->status === 'defaulted' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ ucfirst($loan->status) }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                @if ($loan->status === 'pending')
                    @can('approve-loans')
                        <button data-shortcut="reject" onclick="document.getElementById('rejectModal').classList.remove('hidden')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-[10px] text-sm transition">Reject <span class="hidden lg:inline text-white/60 text-xs">(R)</span></button>
                        <form method="POST" action="{{ route('loans.approve', $loan) }}" data-shortcut="approve">
                            @csrf
                            <button type="submit" onclick="return confirm('Approve this loan?')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-[10px] text-sm transition">Approve <span class="hidden lg:inline text-white/60 text-xs">(A)</span></button>
                        </form>
                    @endcan
                @endif
                @if ($loan->status === 'approved')
                    @if ($disbursementPending > 0)
                        <span class="bg-orange-100 text-orange-700 text-xs font-medium px-3 py-2 rounded-[10px]">Disbursement awaits maker-checker approval ({{ $disbursementPending }}/2)</span>
                        @can('disburse-loans')
                            <form method="POST" action="{{ route('loans.disburse.approve', $loan) }}" data-shortcut="approve">
                                @csrf
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-[10px] text-sm transition">Approve Disbursement <span class="hidden lg:inline text-white/60 text-xs">(A)</span></button>
                            </form>
                        @endcan
                    @elseif ($disbursementApproved)
                        <span class="bg-green-100 text-green-700 text-xs font-medium px-3 py-2 rounded-[10px]">Disbursement approved</span>
                    @endif
                    @can('disburse-loans')
                        <form method="POST" action="{{ route('loans.disburse', $loan) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('Disburse this loan?')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-[10px] text-sm transition">Disburse</button>
                        </form>
                    @endcan
                @endif
                @if (in_array($loan->status, ['disbursed', 'repaying']) && $loan->outstanding > 0)
                    @can('repay-loans')
                        <a href="{{ route('loans.repayment', $loan) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-[10px] text-sm transition">Record Repayment</a>
                    @endcan
                    @can('create-loans')
                        @if ($loan->canTopup())
                            <a href="{{ route('loans.topup', $loan) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-[10px] text-sm transition">Top-up Loan</a>
                        @endif
                    @endcan
                @endif
                @if ($loan->status === 'disbursed')
                    <a href="{{ route('receipts.loan-disbursement', $loan) }}" target="_blank" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-700 px-3 py-2 rounded-[10px] text-sm transition">
                        <span class="material-symbols-outlined text-[14px]">print</span>
                        Disbursement Receipt
                    </a>
                @endif
                @if (in_array($loan->status, ['disbursed', 'repaying', 'completed', 'defaulted']))
                    <a href="{{ route('receipts.loan-statement', $loan) }}" target="_blank" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-700 px-3 py-2 rounded-[10px] text-sm transition">
                        <span class="material-symbols-outlined text-[14px]">description</span>
                        Print Statement
                    </a>
                @endif
            </div>
        </div>

        @if ($loan->status === 'rejected' && $loan->rejection_reason)
            <div class="bg-red-50 border border-red-200 rounded-[16px] p-4 flex items-start gap-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-red-600 text-xl">cancel</span>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-red-800">Loan Rejected</p>
                    <p class="text-red-700 text-sm mt-1">{{ $loan->rejection_reason }}</p>
                </div>
            </div>
        @endif

        {{-- Loan Progress Stepper --}}
        @php
            $stepperCurrent = match($loan->status) {
                'pending' => 1,
                'approved' => 2,
                'disbursed' => 3,
                'repaying' => 4,
                'completed' => 5,
                'rejected' => 1,
                'defaulted' => 4,
                default => 1,
            };
        @endphp
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-[#0F172A] mb-4">Loan Progress</h3>
            <x-stepper :steps="[
                ['label' => 'Applied', 'icon' => 'description'],
                ['label' => 'Guarantors', 'icon' => 'group'],
                ['label' => 'Approved', 'icon' => 'check_circle'],
                ['label' => 'Disbursed', 'icon' => 'account_balance'],
                ['label' => 'Repaid', 'icon' => 'task_alt'],
            ]" :current="$stepperCurrent" />
        </div>

        @if ($loan->isOverdue())
            <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-[16px] p-4 flex items-center gap-4 text-white">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">warning</span>
                </div>
                <div class="flex-1">
                    <p class="font-bold">This loan is {{ $loan->daysOverdue() }} days overdue</p>
                    <p class="text-white/80 text-sm">Maturity date was {{ $loan->maturity_date->format('d M Y') }} — ₦{{ number_format($loan->outstanding, 2) }} still outstanding</p>
                </div>
                <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold">OVERDUE</span>
            </div>
        @endif

        @if ($loan->status === 'defaulted')
            <div class="bg-gradient-to-r from-red-700 to-red-800 rounded-[16px] p-4 flex items-center gap-4 text-white">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">error</span>
                </div>
                <div class="flex-1">
                    <p class="font-bold">This loan has been marked as defaulted</p>
                    <p class="text-white/80 text-sm">The loan exceeded its maturity date and remains unpaid — ₦{{ number_format($loan->outstanding, 2) }} outstanding</p>
                </div>
                <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold">DEFAULTED</span>
            </div>
        @endif

        @if ($loan->isTopup() && $loan->parentLoan)
            <div class="bg-indigo-50 border border-indigo-200 rounded-[16px] p-4 flex items-start gap-4">
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-indigo-600 text-xl">link</span>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-indigo-800">Top-up Loan</p>
                    <p class="text-indigo-700 text-sm mt-1">
                        This is a top-up for <a href="{{ route('loans.show', $loan->parentLoan) }}" class="underline font-medium hover:text-indigo-900">{{ $loan->parentLoan->loan_number }}</a>
                        (Outstanding: ₦{{ number_format($loan->parentLoan->outstanding, 2) }})
                    </p>
                </div>
            </div>
        @endif

        @if ($loan->topupLoans->isNotEmpty())
            <div class="bg-indigo-50 border border-indigo-200 rounded-[16px] p-4">
                <p class="font-bold text-indigo-800 mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600 text-lg">add_circle</span>
                    Linked Top-up Loans ({{ $loan->topupLoans->count() }})
                </p>
                <div class="space-y-2">
                    @foreach ($loan->topupLoans as $topup)
                        <div class="flex items-center justify-between bg-white rounded-[10px] p-3 border border-indigo-100">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('loans.show', $topup) }}" class="font-mono text-sm font-medium text-indigo-700 hover:text-indigo-900 underline">{{ $topup->loan_number }}</a>
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
                            <div class="text-sm text-right">
                                <span class="font-medium">₦{{ number_format($topup->amount, 2) }}</span>
                                <span class="text-slate-400 text-xs block">Outstanding: ₦{{ number_format($topup->outstanding, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-[10px] text-sm">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-[#0F172A] mb-4">Loan Details</h3>
                    <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500">Loan Number</dt>
                            <dd class="font-mono font-medium">{{ $loan->loan_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Member</dt>
                            <dd class="font-medium">{{ $loan->member->first_name ?? '' }} {{ $loan->member->last_name ?? '' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Type</dt>
                            <dd class="font-medium">{{ ucfirst($loan->type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Amount</dt>
                            <dd class="font-bold">₦{{ number_format($loan->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Interest Rate</dt>
                            <dd class="font-medium">{{ $loan->interest_rate }}%</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tenure</dt>
                            <dd class="font-medium">{{ $loan->tenure_months }} months</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Monthly Repayment</dt>
                            <dd class="font-medium">₦{{ number_format($loan->monthly_repayment, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Total Repaid</dt>
                            <dd class="font-medium text-green-700">₦{{ number_format($loan->total_repaid, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Outstanding</dt>
                            <dd class="font-bold text-orange-600">₦{{ number_format($loan->outstanding, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Application Date</dt>
                            <dd class="font-medium">{{ $loan->application_date?->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Approval Date</dt>
                            <dd class="font-medium">{{ $loan->approval_date?->format('d M Y') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Disbursement Date</dt>
                            <dd class="font-medium">{{ $loan->disbursement_date?->format('d M Y') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Maturity Date</dt>
                            <dd class="font-medium {{ $loan->isOverdue() ? 'text-red-600' : '' }}">
                                {{ $loan->maturity_date?->format('d M Y') ?? 'N/A' }}
                                @if ($loan->isOverdue())
                                    <span class="text-xs text-red-500 ml-1">({{ $loan->daysOverdue() }} days overdue)</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Approved By</dt>
                            <dd class="font-medium">{{ $loan->approvedBy?->name ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                    @if ($loan->purpose)
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <dt class="text-sm text-slate-500 mb-1">Purpose</dt>
                            <dd class="text-sm">{{ $loan->purpose }}</dd>
                        </div>
                    @endif
                </div>

                @if ($loan->repayments->isNotEmpty())
                    <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-[#0F172A] mb-4">Repayment History</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-slate-200">
                                    <tr>
                                        <th class="text-left px-3 py-2 font-medium text-slate-600">Date</th>
                                        <th class="text-left px-3 py-2 font-medium text-slate-600">Reference</th>
                                        <th class="text-right px-3 py-2 font-medium text-slate-600">Amount</th>
                                        <th class="text-right px-3 py-2 font-medium text-slate-600">Principal</th>
                                        <th class="text-right px-3 py-2 font-medium text-slate-600">Interest</th>
                                        <th class="text-right px-3 py-2 font-medium text-slate-600">Balance After</th>
                                        <th class="text-right px-3 py-2 font-medium text-slate-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($loan->repayments as $repayment)
                                        <tr>
                                            <td class="px-3 py-2">{{ $repayment->payment_date->format('d M Y') }}</td>
                                            <td class="px-3 py-2 font-mono text-xs">{{ $repayment->reference }}</td>
                                            <td class="px-3 py-2 text-right">₦{{ number_format($repayment->amount, 2) }}</td>
                                            <td class="px-3 py-2 text-right">₦{{ number_format($repayment->principal_portion, 2) }}</td>
                                            <td class="px-3 py-2 text-right">₦{{ number_format($repayment->interest_portion, 2) }}</td>
                                            <td class="px-3 py-2 text-right font-medium">₦{{ number_format($repayment->outstanding_after, 2) }}</td>
                                            <td class="px-3 py-2 text-right">
                                                <a href="{{ route('receipts.loan-repayment', $repayment) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-700">
                                                    <span class="material-symbols-outlined text-[14px]">print</span>
                                                    Receipt
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Loan Lifecycle Timeline --}}
                @php $timeline = $loan->lifecycleTimeline(); @endphp
                @if (! empty($timeline))
                    <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-[#0F172A] mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-500 text-xl">timeline</span>
                            Loan Lifecycle
                        </h3>
                        <div class="relative">
                            <div class="absolute left-[19px] top-2 bottom-2 w-px bg-slate-200"></div>
                            <div class="space-y-6">
                                @foreach ($timeline as $event)
                                    <div class="relative flex items-start gap-4">
                                        <div class="relative z-10 shrink-0">
                                            @if (! empty($event['actor_avatar']))
                                                <img src="{{ $event['actor_avatar'] }}" alt="{{ $event['actor_name'] }}" class="w-10 h-10 rounded-full object-cover border-2 border-white ring-1 ring-slate-200">
                                            @else
                                                <div class="w-10 h-10 {{ $event['color'] }} rounded-full flex items-center justify-center text-white ring-1 ring-white shadow-sm">
                                                    <span class="material-symbols-outlined text-[18px]">{{ $event['icon'] }}</span>
                                                </div>
                                            @endif
                                            <span class="absolute -bottom-1 -right-1 w-4 h-4 {{ $event['color'] }} rounded-full border-2 border-white flex items-center justify-center">
                                                <span class="material-symbols-outlined text-white text-[10px]">{{ $event['icon'] }}</span>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-sm font-semibold text-[#0F172A]">{{ $event['title'] }}</span>
                                                <span class="text-[11px] text-slate-400">&middot; {{ $event['date']->format('d M Y, g:ia') }}</span>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                <span class="font-medium text-slate-600">{{ $event['actor_name'] }}</span>
                                                @if (! empty($event['description']))
                                                    &middot; {{ $event['description'] }}
                                                @endif
                                            </p>
                                            @if (! empty($event['progress']))
                                                <div class="mt-3 bg-slate-50 border border-slate-200 rounded-[10px] p-3">
                                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                                        <span class="font-medium text-slate-600">Instalment progress</span>
                                                        <span class="font-mono font-semibold text-indigo-600">{{ $event['progress']['paid'] }}/{{ $event['progress']['total'] }}</span>
                                                    </div>
                                                    <div class="w-full bg-slate-200 rounded-full h-2">
                                                        <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ min($event['progress']['percent'], 100) }}%"></div>
                                                    </div>
                                                    @if ($event['progress']['next_due'])
                                                        <p class="text-[11px] text-slate-500 mt-1.5">
                                                            Next due {{ $event['progress']['next_due']->due_date->format('d M Y') }} — ₦{{ number_format((float) $event['progress']['next_due']->total_amount, 2) }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-[#0F172A] mb-4">Progress</h3>
                    @php
                        $repaidPercent = $loan->amount > 0 ? round(($loan->total_repaid / $loan->amount) * 100, 1) : 0;
                    @endphp
                    <div class="space-y-3">
                        <div class="w-full bg-slate-200 rounded-full h-4">
                            <div class="bg-green-500 h-4 rounded-full transition-all" style="width: {{ min($repaidPercent, 100) }}%"></div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Repaid</span>
                            <span class="font-medium">{{ $repaidPercent }}%</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Total Repaid</span>
                            <span class="font-medium">₦{{ number_format($loan->total_repaid, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Outstanding</span>
                            <span class="font-medium text-orange-600">₦{{ number_format($loan->outstanding, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Admin Notes --}}
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-[#0F172A] mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-400 text-xl">sticky_note_2</span>
                        Admin Notes
                    </h3>
                    @if ($loan->admin_notes)
                        <p class="text-sm text-slate-700 mb-3 whitespace-pre-wrap">{{ $loan->admin_notes }}</p>
                    @else
                        <p class="text-sm text-slate-400 italic mb-3">No notes yet.</p>
                    @endif
                    <form method="POST" action="{{ route('loans.note', $loan) }}">
                        @csrf
                        <textarea name="admin_notes" rows="3" class="w-full border border-slate-200 rounded-[10px] text-sm p-2 focus:ring-2 focus:ring-blue-500" placeholder="Add an internal note...">{{ $loan->admin_notes }}</textarea>
                        <button type="submit" class="mt-2 w-full bg-slate-600 hover:bg-slate-700 text-white px-3 py-2 rounded-[10px] text-sm transition">Save Note</button>
                    </form>
                </div>

                {{-- Guarantors Section --}}
                @if ($loan->guarantors->isNotEmpty())
                    <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-[#0F172A] flex items-center gap-2">
                                <span class="material-symbols-outlined text-amber-500 text-xl">group_add</span>
                                Guarantors
                            </h3>
                            @php
                                $acceptedCount = $loan->guarantors->where('status', 'accepted')->count();
                                $totalCount = $loan->guarantors->count();
                            @endphp
                            <span class="text-xs font-medium px-2 py-1 rounded-full
                                {{ $acceptedCount === $totalCount ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $acceptedCount }}/{{ $totalCount }} accepted
                            </span>
                        </div>

                        <div class="space-y-3">
                            @foreach ($loan->guarantors as $guarantor)
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'accepted' => 'bg-green-100 text-green-700',
                                        'declined' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <div class="flex items-center justify-between p-3 rounded-[10px] {{ $guarantor->status->value === 'declined' ? 'bg-red-50 border border-red-100' : 'bg-slate-50 border border-slate-200' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                            {{ strtoupper(substr($guarantor->member->first_name ?? '?', 0, 1) . substr($guarantor->member->last_name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-[#0F172A]">
                                                {{ $guarantor->member->first_name ?? 'Deleted' }} {{ $guarantor->member->last_name ?? '' }}
                                            </p>
                                            <p class="text-[11px] text-slate-500">
                                                {{ $guarantor->member->staff_id_display ?? 'N/A' }}
                                                @if ($guarantor->responded_at)
                                                    &middot; Responded {{ $guarantor->responded_at->format('d M Y') }}
                                                @endif
                                            </p>
                                            @if ($guarantor->notes)
                                                <p class="text-[11px] text-slate-400 mt-0.5 italic">"{{ $guarantor->notes }}"</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 text-[10px] font-semibold rounded-full {{ $statusColors[$guarantor->status->value] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ $guarantor->status->label() }}
                                        </span>
                                        @if ($guarantor->status->value === 'pending' && in_array($loan->status, ['pending']))
                                            <div class="flex items-center gap-1">
                                                <form method="POST" action="{{ route('loans.guarantor.update', [$loan, $guarantor]) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="accepted">
                                                    <button type="submit" class="p-1 rounded-md bg-green-50 hover:bg-green-100 text-green-600 transition" title="Accept">
                                                        <span class="material-symbols-outlined text-[16px]">check</span>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('loans.guarantor.update', [$loan, $guarantor]) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="declined">
                                                    <button type="submit" class="p-1 rounded-md bg-red-50 hover:bg-red-100 text-red-600 transition" title="Decline"
                                                        onclick="return confirm('Decline this guarantor request?')">
                                                        <span class="material-symbols-outlined text-[16px]">close</span>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($loan->status === 'pending')
                            <div class="mt-3 pt-3 border-t border-slate-200">
                                @php
                                    $allAccepted = $loan->guarantors->every(fn($g) => $g->status->value === 'accepted');
                                @endphp
                                @if (!$allAccepted)
                                    <p class="text-xs text-amber-600 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">info</span>
                                        All guarantors must accept before this loan can be approved.
                                    </p>
                                @else
                                    <p class="text-xs text-green-600 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                        All guarantors have accepted. This loan is ready for approval.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-red-600 text-xl">cancel</span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-[#0F172A]">Reject Loan Application</h3>
                    <p class="text-sm text-slate-500">{{ $loan->loan_number }} — ₦{{ number_format($loan->amount, 2) }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('loans.reject', $loan) }}">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-slate-700 mb-1">Reason for rejection <span class="text-red-500">*</span></label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="4" required class="w-full border border-slate-200 rounded-[10px] text-sm p-3 focus:ring-2 focus:ring-red-500" placeholder="Enter the reason for rejecting this loan..."></textarea>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 hover:text-[#0F172A] transition">Cancel</button>
                    <button type="submit" onclick="return confirm('Reject this loan application?')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-[10px] text-sm transition">Reject Loan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
