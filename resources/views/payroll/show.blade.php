<x-app-layout title="Payroll {{ $payroll->payroll_number }}">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('payroll.index') }}" class="text-slate-500 hover:text-slate-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-[#0F172A]">Payroll {{ $payroll->payroll_number }}</h2>
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
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-[#0F172A] mb-4">Payroll Progress</h3>
            <x-stepper :steps="[
                ['label' => 'Compiled', 'icon' => 'list_alt'],
                ['label' => 'Deducted', 'icon' => 'download_done'],
                ['label' => 'Completed', 'icon' => 'verified'],
            ]" :current="$payStepperCurrent" />
        </div>

        @if (in_array($payroll->status, ['compiled', 'deducted']))
            <div class="flex items-center gap-2">
                <a href="{{ route('payroll.upload', $payroll) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">upload_file</span>
                    Upload Actual Deductions
                </a>
                <a href="{{ route('payroll.export-deductions', $payroll) }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Export Excel
                </a>
                <a href="{{ route('payroll.export-csv', $payroll) }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">description</span>
                    Download CSV
                </a>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Period</p>
                <p class="text-lg font-bold">{{ $payroll->month }} {{ $payroll->year }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Members</p>
                <p class="text-lg font-bold">{{ $payroll->member_count }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Total Savings</p>
                <p class="text-lg font-bold text-green-700">₦{{ number_format($payroll->total_savings, 2) }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Total Purchases</p>
                <p class="text-lg font-bold text-orange-700">₦{{ number_format($payroll->total_purchases ?? 0, 2) }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Grand Total</p>
                <p class="text-lg font-bold text-blue-700">₦{{ number_format($payroll->grand_total, 2) }}</p>
            </div>
        </div>

        {{-- Reconciliation Summary --}}
        @php
            $deductions = $payroll->deductions;
            $reconTotalExpected = (float) $deductions->sum('total_expected');
            $reconTotalActual = (float) $deductions->sum('total_actual');
            $reconVariance = round($reconTotalExpected - $reconTotalActual, 2);
            $reconTotal = $deductions->count();
            $reconMatched = $deductions->filter(fn($d) => round((float) $d->total_expected - (float) $d->total_actual, 2) == 0)->count();
            $reconPercent = $reconTotal ? round(($reconMatched / $reconTotal) * 100) : 0;
            $reconShort = $deductions->filter(fn($d) => round((float) $d->total_expected - (float) $d->total_actual, 2) > 0)->count();
            $reconOver = $deductions->filter(fn($d) => round((float) $d->total_expected - (float) $d->total_actual, 2) < 0)->count();
        @endphp
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-[#0F172A]">Deduction Reconciliation</h3>
                <span class="px-2 py-1 text-[10px] font-medium rounded-full {{ $reconVariance == 0 ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $reconVariance == 0 ? 'Fully Reconciled' : 'Variance Detected' }}
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-slate-50 rounded-[16px] p-4 border border-slate-200">
                    <p class="text-xs text-slate-500">Expected Deductions (system)</p>
                    <p class="text-2xl font-bold font-mono text-[#0F172A] mt-1">₦{{ number_format($reconTotalExpected, 2) }}</p>
                </div>
                <div class="bg-slate-50 rounded-[16px] p-4 border border-slate-200">
                    <p class="text-xs text-slate-500">Actual Deductions (uploaded)</p>
                    <p class="text-2xl font-bold font-mono text-emerald-600 mt-1">₦{{ number_format($reconTotalActual, 2) }}</p>
                </div>
                <div class="bg-slate-50 rounded-[16px] p-4 border border-slate-200">
                    <p class="text-xs text-slate-500">Variance</p>
                    <p class="text-2xl font-bold font-mono {{ $reconVariance == 0 ? 'text-green-600' : 'text-rose-600' }} mt-1">
                        {{ $reconVariance > 0 ? '+' : '' }}{{ number_format($reconVariance, 2) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $reconVariance == 0 ? 'No shortfall' : ($reconVariance > 0 ? $reconVariance . ' shortfall — under-deducted' : abs($reconVariance) . ' surplus — over-deducted') }}
                    </p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                    <span>{{ $reconMatched }} of {{ $reconTotal }} members fully matched</span>
                    <span>{{ $reconPercent }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full transition-all {{ $reconPercent === 100 ? 'bg-green-500' : ($reconPercent >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                         style="width: {{ $reconPercent }}%"></div>
                </div>
                @if ($reconShort > 0 || $reconOver > 0)
                    <div class="mt-4 flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-[10px] px-4 py-3 text-sm text-amber-800">
                        <span class="material-symbols-outlined text-lg mt-0.5">info</span>
                        <p>
                            @if ($reconShort > 0)
                                <strong>{{ $reconShort }} member(s)</strong> under-deducted — follow up with the salary department to reconcile the shortfall.
                            @endif
                            @if ($reconShort > 0 && $reconOver > 0) &nbsp;&middot;&nbsp; @endif
                            @if ($reconOver > 0)
                                <strong>{{ $reconOver }} member(s)</strong> over-deducted — excess must be refunded or carried forward.
                            @endif
                        </p>
                    </div>
                @endif
                @if ($reconShort > 0)
                    <div class="mt-3 flex items-start gap-2 bg-blue-50 border border-blue-200 rounded-[10px] px-4 py-3 text-sm text-blue-800">
                        <span class="material-symbols-outlined text-lg mt-0.5">flag</span>
                        <p>Carry uncollected shortfalls into the next payroll by flagging them as <strong>arrears</strong> — see the Arrears panel below.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Payroll Arrears --}}
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-[#0F172A]">Payroll Arrears</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Shortfalls carried forward for collection in the next payroll</p>
                </div>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('payroll.arrears.bulk', $payroll) }}" onsubmit="return confirm('Flag every under-deducted member on this payroll as arrears?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0F172A] text-white text-xs font-medium rounded-[10px] hover:bg-slate-800 transition">
                            <span class="material-symbols-outlined text-sm">flag</span>
                            Flag all shortfalls
                        </button>
                    </form>
                </div>
            </div>

            @php
                $openArrears = $payroll->arrears()->with('member')->open()->orderBy('shortfall', 'desc')->get();
                $settledArrears = $payroll->arrears()->with('member')->settled()->get();
                $openArrearsTotal = round($openArrears->sum('shortfall'), 2);
            @endphp

            @if ($openArrears->isEmpty() && $settledArrears->isEmpty())
                <div class="px-6 py-8 text-center">
                    <span class="material-symbols-outlined text-4xl text-slate-300">flag</span>
                    <p class="text-sm text-slate-500 mt-3">No arrears recorded. Use "Flag all shortfalls" above to carry uncollected deductions into the next payroll.</p>
                </div>
            @endif

            @if ($openArrears->isNotEmpty())
                <div class="border-b border-slate-100 bg-rose-50/60 px-6 py-2 flex items-center justify-between">
                    <span class="text-xs font-semibold text-rose-700 uppercase tracking-wide">{{ $openArrears->count() }} open arrear(s)</span>
                    <span class="text-xs font-mono font-semibold text-rose-700">₦{{ number_format($openArrearsTotal, 2) }}</span>
                </div>
                @foreach ($openArrears as $arrear)
                    <div class="border-b border-slate-100 px-6 py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="material-symbols-outlined text-slate-400">flag</span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $arrear->member->first_name }} {{ $arrear->member->last_name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ $arrear->member->staff_id_display ?? '' }} &middot; {{ $arrear->reason ?: 'Uncollected shortfall' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="text-sm font-mono font-semibold text-rose-600">₦{{ number_format($arrear->shortfall, 2) }}</span>
                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-amber-100 text-amber-700">Open</span>
                            <form method="POST" action="{{ route('payroll.arrears.settle', $arrear) }}">
                                @csrf
                                <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">Mark settled</button>
                            </form>
                            <form method="POST" action="{{ route('payroll.arrears.destroy', $arrear) }}" onsubmit="return confirm('Remove this arrear?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-slate-400 hover:text-rose-600">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif

            @if ($settledArrears->isNotEmpty())
                <div class="bg-slate-50 px-6 py-3">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">{{ $settledArrears->count() }} settled</p>
                    @foreach ($settledArrears as $arrear)
                        <div class="flex items-center justify-between gap-3 py-1">
                            <p class="text-sm text-slate-500 line-through">{{ $arrear->member->first_name }} {{ $arrear->member->last_name }}</p>
                            <span class="text-xs text-slate-400 font-mono">₦{{ number_format($arrear->shortfall, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
            <h3 class="font-semibold text-[#0F172A] mb-3">Deduction Reports</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <a href="{{ route('payroll.summary-report', $payroll) }}" target="_blank" class="flex items-center gap-2 p-3 bg-slate-50 rounded-[10px] hover:bg-slate-100 transition text-sm">
                    <span class="material-symbols-outlined text-slate-500 text-lg">summarize</span>
                    Full Summary
                </a>
                <a href="{{ route('payroll.savings-report', $payroll) }}" target="_blank" class="flex items-center gap-2 p-3 bg-green-50 rounded-[10px] hover:bg-green-100 transition text-sm">
                    <span class="material-symbols-outlined text-green-600 text-lg">savings</span>
                    Savings Report
                </a>
                <a href="{{ route('payroll.loans-report', $payroll) }}" target="_blank" class="flex items-center gap-2 p-3 bg-orange-50 rounded-[10px] hover:bg-orange-100 transition text-sm">
                    <span class="material-symbols-outlined text-orange-600 text-lg">account_balance</span>
                    Loans Report
                </a>
                <a href="{{ route('payroll.shares-report', $payroll) }}" target="_blank" class="flex items-center gap-2 p-3 bg-purple-50 rounded-[10px] hover:bg-purple-100 transition text-sm">
                    <span class="material-symbols-outlined text-purple-600 text-lg">trending_up</span>
                    Shares Report
                </a>
                <a href="{{ route('payroll.purchases-report', $payroll) }}" target="_blank" class="flex items-center gap-2 p-3 bg-blue-50 rounded-[10px] hover:bg-blue-100 transition text-sm">
                    <span class="material-symbols-outlined text-blue-600 text-lg">shopping_cart</span>
                    Purchases Report
                </a>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-[#0F172A]">Member Deductions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th rowspan="2" class="text-left px-4 py-3 font-medium text-slate-600">Member</th>
                            <th rowspan="2" class="text-left px-4 py-3 font-medium text-slate-600">Region</th>
                            <th colspan="6" class="text-center px-4 py-3 font-medium text-slate-600 border-l border-slate-200 bg-blue-50/60">Expected</th>
                            <th colspan="6" class="text-center px-4 py-3 font-medium text-slate-600 border-l border-slate-200 bg-emerald-50/60">Actual</th>
                            <th rowspan="2" class="text-right px-4 py-3 font-medium text-slate-600 border-l border-slate-200">Variance</th>
                            <th rowspan="2" class="text-left px-4 py-3 font-medium text-slate-600">Status</th>
                        </tr>
                        <tr>
                            <th class="text-right px-3 py-2 font-medium text-slate-500 border-l border-slate-200">Savings</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500">Loan</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500">Shares</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500">Purchase</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500">Arrears</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500">Total</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500 border-l border-slate-200">Savings</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500">Loan</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500">Shares</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500">Purchase</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500">Arrears</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($payroll->deductions as $deduction)
                            @php
                                $variance = round((float) $deduction->total_expected - (float) $deduction->total_actual, 2);
                            @endphp
                            <tr class="hover:bg-slate-50 {{ $variance != 0 ? 'bg-rose-50/60' : '' }}">
                                <td class="px-4 py-3 font-medium">
                                    {{ $deduction->member->first_name ?? '' }} {{ $deduction->member->last_name ?? '' }}
                                    <p class="text-xs text-slate-500 font-normal">{{ $deduction->member->staff_id_display ?? '' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs">{{ $deduction->member->region->code ?? 'N/A' }}</td>
                                <td class="px-3 py-3 text-right text-xs border-l border-slate-100">₦{{ number_format($deduction->expected_savings, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs">₦{{ number_format($deduction->expected_loan_repayment, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs">₦{{ number_format($deduction->expected_share_contribution, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs">₦{{ number_format($deduction->expected_purchase ?? 0, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs">₦{{ number_format($deduction->expected_arrears ?? 0, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs font-medium">₦{{ number_format($deduction->total_expected, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs border-l border-slate-100">{{ number_format($deduction->actual_savings ?? 0, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs">{{ number_format($deduction->actual_loan_repayment ?? 0, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs">{{ number_format($deduction->actual_share_contribution ?? 0, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs">{{ number_format($deduction->actual_purchase ?? 0, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs">{{ number_format($deduction->actual_arrears ?? 0, 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs font-medium {{ (float) $deduction->total_actual > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                                    ₦{{ number_format($deduction->total_actual, 2) }}
                                </td>
                                <td class="px-3 py-3 text-right text-xs font-mono font-semibold border-l border-slate-100 {{ $variance == 0 ? 'text-green-600' : 'text-rose-600' }}">
                                    {{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}
                                    @if ($variance != 0)
                                        <span class="block text-[9px] font-medium {{ $variance > 0 ? 'text-rose-500' : 'text-amber-600' }}">
                                            {{ $variance > 0 ? 'short' : 'over' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                        {{ $deduction->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ ucfirst($deduction->status) }}
                                    </span>
                                    @if ($variance > 0)
                                        <form method="POST" action="{{ route('payroll.arrears.store', $payroll) }}" class="mt-1.5">
                                            @csrf
                                            <input type="hidden" name="member_id" value="{{ $deduction->member_id }}">
                                            <button type="submit" class="text-[10px] text-rose-600 hover:text-rose-800 font-medium flex items-center gap-0.5">
                                                <span class="material-symbols-outlined text-[12px]">flag</span>
                                                Flag as arrear
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="16" class="px-4 py-8 text-center text-slate-500">No deductions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 border-t border-slate-200 font-medium text-xs">
                        <tr>
                            <td colspan="2" class="px-4 py-3">Totals</td>
                            <td class="px-3 py-3 text-right">₦{{ number_format($deductions->sum('expected_savings'), 2) }}</td>
                            <td class="px-3 py-3 text-right">₦{{ number_format($deductions->sum('expected_loan_repayment'), 2) }}</td>
                            <td class="px-3 py-3 text-right">₦{{ number_format($deductions->sum('expected_share_contribution'), 2) }}</td>
                            <td class="px-3 py-3 text-right">₦{{ number_format($deductions->sum('expected_purchase'), 2) }}</td>
                            <td class="px-3 py-3 text-right">₦{{ number_format($deductions->sum('expected_arrears'), 2) }}</td>
                            <td class="px-3 py-3 text-right">₦{{ number_format($reconTotalExpected, 2) }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($deductions->sum('actual_savings'), 2) }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($deductions->sum('actual_loan_repayment'), 2) }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($deductions->sum('actual_share_contribution'), 2) }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($deductions->sum('actual_purchase'), 2) }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($deductions->sum('actual_arrears'), 2) }}</td>
                            <td class="px-3 py-3 text-right">₦{{ number_format($reconTotalActual, 2) }}</td>
                            <td class="px-3 py-3 text-right font-mono font-semibold {{ $reconVariance == 0 ? 'text-green-600' : 'text-rose-600' }}">
                                {{ $reconVariance > 0 ? '+' : '' }}{{ number_format($reconVariance, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
