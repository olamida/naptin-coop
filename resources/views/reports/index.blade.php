<x-app-layout title="Member Status Reports">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Reports']]" />
        <h2 class="text-xl font-bold text-gray-800">Member Status Reports</h2>
        <p class="text-sm text-gray-600">Select a member to generate a printable status report showing their savings, shares, loans, and purchases.</p>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <input type="text" id="memberSearch" placeholder="Search by name or staff ID..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                       oninput="filterMembers()">
            </div>
            <div id="membersList" class="divide-y divide-gray-100">
                @forelse ($members as $member)
                    <div class="member-row px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition cursor-pointer"
                         data-search="{{ strtolower($member->first_name . ' ' . $member->last_name . ' ' . $member->staff_id) }}"
                         onclick="window.location='{{ route('reports.member-status', $member) }}'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-xs font-bold text-gray-600">
                                {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $member->first_name }} {{ $member->last_name }}</p>
                                <p class="text-xs text-gray-500">{{ $member->staff_id }} &middot; {{ $member->region->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">₦{{ number_format($member->monthly_salary, 2) }}/mo</span>
                            <span class="material-symbols-outlined text-gray-400 text-lg">chevron_right</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500 text-sm">No active members found.</div>
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
