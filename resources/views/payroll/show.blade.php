<x-app-layout title="Payroll {{ $payroll->payroll_number }}">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('payroll.index') }}" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-gray-800">Payroll {{ $payroll->payroll_number }}</h2>
            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">{{ ucfirst($payroll->status) }}</span>
        </div>

        {{-- Payroll Progress Stepper --}}
        @php
            $payStepperCurrent = match($payroll->status) {
                'compiled' => 1,
                'deducted' => 2,
                'completed' => 3,
                default => 1,
            };
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Payroll Progress</h3>
            <x-stepper :steps="[
                ['label' => 'Compiled', 'icon' => 'list_alt'],
                ['label' => 'Deducted', 'icon' => 'download_done'],
                ['label' => 'Completed', 'icon' => 'verified'],
            ]" :current="$payStepperCurrent" />
        </div>

        @if (in_array($payroll->status, ['compiled', 'deducted']))
            <div class="flex items-center gap-2">
                <a href="{{ route('payroll.upload', $payroll) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">upload_file</span>
                    Upload Actual Deductions
                </a>
                <a href="{{ route('payroll.export-deductions', $payroll) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Export Excel
                </a>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Period</p>
                <p class="text-lg font-bold">{{ $payroll->month }} {{ $payroll->year }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Members</p>
                <p class="text-lg font-bold">{{ $payroll->member_count }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Total Savings</p>
                <p class="text-lg font-bold text-green-700">₦{{ number_format($payroll->total_savings, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Total Purchases</p>
                <p class="text-lg font-bold text-orange-700">₦{{ number_format($payroll->total_purchases ?? 0, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Grand Total</p>
                <p class="text-lg font-bold text-blue-700">₦{{ number_format($payroll->grand_total, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Deduction Reports</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <a href="{{ route('payroll.summary-report', $payroll) }}" target="_blank" class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition text-sm">
                    <span class="material-symbols-outlined text-gray-500 text-lg">summarize</span>
                    Full Summary
                </a>
                <a href="{{ route('payroll.savings-report', $payroll) }}" target="_blank" class="flex items-center gap-2 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition text-sm">
                    <span class="material-symbols-outlined text-green-600 text-lg">savings</span>
                    Savings Report
                </a>
                <a href="{{ route('payroll.loans-report', $payroll) }}" target="_blank" class="flex items-center gap-2 p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition text-sm">
                    <span class="material-symbols-outlined text-orange-600 text-lg">account_balance</span>
                    Loans Report
                </a>
                <a href="{{ route('payroll.shares-report', $payroll) }}" target="_blank" class="flex items-center gap-2 p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition text-sm">
                    <span class="material-symbols-outlined text-purple-600 text-lg">trending_up</span>
                    Shares Report
                </a>
                <a href="{{ route('payroll.purchases-report', $payroll) }}" target="_blank" class="flex items-center gap-2 p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition text-sm">
                    <span class="material-symbols-outlined text-blue-600 text-lg">shopping_cart</span>
                    Purchases Report
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Member Deductions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Member</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Region</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Savings</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Loan</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Shares</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Purchase</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Total Expected</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Total Actual</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($payroll->deductions as $deduction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">
                                    {{ $deduction->member->first_name ?? '' }} {{ $deduction->member->last_name ?? '' }}
                                    <p class="text-xs text-gray-500 font-normal">{{ $deduction->member->staff_id ?? '' }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $deduction->member->region->code ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-right text-xs">₦{{ number_format($deduction->expected_savings, 2) }}</td>
                                <td class="px-4 py-3 text-right text-xs">₦{{ number_format($deduction->expected_loan_repayment, 2) }}</td>
                                <td class="px-4 py-3 text-right text-xs">₦{{ number_format($deduction->expected_share_contribution, 2) }}</td>
                                <td class="px-4 py-3 text-right text-xs">₦{{ number_format($deduction->expected_purchase ?? 0, 2) }}</td>
                                <td class="px-4 py-3 text-right text-xs font-medium">₦{{ number_format($deduction->total_expected, 2) }}</td>
                                <td class="px-4 py-3 text-right text-xs font-medium {{ $deduction->total_actual > 0 ? 'text-green-700' : 'text-gray-400' }}">
                                    ₦{{ number_format($deduction->total_actual, 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                        {{ $deduction->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ ucfirst($deduction->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500">No deductions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200 font-medium text-xs">
                        <tr>
                            <td colspan="2" class="px-4 py-3">Totals</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($payroll->deductions->sum('expected_savings'), 2) }}</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($payroll->deductions->sum('expected_loan_repayment'), 2) }}</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($payroll->deductions->sum('expected_share_contribution'), 2) }}</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($payroll->deductions->sum('expected_purchase'), 2) }}</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($payroll->deductions->sum('total_expected'), 2) }}</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($payroll->deductions->sum('total_actual'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
