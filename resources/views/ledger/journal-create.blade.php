<x-app-layout title="New Journal Entry">
    <div class="max-w-4xl mx-auto space-y-6">
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">{{ $errors->first() }}</div>
        @endif

        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-[#0F172A]">New Journal Entry</h2>
            <span class="text-sm text-slate-400 font-mono">{{ $nextNumber }}</span>
        </div>

        <form method="POST" action="{{ route('ledger.journals.store') }}" x-data="journalEntry()">
            @csrf
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Entry Date</label>
                        <input type="date" name="entry_date" value="{{ old('entry_date', now()->format('Y-m-d')) }}" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div></div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="2" required
                              class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-slate-700">Journal Lines</label>
                        <button type="button" x-on:click="addLine()" class="text-blue-600 hover:text-blue-800 text-xs font-medium flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">add</span> Add Line
                        </button>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs text-slate-500">
                                <th class="px-3 py-2 rounded-l-lg">Account</th>
                                <th class="px-3 py-2">Debit (₦)</th>
                                <th class="px-3 py-2">Credit (₦)</th>
                                <th class="px-3 py-2 rounded-r-lg"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(line, i) in lines" :key="i">
                                <tr>
                                    <td class="px-3 py-1.5">
                                        <select :name="'lines['+i+'][account_id]'" x-model="line.account_id" required
                                                class="w-full px-2 py-1.5 border border-slate-300 rounded-[10px] text-xs outline-none">
                                            <option value="">Select account</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-1.5">
                                        <input type="number" step="0.01" min="0" :name="'lines['+i+'][debit]'" x-model="line.debit" x-on:input="updateTotals()"
                                               class="w-full px-2 py-1.5 border border-slate-300 rounded-[10px] text-xs outline-none text-right">
                                    </td>
                                    <td class="px-3 py-1.5">
                                        <input type="number" step="0.01" min="0" :name="'lines['+i+'][credit]'" x-model="line.credit" x-on:input="updateTotals()"
                                               class="w-full px-2 py-1.5 border border-slate-300 rounded-[10px] text-xs outline-none text-right">
                                    </td>
                                    <td class="px-3 py-1.5">
                                        <button type="button" x-on:click="removeLine(i)" x-show="lines.length > 2" class="text-red-400 hover:text-red-600">
                                            <span class="material-symbols-outlined text-sm">remove_circle</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-medium text-xs">
                                <td class="px-3 py-2 rounded-bl-lg text-slate-600">Totals</td>
                                <td class="px-3 py-2 text-right text-slate-700" x-text="'₦' + totalDebit.toLocaleString('en-US', {minimumFractionDigits:2})"></td>
                                <td class="px-3 py-2 text-right text-slate-700" x-text="'₦' + totalCredit.toLocaleString('en-US', {minimumFractionDigits:2})"></td>
                                <td class="px-3 py-2 rounded-br-lg">
                                    <span x-show="Math.abs(totalDebit - totalCredit) < 0.01" class="text-green-600 text-xs">Balanced</span>
                                    <span x-show="Math.abs(totalDebit - totalCredit) >= 0.01" class="text-red-500 text-xs">Unbalanced</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('ledger.journals') }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancel</a>
                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">Save as Draft</button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function journalEntry() {
            return {
                lines: [{ account_id: '', debit: 0, credit: 0 }, { account_id: '', debit: 0, credit: 0 }],
                totalDebit: 0,
                totalCredit: 0,
                addLine() {
                    this.lines.push({ account_id: '', debit: 0, credit: 0 });
                },
                removeLine(i) {
                    this.lines.splice(i, 1);
                    this.updateTotals();
                },
                updateTotals() {
                    this.totalDebit = this.lines.reduce((s, l) => s + parseFloat(l.debit || 0), 0);
                    this.totalCredit = this.lines.reduce((s, l) => s + parseFloat(l.credit || 0), 0);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
