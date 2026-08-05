<x-app-layout title="Checkout">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Cart', 'url' => route('cart.index')], ['label' => 'Checkout']]" />
        <div class="flex items-center gap-3">
            <a href="{{ route('cart.index') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Checkout</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200">
                        <h3 class="font-semibold text-[#0F172A]">Order Summary</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="text-left px-5 py-2.5 font-medium text-slate-600">Product</th>
                                <th class="text-right px-5 py-2.5 font-medium text-slate-600">Qty</th>
                                <th class="text-right px-5 py-2.5 font-medium text-slate-600">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($items as $item)
                                <tr>
                                    <td class="px-5 py-3 font-medium">{{ $item['product']->name }}</td>
                                    <td class="px-5 py-3 text-right">{{ $item['quantity'] }}</td>
                                    <td class="px-5 py-3 text-right">₦{{ number_format($item['subtotal'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('cart.process') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select Member *</label>
                        <x-member-combobox :endpoint="route('members.search.form')" :selected-id="old('member_id')" />
                    </div>

                    <div x-data="{ type: 'cash' }">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Payment Type *</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input type="radio" name="payment_type" value="cash" checked class="w-4 h-4 text-blue-600" x-model="type">
                                Cash (Full Payment)
                            </label>
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input type="radio" name="payment_type" value="hire_purchase" class="w-4 h-4 text-blue-600" x-model="type">
                                Hire Purchase (Monthly Deductions)
                            </label>
                        </div>
                    </div>

                    <div x-show="type === 'hire_purchase'" x-cloak>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Repayment (₦) *</label>
                        <input type="number" name="monthly_repayment" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Enter monthly deduction amount">
                    </div>
                    </div>

                    <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-[10px] bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="is_society_expense" value="1" class="mt-0.5 w-4 h-4 text-blue-600 rounded"
                               {{ old('is_society_expense') ? 'checked' : '' }}>
                        <span class="text-sm">
                            <span class="font-medium text-slate-800">Executive / Society procurement</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Records this order as a society expense (not a member sale) and posts it to the expense ledger.</span>
                        </span>
                    </label>

                    <div class="flex items-center gap-3 pt-3 border-t border-slate-200">
                        <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2.5 rounded-[10px] text-sm font-medium transition">
                            Place Order
                        </button>
                        <a href="{{ route('cart.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Back to Cart</a>
                    </div>
                </form>
            </div>

            <div>
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                    <h3 class="font-semibold text-[#0F172A] mb-3">Payment Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Items:</span>
                            <span>{{ count($items) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg pt-2 border-t border-slate-200">
                            <span>Total:</span>
                            <span>₦{{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
