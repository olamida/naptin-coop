<x-app-layout title="Control Account Reconciliation">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance', 'url' => route('finance.index')], ['label' => 'Control Reconciliation']]" />

        <div>
            <h2 class="text-2xl font-bold text-[#0F172A]">Control Account Reconciliation</h2>
            <p class="text-xs text-slate-500 mt-1">Ledger control accounts vs sub-ledger totals. Variances indicate posting gaps.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @foreach (['savings' => ['Members Savings', 'members_savings'], 'loans' => ['Loans Receivable', 'account_balance'], 'shares' => ['Share Capital', 'trending_up']] as $key => [$title, $icon])
                @php $r = $reports[$key]; @endphp
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-[#0F172A] flex items-center gap-2">
                            <span class="material-symbols-outlined text-slate-400">{{ $icon }}</span> {{ $title }}
                        </h3>
                        @if ($r['reconciled'])
                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-green-100 text-green-700">Reconciled ✓</span>
                        @else
                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-red-100 text-red-700">Variance</span>
                        @endif
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Ledger ({{ $r['code'] }})</span>
                            <span class="font-mono">₦{{ number_format($r['ledger_balance'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Sub-ledger</span>
                            <span class="font-mono">₦{{ number_format($r['sub_ledger_balance'], 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-2">
                            <span class="text-slate-500 font-medium">Variance</span>
                            <span class="font-mono font-semibold {{ $r['variance'] == 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($r['variance'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @unless ($reports['savings']['reconciled'] && $reports['loans']['reconciled'] && $reports['shares']['reconciled'])
            <div class="bg-amber-50 border border-amber-200 rounded-[16px] p-4 text-sm text-amber-800">
                <strong>Action required:</strong> One or more control accounts are out of balance. Review recent postings, reversals, and imports before closing a period.
            </div>
        @endunless
    </div>
</x-app-layout>
