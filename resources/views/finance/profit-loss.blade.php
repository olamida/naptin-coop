<x-app-layout title="Profit & Loss">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance', 'url' => route('finance.index')], ['label' => 'Profit & Loss']]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Profit & Loss</h2>
                <p class="text-xs text-slate-500 mt-1">Generated {{ now()->format('M d, Y h:i A') }} &middot; {{ auth()->user()->name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-report-export-buttons route="finance.export.profit-loss" :params="['from' => $from, 'to' => $to]" />
                <button onclick="window.print()" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">print</span> Print
                </button>
            </div>
        </div>

        <form method="GET" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">From</label>
                <input type="date" name="from" value="{{ $from }}" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">To</label>
                <input type="date" name="to" value="{{ $to }}" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-[#0F172A] text-white px-4 py-2 rounded-[10px] text-sm font-medium">Apply</button>
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-3 bg-emerald-50 border-b border-emerald-100">
                    <h3 class="text-sm font-semibold text-emerald-800">INCOME</h3>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($income as $row)
                            <tr>
                                <td class="px-5 py-2.5 font-mono text-xs text-slate-400">{{ $row['account']->code }}</td>
                                <td class="px-5 py-2.5 text-slate-700">{{ $row['account']->name }}</td>
                                <td class="px-5 py-2.5 text-right font-mono text-emerald-600">₦{{ number_format($row['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400 text-sm">No income posted in this period.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-emerald-50 font-semibold">
                            <td colspan="2" class="px-5 py-3 text-slate-700">Total Income</td>
                            <td class="px-5 py-3 text-right font-mono text-emerald-700">₦{{ number_format($totalIncome, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-3 bg-rose-50 border-b border-rose-100">
                    <h3 class="text-sm font-semibold text-rose-800">EXPENSES</h3>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($expenses as $row)
                            <tr>
                                <td class="px-5 py-2.5 font-mono text-xs text-slate-400">{{ $row['account']->code }}</td>
                                <td class="px-5 py-2.5 text-slate-700">{{ $row['account']->name }}</td>
                                <td class="px-5 py-2.5 text-right font-mono text-rose-600">₦{{ number_format($row['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400 text-sm">No expenses posted in this period.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-rose-50 font-semibold">
                            <td colspan="2" class="px-5 py-3 text-slate-700">Total Expenses</td>
                            <td class="px-5 py-3 text-right font-mono text-rose-700">₦{{ number_format($totalExpenses, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500">Net Profit ({{ $from }} → {{ $to }})</p>
                <p class="text-2xl font-mono font-bold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">₦{{ number_format($netProfit, 2) }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $netProfit >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}
            </span>
        </div>
    </div>
</x-app-layout>
