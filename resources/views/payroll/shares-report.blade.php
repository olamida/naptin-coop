<x-app-layout title="Shares Contribution Report">
    <div class="space-y-6">
        <div class="flex items-center justify-between no-print">
            <div class="flex items-center gap-3">
                <a href="{{ route('payroll.show', $payroll) }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Shares Contribution Report</h2>
                    <p class="text-sm text-gray-500">{{ $payroll->month }} {{ $payroll->year }} &middot; {{ $payroll->payroll_number }}</p>
                </div>
            </div>
            <button x-on:click="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-1.5">
                <span class="material-symbols-outlined text-lg">print</span>
                Print Report
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">S/N</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Staff ID</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Member Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Region</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Expected Contribution</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actual Contribution</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($payroll->deductions as $i => $d)
                        @if ($d->expected_share_contribution > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $d->member->staff_id ?? '' }}</td>
                            <td class="px-4 py-3 font-medium">{{ $d->member->full_name ?? '' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $d->member->region->name ?? '' }}</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($d->expected_share_contribution, 2) }}</td>
                            <td class="px-4 py-3 text-right {{ $d->actual_share_contribution > 0 ? 'text-green-700 font-semibold' : '' }}">₦{{ number_format($d->actual_share_contribution, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $d->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($d->status) }}
                                </span>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200 font-semibold">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right">Total Expected:</td>
                        <td class="px-4 py-3 text-right">₦{{ number_format($payroll->total_share_contributions, 2) }}</td>
                        <td class="px-4 py-3 text-right text-green-700">₦{{ number_format($payroll->deductions->sum('actual_share_contribution'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
