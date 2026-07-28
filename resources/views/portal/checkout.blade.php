<x-portal-layout title="Checkout">
    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Cart', 'url' => route('portal.cart')],
            ['label' => 'Checkout'],
        ]" />

        <div class="flex items-center gap-3">
            <a href="{{ route('portal.cart') }}" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-2xl font-bold text-gray-800">Checkout</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">Order Summary</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-5 py-2.5 font-medium text-gray-600">Product</th>
                                <th class="text-right px-5 py-2.5 font-medium text-gray-600">Qty</th>
                                <th class="text-right px-5 py-2.5 font-medium text-gray-600">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
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

                <form method="POST" action="{{ route('portal.checkout.process') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                    @csrf

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-600">person</span>
                        <div>
                            <p class="text-sm font-medium text-blue-800">Ordering as: {{ $member->full_name }}</p>
                            <p class="text-xs text-blue-600">{{ $member->staff_id }}</p>
                        </div>
                    </div>

                    <div x-data="{ type: 'cash' }">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type *</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="radio" name="payment_type" value="cash" checked class="w-4 h-4 text-blue-600" x-model="type">
                                    Cash (Full Payment)
                                </label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="radio" name="payment_type" value="hire_purchase" class="w-4 h-4 text-blue-600" x-model="type">
                                    Hire Purchase
                                </label>
                            </div>
                        </div>

                        <div x-show="type === 'hire_purchase'" x-cloak class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Repayment (₦) *</label>
                            <input type="number" name="monthly_repayment" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="Enter monthly deduction amount">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                            Place Order
                        </button>
                        <a href="{{ route('portal.cart') }}" class="text-sm text-gray-500 hover:text-gray-700">Back to Cart</a>
                    </div>
                </form>
            </div>

            <div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-3">Payment Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Items:</span>
                            <span>{{ count($items) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg pt-2 border-t border-gray-100">
                            <span>Total:</span>
                            <span>₦{{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
