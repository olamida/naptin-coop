<x-app-layout title="Finance & Compliance">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance & Compliance']]" />

        <div>
            <h2 class="text-2xl font-bold text-[#0F172A]">Finance & Compliance</h2>
            <p class="text-xs text-slate-500 mt-1">CBN-compliant financial statements, period closing, loan provisioning, and audit controls.</p>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-[10px] text-sm">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">
                @foreach ($errors->all() as $error) {{ $error }} @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $tiles = [
                    ['route' => 'finance.period-close', 'icon' => 'lock_clock', 'color' => 'bg-indigo-100', 'icon_color' => 'text-indigo-600', 'title' => 'Period Close', 'desc' => 'Close financial periods to freeze postings'],
                    ['route' => 'finance.profit-loss', 'icon' => 'monitoring', 'color' => 'bg-emerald-100', 'icon_color' => 'text-emerald-600', 'title' => 'Profit & Loss', 'desc' => 'Income statement for any period'],
                    ['route' => 'finance.balance-sheet', 'icon' => 'account_balance_wallet', 'color' => 'bg-blue-100', 'icon_color' => 'text-blue-600', 'title' => 'Balance Sheet', 'desc' => 'Assets = Liabilities + Equity check'],
                    ['route' => 'finance.cash-flow', 'icon' => 'currency_exchange', 'color' => 'bg-cyan-100', 'icon_color' => 'text-cyan-600', 'title' => 'Cash Flow', 'desc' => 'Direct method cash inflows & outflows'],
                    ['route' => 'finance.loan-aging', 'icon' => 'fact_check', 'color' => 'bg-amber-100', 'icon_color' => 'text-amber-600', 'title' => 'Loan Aging', 'desc' => 'IFRS 9 aging buckets + provisioning'],
                    ['route' => 'finance.control-reconciliation', 'icon' => 'balance', 'color' => 'bg-violet-100', 'icon_color' => 'text-violet-600', 'title' => 'Control Reconciliation', 'desc' => 'Ledger vs sub-ledger variance check'],
                    ['route' => 'finance.audit-trail', 'icon' => 'verified_user', 'color' => 'bg-rose-100', 'icon_color' => 'text-rose-600', 'title' => 'Audit Trail', 'desc' => 'Activity logs + ledger hash verification'],
                ];
            @endphp

            @foreach ($tiles as $tile)
                <a href="{{ route($tile['route']) }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 hover:shadow-md hover:border-slate-300 transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl {{ $tile['color'] }} flex items-center justify-center group-hover:scale-105 transition">
                            <span class="material-symbols-outlined {{ $tile['icon_color'] }} text-2xl">{{ $tile['icon'] }}</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">{{ $tile['title'] }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $tile['desc'] }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($closedPeriods->isNotEmpty())
            <div class="bg-indigo-50 border border-indigo-200 rounded-[16px] p-4 text-sm text-indigo-800">
                <strong>Closed periods:</strong> {{ $closedPeriods->implode(', ') }}
            </div>
        @endif
    </div>
</x-app-layout>
