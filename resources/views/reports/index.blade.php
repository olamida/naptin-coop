<x-app-layout title="Member Status Reports">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Reports']]" />

        <div>
            <h2 class="text-2xl font-bold text-[#0F172A]">Member Status Reports</h2>
            <p class="text-xs text-slate-500 mt-1">Select a member to generate a printable status report showing their savings, shares, loans, and purchases.</p>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input type="text" id="memberSearch" placeholder="Search by name or staff ID..."
                           class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-[#0F172A] focus:border-[#0F172A] outline-none transition"
                           oninput="filterMembers()">
                </div>
            </div>
            <div id="membersList" class="divide-y divide-slate-50">
                @forelse ($members as $member)
                    <div class="member-row px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer"
                         data-search="{{ strtolower($member->first_name . ' ' . $member->last_name . ' ' . $member->staff_id . ' ' . $member->staff_id_display) }}"
                         onclick="window.location='{{ route('reports.member-status', $member) }}'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-slate-100 rounded-[10px] flex items-center justify-center text-xs font-bold text-slate-600">
                                {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-[#0F172A]">{{ $member->first_name }} {{ $member->last_name }}</p>
                                <p class="text-xs text-slate-500">{{ $member->staff_id_display }} &middot; {{ $member->region->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500 font-mono">₦{{ number_format($member->monthly_salary, 2) }}/mo</span>
                            <span class="material-symbols-outlined text-slate-400 text-lg">chevron_right</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <span class="material-symbols-outlined text-4xl text-slate-300">description</span>
                        <p class="text-sm text-slate-500 mt-2">No active members found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        function filterMembers() {
            const query = document.getElementById('memberSearch').value.toLowerCase();
            document.querySelectorAll('.member-row').forEach(row => {
                const search = row.dataset.search;
                row.style.display = search.includes(query) ? '' : 'none';
            });
        }
    </script>
</x-app-layout>
