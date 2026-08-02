<x-app-layout title="Period Close">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance', 'url' => route('finance.index')], ['label' => 'Period Close']]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Period Close</h2>
                <p class="text-xs text-slate-500 mt-1">Closing a period freezes new postings to that month. Reopening requires a reason.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-[10px] text-sm">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">
                @foreach ($errors->all() as $error) {{ $error }} @endforeach
            </div>
        @endif

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs text-slate-500">
                        <th class="px-5 py-3">Period</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Closed At</th>
                        <th class="px-5 py-3">By</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($months as $month)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-slate-700">{{ $month['period'] }}</td>
                            <td class="px-5 py-3">
                                @if ($month['close']?->is_closed)
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-indigo-100 text-indigo-700">Closed</span>
                                @elseif ($month['close'])
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-amber-100 text-amber-700">Reopened</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-green-100 text-green-700">Open</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $month['close']?->closed_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $month['close']?->closedBy?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                @if ($month['close']?->is_closed)
                                    <button x-data x-on:click="$dispatch('open-modal', 'reopen-{{ $month['period'] }}')" class="text-xs text-amber-600 hover:text-amber-700 font-medium">Reopen</button>
                                @else
                                    <button x-data x-on:click="$dispatch('open-modal', 'close-{{ $month['period'] }}')" class="text-xs text-[#0F172A] hover:text-slate-600 font-medium">Close</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-sm">No periods found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($months as $month)
        <div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail === 'close-{{ $month['period'] }}') open = true" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center" style="display:none">
            <div class="fixed inset-0 bg-black/40" x-on:click="open = false"></div>
            <div class="bg-white rounded-[16px] shadow-xl p-6 w-full max-w-md relative z-10">
                <h3 class="text-sm font-bold text-[#0F172A] mb-1">Close {{ $month['label'] }}</h3>
                <p class="text-xs text-slate-500 mb-4">No new journal entries will be allowed for {{ $month['period'] }} after closing.</p>
                <form method="POST" action="{{ route('finance.period-close.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="period" value="{{ $month['period'] }}">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Notes (optional)</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" x-on:click="open = false" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">Close Period</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail === 'reopen-{{ $month['period'] }}') open = true" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center" style="display:none">
            <div class="fixed inset-0 bg-black/40" x-on:click="open = false"></div>
            <div class="bg-white rounded-[16px] shadow-xl p-6 w-full max-w-md relative z-10">
                <h3 class="text-sm font-bold text-[#0F172A] mb-1">Reopen {{ $month['label'] }}</h3>
                <p class="text-xs text-slate-500 mb-4">Reopening is logged for audit. Provide a reason.</p>
                <form method="POST" action="{{ route('finance.period-close.reopen', $month['period']) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Reason <span class="text-red-500">*</span></label>
                        <textarea name="reason" rows="2" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" x-on:click="open = false" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">Reopen Period</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-app-layout>
