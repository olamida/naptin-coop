<x-app-layout title="Shopping Cart">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Products', 'url' => route('products.index')], ['label' => 'Shopping Cart']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-[#0F172A]">Shopping Cart</h2>
            <a href="{{ route('products.index') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Continue Shopping
            </a>
        </div>

        @if ($items)
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Product</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Price</th>
                            <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Quantity</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Subtotal</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($items as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($item['product']->image_url)
                                            <img src="{{ $item['product']->image_url }}" class="w-10 h-10 rounded-lg object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-slate-400">inventory_2</span>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-[#0F172A]">{{ $item['product']->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right text-slate-700">₦{{ number_format($item['product']->unit_price, 2) }}</td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="{{ route('cart.update') }}" class="flex items-center justify-center gap-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1"
                                               class="w-16 text-center border border-slate-300 rounded-[10px] text-sm py-1 focus:ring-2 focus:ring-blue-500 outline-none">
                                        <button type="submit" class="text-slate-400 hover:text-blue-600">
                                            <span class="material-symbols-outlined text-[18px]">refresh</span>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-4 text-right font-semibold text-[#0F172A]">₦{{ number_format($item['subtotal'], 2) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <form id="remove-cart-{{ $item['product']->id }}" method="POST" action="{{ route('cart.remove') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                        <button type="button" onclick="deleteConfirm('remove-cart-{{ $item['product']->id }}')" class="text-slate-400 hover:text-red-600">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:underline">Clear Cart</button>
                </form>
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5 w-80">
                    <div class="flex justify-between text-lg font-bold mb-4">
                        <span>Total:</span>
                        <span>₦{{ number_format($total, 2) }}</span>
                    </div>
                    <a href="{{ route('cart.checkout') }}" class="block w-full bg-[#0F172A] hover:bg-slate-800 text-white text-center px-4 py-3 rounded-[10px] text-sm font-medium transition">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-12 text-center">
                <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">shopping_cart</span>
                <p class="text-slate-500">Your cart is empty.</p>
                <a href="{{ route('products.index') }}" class="inline-block mt-3 text-blue-600 hover:underline text-sm">Browse Products &rarr;</a>
            </div>
        @endif
    </div>
</x-app-layout>
