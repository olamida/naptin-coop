<x-app-layout title="{{ $member->first_name }} {{ $member->last_name }}">
    <div class="space-y-6" x-data="{ tab: 'overview' }">
        <x-breadcrumb :items="[
            ['label' => 'Members', 'url' => route('members.index')],
            ['label' => $member->full_name],
        ]" />

        {{-- Header --}}
        <div class="bg-white border border-slate-200 rounded-[16px] overflow-hidden">
            <div class="px-6 py-6 flex flex-wrap justify-between items-start gap-4">
                <div class="flex gap-4 items-start">
                    @if ($member->photo_url)
                        <img src="{{ $member->photo_url }}" class="w-16 h-16 rounded-[16px] object-cover border border-slate-200">
                    @else
                        <div class="w-16 h-16 rounded-[16px] bg-slate-100 flex items-center justify-center text-xl font-bold text-slate-500 border border-slate-200">
                            {{ $member->initials }}
                        </div>
                    @endif
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl font-semibold text-[#0F172A]">{{ $member->full_name }}</h1>
                            <x-status-badge :status="$member->status" />
                            <span class="text-[10px] font-mono px-2 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600">{{ $member->staff_id_display }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ $member->region->name ?? 'N/A' }} &middot; {{ $member->email ?? 'No email' }} &middot; {{ $member->phone ?? '' }}</p>
                        <div class="flex gap-2 mt-3">
                            @if ($member->phone)
                                <a href="tel:{{ $member->phone }}" class="text-xs bg-[#0F172A] text-white px-3 py-1.5 rounded-[10px] hover:bg-slate-800 transition">Contact</a>
                            @endif
                            <a href="{{ route('receipts.savings-statement', $member->savingsAccount) }}" class="text-xs bg-white border border-slate-300 px-3 py-1.5 rounded-[10px] hover:bg-slate-50 transition">Statement</a>
                            <a href="{{ route('reports.member-status', $member) }}" class="text-xs bg-white border border-slate-300 px-3 py-1.5 rounded-[10px] hover:bg-slate-50 transition">Report</a>
                        </div>
                    </div>
                </div>
                @php
                    $healthScore = $member->healthScore();
                    $healthColor = $member->healthColor();
                    $borderColor = match($healthColor) {
                        'emerald' => 'border-emerald-500', 'blue' => 'border-blue-500',
                        'amber' => 'border-amber-400', 'rose' => 'border-rose-500',
                        default => 'border-slate-400',
                    };
                    $textColor = match($healthColor) {
                        'emerald' => 'text-emerald-600', 'blue' => 'text-blue-600',
                        'amber' => 'text-amber-600', 'rose' => 'text-rose-600',
                        default => 'text-slate-600',
                    };
                @endphp
                <div class="text-center flex-shrink-0">
                    <div class="w-20 h-20 rounded-full border-4 {{ $borderColor }} flex items-center justify-center bg-white">
                        <span class="text-lg font-mono font-bold {{ $textColor }}">{{ min(999, $healthScore) }}</span>
                    </div>
                    <p class="text-[10px] uppercase tracking-wider text-slate-500 mt-1 font-semibold">Health Score</p>
                    <p class="text-[10px] {{ $textColor }}">{{ $member->healthLabel() }}</p>
                </div>
            </div>
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center gap-2">
                @if ($member->status === 'pending')
                    <form method="POST" action="{{ route('members.approve', $member) }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Approve this member?')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded-[10px] transition">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('members.reject', $member) }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Reject this member?')" class="bg-rose-600 hover:bg-rose-700 text-white text-xs px-3 py-1.5 rounded-[10px] transition">Reject</button>
                    </form>
                @endif
                @can('edit-members')
                    <a href="{{ route('members.edit', $member) }}" class="bg-white border border-slate-300 text-xs px-3 py-1.5 rounded-[10px] hover:bg-slate-50 transition">Edit Member</a>
                @endcan
                <a href="{{ route('products.index', ['member_id' => $member->id]) }}" class="bg-white border border-slate-300 text-xs px-3 py-1.5 rounded-[10px] hover:bg-slate-50 transition">Place Order</a>
                <a href="{{ route('loans.create', ['member_id' => $member->id]) }}" class="bg-white border border-slate-300 text-xs px-3 py-1.5 rounded-[10px] hover:bg-slate-50 transition">New Loan</a>
                <a href="{{ route('savings.deposit', ['member_id' => $member->id]) }}" class="bg-white border border-slate-300 text-xs px-3 py-1.5 rounded-[10px] hover:bg-slate-50 transition">Record Deposit</a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[12px] text-sm">{{ session('success') }}</div>
        @endif

        {{-- Tabs --}}
        <div class="flex items-center gap-1 border-b border-slate-200 overflow-x-auto">
            <button @click="tab = 'overview'" :class="tab === 'overview' ? 'border-[#0F172A] text-[#0F172A]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition whitespace-nowrap">Overview</button>
            <button @click="tab = 'transactions'" :class="tab === 'transactions' ? 'border-[#0F172A] text-[#0F172A]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition whitespace-nowrap">Transactions</button>
            <button @click="tab = 'guarantor-risk'" :class="tab === 'guarantor-risk' ? 'border-[#0F172A] text-[#0F172A]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition whitespace-nowrap">Guarantor Risk</button>
            <button @click="tab = 'documents'" :class="tab === 'documents' ? 'border-[#0F172A] text-[#0F172A]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition whitespace-nowrap">Documents</button>
            <button @click="tab = 'next-of-kin'" :class="tab === 'next-of-kin' ? 'border-[#0F172A] text-[#0F172A]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition whitespace-nowrap">Next of Kin</button>
        </div>

        {{-- Overview Tab --}}
        <div x-show="tab === 'overview'" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Savings Chart --}}
                <div class="bg-white rounded-[16px] border border-slate-200 p-5 shadow-sm">
                    <h4 class="text-sm font-semibold text-[#0F172A] mb-3">Savings Activity • 12 Months</h4>
                    <div style="position: relative; height: 200px;">
                        <canvas id="savingsChart"></canvas>
                    </div>
                    <div class="flex gap-6 mt-4 text-xs">
                        <div><p class="text-slate-500">Current Balance</p><p class="font-mono font-semibold text-[#0F172A]">₦{{ number_format($member->savingsAccount?->balance ?? 0, 2) }}</p></div>
                        <div><p class="text-slate-500">Total Deposits</p><p class="font-mono text-emerald-600">₦{{ number_format($member->savingsAccount?->transactions->where('type', 'deposit')->sum('amount') ?? 0, 2) }}</p></div>
                        <div><p class="text-slate-500">Total Withdrawals</p><p class="font-mono text-rose-600">₦{{ number_format($member->savingsAccount?->transactions->where('type', 'withdrawal')->sum('amount') ?? 0, 2) }}</p></div>
                    </div>
                </div>

                {{-- Loan Timeline --}}
                @if ($activeLoan)
                <div class="bg-white rounded-[16px] border border-slate-200 p-5 shadow-sm">
                    <h4 class="text-sm font-semibold text-[#0F172A] mb-4">Loan Timeline</h4>
                    <div class="relative border-l border-slate-200 ml-2 pl-6 space-y-5">
                        <div class="relative">
                            <span class="absolute -left-[29px] w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"></span>
                            <p class="text-xs font-medium text-slate-800">Applied {{ $activeLoan->loan_number }}</p>
                            <p class="text-[11px] text-slate-500">₦{{ number_format($activeLoan->amount, 2) }} &middot; {{ $activeLoan->created_at->format('d M Y') }}</p>
                        </div>
                        @foreach ($activeLoan->approvalLogs ?? [] as $log)
                            <div class="relative">
                                <span class="absolute -left-[29px] w-3 h-3 bg-[#0F172A] rounded-full border-2 border-white"></span>
                                <p class="text-xs font-medium text-slate-800">{{ ucfirst($log->action) }} by {{ $log->user?->name ?? 'System' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $log->created_at->format('d M Y · h:i A') }}</p>
                            </div>
                        @endforeach
                        <div class="relative">
                            <span class="absolute -left-[29px] w-3 h-3 bg-amber-400 rounded-full border-2 border-white animate-pulse"></span>
                            <p class="text-xs font-medium text-slate-800">Repaying {{ $paidInstallments }}/{{ $totalInstallments }}</p>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full mt-2">
                                <div class="bg-[#0F172A] h-1.5 rounded-full" style="width: {{ $totalInstallments > 0 ? round(($paidInstallments / $totalInstallments) * 100) : 0 }}%"></div>
                            </div>
                            @if ($nextDue)
                                <p class="text-[11px] mt-2 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[13px] {{ $nextDue->due_date->isPast() ? 'text-rose-500' : 'text-slate-400' }}">event</span>
                                    <span class="{{ $nextDue->due_date->isPast() ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
                                        Next due {{ $nextDue->due_date->format('d M Y') }} &middot; ₦{{ number_format($nextDue->total_amount, 2) }}
                                        @if ($nextDue->due_date->isPast()) &middot; OVERDUE @endif
                                    </span>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Sidebar --}}
            <div class="space-y-5">
                {{-- Savings Card --}}
                <div class="bg-white rounded-[16px] border border-slate-200 p-5 shadow-sm">
                    <h4 class="text-sm font-semibold text-[#0F172A] mb-3">Savings Account</h4>
                    @if ($member->savingsAccount)
                        <div class="text-center p-4 bg-gradient-to-br from-emerald-50 to-green-50 rounded-[12px]">
                            <p class="text-xs text-slate-500">Balance</p>
                            <p class="text-2xl font-mono font-bold text-emerald-700 truncate" title="₦{{ number_format($member->savingsAccount->balance, 2) }}">₦{{ number_format($member->savingsAccount->balance, 2) }}</p>
                        </div>
                        <p class="text-xs text-slate-500 text-center mt-2 font-mono">{{ $member->savingsAccount->account_number }}</p>
                    @else
                        <p class="text-sm text-slate-500">No savings account found.</p>
                    @endif
                </div>

                {{-- Share Card --}}
                <div class="bg-white rounded-[16px] border border-slate-200 p-5 shadow-sm">
                    <h4 class="text-sm font-semibold text-[#0F172A] mb-3">Share Account</h4>
                    @if ($member->shareAccount)
                        <div class="text-center p-4 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-[12px]">
                            <p class="text-xs text-slate-500">Total Value</p>
                            <p class="text-2xl font-mono font-bold text-indigo-700 truncate" title="₦{{ number_format($member->shareAccount->total_value, 2) }}">₦{{ number_format($member->shareAccount->total_value, 2) }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ number_format($member->shareAccount->total_shares) }} shares</p>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">No share account found.</p>
                    @endif
                </div>

                {{-- Exposure --}}
                <div class="bg-white rounded-[16px] border border-slate-200 p-5 shadow-sm">
                    <h4 class="text-sm font-semibold text-[#0F172A] mb-3">Exposure Summary</h4>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-slate-500">As Borrower</p>
                            <p class="font-mono font-semibold text-[#0F172A]">₦{{ number_format($member->loans->whereIn('status', ['disbursed', 'repaying'])->sum('outstanding'), 2) }} outstanding</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">As Guarantor</p>
                            <p class="font-mono font-semibold {{ $guarantorRisk->sum(fn($g) => $g->loan->outstanding ?? 0) > 0 ? 'text-amber-600' : 'text-[#0F172A]' }}">
                                ₦{{ number_format($guarantorRisk->sum(fn($g) => $g->loan->outstanding ?? 0), 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transactions Tab --}}
        <div x-show="tab === 'transactions'" x-cloak
             x-data="ledgerCanvas({{ Js::from($ledger) }})"
             class="bg-white rounded-[16px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-[#0F172A]">Unified Ledger</h3>
                <p class="text-xs text-slate-400"><span x-text="filtered().length" class="font-mono font-semibold text-slate-600"></span> of {{ $ledger->count() }} entries</p>
            </div>

            {{-- Filters --}}
            <div class="px-5 py-3 bg-slate-50/70 border-b border-slate-200 grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined text-[16px] text-slate-400 absolute left-3 top-1/2 -translate-y-1/2">search</span>
                    <input type="text" x-model="filters.q" placeholder="Search reference..." class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <select x-model="filters.type" class="w-full px-3 py-2 border border-slate-200 rounded-[10px] text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">All Types</option>
                    <template x-for="label in typeOptions" :key="label">
                        <option :value="label" x-text="label"></option>
                    </template>
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" x-model="filters.from" title="From date" class="w-full px-3 py-2 border border-slate-200 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <input type="date" x-model="filters.to" title="To date" class="w-full px-3 py-2 border border-slate-200 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="flex items-center gap-2">
                    <button @click="clearFilters()" class="text-xs text-slate-500 hover:text-[#0F172A] font-medium transition">Clear</button>
                    <span class="text-xs text-slate-400" x-show="filtered().length === 0">No matches</span>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 sticky top-0">
                        <tr class="border-b border-slate-200">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Balance After</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="entry in filtered()" :key="entry.reference + entry.date_iso + entry.amount">
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap" x-text="entry.date_display"></td>
                                <td class="px-5 py-3">
                                    <span class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs"
                                              :class="categoryStyle(entry.category)">
                                            <span class="material-symbols-outlined text-[12px]"
                                                  x-text="categoryIcon(entry.category)"></span>
                                        </span>
                                        <span class="text-xs text-slate-700" x-text="entry.type_label"></span>
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-mono text-xs text-slate-500" x-text="entry.reference"></td>
                                <td class="px-5 py-3 text-right font-mono text-sm font-medium"
                                    :class="entry.amount >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                    <span x-text="entry.amount >= 0 ? '+' : ''"></span><span x-text="fmtMoney(entry.amount)"></span>
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-xs text-slate-500">
                                    <template x-if="entry.balance_after !== null">
                                        <span x-text="'₦' + fmtMoney(entry.balance_after)"></span>
                                    </template>
                                    <template x-if="entry.balance_after === null">
                                        <span class="text-slate-300">—</span>
                                    </template>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border"
                                          :class="badgeStyle(entry.status)">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="badgeDot(entry.status)"></span>
                                        <span x-text="badgeLabel(entry.status)"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Guarantor Risk Tab --}}
        <div x-show="tab === 'guarantor-risk'" x-cloak class="bg-white rounded-[16px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-[#0F172A]">Guarantor Risk — Members {{ $member->first_name }} guarantees</h3>
            </div>
            @if ($guarantorRisk->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="border-b border-slate-200">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Loan</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Outstanding</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Risk %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($guarantorRisk as $g)
                                @php $riskPct = $g->loan->amount > 0 ? round(($g->loan->outstanding / $g->loan->amount) * 100) : 0; @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('members.show', $g->loan->member) }}" class="text-slate-800 font-medium hover:text-blue-600 transition">{{ $g->loan->member->full_name }}</a>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $g->loan->loan_number }}</td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-slate-800">₦{{ number_format($g->loan->amount, 2) }}</td>
                                    <td class="px-5 py-3 text-right font-mono text-sm {{ $riskPct > 50 ? 'text-rose-600' : ($riskPct > 25 ? 'text-amber-600' : 'text-emerald-600') }}">
                                        ₦{{ number_format($g->loan->outstanding, 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                            {{ $riskPct > 75 ? 'bg-rose-100 text-rose-700' : ($riskPct > 50 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                            {{ $riskPct }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-5 py-12 text-center text-sm text-slate-500">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">security</span>
                    <p>Not a guarantor for any active loan.</p>
                </div>
            @endif
        </div>

        {{-- Documents Tab --}}
        <div x-show="tab === 'documents'" x-cloak class="bg-white rounded-[16px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-[#0F172A]">Uploaded Documents</h3>
            </div>
            @if ($documents->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
                    @foreach ($documents as $doc)
                        <div class="border border-slate-200 rounded-[10px] p-4 hover:shadow-sm transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-[10px] flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-blue-600">description</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-slate-700 truncate">{{ basename($doc->payment_evidence_path) }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $doc->created_at->format('d M Y') }}</p>
                                </div>
                                <a href="{{ Storage::disk('public')->url($doc->payment_evidence_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    <span class="material-symbols-outlined text-lg">open_in_new</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-12 text-center text-sm text-slate-500">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">folder_open</span>
                    <p>No documents uploaded yet.</p>
                </div>
            @endif
        </div>

        {{-- Next of Kin Tab --}}
        <div x-show="tab === 'next-of-kin'" x-cloak class="bg-white rounded-[16px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-[#0F172A]">Next of Kin</h3>
                @can('manage-next-of-kin')
                    <details class="group">
                        <summary class="text-xs text-blue-600 hover:text-blue-800 cursor-pointer font-medium">+ Add</summary>
                        <form method="POST" action="{{ route('members.next-of-kin.store', $member) }}" class="mt-3 space-y-2 p-4 bg-slate-50 rounded-[10px]">
                            @csrf
                            <input type="text" name="name" placeholder="Full Name" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="relationship" placeholder="Relationship" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <input type="text" name="phone" placeholder="Phone" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <input type="email" name="email" placeholder="Email (optional)" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <label class="flex items-center gap-2 text-xs cursor-pointer">
                                <input type="checkbox" name="is_primary" value="1" class="rounded border-slate-300">
                                Set as primary next of kin
                            </label>
                            <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-xs font-medium transition">Add</button>
                        </form>
                    </details>
                @endcan
            </div>
            @if ($member->nextOfKins->isNotEmpty())
                <div class="divide-y divide-slate-50">
                    @foreach ($member->nextOfKins as $kin)
                        <div class="px-5 py-4 flex items-center justify-between hover:bg-slate-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-sm font-bold text-slate-500">
                                    {{ strtoupper(substr($kin->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">{{ $kin->name }}
                                        @if ($kin->is_primary) <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium ml-1">Primary</span> @endif
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $kin->relationship }} &middot; {{ $kin->phone ?? 'N/A' }}</p>
                                </div>
                            </div>
                            @can('manage-next-of-kin')
                                <form id="delete-kin-{{ $kin->id }}" method="POST" action="{{ route('members.next-of-kin.destroy', [$member, $kin]) }}">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="deleteConfirm('delete-kin-{{ $kin->id }}')" class="text-slate-400 hover:text-rose-600 transition p-1">
                                        <span class="material-symbols-outlined text-lg">close</span>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-12 text-center text-sm text-slate-500">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">family_history</span>
                    <p>No next of kin added yet.</p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function ledgerCanvas(ledger) {
            const badgeColors = {
                success: { badge: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500' },
                warning: { badge: 'bg-amber-50 text-amber-700 border-amber-200', dot: 'bg-amber-500' },
                danger:  { badge: 'bg-red-50 text-red-700 border-red-200',       dot: 'bg-red-500' },
                blue:    { badge: 'bg-blue-50 text-blue-700 border-blue-200',    dot: 'bg-blue-500' },
                gray:    { badge: 'bg-gray-50 text-gray-700 border-gray-200',    dot: 'bg-gray-500' },
            };
            const statusColor = {
                active: 'success', completed: 'success', approved: 'success', accepted: 'success', paid: 'success', collected: 'success',
                pending: 'warning', draft: 'warning', compiled: 'warning',
                rejected: 'danger', declined: 'danger', defaulted: 'danger',
                disbursed: 'blue', repaying: 'blue', deducted: 'blue',
                inactive: 'gray', retired: 'gray', suspended: 'gray',
            };
            const categoryStyleMap = {
                savings: 'bg-emerald-100 text-emerald-700',
                loan_repayment: 'bg-blue-100 text-blue-700',
                shares: 'bg-purple-100 text-purple-700',
                purchase: 'bg-orange-100 text-orange-700',
            };
            const categoryIconMap = {
                savings: 'savings',
                loan_repayment: 'payments',
                shares: 'trending_up',
                purchase: 'shopping_cart',
            };
            const fmt = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            return {
                ledger: ledger,
                filters: { q: '', type: '', from: '', to: '' },
                get typeOptions() {
                    return [...new Set(this.ledger.map(e => e.type_label))].sort();
                },
                filtered() {
                    const q = this.filters.q.trim().toLowerCase();
                    return this.ledger.filter(e => {
                        if (this.filters.type && e.type_label !== this.filters.type) return false;
                        if (this.filters.from && e.date_iso < this.filters.from) return false;
                        if (this.filters.to && e.date_iso > this.filters.to) return false;
                        if (q && !(e.reference + ' ' + e.type_label).toLowerCase().includes(q)) return false;
                        return true;
                    });
                },
                clearFilters() {
                    this.filters = { q: '', type: '', from: '', to: '' };
                },
                fmtMoney(n) {
                    return fmt.format(Math.abs(n));
                },
                categoryStyle(cat) {
                    return categoryStyleMap[cat] || 'bg-slate-100 text-slate-600';
                },
                categoryIcon(cat) {
                    return categoryIconMap[cat] || 'receipt_long';
                },
                badgeStyle(status) {
                    return (badgeColors[statusColor[status] || 'gray'] || badgeColors.gray).badge;
                },
                badgeDot(status) {
                    return (badgeColors[statusColor[status] || 'gray'] || badgeColors.gray).dot;
                },
                badgeLabel(status) {
                    return (status || '').charAt(0).toUpperCase() + (status || '').slice(1);
                },
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const savingsCtx = document.getElementById('savingsChart');
            if (savingsCtx) {
                const months = @json($savingsChartMonths->pluck('label'));
                const deposits = @json($savingsChartMonths->pluck('deposits'));
                const withdrawals = @json($savingsChartMonths->pluck('withdrawals'));
                new Chart(savingsCtx, {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [
                            { label: 'Deposits', data: deposits, backgroundColor: '#10B981', borderRadius: 4, borderSkipped: false },
                            { label: 'Withdrawals', data: withdrawals.map(v => -v), backgroundColor: '#F43F5E', borderRadius: 4, borderSkipped: false }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', labels: { boxWidth: 8, padding: 8, font: { size: 10 } } } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => '₦' + (v/1000).toFixed(0) + 'k', font: { size: 10 } } },
                            x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>