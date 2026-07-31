<x-app-layout title="Purchase Deduction Report">
    <style>
        @media print {
            @page { size: landscape; margin: 10mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            table { font-size: 8px !important; width: 100% !important; }
            th, td { padding: 3px 4px !important; }
        }
    </style>
    <div class="space-y-6">
        <div class="flex items-center justify-between no-print">
            <div class="flex items-center gap-3">
                <a href="{{ route('payroll.show', $payroll) }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
                <div>
                    <h2 class="text-2xl font-bold text-[#0F172A]">Purchase Deduction Report</h2>
                    <p class="text-sm text-slate-500">{{ $payroll->month }} {{ $payroll->year }} &middot; {{ $payroll->payroll_number }}</p>
                </div>
            </div>
            <button x-on:click="window.print()" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium flex items-center gap-1.5">
                <span class="material-symbols-outlined text-lg">print</span>
                Print Report
            </button>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">S/N</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Staff ID</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Member Name</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Region</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Expected Deduction</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Actual Deduction</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($payroll->deductions->where('expected_purchase', '>', 0) as $i => $d)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $d->member->staff_id_display ?? '' }}</td>
                            <td class="px-4 py-3 font-medium">{{ $d->member->full_name ?? '' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $d->member->region->name ?? '' }}</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($d->expected_purchase, 2) }}</td>
                            <td class="px-4 py-3 text-right {{ $d->actual_purchase > 0 ? 'text-green-700 font-semibold' : '' }}">₦{{ number_format($d->actual_purchase, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $d->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($d->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200 font-semibold">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right">Total Expected:</td>
                        <td class="px-4 py-3 text-right">₦{{ number_format($payroll->total_purchases, 2) }}</td>
                        <td class="px-4 py-3 text-right text-green-700">₦{{ number_format($payroll->deductions->sum('actual_purchase'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
