<x-app-layout title="Control Account Reconciliation">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance', 'url' => route('finance.index')], ['label' => 'Control Reconciliation']]" />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Control Account Reconciliation</h2>
                <p class="text-xs text-slate-500 mt-1">Ledger control accounts vs sub-ledger totals. Variances indicate posting gaps.</p>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('finance.sync-opening-balances') }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Post opening-balance journal entries so the ledger matches the sub-ledger? This is a one-time conversion step.');"
                            class="inline-flex items-center gap-2 bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                        <span class="material-symbols-outlined text-lg">sync</span>
                        Sync Opening Balances
                    </button>
                </form>
                <a href="{{ route('finance.audit-trail') }}" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-[10px] text-sm font-medium transition">
                    <span class="material-symbols-outlined text-lg">verified_user</span>
                    Audit Trail
                </a>
            </div>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @php
                $cards = [
                    'savings' => ['Members Savings', 'savings', 'bg-emerald-100', 'text-emerald-600'],
                    'loans' => ['Loans Receivable', 'account_balance', 'bg-amber-100', 'text-amber-600'],
                    'shares' => ['Share Capital', 'trending_up', 'bg-violet-100', 'text-violet-600'],
                    'purchases' => ['Purchase Receivables', 'shopping_bag', 'bg-blue-100', 'text-blue-600'],
                ];
            @endphp
            @foreach ($cards as $key => [$title, $icon, $iconBg, $iconColor])
                @php $r = $reports[$key]; @endphp
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5 min-w-0">
                    <div class="flex items-start justify-between mb-3 gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="shrink-0 w-9 h-9 rounded-xl {{ $iconBg }} flex items-center justify-center">
                                <span class="material-symbols-outlined {{ $iconColor }} text-lg">{{ $icon }}</span>
                            </span>
                            <h3 class="text-sm font-semibold text-[#0F172A] leading-tight">{{ $title }}</h3>
                        </div>
                        @if ($r['reconciled'])
                            <span class="shrink-0 px-2 py-0.5 text-[10px] font-medium rounded-full bg-green-100 text-green-700">Reconciled</span>
                        @else
                            <span class="shrink-0 px-2 py-0.5 text-[10px] font-medium rounded-full bg-red-100 text-red-700">Variance</span>
                        @endif
                    </div>
                    <div class="space-y-2 text-sm min-w-0">
                        <div class="flex justify-between gap-2">
                            <span class="text-slate-500">Ledger ({{ $r['code'] }})</span>
                            <span class="font-mono break-all text-right">₦{{ number_format($r['ledger_balance'], 2) }}</span>
                        </div>
                        <div class="flex justify-between gap-2">
                            <span class="text-slate-500">Sub-ledger</span>
                            <span class="font-mono break-all text-right">₦{{ number_format($r['sub_ledger_balance'], 2) }}</span>
                        </div>
                        <div class="flex justify-between gap-2 border-t border-slate-100 pt-2">
                            <span class="text-slate-500 font-medium">Variance</span>
                            <span class="font-mono font-semibold break-all text-right {{ $r['variance'] == 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($r['variance'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @php $allReconciled = collect($reports)->every(fn ($r) => $r['reconciled']); @endphp

        @unless ($allReconciled)
            <div class="bg-amber-50 border border-amber-200 rounded-[16px] p-4 text-sm text-amber-800">
                <strong>Action required:</strong> One or more control accounts are out of balance. Existing transactions may predate the ledger module — run
                <strong>Sync Opening Balances</strong> to post a one-time conversion entry, then review recent postings, reversals, and imports before closing a period.
            </div>
        @endunless
    </div>
</x-app-layout>
