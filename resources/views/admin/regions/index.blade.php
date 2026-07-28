<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Regional Centers</h2>
                <p class="text-gray-500 text-sm mt-1">Manage cooperative regional office locations</p>
            </div>
            <a href="{{ route('admin.regions.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                New Region
            </a>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Region</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Zone</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Headquarters</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Members</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($regions as $region)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-indigo-600 text-xl">location_on</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 text-sm">{{ $region->name }}</p>
                                            @if ($region->address)
                                                <p class="text-xs text-gray-500 max-w-[200px] truncate">{{ $region->address }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $region->code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $region->zone ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $region->headquarters ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $region->phone ?: '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                                        {{ $region->members_count ?? $region->members->count() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.regions.edit', $region) }}"
                                           class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                           title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form id="delete-region-{{ $region->id }}" action="{{ route('admin.regions.destroy', $region) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                    onclick="deleteConfirm('delete-region-{{ $region->id }}')"
                                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
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
                                    <span class="material-symbols-outlined text-4xl text-gray-300 mb-2">location_city</span>
                                    <p class="text-gray-500">No regions created yet</p>
                                    <a href="{{ route('admin.regions.create') }}" class="text-indigo-600 text-sm hover:underline mt-1 inline-block">Create your first region</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($regions->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $regions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
