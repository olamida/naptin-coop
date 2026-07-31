<x-app-layout title="Record Repayment - {{ $loan->loan_number }}">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('loans.show', $loan) }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Record Repayment</h2>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <div class="grid grid-cols-2 gap-4 text-sm mb-4 pb-4 border-b border-slate-200">
                <div>
                    <p class="text-slate-500">Loan Number</p>
                    <p class="font-mono font-medium">{{ $loan->loan_number }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Member</p>
                    <p class="font-medium">{{ $loan->member->first_name ?? '' }} {{ $loan->member->last_name ?? '' }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Outstanding Amount</p>
                    <p class="text-xl font-bold text-orange-600">₦{{ number_format($loan->outstanding, 2) }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Monthly Repayment</p>
                    <p class="font-medium">₦{{ number_format($loan->monthly_repayment, 2) }}</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('loans.repayment.store', $loan) }}" class="space-y-5" x-data="repaymentForm()">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Amount (₦) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none z-10">₦</span>
                        <input type="text" name="amount_display" inputmode="decimal" autocomplete="off" required
                            class="w-full pl-10 pr-10 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                            x-model="amountDisplay" @input="onAmountInput($event)" @blur="onAmountBlur">
                        <span x-show="amountDisplay && !amountError" class="absolute right-3 top-1/2 -translate-y-1/2 text-green-500">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                        </span>
                    </div>
                    <p x-show="amountError" x-text="amountError" class="text-[11px] text-red-600 font-medium mt-1"></p>
                    <input type="hidden" name="amount" :value="amountRaw">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="salary_deduction" {{ old('payment_method') === 'salary_deduction' ? 'selected' : '' }}>Salary Deduction</option>
                        <option value="savings_deduction" {{ old('payment_method') === 'savings_deduction' ? 'selected' : '' }}>Savings Deduction</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Date *</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">
                        Record Payment
                    </button>
                    <a href="{{ route('loans.show', $loan) }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function repaymentForm() {
            return {
                amountRaw: {{ old('amount', $loan->monthly_repayment) }},
                amountDisplay: formatMoney({{ old('amount', $loan->monthly_repayment) }}),
                amountError: '',
                onAmountInput(event) {
                    let raw = event.target.value.replace(/[^0-9.]/g, '');
                    const dots = raw.match(/\./g);
                    if (dots && dots.length > 1) raw = raw.replace(/\.+$/, '');
                    this.amountRaw = parseFloat(raw) || 0;
                    this.amountDisplay = raw ? formatMoney(this.amountRaw) : '';
                },
                onAmountBlur() {
                    if (this.amountDisplay) {
                        this.amountDisplay = formatMoney(this.amountRaw);
                    }
                }
            }
        }
        function formatMoney(value) {
            if (!value && value !== 0) return '';
            const parts = Number(value).toFixed(2).split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.join('.');
        }
    </script>
    @endpush
</x-app-layout>
