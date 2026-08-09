<x-public-layout title="Shop">
    {{-- Page Header --}}
    <div class="bg-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-2xl font-bold text-[#0F172A]">Cooperative Shop</h1>
            <p class="text-sm text-slate-500 mt-1">Browse our products available for members</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Search Bar --}}
        <form method="GET" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4 mb-8">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-slate-500 mb-1">Search Products</label>
                    <x-search-autocomplete :endpoint="route('shop.search')" name="search" placeholder="Search by name or description..." />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="in_stock" value="1" id="inStock" {{ request('in_stock') ? 'checked' : '' }}
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="inStock" class="text-sm text-slate-600">In Stock Only</label>
                </div>
                <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">Search</button>
                @if (request()->hasAny(['search', 'in_stock']))
                    <a href="{{ route('shop') }}" class="text-sm text-slate-500 hover:underline py-2">Clear</a>
                @endif
            </div>
        </form>

        {{-- Products Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse ($products as $product)
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition group">
                    <div class="h-48 bg-slate-100 flex items-center justify-center overflow-hidden">
                        @if ($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <span class="material-symbols-outlined text-5xl text-gray-300">inventory_2</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-[#0F172A] text-sm mb-1">{{ $product->name }}</h3>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-3">{{ $product->description ?? 'No description available' }}</p>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-[#0F172A]">₦{{ number_format($product->unit_price, 2) }}</span>
                            @if ($product->stock_quantity > 0)
                                <span class="text-xs text-green-600 font-medium bg-green-50 px-2 py-0.5 rounded-full">In Stock</span>
                            @else
                                <span class="text-xs text-red-500 font-medium bg-red-50 px-2 py-0.5 rounded-full">Out of Stock</span>
                            @endif
                        </div>
                        @if ($product->stock_quantity > 0 && $product->enabled)
                            @auth
                                @php $memberId = Auth::user()->member_id ?? null; @endphp
                                @if ($memberId)
                                    <div x-data="{ added: false, loading: false }">
                                        <form method="POST" action="{{ route('cart.add') }}" @submit.prevent="
                                            loading = true;
                                            fetch('{{ route('cart.add') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                    'Content-Type': 'application/x-www-form-urlencoded',
                                                },
                                                body: new URLSearchParams(new FormData($el))
                                            }).then(r => r.json()).then(data => {
                                                loading = false;
                                                if (data.success) { added = true; }
                                            });
                                        ">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="member_id" value="{{ $memberId }}">
                                            <button type="submit" :disabled="loading || added" :class="added ? 'bg-green-600 hover:bg-green-600 cursor-not-allowed' : 'bg-[#0F172A] hover:bg-slate-800'" class="w-full text-white px-3 py-2 rounded-[10px] text-xs font-medium transition flex items-center justify-center gap-1 disabled:opacity-60 disabled:cursor-not-allowed">
                                                <template x-if="!added && !loading">
                                                    <span class="flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-[14px]">add_shopping_cart</span>
                                                        Add to Cart
                                                    </span>
                                                </template>
                                                <template x-if="loading">
                                                    <span>Adding...</span>
                                                </template>
                                                <template x-if="added">
                                                    <span class="flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                                        Added!
                                                    </span>
                                                </template>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <a href="{{ route('login') }}" class="w-full block text-center bg-[#0F172A] hover:bg-slate-800 text-white px-3 py-2 rounded-[10px] text-xs font-medium transition">
                                        Login to Order
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="w-full block text-center bg-[#0F172A] hover:bg-slate-800 text-white px-3 py-2 rounded-[10px] text-xs font-medium transition">
                                    Login to Order
                                </a>
                            @endauth
                        @else
                            <button disabled class="w-full bg-slate-200 text-slate-500 px-3 py-2 rounded-[10px] text-xs font-medium cursor-not-allowed">
                                Out of Stock
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-[16px] shadow-sm border border-slate-200 p-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-gray-300 mb-3">inventory_2</span>
                    <p class="text-slate-500 mb-1">No products found.</p>
                    @if (request()->hasAny(['search', 'in_stock']))
                        <a href="{{ route('shop') }}" class="text-sm text-blue-600 hover:underline">Clear filters</a>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </div>
</x-public-layout>
