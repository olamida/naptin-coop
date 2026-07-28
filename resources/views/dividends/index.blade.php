<x-app-layout title="Dividends">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Dividends']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Dividends</h2>
            @can('declare-dividends')
                <a href="{{ route('dividends.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                    New Dividend
                </a>
            @endcan
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-blue-500 text-lg">summarize</span>
                    <p class="text-xs text-gray-500">Declared</p>
                </div>
                <p class="text-xl font-bold">{{ $stats['total_declared'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-green-500 text-lg">paid</span>
                    <p class="text-xs text-gray-500">Total Distributed</p>
                </div>
                <p class="text-xl font-bold text-green-700">₦{{ number_format($stats['total_distributed'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-purple-500 text-lg">account_balance</span>
                    <p class="text-xs text-gray-500">Total Profit</p>
                </div>
                <p class="text-xl font-bold text-purple-700">₦{{ number_format($stats['total_profit'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-amber-500 text-lg">schedule</span>
                    <p class="text-xs text-gray-500">Pending Payouts</p>
                </div>
                <p class="text-xl font-bold {{ $stats['pending_distributions'] > 0 ? 'text-amber-600' : '' }}">{{ $stats['pending_distributions'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Dividend #</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Year</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600">Total Profit</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600">Distributed</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600">Members</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($dividends as $dividend)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3 font-mono text-xs">{{ $dividend->dividend_number }}</td>
                            <td class="px-5 py-3">{{ $dividend->year }}</td>
                            <td class="px-5 py-3 text-right">₦{{ number_format($dividend->total_profit, 2) }}</td>
                            <td class="px-5 py-3 text-right font-medium text-green-700">₦{{ number_format($dividend->total_distributed, 2) }}</td>
                            <td class="px-5 py-3 text-right">{{ $dividend->eligible_members }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-600',
                                        'calculated' => 'bg-blue-100 text-blue-700',
                                        'approved' => 'bg-yellow-100 text-yellow-700',
                                        'completed' => 'bg-green-100 text-green-700',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $statusColors[$dividend->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($dividend->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('dividends.show', $dividend) }}" class="text-xs text-blue-600 hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-gray-500">No dividends declared yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $dividends->links() }}
    </div>
</x-app-layout>
