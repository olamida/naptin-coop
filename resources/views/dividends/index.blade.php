<x-app-layout title="Dividends">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Dividends']]" />
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Dividends</h2>
                <p class="text-xs text-slate-500 mt-1">Declare, calculate and distribute annual dividends to members</p>
            </div>
            @can('declare-dividends')
                <a href="{{ route('dividends.create') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                    New Dividend
                </a>
            @endcan
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Declared</p>
                <p class="mt-2 text-2xl font-bold text-[#0F172A]">{{ $stats['total_declared'] }}</p>
                <p class="text-xs text-slate-400 mt-1">Dividend declarations</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Distributed</p>
                <p class="mt-2 text-2xl font-mono font-bold text-emerald-700 truncate" title="₦{{ number_format($stats['total_distributed'], 2) }}">₦{{ number_format($stats['total_distributed'], 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Paid out to members</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Profit</p>
                <p class="mt-2 text-2xl font-mono font-bold text-purple-700 truncate" title="₦{{ number_format($stats['total_profit'], 2) }}">₦{{ number_format($stats['total_profit'], 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Across all declarations</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Pending Payouts</p>
                <p class="mt-2 text-2xl font-bold {{ $stats['pending_distributions'] > 0 ? 'text-amber-600' : 'text-slate-800' }}">{{ $stats['pending_distributions'] }}</p>
                <p class="text-xs {{ $stats['pending_distributions'] > 0 ? 'text-amber-500' : 'text-slate-400' }} mt-1">
                    {{ $stats['pending_distributions'] > 0 ? 'Awaiting distribution' : 'All paid' }}
                </p>
            </div>
        </div>

        {{-- Action Queue --}}
        @if ($stats['pending_distributions'] > 0)
            <div class="bg-white border border-amber-200 rounded-[16px] p-4 flex justify-between items-center hover:shadow-sm transition">
                <div>
                    <p class="text-sm font-medium text-[#0F172A]">{{ $stats['pending_distributions'] }} Distribution{{ $stats['pending_distributions'] > 1 ? 's' : '' }} Pending</p>
                    <p class="text-xs text-slate-500">Dividend calculations need approval and distribution</p>
                </div>
                <a href="{{ route('dividends.index') }}" class="bg-[#0F172A] text-white text-xs px-3 py-2 rounded-[10px] hover:bg-slate-800 transition">Review</a>
            </div>
        @endif

        <div class="bg-white rounded-[16px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Dividend #</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Year</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Profit</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Distributed</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Members</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($dividends as $dividend)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $dividend->dividend_number }}</td>
                                <td class="px-5 py-3.5 text-slate-800 font-medium">{{ $dividend->year }}</td>
                                <td class="px-5 py-3.5 text-right font-mono text-slate-800">₦{{ number_format($dividend->total_profit, 2) }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-medium text-emerald-700">₦{{ number_format($dividend->total_distributed, 2) }}</td>
                                <td class="px-5 py-3.5 text-right text-slate-600">{{ $dividend->eligible_members }}</td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-slate-50 text-slate-600 border-slate-200',
                                            'calculated' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'approved' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full border {{ $statusColors[$dividend->status] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                        {{ ucfirst($dividend->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('dividends.show', $dividend) }}" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 transition">
                                        View
                                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">diversity_3</span>
                                    <p class="text-sm">No dividends declared yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $dividends->links() }}
    </div>
</x-app-layout>