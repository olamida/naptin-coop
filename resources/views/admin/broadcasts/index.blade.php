<x-app-layout title="Broadcast Notifications">
    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Management', 'url' => route('admin.manage')],
            ['label' => 'Broadcast Notifications']
        ]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Broadcast Notifications</h2>
                <p class="text-sm text-gray-500 mt-1">Send announcements to all active members</p>
            </div>
            <a href="{{ route('admin.broadcasts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <span class="material-symbols-outlined text-6xl text-gray-300">campaign</span>
                <h3 class="text-lg font-semibold text-gray-600 mt-4">No broadcasts yet</h3>
                <p class="text-sm text-gray-400 mt-1">Send your first announcement to all members.</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Title</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Category</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Priority</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Sent By</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Recipients</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($broadcasts as $broadcast)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-800">{{ $broadcast->title }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ Str::limit($broadcast->body, 80) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $categoryColors = [
                                                'general' => 'bg-gray-100 text-gray-700',
                                                'urgent' => 'bg-red-100 text-red-700',
                                                'meeting' => 'bg-blue-100 text-blue-700',
                                                'policy' => 'bg-purple-100 text-purple-700',
                                                'financial' => 'bg-emerald-100 text-emerald-700',
                                                'other' => 'bg-amber-100 text-amber-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $categoryColors[$broadcast->category] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ ucfirst($broadcast->category) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $priorityColors = [
                                                'normal' => 'bg-gray-100 text-gray-600',
                                                'high' => 'bg-orange-100 text-orange-600',
                                                'urgent' => 'bg-red-100 text-red-600',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$broadcast->priority] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($broadcast->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $broadcast->sender->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $broadcast->recipients_count }}</td>
                                    <td class="px-6 py-4 text-gray-500 text-xs">{{ $broadcast->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t border-gray-100">
                    {{ $broadcasts->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
