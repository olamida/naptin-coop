<x-portal-layout title="My Cart">
    <div class="space-y-6" x-data="cartPage()">
        <x-breadcrumb :items="[
            ['label' => 'Order Products', 'url' => route('portal.products')],
            ['label' => 'My Cart'],
        ]" />

        <div class="flex items-center gap-3">
            <a href="{{ route('portal.products') }}" class="text-slate-500 hover:text-slate-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Shopping Cart</h2>
            <span class="text-sm text-slate-500" x-show="items.length > 0" x-text="'(' + items.length + ' item' + (items.length !== 1 ? 's' : '') + ')'"></span>
        </div>

        <div x-show="items.length > 0" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-3">
                <template x-for="item in items" :key="item.product.id">
                    <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-[10px] bg-slate-100 overflow-hidden flex-shrink-0">
                            <template x-if="item.product.image_url">
                                <img :src="item.product.image_url" alt="" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!item.product.image_url">
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-slate-300">inventory_2</span>
                                </div>
                            </template>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-[#0F172A] text-sm" x-text="item.product.name"></h3>
                            <p class="text-xs text-slate-500" x-text="formatCurrency(item.product.unit_price) + ' each'"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" :value="item.quantity" min="1"
                                class="w-16 px-2 py-1 border border-slate-300 rounded-[10px] text-sm text-center focus:ring-2 focus:ring-blue-500 outline-none"
                                @change="updateItem(item.product.id, $event.target.value)">
                        </div>
                        <p class="text-sm font-semibold text-[#0F172A] w-28 text-right" x-text="formatCurrency(item.subtotal)"></p>
                        <button @click="removeItem(item.product.id)" class="text-slate-400 hover:text-red-500 transition">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    </div>
                </template>
            </div>

            <div>
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5 sticky top-6">
                    <h3 class="font-semibold text-[#0F172A] mb-3">Order Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Items:</span>
                            <span x-text="items.length"></span>
                        </div>
                        <div class="flex justify-between font-bold text-lg pt-2 border-t border-slate-200">
                            <span>Total:</span>
                            <span x-text="formatCurrency(total)"></span>
                        </div>
                    </div>
                    <a href="{{ route('portal.checkout') }}" class="block w-full mt-4 bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2.5 rounded-[10px] text-sm font-medium text-center transition">
                        Proceed to Checkout
                    </a>
                    <button @click="clearCart()" class="w-full mt-2 text-sm text-slate-500 hover:text-red-600 py-2 transition">
                        Clear Cart
                    </button>
                </div>
            </div>
        </div>

        <div x-show="items.length === 0" x-cloak class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-12 text-center">
            <span class="material-symbols-outlined text-5xl text-slate-300 mb-3 block">shopping_cart</span>
            <p class="text-slate-500 mb-4">Your cart is empty</p>
            <a href="{{ route('portal.products') }}" class="inline-flex items-center gap-2 bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2.5 rounded-[10px] text-sm font-medium transition">
                <span class="material-symbols-outlined text-lg">storefront</span>
                Browse Products
            </a>
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

                async updateItem(productId, quantity) {
                    quantity = parseInt(quantity);
                    if (quantity < 1) {
                        this.removeItem(productId);
                        return;
                    }

                    try {
                        const response = await fetch('{{ route("portal.cart.update") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `product_id=${productId}&quantity=${quantity}&_token=${encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content)}`,
                        });
                        const data = await response.json();
                        if (data.success) {
                            const item = this.items.find(i => i.product.id === productId);
                            if (item) {
                                item.quantity = quantity;
                                item.subtotal = item.product.unit_price * quantity;
                            }
                            this.total = this.items.reduce((sum, i) => sum + i.subtotal, 0);
                            document.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_count: data.cart_count } }));
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Cart updated.', type: 'success' } }));
                        }
                    } catch (e) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to update cart.', type: 'error' } }));
                    }
                },

                async removeItem(productId) {
                    try {
                        const response = await fetch('{{ route("portal.cart.remove") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `product_id=${productId}&_token=${encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content)}`,
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.items = this.items.filter(i => i.product.id !== productId);
                            this.total = this.items.reduce((sum, i) => sum + i.subtotal, 0);
                            document.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_count: data.cart_count } }));
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Item removed.', type: 'success' } }));
                        }
                    } catch (e) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to remove item.', type: 'error' } }));
                    }
                },

                async clearCart() {
                    if (!confirm('Clear all items from cart?')) return;
                    try {
                        const response = await fetch('{{ route("portal.cart.clear") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `_token=${encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content)}`,
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.items = [];
                            this.total = 0;
                            document.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_count: 0 } }));
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Cart cleared.', type: 'success' } }));
                        }
                    } catch (e) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to clear cart.', type: 'error' } }));
                    }
                },
            }
        }
    </script>
    @endpush
</x-portal-layout>
