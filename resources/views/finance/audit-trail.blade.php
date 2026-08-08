<x-app-layout title="Audit Trail">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance', 'url' => route('finance.index')], ['label' => 'Audit Trail']]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Audit Trail</h2>
                <p class="text-xs text-slate-500 mt-1">System activity logs + ledger hash-chain integrity check.</p>
            </div>
            <x-report-export-buttons
                route="finance.export.audit-trail"
                :params="['user_id' => request('user_id'), 'event' => request('event'), 'from' => request('from'), 'to' => request('to')]"
            />
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-[#0F172A]">Ledger Hash Chain Verification</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Every posted journal entry references the previous entry's hash (SHA-256). Tampering breaks the chain.</p>
                </div>
                @if (empty($hashViolations))
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Integrity verified ✓ ({{ \App\Models\JournalEntry::whereNotNull('hash')->count() }} entries)</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ count($hashViolations) }} violation(s) found</span>
                @endif
            </div>
            @if (! empty($hashViolations))
                <div class="mt-4 bg-red-50 border border-red-200 rounded-[10px] p-4">
                    <p class="text-xs font-semibold text-red-700 mb-2">CRITICAL: Journal entries fail hash verification. Possible data tampering.</p>
                    <ul class="text-xs text-red-600 space-y-1">
                        @foreach ($hashViolations as $v)
                            <li>#{{ $v['id'] }} ({{ $v['entry_number'] }}): expected prev-hash <code>{{ $v['expected_prev_hash'] }}</code> but stored <code>{{ $v['stored_prev_hash'] }}</code></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <form method="GET" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">User</label>
                <select name="user_id" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Event</label>
                <select name="event" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All events</option>
                    @foreach ($events as $event)
                        <option value="{{ $event }}" @selected(request('event') === $event)>{{ $event }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-[#0F172A] text-white px-4 py-2 rounded-[10px] text-sm font-medium">Filter</button>
        </form>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs text-slate-500">
                        <th class="px-5 py-3">When</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-2.5 text-slate-500 text-xs">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                            <td class="px-5 py-2.5 text-slate-700">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-5 py-2.5"><span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-slate-100 text-slate-600 font-mono">{{ $log->event }}</span></td>
                            <td class="px-5 py-2.5 text-slate-600">{{ $log->description }}</td>
                            <td class="px-5 py-2.5 text-slate-400 text-xs font-mono">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-sm">No activity found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
