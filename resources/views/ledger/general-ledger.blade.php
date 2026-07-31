<x-app-layout title="General Ledger">
    <div class="max-w-5xl mx-auto space-y-6">
        <h2 class="text-lg font-bold text-[#0F172A]">General Ledger</h2>

        <form method="GET" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Select Account</label>
                    <select name="account_id" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Choose an account —</option>
                        @foreach ($accounts as $acct)
                            <option value="{{ $acct->id }}" @selected($account?->id === $acct->id)>{{ $acct->code }} - {{ $acct->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                    View Ledger
                </button>
            </div>
        </form>

        @if ($account)
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
                <div class="mb-4 pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-[#0F172A]">{{ $account->code }} - {{ $account->name }}</h3>
                    <p class="text-xs text-slate-400 capitalize">{{ $account->type }} (Normal: {{ $account->normal_side }})</p>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs text-slate-500">
                            <th class="px-3 py-2 rounded-l-lg">Date</th>
                            <th class="px-3 py-2">Entry #</th>
                            <th class="px-3 py-2">Description</th>
                            <th class="px-3 py-2 text-right">Debit (₦)</th>
                            <th class="px-3 py-2 text-right">Credit (₦)</th>
                            <th class="px-3 py-2 text-right rounded-r-lg">Balance (₦)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php $runningBalance = 0; @endphp
                        @forelse ($lines as $line)
                            @php
                                $runningBalance += $line->debit - $line->credit;
                                $balance = $account->normal_side === 'debit' ? $runningBalance : -$runningBalance;
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 text-slate-600 text-xs">{{ $line->journalEntry?->entry_date->format('d M Y') }}</td>
                                <td class="px-3 py-2 font-mono text-xs text-slate-400">{{ $line->journalEntry?->entry_number }}</td>
                                <td class="px-3 py-2 text-slate-700 text-xs">{{ $line->description ?? $line->journalEntry?->description }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $line->debit > 0 ? '₦' . number_format($line->debit, 2) : '' }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $line->credit > 0 ? '₦' . number_format($line->credit, 2) : '' }}</td>
                                <td class="px-3 py-2 text-right font-medium text-slate-700">₦{{ number_format($balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-slate-400">No posted transactions for this account.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
