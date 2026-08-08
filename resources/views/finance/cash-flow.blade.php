<x-app-layout title="Cash Flow Statement">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance', 'url' => route('finance.index')], ['label' => 'Cash Flow']]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Cash Flow Statement</h2>
                <p class="text-xs text-slate-500 mt-1">Direct method &middot; Generated {{ now()->format('M d, Y h:i A') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-report-export-buttons route="finance.export.cash-flow" :params="['from' => $from, 'to' => $to]" />
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

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <p class="text-xs text-slate-500">Total Inflows</p>
                <p class="text-xl font-mono font-semibold text-emerald-600 mt-1">₦{{ number_format($inflows, 2) }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <p class="text-xs text-slate-500">Total Outflows</p>
                <p class="text-xl font-mono font-semibold text-rose-600 mt-1">₦{{ number_format($outflows, 2) }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <p class="text-xs text-slate-500">Net Cash Flow</p>
                <p class="text-xl font-mono font-semibold {{ $netCash >= 0 ? 'text-[#0F172A]' : 'text-rose-600' }} mt-1">₦{{ number_format($netCash, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs text-slate-500">
                        <th class="px-5 py-3">Entry</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3 text-right">Inflow (₦)</th>
                        <th class="px-5 py-3 text-right">Outflow (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($entries as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-2.5 font-mono text-xs text-slate-400">{{ $row['entry_number'] }}</td>
                            <td class="px-5 py-2.5 text-slate-600">{{ $row['entry_date'] }}</td>
                            <td class="px-5 py-2.5 text-slate-700">{{ $row['description'] }}</td>
                            <td class="px-5 py-2.5 text-right font-mono text-emerald-600">{{ $row['inflow'] > 0 ? '₦'.number_format($row['inflow'], 2) : '—' }}</td>
                            <td class="px-5 py-2.5 text-right font-mono text-rose-600">{{ $row['outflow'] > 0 ? '₦'.number_format($row['outflow'], 2) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-sm">No cash movements in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
