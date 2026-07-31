<x-app-layout title="Journal Entry">
    <div class="max-w-4xl mx-auto space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-[10px] text-sm">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">{{ $errors->first() }}</div>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-[#0F172A]">Journal Entry</h2>
                <p class="text-sm text-slate-400 font-mono">{{ $entry->entry_number }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($entry->status === 'draft')
                    <form method="POST" action="{{ route('ledger.journals.post', $entry) }}" onsubmit="return confirm('Post this entry? This action cannot be undone.')">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                            Post Entry
                        </button>
                    </form>
                @endif
                <a href="{{ route('ledger.journals') }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Back</a>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <div class="grid grid-cols-3 gap-4 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <span class="text-xs text-slate-400">Date</span>
                    <p class="text-sm font-medium text-slate-700">{{ $entry->entry_date->format('d M Y') }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400">Status</span>
                    <p class="text-sm">
                        @if ($entry->status === 'posted')
                            <span class="text-green-600 font-medium">Posted</span>
                            <span class="text-xs text-slate-400 ml-1">by {{ $entry->postedBy?->name }} on {{ $entry->posted_at?->format('d M Y H:i') }}</span>
                        @else
                            <span class="text-amber-600 font-medium">Draft</span>
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-xs text-slate-400">Reference</span>
                    <p class="text-sm text-slate-700">{{ $entry->reference_type ?? '—' }}</p>
                </div>
            </div>

            <div class="mb-4">
                <span class="text-xs text-slate-400">Description</span>
                <p class="text-sm text-slate-700 mt-1">{{ $entry->description }}</p>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs text-slate-500">
                        <th class="px-3 py-2 rounded-l-lg">Account</th>
                        <th class="px-3 py-2">Code</th>
                        <th class="px-3 py-2 text-right">Debit (₦)</th>
                        <th class="px-3 py-2 text-right rounded-r-lg">Credit (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($entry->lines as $line)
                        <tr>
                            <td class="px-3 py-2 text-slate-700">{{ $line->account?->name }}</td>
                            <td class="px-3 py-2 text-slate-400 font-mono text-xs">{{ $line->account?->code }}</td>
                            <td class="px-3 py-2 text-right text-slate-700">{{ $line->debit > 0 ? '₦' . number_format($line->debit, 2) : '' }}</td>
                            <td class="px-3 py-2 text-right text-slate-700">{{ $line->credit > 0 ? '₦' . number_format($line->credit, 2) : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-medium text-xs">
                        <td colspan="2" class="px-3 py-2 rounded-bl-lg text-slate-600">Totals</td>
                        <td class="px-3 py-2 text-right text-slate-700">₦{{ number_format($entry->lines->sum('debit'), 2) }}</td>
                        <td class="px-3 py-2 text-right text-slate-700 rounded-br-lg">₦{{ number_format($entry->lines->sum('credit'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
