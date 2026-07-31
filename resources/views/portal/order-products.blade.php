<x-portal-layout title="Shop">
    <div class="space-y-6" x-data="shopApp()">
        {{-- Search & Filter Bar --}}
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4">
            <form method="GET" action="{{ route('portal.products') }}">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
                               class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="flex gap-2 items-center">
                        <select name="sort" class="px-3 py-2.5 border border-slate-300 rounded-[10px] text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name A-Z</option>
                        </select>
                        <div class="flex items-center gap-1">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min"
                                   class="w-20 px-2 py-2.5 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none" step="100">
                            <span class="text-slate-400 text-sm">-</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max"
                                   class="w-20 px-2 py-2.5 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none" step="100">
                        </div>
                        <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2.5 rounded-[10px] text-sm font-medium transition">
                            Filter
                        </button>
                        @if (request()->hasAny(['search', 'sort', 'min_price', 'max_price']))
                            <a href="{{ route('portal.products') }}" class="text-slate-500 hover:text-slate-700 px-2 py-2 text-sm">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Results Info --}}
        @if (request()->hasAny(['search', 'min_price', 'max_price']))
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <span class="material-symbols-outlined text-lg text-slate-400">filter_alt</span>
                Showing {{ $products->count() }} {{ Str::plural('product', $products->count()) }}
                @if (request('search'))
                    for "<strong>{{ request('search') }}</strong>"
                @endif
            </div>
        @endif

        {{-- Products Grid --}}
        @if ($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach ($products as $product)
                    <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg transition-all duration-200 group flex flex-col">
                        <div class="relative h-48 bg-gradient-to-br from-slate-100 to-slate-50 flex items-center justify-center overflow-hidden">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <span class="material-symbols-outlined text-5xl text-slate-200">inventory_2</span>
                            @endif
                            @if ($product->stock_quantity <= 5 && $product->stock_quantity > 0)
                                <span class="absolute top-3 left-3 bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Only {{ $product->stock_quantity }} left</span>
                            @endif
                        </div>
                        <div class="p-4 flex flex-col flex-1">
                            <h3 class="font-semibold text-[#0F172A] text-sm mb-1 line-clamp-1">{{ $product->name }}</h3>
                            <p class="text-xs text-slate-500 line-clamp-2 mb-3 flex-1">{{ $product->description ?? 'No description available.' }}</p>
                            <div class="flex items-end justify-between mb-3">
                                <span class="text-lg font-bold text-[#0F172A]">₦{{ number_format($product->unit_price, 2) }}</span>
                                <span class="text-[11px] text-slate-400">Stock: {{ $product->stock_quantity }}</span>
                            </div>
                            <div x-data="{ added: false, loading: false }">
                                <form method="POST" action="{{ route('portal.cart.add') }}" @submit.prevent="
                                    loading = true;
                                    fetch('{{ route('portal.cart.add') }}', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: new URLSearchParams(new FormData($el))
                                    }).then(r => r.json()).then(data => {
                                        loading = false;
                                        added = true;
                                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_count: data.cart_count } }));
                                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Added to cart!', type: 'success' } }));
                                        setTimeout(() => added = false, 2000);
                                    });
                                ">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" :disabled="loading || added"
                                            :class="added ? 'bg-green-500 hover:bg-green-500' : 'bg-[#0F172A] hover:bg-slate-800'"
                                            class="w-full text-white px-3 py-2.5 rounded-[16px] text-sm font-medium transition flex items-center justify-center gap-1.5 disabled:opacity-60 disabled:cursor-not-allowed">
                                        <template x-if="!added && !loading">
                                            <span class="flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                                                Add to Cart
                                            </span>
                                        </template>
                                        <template x-if="loading">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                                Adding...
                                            </span>
                                        </template>
                                        <template x-if="added">
                                            <span class="flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                                Added!
                                            </span>
                                        </template>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-16 text-center">
                <span class="material-symbols-outlined text-6xl text-slate-200 mb-4 block">search_off</span>
                <p class="text-slate-500 text-lg mb-2">No products found</p>
                <p class="text-sm text-slate-400 mb-6">Try adjusting your search or filter criteria.</p>
                <a href="{{ route('portal.products') }}" class="inline-flex items-center gap-2 bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2.5 rounded-[10px] text-sm font-medium transition">
                    <span class="material-symbols-outlined text-lg">refresh</span>
                    View All Products
                </a>
            </div>
        @endif
    </div>
</x-portal-layout>
