<x-app-layout title="Daily Cash Reconciliation">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance', 'url' => route('finance.index')], ['label' => 'Daily Cash Reconciliation']]" />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Daily Cash Reconciliation</h2>
                <p class="text-xs text-slate-500 mt-1">Report 8 — record the physical cash count each day. Variances are posted to Cash Suspense (1005).</p>
            </div>
            <a href="{{ route('finance.control-reconciliation') }}" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-[10px] text-sm font-medium transition">
                <span class="material-symbols-outlined text-lg">balance</span>
                Control Reconciliation
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-[10px] text-sm">{{ session('success') }}</div>
        @endif

        @if (session('info'))
            <div class="bg-slate-50 border border-slate-200 text-slate-600 px-4 py-3 rounded-[10px] text-sm">{{ session('info') }}</div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">
                @foreach ($errors->all() as $error) {{ $error }} @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-1 space-y-6">
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-slate-600 text-lg">payments</span>
                        </span>
                        <div>
                            <h3 class="text-sm font-semibold text-[#0F172A]">System Cash Balance</h3>
                            <p class="text-[10px] text-slate-500">Ledger account 1001 (Cash &amp; Bank)</p>
                        </div>
                    </div>
                    <p class="text-2xl font-bold font-mono text-[#0F172A]">₦{{ number_format($systemBalance, 2) }}</p>
                    <p class="text-[11px] text-slate-500 mt-1">Compare this against the physical cash on hand today.</p>
                </div>

                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-[#0F172A] mb-4">Record Cash Count</h3>
                    <form method="POST" action="{{ route('finance.cash-count.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1" for="count_date">Count Date</label>
                            <input type="date" id="count_date" name="count_date" value="{{ old('count_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required
                                   class="w-full rounded-[10px] border-slate-300 text-sm focus:border-slate-400 focus:ring-slate-300">
                            @error('count_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1" for="physical_count">Physical Cash Count (₦)</label>
                            <input type="number" step="0.01" min="0" id="physical_count" name="physical_count" value="{{ old('physical_count') }}" required placeholder="0.00"
                                   class="w-full rounded-[10px] border-slate-300 text-sm focus:border-slate-400 focus:ring-slate-300 font-mono">
                            @error('physical_count') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1" for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="2" placeholder="Optional — e.g. cashier desk count at close of day"
                                      class="w-full rounded-[10px] border-slate-300 text-sm focus:border-slate-400 focus:ring-slate-300">{{ old('notes') }}</textarea>
                            @error('notes') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2.5 rounded-[10px] text-sm font-medium transition">
                            <span class="material-symbols-outlined text-lg">save</span>
                            Save Cash Count
                        </button>
                    </form>
                </div>
            </div>

            <div class="xl:col-span-2">
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-[#0F172A]">Count History</h3>
                        <span class="text-[11px] text-slate-500">{{ $counts->total() }} recorded</span>
                    </div>

                    @forelse ($counts as $count)
                        <div class="px-5 py-4 border-b border-slate-100 last:border-0 flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-sm text-[#0F172A]">{{ $count->count_date->format('D, M j, Y') }}</span>
                                    <x-status-badge :status="$count->status" />
                                    @if ($count->verified_by)
                                        <span class="text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2 py-0.5 inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[11px]">verified</span> Verified
                                        </span>
                                    @endif
                                </div>
                                @if ($count->notes)
                                    <p class="text-xs text-slate-500 mt-1 truncate">{{ $count->notes }}</p>
                                @endif
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Counted by {{ $count->countedBy?->name ?? '—' }}
                                    @if ($count->verified_by)
                                        · Verified by {{ $count->verifiedBy?->name ?? '—' }}
                                    @endif
                                </p>
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-right shrink-0">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wide text-slate-400">System</p>
                                    <p class="text-xs font-mono text-slate-700">₦{{ number_format($count->system_balance, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Physical</p>
                                    <p class="text-xs font-mono text-slate-700">₦{{ number_format($count->physical_count, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Variance</p>
                                    <p class="text-xs font-mono font-semibold {{ $count->variance == 0 ? 'text-green-600' : 'text-red-600' }}">{{ $count->variance > 0 ? '+' : '' }}₦{{ number_format($count->variance, 2) }}</p>
                                </div>
                            </div>
                            @if (! $count->verified_by)
                                <form method="POST" action="{{ route('finance.cash-count.verify', $count) }}" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-[10px] text-xs font-medium transition">
                                        <span class="material-symbols-outlined text-sm">task_alt</span>
                                        Verify
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">payments</span>
                            <p class="text-sm text-slate-500">No cash counts recorded yet.</p>
                            <p class="text-xs text-slate-400 mt-1">Record the first physical count for today using the form.</p>
                        </div>
                    @endforelse

                    <div class="px-5 py-3 border-t border-slate-100">
                        {{ $counts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
