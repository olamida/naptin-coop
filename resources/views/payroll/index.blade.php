<x-app-layout title="Payroll">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Payroll']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Payroll</h2>
            @can('compile-payroll')
                <a href="{{ route('payroll.compile') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    + Compile Payroll
                </a>
            @endcan
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-blue-500 text-lg">receipt_long</span>
                    <p class="text-xs text-gray-500">Total Payrolls</p>
                </div>
                <p class="text-xl font-bold">{{ $stats['total_payrolls'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-green-500 text-lg">payments</span>
                    <p class="text-xs text-gray-500">Total Deductions</p>
                </div>
                <p class="text-xl font-bold text-green-700">₦{{ number_format($stats['total_deductions'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-purple-500 text-lg">people</span>
                    <p class="text-xs text-gray-500">Members (Latest)</p>
                </div>
                <p class="text-xl font-bold">{{ $stats['total_members'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-amber-500 text-lg">info</span>
                    <p class="text-xs text-gray-500">Latest Status</p>
                </div>
                <p class="text-xl font-bold capitalize">{{ $stats['latest_status'] ?? 'N/A' }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Payroll #</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Period</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Members</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Grand Total</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($payrolls as $payroll)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs">{{ $payroll->payroll_number }}</td>
                            <td class="px-4 py-3">{{ $payroll->month }} {{ $payroll->year }}</td>
                            <td class="px-4 py-3 text-right">{{ $payroll->member_count }}</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($payroll->grand_total, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">{{ ucfirst($payroll->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('payroll.show', $payroll) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No payroll records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $payrolls->links() }}
    </div>
</x-app-layout>
