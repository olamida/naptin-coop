<x-app-layout title="Journal Entries">
    <div class="max-w-6xl mx-auto space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-[10px] text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">{{ session('error') }}</div>
        @endif

        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-[#0F172A]">Journal Entries</h2>
            <a href="{{ route('ledger.journals.create') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">add</span> New Entry
            </a>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-4 py-3 font-medium text-slate-600">Entry #</th>
                        <th class="px-4 py-3 font-medium text-slate-600">Date</th>
                        <th class="px-4 py-3 font-medium text-slate-600">Description</th>
                        <th class="px-4 py-3 font-medium text-slate-600">Reference</th>
                        <th class="px-4 py-3 font-medium text-slate-600">Status</th>
                        <th class="px-4 py-3 font-medium text-slate-600">Posted By</th>
                        <th class="px-4 py-3 font-medium text-slate-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($entries as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $entry->entry_number }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $entry->entry_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-slate-600 max-w-xs truncate">{{ $entry->description }}</td>
                            <td class="px-4 py-3">
                                @if ($entry->reference_type)
                                    <span class="text-xs bg-slate-100 px-2 py-0.5 rounded">{{ $entry->reference_type }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($entry->status === 'posted')
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Posted</span>
                                @else
                                    <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $entry->postedBy?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('ledger.journals.show', $entry) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">No journal entries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-center">
            {{ $entries->links() }}
        </div>
    </div>
</x-app-layout>
