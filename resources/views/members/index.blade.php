<x-app-layout title="Members">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Members']]" />

        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-[#0F172A]">Members</h2>
            <div class="flex items-center gap-2">
                @can('view-members')
                    <a href="{{ route('members.export') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-lg">download</span>
                        Export Excel
                    </a>
                @endcan
                @can('create-members')
                    <a href="{{ route('members.import') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-lg">upload_file</span>
                        Import
                    </a>
                    <a href="{{ route('members.create') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-lg">person_add</span>
                        Add Member
                    </a>
                @endcan
            </div>
        </div>

        <form method="GET" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-slate-500 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or Staff ID"
                            class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-xs text-slate-500 mb-1">Region</label>
                    <select name="region_id" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">All Regions</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs text-slate-500 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">All Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                        <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2 rounded-[10px] text-sm font-medium transition">Filter</button>
                @if (request()->hasAny(['search', 'region_id', 'status']))
                    <a href="{{ route('members.index') }}" class="text-sm text-slate-500 hover:underline py-2">Clear</a>
                @endif
            </div>
        </form>

        @can('edit-members')
        {{-- Bulk Action Bar --}}
        <form id="bulk-status-form" method="POST" action="{{ route('members.bulk-status') }}" class="hidden">
            @csrf
            <div id="bulk-action-bar" class="bg-blue-50 border border-blue-200 rounded-[16px] p-4 flex items-center gap-4 transition-all">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600 text-lg">checklist</span>
                    <span class="text-sm font-medium text-blue-800"><span id="selected-count">0</span> member(s) selected</span>
                </div>
                <div class="h-6 w-px bg-blue-200"></div>
                <div class="flex items-center gap-3">
                    <label class="text-sm text-slate-600">Change status to:</label>
                    <select name="status" id="bulk-status-select" class="px-3 py-1.5 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="">Select status...</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" id="bulk-submit-btn" disabled
                    class="bg-[#0F172A] hover:bg-slate-800 disabled:bg-slate-300 disabled:cursor-not-allowed text-white px-4 py-1.5 rounded-[10px] text-sm font-medium transition">
                    Update Status
                </button>
                <button type="button" onclick="clearSelection()" class="text-slate-500 hover:text-slate-700 text-sm ml-auto">
                    Clear selection
                </button>
            </div>
            {{-- Hidden inputs will be populated by JS --}}
            <div id="bulk-member-inputs"></div>
        </form>

        @endcan

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        @can('edit-members')
                        <th class="w-10 px-5 py-3.5">
                            <input type="checkbox" id="select-all" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        @endcan
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Staff ID</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Region</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Phone</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($members as $member)
                        <tr class="hover:bg-slate-50 member-row">
                            @can('edit-members')
                            <td class="px-5 py-3.5">
                                <input type="checkbox" name="member_ids[]" value="{{ $member->id }}"
                                    class="member-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </td>
                            @endcan
                            <td class="px-5 py-3.5">
                                <a href="{{ route('members.show', $member) }}" class="flex items-center gap-3">
                                    @if ($member->photo_url)
                                        <img src="{{ $member->photo_url }}" alt="" class="w-9 h-9 rounded-full object-cover border border-slate-200">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ $member->initials }}
                                        </div>
                                    @endif
                                    @if ($member->photo_url)
                                        <img src="{{ $member->photo_url }}" alt="" class="w-9 h-9 rounded-full object-cover border border-slate-200">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ $member->initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-[#0F172A] hover:text-blue-600">{{ $member->first_name }} {{ $member->last_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $member->email ?? '' }}</p>
                                    </div>
                                </a>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-600">{{ $member->staff_id_display }}</td>
                            <td class="px-5 py-3.5 text-slate-600 text-xs">{{ $member->region->name ?? 'N/A' }}</td>
                            <td class="px-5 py-3.5 text-slate-600 text-xs">{{ $member->phone ?? 'N/A' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-1 text-[10px] font-medium rounded-full
                                    {{ $member->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $member->status === 'inactive' ? 'bg-slate-100 text-slate-600' : '' }}
                                    {{ $member->status === 'retired' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $member->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ ucfirst($member->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('members.show', $member) }}" class="text-blue-600 hover:underline text-xs font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->can('edit-members') ? 7 : 6 }}" class="px-5 py-12 text-center text-slate-500">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">group</span>
                                No members found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $members->links() }}

        <x-show-all-toggle />
    </div>

    @push('scripts')
    <script>
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.member-checkbox');
        const bulkForm = document.getElementById('bulk-status-form');
        const bulkInputs = document.getElementById('bulk-member-inputs');
        const bulkBar = document.getElementById('bulk-action-bar');
        const countEl = document.getElementById('selected-count');
        const statusSelect = document.getElementById('bulk-status-select');
        const submitBtn = document.getElementById('bulk-submit-btn');

        function getSelectedIds() {
            const ids = [];
            checkboxes.forEach(cb => {
                if (cb.checked) ids.push(cb.value);
            });
            return ids;
        }

        function updateBulkBar() {
            const ids = getSelectedIds();
            countEl.textContent = ids.length;

            if (ids.length > 0) {
                bulkBar.classList.remove('hidden');
                bulkBar.parentElement.classList.remove('hidden');
            } else {
                bulkBar.classList.add('hidden');
                bulkBar.parentElement.classList.add('hidden');
            }

            submitBtn.disabled = ids.length === 0 || !statusSelect.value;
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateBulkBar();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                selectAll.checked = allChecked;
                updateBulkBar();
            });
        });

        statusSelect.addEventListener('change', updateBulkBar);

        bulkForm.addEventListener('submit', function(e) {
            const ids = getSelectedIds();
            if (ids.length === 0 || !statusSelect.value) {
                e.preventDefault();
                return;
            }

            const statusLabel = statusSelect.options[statusSelect.selectedIndex].text;
            if (!confirm('Are you sure you want to change the status of ' + ids.length + ' member(s) to "' + statusLabel + '"?')) {
                e.preventDefault();
                return;
            }

            bulkInputs.innerHTML = '';
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'member_ids[]';
                input.value = id;
                bulkInputs.appendChild(input);
            });
        });

        function clearSelection() {
            checkboxes.forEach(cb => { cb.checked = false; });
            selectAll.checked = false;
            statusSelect.value = '';
            updateBulkBar();
        }

        document.querySelectorAll('.member-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.closest('a') || e.target.closest('input')) return;
                const cb = row.querySelector('.member-checkbox');
                if (cb) {
                    cb.checked = !cb.checked;
                    cb.dispatchEvent(new Event('change'));
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
