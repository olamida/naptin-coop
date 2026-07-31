<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Regional Centers</h2>
                <p class="text-xs text-slate-500 mt-1">Manage cooperative regional office locations</p>
            </div>
            <a href="{{ route('admin.regions.create') }}"
               class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2.5 rounded-[10px] text-sm font-medium flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                New Region
            </a>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Region</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Zone</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Headquarters</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Members</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($regions as $region)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-indigo-600 text-xl">location_on</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-[#0F172A] text-sm">{{ $region->name }}</p>
                                            @if ($region->address)
                                                <p class="text-xs text-slate-500 max-w-[200px] truncate">{{ $region->address }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $region->code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $region->zone ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $region->headquarters ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $region->phone ?: '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $region->members_count ?? $region->members->count() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.regions.edit', $region) }}"
                                           class="p-2 text-slate-400 hover:text-[#0F172A] hover:bg-slate-100 rounded-[10px] transition-colors"
                                           title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form id="delete-region-{{ $region->id }}" action="{{ route('admin.regions.destroy', $region) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                    onclick="deleteConfirm('delete-region-{{ $region->id }}')"
                                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-[10px] transition-colors"
                                                    title="Delete">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">location_city</span>
                                    <p class="text-slate-500">No regions created yet</p>
                                    <a href="{{ route('admin.regions.create') }}" class="text-[#0F172A] text-sm hover:underline mt-1 inline-block">Create your first region</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($regions->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $regions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
