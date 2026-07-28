<x-app-layout title="Payroll Summary Report">
    <div class="space-y-6">
        <div class="flex items-center justify-between no-print">
            <div class="flex items-center gap-3">
                <a href="{{ route('payroll.show', $payroll) }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Payroll Summary Report</h2>
                    <p class="text-sm text-gray-500">{{ $payroll->month }} {{ $payroll->year }} &middot; {{ $payroll->payroll_number }}</p>
                </div>
            </div>
            <button x-on:click="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-1.5">
                <span class="material-symbols-outlined text-lg">print</span>
                Print Report
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 no-print">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Total Savings</p>
                <p class="text-xl font-bold text-green-600">₦{{ number_format($payroll->total_savings, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Total Loan Repayments</p>
                <p class="text-xl font-bold text-orange-600">₦{{ number_format($payroll->total_loan_repayments, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Total Share Contributions</p>
                <p class="text-xl font-bold text-purple-600">₦{{ number_format($payroll->total_share_contributions, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Total Purchase Deductions</p>
                <p class="text-xl font-bold text-blue-600">₦{{ number_format($payroll->total_purchases, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-gray-600">S/N</th>
                        <th class="text-left px-3 py-3 font-medium text-gray-600">Staff ID</th>
                        <th class="text-left px-3 py-3 font-medium text-gray-600">Member Name</th>
                        <th class="text-left px-3 py-3 font-medium text-gray-600">Region</th>
                        <th class="text-right px-3 py-3 font-medium text-gray-600">Salary</th>
                        <th class="text-right px-3 py-3 font-medium text-gray-600">Savings</th>
                        <th class="text-right px-3 py-3 font-medium text-gray-600">Loan</th>
                        <th class="text-right px-3 py-3 font-medium text-gray-600">Shares</th>
                        <th class="text-right px-3 py-3 font-medium text-gray-600">Purchases</th>
                        <th class="text-right px-3 py-3 font-medium text-gray-600">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($payroll->deductions as $i => $d)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2.5 text-gray-500 text-xs">{{ $i + 1 }}</td>
                            <td class="px-3 py-2.5 font-mono text-xs">{{ $d->member->staff_id ?? '' }}</td>
                            <td class="px-3 py-2.5 font-medium text-xs">{{ $d->member->full_name ?? '' }}</td>
                            <td class="px-3 py-2.5 text-gray-600 text-xs">{{ $d->member->region->name ?? '' }}</td>
                            <td class="px-3 py-2.5 text-right text-xs">₦{{ number_format($d->member->monthly_salary ?? 0, 2) }}</td>
                            <td class="px-3 py-2.5 text-right text-xs">₦{{ number_format($d->expected_savings, 2) }}</td>
                            <td class="px-3 py-2.5 text-right text-xs">₦{{ number_format($d->expected_loan_repayment, 2) }}</td>
                            <td class="px-3 py-2.5 text-right text-xs">₦{{ number_format($d->expected_share_contribution, 2) }}</td>
                            <td class="px-3 py-2.5 text-right text-xs">₦{{ number_format($d->expected_purchase, 2) }}</td>
                            <td class="px-3 py-2.5 text-right text-xs font-semibold">₦{{ number_format($d->total_expected, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200 font-bold text-sm">
                    <tr>
                        <td colspan="5" class="px-3 py-3 text-right">Grand Total:</td>
                        <td class="px-3 py-3 text-right">₦{{ number_format($payroll->total_savings, 2) }}</td>
                        <td class="px-3 py-3 text-right">₦{{ number_format($payroll->total_loan_repayments, 2) }}</td>
                        <td class="px-3 py-3 text-right">₦{{ number_format($payroll->total_share_contributions, 2) }}</td>
                        <td class="px-3 py-3 text-right">₦{{ number_format($payroll->total_purchases, 2) }}</td>
                        <td class="px-3 py-3 text-right">₦{{ number_format($payroll->grand_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
