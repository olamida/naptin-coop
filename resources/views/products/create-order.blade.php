<x-app-layout title="New Purchase Order">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.orders') }}" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-gray-800">New Purchase Order</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ route('products.orders.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member *</label>
                    <select name="member_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Select member</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                {{ $member->first_name }} {{ $member->last_name }} ({{ $member->staff_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                    <select name="product_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type *</label>
                        <select name="payment_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="cash" {{ old('payment_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="hire_purchase" {{ old('payment_type') === 'hire_purchase' ? 'selected' : '' }}>Hire Purchase</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Repayment (₦) <span class="text-gray-400">(for hire purchase)</span></label>
                    <input type="number" name="monthly_repayment" value="{{ old('monthly_repayment', 0) }}" step="0.01" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                        Create Order
                    </button>
                    <a href="{{ route('products.orders') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
