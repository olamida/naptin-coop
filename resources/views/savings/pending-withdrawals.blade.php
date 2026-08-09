<x-app-layout title="Pending Approvals">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Savings', 'url' => route('savings.index')], ['label' => 'Pending Approvals']]" />
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Pending Approvals</h2>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                    <kbd class="bg-slate-100 px-1.5 py-0.5 rounded font-mono text-[10px]">A</kbd> approve
                    <kbd class="bg-slate-100 px-1.5 py-0.5 rounded font-mono text-[10px]">R</kbd> reject
                    the first pending request
                </p>
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-slate-200">
            <a href="{{ route('savings.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 -mb-px transition">
                Transactions
            </a>
            <a href="{{ route('savings.accounts') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 -mb-px transition">
                Accounts
            </a>
            <a href="{{ route('savings.pending-withdrawals') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-[#0F172A] text-[#0F172A] -mb-px flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">pending</span>
                Pending ({{ $withdrawals->count() + $pendingDeposits->count() }})
            </a>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[12px] text-sm">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Pending Deposits --}}
        @if ($pendingDeposits->count() > 0)
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-green-600 text-lg">add_circle</span>
                <h3 class="font-semibold text-[#0F172A]">Pending Deposits ({{ $pendingDeposits->count() }})</h3>
            </div>

            @foreach ($pendingDeposits as $txn)
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-green-600 text-xl">add_circle</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-lg font-semibold text-[#0F172A]">
                                        {{ $txn->savingsAccount->member->first_name ?? 'Deleted' }} {{ $txn->savingsAccount->member->last_name ?? '' }}
                                    </h3>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                                    @if ($txn->source === 'member_request')
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Member Request</span>
                                    @endif
                                </div>
                                <p class="text-sm text-slate-500 mt-0.5">
                                    {{ $txn->savingsAccount->member->staff_id_display ?? 'N/A' }}
                                    &middot; Account: {{ $txn->savingsAccount->account_number }}
                                </p>
                                <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3 text-sm">
                                    <div>
                                        <dt class="text-slate-500 text-xs">Deposit Amount</dt>
                                        <dd class="font-bold text-green-600">&#8358;{{ number_format($txn->amount, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500 text-xs">Current Balance</dt>
                                        <dd class="font-medium">&#8358;{{ number_format($txn->savingsAccount->balance, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500 text-xs">After Confirmation</dt>
                                        <dd class="font-medium text-green-700">&#8358;{{ number_format($txn->savingsAccount->balance + $txn->amount, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500 text-xs">Requested</dt>
                                        <dd class="font-medium">{{ $txn->transaction_date->format('d M Y, g:ia') }}</dd>
                                    </div>
                                </dl>
                                @if ($txn->notes)
                                    <p class="text-sm text-slate-600 mt-2 italic">"{{ $txn->notes }}"</p>
                                @endif
                                @if ($txn->payment_evidence_path)
                                    <div class="mt-3">
                                        <p class="text-xs text-slate-500 mb-1">Payment Evidence:</p>
                                        <a href="{{ asset('storage/' . $txn->payment_evidence_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                            <span class="material-symbols-outlined text-[14px]">image</span>
                                            View Evidence
                                        </a>
                                    </div>
                                @endif
                                <p class="text-[10px] font-mono text-slate-400 mt-1">{{ $txn->reference }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <form method="POST" action="{{ route('savings.deposits.confirm', $txn) }}" data-shortcut="approve">
                                @csrf
                                <button type="submit" onclick="return confirm('Confirm this deposit of &#8358;{{ number_format($txn->amount, 2) }}? This will add to the member\'s balance.')"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">check</span>
                                    Confirm @if ($loop->first)<span class="text-white/60 text-xs">(A)</span>@endif
                                </button>
                            </form>
                            <button onclick="document.getElementById('rejectDepositModal{{ $txn->id }}').classList.remove('hidden')"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5" data-shortcut="reject">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                                Reject @if ($loop->first)<span class="text-white/60 text-xs">(R)</span>@endif
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Reject Deposit Modal --}}
                <div id="rejectDepositModal{{ $txn->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="bg-white rounded-[16px] shadow-xl w-full max-w-md mx-4 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-red-600 text-xl">cancel</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-[#0F172A]">Reject Deposit</h3>
                                <p class="text-sm text-slate-500">&#8358;{{ number_format($txn->amount, 2) }} — {{ $txn->savingsAccount->member->first_name ?? '' }} {{ $txn->savingsAccount->member->last_name ?? '' }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('savings.deposits.reject', $txn) }}">
                            @csrf
                            <div class="mb-4">
                                <label for="rejection_reason{{ $txn->id }}" class="block text-sm font-medium text-slate-700 mb-1">Reason for rejection <span class="text-red-500">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason{{ $txn->id }}" rows="3" required class="w-full border border-slate-200 rounded-[10px] text-sm p-3 focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Enter reason..."></textarea>
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="document.getElementById('rejectDepositModal{{ $txn->id }}').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 hover:text-[#0F172A] transition">Cancel</button>
                                <button type="submit" onclick="return confirm('Reject this deposit?')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-[10px] text-sm transition">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Pending Withdrawals --}}
        @if ($withdrawals->count() > 0)
            <div class="flex items-center gap-2 mb-2 {{ $pendingDeposits->count() > 0 ? 'mt-8' : '' }}">
                <span class="material-symbols-outlined text-amber-600 text-lg">pending</span>
                <h3 class="font-semibold text-[#0F172A]">Pending Withdrawals ({{ $withdrawals->count() }})</h3>
            </div>

            @foreach ($withdrawals as $txn)
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-amber-600 text-xl">pending</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-lg font-semibold text-[#0F172A]">
                                        {{ $txn->savingsAccount->member->first_name ?? 'Deleted' }} {{ $txn->savingsAccount->member->last_name ?? '' }}
                                    </h3>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                                </div>
                                <p class="text-sm text-slate-500 mt-0.5">
                                    {{ $txn->savingsAccount->member->staff_id_display ?? 'N/A' }}
                                    &middot; Account: {{ $txn->savingsAccount->account_number }}
                                </p>
                                <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3 text-sm">
                                    <div>
                                        <dt class="text-slate-500 text-xs">Withdrawal Amount</dt>
                                        <dd class="font-bold text-red-600">&#8358;{{ number_format($txn->amount, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500 text-xs">Current Balance</dt>
                                        <dd class="font-medium">&#8358;{{ number_format($txn->savingsAccount->balance, 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500 text-xs">Remaining After</dt>
                                        <dd class="font-medium {{ ($txn->savingsAccount->balance - $txn->amount) < 0 ? 'text-red-600' : 'text-green-700' }}">
                                            &#8358;{{ number_format($txn->savingsAccount->balance - $txn->amount, 2) }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500 text-xs">Requested</dt>
                                        <dd class="font-medium">{{ $txn->transaction_date->format('d M Y, g:ia') }}</dd>
                                    </div>
                                </dl>
                                @if ($txn->notes)
                                    <p class="text-sm text-slate-600 mt-2 italic">"{{ $txn->notes }}"</p>
                                @endif
                                <p class="text-[10px] font-mono text-slate-400 mt-1">{{ $txn->reference }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <form method="POST" action="{{ route('savings.withdrawals.approve', $txn) }}" data-shortcut="approve">
                                @csrf
                                <button type="submit" onclick="return confirm('Approve this withdrawal of &#8358;{{ number_format($txn->amount, 2) }}? This will deduct from the member\'s balance.')"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">check</span>
                                    Approve @if ($loop->first && $pendingDeposits->isEmpty())<span class="text-white/60 text-xs">(A)</span>@endif
                                </button>
                            </form>
                            <button onclick="document.getElementById('rejectModal{{ $txn->id }}').classList.remove('hidden')"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5" data-shortcut="reject">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                                Reject @if ($loop->first && $pendingDeposits->isEmpty())<span class="text-white/60 text-xs">(R)</span>@endif
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Reject Modal --}}
                <div id="rejectModal{{ $txn->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="bg-white rounded-[16px] shadow-xl w-full max-w-md mx-4 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-red-600 text-xl">cancel</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-[#0F172A]">Reject Withdrawal</h3>
                                <p class="text-sm text-slate-500">&#8358;{{ number_format($txn->amount, 2) }} — {{ $txn->savingsAccount->member->first_name ?? '' }} {{ $txn->savingsAccount->member->last_name ?? '' }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('savings.withdrawals.reject', $txn) }}">
                            @csrf
                            <div class="mb-4">
                                <label for="rejection_reason{{ $txn->id }}" class="block text-sm font-medium text-slate-700 mb-1">Reason for rejection <span class="text-red-500">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason{{ $txn->id }}" rows="3" required class="w-full border border-slate-200 rounded-[10px] text-sm p-3 focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Enter reason..."></textarea>
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="document.getElementById('rejectModal{{ $txn->id }}').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 hover:text-[#0F172A] transition">Cancel</button>
                                <button type="submit" onclick="return confirm('Reject this withdrawal?')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-[10px] text-sm transition">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif

        @if ($withdrawals->count() === 0 && $pendingDeposits->count() === 0)
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-green-600 text-3xl">check_circle</span>
                </div>
                <h3 class="text-lg font-semibold text-[#0F172A] mb-1">All caught up!</h3>
                <p class="text-sm text-slate-500">No pending requests to review.</p>
                <a href="{{ route('savings.index') }}" class="mt-4 inline-block text-sm text-blue-600 hover:underline">View all transactions</a>
            </div>
        @endif
    </div>
</x-app-layout>
