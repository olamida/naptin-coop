<x-app-layout title="Statistics">
    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Management', 'url' => route('admin.manage')],
            ['label' => 'Statistics'],
        ]" />

        <div>
            <h2 class="text-2xl font-bold text-[#0F172A]">System Statistics</h2>
            <p class="text-xs text-slate-500 mt-1">Login activity, errors, user/member data and site overview</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-medium">Users</p>
                <p class="text-2xl font-bold text-[#0F172A] mt-1">{{ $totalUsers }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-medium">Total Members</p>
                <p class="text-2xl font-bold text-[#0F172A] mt-1">{{ $totalMembers }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-medium">Active Members</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $activeMembers }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-medium">Total Savings</p>
                <p class="text-2xl font-bold text-[#0F172A] mt-1">₦{{ number_format($totalSavings / 1000, 0) }}k</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-medium">Total Loans</p>
                <p class="text-2xl font-bold text-[#0F172A] mt-1">{{ $totalLoans }}</p>
                <p class="text-[10px] text-slate-400">{{ $activeLoans }} active</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-medium">Products</p>
                <p class="text-2xl font-bold text-[#0F172A] mt-1">{{ $totalProducts }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-[#0F172A] mb-3">Logins by Day (Last 30 Days)</h3>
                <div class="relative" style="height: 220px;">
                    <canvas id="loginsChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-[#0F172A] mb-3">Top Users by Login Count</h3>
                <table class="w-full text-sm">
                    <thead class="border-b border-slate-100">
                        <tr>
                            <th class="text-left py-2 font-medium text-slate-500 text-xs uppercase tracking-wider">User</th>
                            <th class="text-right py-2 font-medium text-slate-500 text-xs uppercase tracking-wider">Logins</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($loginsByUser as $row)
                            <tr>
                                <td class="py-2 text-[#0F172A] text-xs">{{ $row->name }}</td>
                                <td class="py-2 text-right font-semibold text-xs">{{ $row->count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-center text-xs text-slate-400">No login data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-[#0F172A] mb-3">Members by Region</h3>
                <div class="relative" style="height: 200px;">
                    <canvas id="regionChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-[#0F172A] mb-3">Members by Status</h3>
                <div class="relative" style="height: 200px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-[#0F172A] mb-3">Financial Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Total Savings</span>
                        <span class="font-semibold text-[#0F172A]">₦{{ number_format($totalSavings, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Total Shares</span>
                        <span class="font-semibold text-[#0F172A]">₦{{ number_format($totalShares, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Loan Value</span>
                        <span class="font-semibold text-[#0F172A]">₦{{ number_format($totalLoanValue, 2) }}</span>
                    </div>
                    <hr>
                    @foreach ($purchaseSummary as $ps)
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500 capitalize">{{ $ps->status }} orders ({{ $ps->count }})</span>
                            <span class="font-medium text-[#0F172A]">₦{{ number_format($ps->total, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-semibold text-[#0F172A]">Recent Failed Logins</h3>
                    @if ($recentErrors->count() > 0)
                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-red-100 text-red-700">{{ $recentErrors->count() }} recent</span>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="text-left px-5 py-2.5 font-medium text-slate-600 text-xs">Time</th>
                                <th class="text-left px-5 py-2.5 font-medium text-slate-600 text-xs">IP Address</th>
                                <th class="text-left px-5 py-2.5 font-medium text-slate-600 text-xs">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse ($recentErrors as $error)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-2.5 text-xs text-slate-500">{{ $error->created_at->format('M d, Y H:i:s') }}</td>
                                    <td class="px-5 py-2.5 font-mono text-xs text-slate-600">{{ $error->ip_address ?? 'N/A' }}</td>
                                    <td class="px-5 py-2.5 text-xs text-slate-600">{{ $error->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-6 text-center text-xs text-slate-400">
                                        <span class="material-symbols-outlined text-2xl text-slate-300 mb-1 block">check_circle</span>
                                        No failed login attempts
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h3 class="font-semibold text-[#0F172A]">All Activity Log</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-5 py-2.5 font-medium text-slate-600 text-xs">Time</th>
                            <th class="text-left px-5 py-2.5 font-medium text-slate-600 text-xs">Event</th>
                            <th class="text-left px-5 py-2.5 font-medium text-slate-600 text-xs">User</th>
                            <th class="text-left px-5 py-2.5 font-medium text-slate-600 text-xs">Description</th>
                            <th class="text-left px-5 py-2.5 font-medium text-slate-600 text-xs">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($recentActivity as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-2.5 text-xs text-slate-500">{{ $log->created_at->format('M d, H:i') }}</td>
                                <td class="px-5 py-2.5">
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                        {{ $log->event === 'login' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $log->event === 'login_failed' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $log->event === 'logout' ? 'bg-slate-100 text-slate-600' : '' }}
                                        {{ !in_array($log->event, ['login', 'login_failed', 'logout']) ? 'bg-blue-100 text-blue-700' : '' }}">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 text-xs text-[#0F172A]">{{ $log->user->name ?? 'N/A' }}</td>
                                <td class="px-5 py-2.5 text-xs text-slate-600 max-w-[300px] truncate">{{ $log->description }}</td>
                                <td class="px-5 py-2.5 font-mono text-xs text-slate-500">{{ $log->ip_address ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">No activity logged yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-200">
                {{ $recentActivity->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logins by Day chart
            const loginsCtx = document.getElementById('loginsChart');
            if (loginsCtx) {
                new Chart(loginsCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($loginsByDay->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
                        datasets: [{
                            label: 'Logins',
                            data: {!! json_encode($loginsByDay->pluck('count')) !!},
                            backgroundColor: '#3b82f6',
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            }

            // Members by Region
            const regionCtx = document.getElementById('regionChart');
            if (regionCtx) {
                new Chart(regionCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($membersByRegion->pluck('region.name', 'region.name')->keys()) !!},
                        datasets: [{
                            data: {!! json_encode($membersByRegion->pluck('count')) !!},
                            backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } }
                    }
                });
            }

            // Members by Status
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                const statusColors = { active: '#10b981', inactive: '#9ca3af', retired: '#f59e0b', suspended: '#ef4444' };
                const statusData = {!! json_encode($membersByStatus) !!};
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1)),
                        datasets: [{
                            data: statusData.map(s => s.count),
                            backgroundColor: statusData.map(s => statusColors[s.status] || '#6b7280'),
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
