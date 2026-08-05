<x-app-layout title="Shopping Cart">
    <div class="space-y-6" x-data="cartPage()">
        <x-breadcrumb :items="[['label' => 'Products', 'url' => route('products.index')], ['label' => 'Shopping Cart']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-[#0F172A]">
                Shopping Cart
                <span class="text-sm font-normal text-slate-500" x-show="items.length > 0" x-text="'(' + items.length + ' item' + (items.length !== 1 ? 's' : '') + ')'"></span>
            </h2>
            <a href="{{ route('products.index') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Continue Shopping
            </a>
        </div>

        <div x-show="items.length > 0" x-cloak>
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
                        <template x-for="item in items" :key="item.product.id">
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <template x-if="item.product.image_url">
                                            <img :src="item.product.image_url" class="w-10 h-10 rounded-lg object-cover">
                                        </template>
                                        <template x-if="!item.product.image_url">
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-slate-400">inventory_2</span>
                                            </div>
                                        </template>
                                        <p class="font-medium text-[#0F172A]" x-text="item.product.name"></p>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right text-slate-700" x-text="formatCurrency(item.product.unit_price)"></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <input type="number" :value="item.quantity" min="1"
                                               @change="updateItem(item.product.id, $event.target.value)"
                                               class="w-16 text-center border border-slate-300 rounded-[10px] text-sm py-1 focus:ring-2 focus:ring-blue-500 outline-none">
                                        <button type="button" @click="updateItem(item.product.id, item.quantity - 1)" class="text-slate-400 hover:text-blue-600" title="Decrease">
                                            <span class="material-symbols-outlined text-[18px]">remove</span>
                                        </button>
                                        <button type="button" @click="updateItem(item.product.id, item.quantity + 1)" class="text-slate-400 hover:text-blue-600" title="Increase">
                                            <span class="material-symbols-outlined text-[18px]">add</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right font-semibold text-[#0F172A]" x-text="formatCurrency(item.subtotal)"></td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" @click="removeItem(item.product.id)" class="text-slate-400 hover:text-red-600" title="Remove">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between mt-6">
                <button type="button" @click="clearCart()" class="text-sm text-red-600 hover:underline">Clear Cart</button>
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5 w-80">
                    <div class="flex justify-between text-lg font-bold mb-4">
                        <span>Total:</span>
                        <span x-text="formatCurrency(total)"></span>
                    </div>
                    <a href="{{ route('cart.checkout') }}" class="block w-full bg-[#0F172A] hover:bg-slate-800 text-white text-center px-4 py-3 rounded-[10px] text-sm font-medium transition">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>

        <div x-show="items.length === 0" x-cloak
             class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-12 text-center">
            <span class="material-symbols-outlined text-5xl text-slate-300 mb-3 inline-block">shopping_cart</span>
            <p class="text-slate-500">Your cart is empty.</p>
            <a href="{{ route('products.index') }}" class="inline-block mt-3 text-blue-600 hover:underline text-sm">Browse Products &rarr;</a>
        </div>
    </div>

    @push('scripts')
    <script>
        function cartPage() {
            return {
                items: @json($items),
                total: {{ $total }},

                formatCurrency(amount) {
                    return '\u20A6' + Number(amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                },

                refreshTotal() {
                    this.total = this.items.reduce((sum, item) => sum + item.subtotal, 0);
                },

                async updateItem(productId, quantity) {
                    quantity = parseInt(quantity, 10);
                    if (Number.isNaN(quantity)) return;
                    if (quantity < 1) {
                        this.removeItem(productId);
                        return;
                    }

                    try {
                        const response = await fetch('{{ route("cart.update") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `product_id=${productId}&quantity=${quantity}`,
                        });
                        const data = await response.json();
                        if (data.success) {
                            const item = this.items.find(i => i.product.id === productId);
                            if (item) {
                                item.quantity = quantity;
                                item.subtotal = item.product.unit_price * quantity;
                            }
                            this.refreshTotal();
                            document.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_count: data.cart_count } }));
                            document.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Cart updated.', type: 'success' } }));
                        }
                    } catch (e) {
                        document.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to update cart.', type: 'error' } }));
                    }
                },

                async removeItem(productId) {
                    if (!confirm('Remove this item from the cart?')) return;

                    try {
                        const response = await fetch('{{ route("cart.remove") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `product_id=${productId}`,
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.items = this.items.filter(i => i.product.id !== productId);
                            this.refreshTotal();
                            document.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_count: data.cart_count } }));
                            document.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Item removed.', type: 'success' } }));
                        }
                    } catch (e) {
                        document.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to remove item.', type: 'error' } }));
                    }
                },

                async clearCart() {
                    if (!confirm('Clear all items from the cart?')) return;

                    try {
                        const response = await fetch('{{ route("cart.clear") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.items = [];
                            this.total = 0;
                            document.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_count: 0 } }));
                            document.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Cart cleared.', type: 'success' } }));
                        }
                    } catch (e) {
                        document.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to clear cart.', type: 'error' } }));
                    }
                },
            }
        }
    </script>
    @endpush
</x-app-layout>
