<x-app-layout title="Payroll">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Payroll']]" />
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Payroll</h2>
                <p class="text-xs text-slate-500 mt-1">Compile, manage and reconcile monthly payroll deductions</p>
            </div>
            @can('compile-payroll')
                <a href="{{ route('payroll.compile') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Compile Payroll
                </a>
            @endcan
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Payrolls</p>
                <p class="mt-2 text-2xl font-bold text-[#0F172A]">{{ $stats['total_payrolls'] }}</p>
                <p class="text-xs text-slate-400 mt-1">All time runs</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Deductions</p>
                <p class="mt-2 text-2xl font-mono font-bold text-emerald-700 truncate" title="₦{{ number_format($stats['total_deductions'], 2) }}">₦{{ number_format($stats['total_deductions'], 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Aggregate deducted</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Members</p>
                <p class="mt-2 text-2xl font-bold text-[#0F172A]">{{ $stats['total_members'] }}</p>
                <p class="text-xs text-slate-400 mt-1">In latest payroll</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Latest Status</p>
                <p class="mt-2 text-2xl font-bold capitalize {{ $stats['latest_status'] === 'completed' ? 'text-emerald-600' : ($stats['latest_status'] === 'deducted' ? 'text-blue-600' : 'text-amber-600') }}">
                    {{ $stats['latest_status'] ?? 'N/A' }}
                </p>
                <p class="text-xs text-slate-400 mt-1">Current payroll run</p>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[12px] text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-[16px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Payroll #</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Period</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Members</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Grand Total</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($payrolls as $payroll)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $payroll->payroll_number }}</td>
                                <td class="px-5 py-3.5 text-slate-800 font-medium">{{ $payroll->month }} {{ $payroll->year }}</td>
                                <td class="px-5 py-3.5 text-right text-slate-600">{{ $payroll->member_count }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-medium text-slate-800">₦{{ number_format($payroll->grand_total, 2) }}</td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $statusStyles = [
                                            'draft' => 'bg-slate-50 text-slate-600 border-slate-200',
                                            'compiled' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'deducted' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full border {{ $statusStyles[$payroll->status] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                        {{ ucfirst($payroll->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('payroll.show', $payroll) }}" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 transition">
                                        View
                                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">payments</span>
                                    <p class="text-sm">No payroll records found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $payrolls->links() }}
    </div>
</x-app-layout>