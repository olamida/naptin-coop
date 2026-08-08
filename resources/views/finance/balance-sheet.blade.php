<x-app-layout title="Balance Sheet">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance', 'url' => route('finance.index')], ['label' => 'Balance Sheet']]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Balance Sheet</h2>
                <p class="text-xs text-slate-500 mt-1">As at {{ \Carbon\Carbon::parse($asOf)->format('M d, Y') }} &middot; Generated {{ now()->format('M d, Y h:i A') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-report-export-buttons route="finance.export.balance-sheet" :params="['as_of' => $asOf]" />
                <button onclick="window.print()" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">print</span> Print
                </button>
            </div>
        </div>

        <form method="GET" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4 flex items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">As at</label>
                <input type="date" name="as_of" value="{{ $asOf }}" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-[#0F172A] text-white px-4 py-2 rounded-[10px] text-sm font-medium">Apply</button>
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-3 bg-blue-50 border-b border-blue-100">
                    <h3 class="text-sm font-semibold text-blue-800">ASSETS</h3>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($assets as $row)
                            <tr>
                                <td class="px-5 py-2.5 font-mono text-xs text-slate-400">{{ $row['account']->code }}</td>
                                <td class="px-5 py-2.5 text-slate-700">{{ $row['account']->name }}</td>
                                <td class="px-5 py-2.5 text-right font-mono">₦{{ number_format($row['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400 text-sm">No asset balances.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-blue-50 font-semibold">
                            <td colspan="2" class="px-5 py-3 text-slate-700">Total Assets</td>
                            <td class="px-5 py-3 text-right font-mono text-blue-800">₦{{ number_format($totalAssets, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-3 bg-violet-50 border-b border-violet-100">
                        <h3 class="text-sm font-semibold text-violet-800">LIABILITIES</h3>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($liabilities as $row)
                                <tr>
                                    <td class="px-5 py-2.5 font-mono text-xs text-slate-400">{{ $row['account']->code }}</td>
                                    <td class="px-5 py-2.5 text-slate-700">{{ $row['account']->name }}</td>
                                    <td class="px-5 py-2.5 text-right font-mono">₦{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-6 text-center text-slate-400 text-sm">No liabilities.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-violet-50 font-semibold">
                                <td colspan="2" class="px-5 py-3 text-slate-700">Total Liabilities</td>
                                <td class="px-5 py-3 text-right font-mono text-violet-800">₦{{ number_format($totalLiabilities, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-3 bg-emerald-50 border-b border-emerald-100">
                        <h3 class="text-sm font-semibold text-emerald-800">EQUITY</h3>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($equityRows as $row)
                                <tr>
                                    <td class="px-5 py-2.5 font-mono text-xs text-slate-400">{{ $row['account']->code }}</td>
                                    <td class="px-5 py-2.5 text-slate-700">{{ $row['account']->name }}</td>
                                    <td class="px-5 py-2.5 text-right font-mono">₦{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-6 text-center text-slate-400 text-sm">No equity.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-emerald-50 font-semibold">
                                <td colspan="2" class="px-5 py-3 text-slate-700">Total Equity</td>
                                <td class="px-5 py-3 text-right font-mono text-emerald-800">₦{{ number_format($totalEquity, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm">
                    <p class="text-slate-500">Assets</p>
                    <p class="font-mono font-semibold text-[#0F172A]">₦{{ number_format($assetsSide, 2) }}</p>
                </div>
                <div class="text-center">
                    <span class="text-xs text-slate-400">=</span>
                </div>
                <div class="text-sm">
                    <p class="text-slate-500">Liabilities + Equity</p>
                    <p class="font-mono font-semibold text-[#0F172A]">₦{{ number_format($liabilitiesSide, 2) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500 mb-1">Variance</p>
                    @if (abs($variance) < 0.01)
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Balanced ✓</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Off by ₦{{ number_format(abs($variance), 2) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
