<x-app-layout title="Trial Balance">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-[#0F172A]">Trial Balance</h2>
            <button onclick="window.print()" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">print</span> Print
            </button>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs text-slate-500">
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Account</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3 text-right">Debit (₦)</th>
                        <th class="px-4 py-3 text-right">Credit (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($accounts as $account)
                        @if ($account->balance_value > 0)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5 font-mono text-xs text-slate-400">{{ $account->code }}</td>
                                <td class="px-4 py-2.5 text-slate-700">{{ $account->name }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-600">{{ ucfirst($account->type) }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-right text-slate-700">
                                    @if ($account->balance_side === 'debit')
                                        ₦{{ number_format($account->balance_value, 2) }}
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right text-slate-700">
                                    @if ($account->balance_side === 'credit')
                                        ₦{{ number_format($account->balance_value, 2) }}
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-medium text-sm">
                        <td colspan="3" class="px-4 py-3 text-slate-600">Total</td>
                        <td class="px-4 py-3 text-right text-slate-700">₦{{ number_format($totalDebit, 2) }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">₦{{ number_format($totalCredit, 2) }}</td>
                    </tr>
                    @if (abs($totalDebit - $totalCredit) < 0.01)
                        <tr class="bg-green-50">
                            <td colspan="5" class="px-4 py-2 text-center text-green-600 text-xs font-medium">✓ Trial balance is in balance.</td>
                        </tr>
                    @else
                        <tr class="bg-red-50">
                            <td colspan="5" class="px-4 py-2 text-center text-red-600 text-xs font-medium">✗ Trial balance is out of balance by ₦{{ number_format(abs($totalDebit - $totalCredit), 2) }}</td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
