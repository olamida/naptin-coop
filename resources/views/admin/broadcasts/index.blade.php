<x-app-layout title="Broadcast Notifications">
    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Management', 'url' => route('admin.manage')],
            ['label' => 'Broadcast Notifications']
        ]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Broadcast Notifications</h2>
                <p class="text-sm text-slate-500 mt-1">Send announcements to all active members</p>
            </div>
            <a href="{{ route('admin.broadcasts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#0F172A] text-white rounded-[10px] hover:bg-slate-800 transition">
                <span class="material-symbols-outlined text-lg">campaign</span>
                New Broadcast
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if ($broadcasts->isEmpty())
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200">
                <x-empty-state icon="campaign" title="No broadcasts yet"
                    message="Send your first announcement to all members."
                    actionUrl="{{ route('admin.broadcasts.create') }}" actionLabel="New Broadcast" />
            </div>
        @else
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Title</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Category</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Priority</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Sent By</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Recipients</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($broadcasts as $broadcast)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-[#0F172A]">{{ $broadcast->title }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ Str::limit($broadcast->body, 80) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $categoryColors = [
                                                'general' => 'bg-slate-100 text-slate-700',
                                                'urgent' => 'bg-red-100 text-red-700',
                                                'meeting' => 'bg-blue-100 text-blue-700',
                                                'policy' => 'bg-purple-100 text-purple-700',
                                                'financial' => 'bg-emerald-100 text-emerald-700',
                                                'other' => 'bg-amber-100 text-amber-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $categoryColors[$broadcast->category] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ ucfirst($broadcast->category) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $priorityColors = [
                                                'normal' => 'bg-slate-100 text-slate-600',
                                                'high' => 'bg-orange-100 text-orange-600',
                                                'urgent' => 'bg-red-100 text-red-600',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$broadcast->priority] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ ucfirst($broadcast->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $broadcast->sender->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $broadcast->recipients_count }}</td>
                                    <td class="px-6 py-4 text-slate-500 text-xs">{{ $broadcast->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t border-slate-200">
                    {{ $broadcasts->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
