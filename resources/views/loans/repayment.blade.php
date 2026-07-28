<x-app-layout title="Record Repayment - {{ $loan->loan_number }}">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('loans.show', $loan) }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <h2 class="text-2xl font-bold text-gray-800">Record Repayment</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="grid grid-cols-2 gap-4 text-sm mb-4 pb-4 border-b border-gray-100">
                <div>
                    <p class="text-gray-500">Loan Number</p>
                    <p class="font-mono font-medium">{{ $loan->loan_number }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Member</p>
                    <p class="font-medium">{{ $loan->member->first_name ?? '' }} {{ $loan->member->last_name ?? '' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Outstanding Amount</p>
                    <p class="text-xl font-bold text-orange-600">₦{{ number_format($loan->outstanding, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Monthly Repayment</p>
                    <p class="font-medium">₦{{ number_format($loan->monthly_repayment, 2) }}</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('loans.repayment.store', $loan) }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount (₦) *</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $loan->monthly_repayment) }}" required min="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="salary_deduction" {{ old('payment_method') === 'salary_deduction' ? 'selected' : '' }}>Salary Deduction</option>
                        <option value="savings_deduction" {{ old('payment_method') === 'savings_deduction' ? 'selected' : '' }}>Savings Deduction</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                        Record Payment
                    </button>
                    <a href="{{ route('loans.show', $loan) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
