<x-app-layout title="New Purchase Order">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.orders') }}" class="text-slate-500 hover:text-slate-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-[#0F172A]">New Purchase Order</h2>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <form method="POST" action="{{ route('products.orders.store') }}" class="space-y-5">
                @csrf
                @php $memberSearchData = $members->map(fn($m) => ['id' => $m->id, 'first_name' => $m->first_name, 'last_name' => $m->last_name, 'staff_id' => $m->staff_id, 'staff_id_display' => $m->staff_id_display])->values(); @endphp
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Member *</label>
                    <x-member-combobox :members="$memberSearchData" :selected-id="old('member_id')" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Product *</label>
                    <select name="product_id" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Select product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} - ₦{{ number_format($product->unit_price, 2) }} ({{ $product->stock_quantity }} in stock)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Quantity *</label>
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Payment Type *</label>
                        <select name="payment_type" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="cash" {{ old('payment_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="hire_purchase" {{ old('payment_type') === 'hire_purchase' ? 'selected' : '' }}>Hire Purchase</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Repayment (₦) <span class="text-slate-400">(for hire purchase)</span></label>
                    <input type="number" name="monthly_repayment" value="{{ old('monthly_repayment', 0) }}" step="0.01" min="0"
                           class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-[10px] bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="is_society_expense" value="1" class="mt-0.5 w-4 h-4 text-blue-600 rounded"
                           {{ old('is_society_expense') ? 'checked' : '' }}>
                    <span class="text-sm">
                        <span class="font-medium text-slate-800">Executive / Society procurement</span>
                        <span class="block text-xs text-slate-500 mt-0.5">Records this order as a society expense (not a member sale) and posts it to the expense ledger.</span>
                    </span>
                </label>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">
                        Create Order
                    </button>
                    <a href="{{ route('products.orders') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
