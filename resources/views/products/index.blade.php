<x-app-layout title="Products">
    <div class="space-y-6" x-data="{ viewMode: localStorage.getItem('productViewMode') || '{{ $isAdmin ? 'list' : 'grid' }}' }">
        <x-breadcrumb :items="[['label' => 'Products']]" />
        @if ($orderMember)
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-blue-600">person</span>
                    <div>
                        <p class="text-sm font-semibold text-blue-800">Ordering for: {{ $orderMember->full_name }} ({{ $orderMember->staff_id }})</p>
                        <p class="text-xs text-blue-600">Products added to cart will be assigned to this member.</p>
                    </div>
                </div>
                <a href="{{ route('products.index') }}" class="text-xs text-blue-600 hover:underline">Clear &times;</a>
            </div>
        @endif
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">Products</h2>
            <div class="flex items-center gap-3">
                <div class="flex items-center bg-gray-100 rounded-lg p-0.5">
                    <button x-on:click="viewMode = 'grid'; localStorage.setItem('productViewMode', 'grid')"
                            :class="viewMode === 'grid' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500'"
                            class="p-1.5 rounded-md transition">
                        <span class="material-symbols-outlined text-lg">grid_view</span>
                    </button>
                    <button x-on:click="viewMode = 'list'; localStorage.setItem('productViewMode', 'list')"
                            :class="viewMode === 'list' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500'"
                            class="p-1.5 rounded-md transition">
                        <span class="material-symbols-outlined text-lg">view_list</span>
                    </button>
                </div>
                @if (! $isAdmin || $memberId)
                    <a href="{{ route('cart.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1.5 relative">
                        <span class="material-symbols-outlined text-lg">shopping_cart</span>
                        Cart
                        @php $cartCount = count(session('cart', [])); @endphp
                        @if ($cartCount > 0)
                            <span class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full text-[10px] font-bold flex items-center justify-center">{{ $cartCount }}</span>
                        @endif
                    </a>
                @endif
                @if ($isAdmin)
                    @can('manage-products')
                        <a href="{{ route('products.import') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-lg">upload_file</span>
                            Import
                        </a>
                        <a href="{{ route('products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">add</span>
                            Add Product
                        </a>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        @if ($isAdmin)
            <div class="flex items-center gap-1 border-b border-gray-200">
                <a href="{{ route('products.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px">
                    Products
                </a>
                <a href="{{ route('products.orders') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 -mb-px transition">
                    Orders
                </a>
            </div>
        @endif

        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-gray-500 mb-1">Search Products</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name or description..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                @if ($isAdmin)
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="in_stock" value="1" id="inStock" {{ request('in_stock') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="inStock" class="text-sm text-gray-600">In Stock Only</label>
                    </div>
                @endif
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Search</button>
                @if (request()->hasAny(['search', 'in_stock']))
                    <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
                @endif
            </div>
        </form>

        {{-- Grid View (default for members) --}}
        <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse ($products as $product)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                    <div class="h-40 bg-gray-100 flex items-center justify-center">
                        @if ($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="material-symbols-outlined text-4xl text-gray-300">inventory_2</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 text-sm mb-1">{{ $product->name }}</h3>
                        <p class="text-xs text-gray-500 line-clamp-1 mb-3">{{ $product->description ?? 'No description' }}</p>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-gray-900">₦{{ number_format($product->unit_price, 2) }}</span>
                            @if ($isAdmin)
                                <span class="text-xs text-gray-500">Stock: <span class="font-semibold {{ $product->stock_quantity <= 0 ? 'text-red-600' : '' }}">{{ $product->stock_quantity }}</span></span>
                            @endif
                        </div>
                        @if (! $isAdmin || $memberId)
                            @if ($product->stock_quantity > 0 && $product->enabled)
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
                                        }).then(() => {
                                            loading = false;
                                            added = true;
                                        });
                                    ">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        @if ($memberId)
                                            <input type="hidden" name="member_id" value="{{ $memberId }}">
                                        @endif
                                        <button type="submit" :disabled="loading || added" :class="added ? 'bg-green-600 hover:bg-green-600 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'" class="w-full text-white px-3 py-2 rounded-lg text-xs font-medium transition flex items-center justify-center gap-1 disabled:opacity-60 disabled:cursor-not-allowed">
                                            <template x-if="!added && !loading">
                                                <span class="flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[14px]">add_shopping_cart</span>
                                                    Add to Cart
                                                </span>
                                            </template>
                                            <template x-if="loading">
                                                <span class="flex items-center gap-1">
                                                    <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                                    Adding...
                                                </span>
                                            </template>
                                            <template x-if="added">
                                                <span class="flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                                    Added to Cart
                                                </span>
                                            </template>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <button disabled class="w-full bg-gray-200 text-gray-500 px-3 py-2 rounded-lg text-xs font-medium cursor-not-allowed">
                                    Out of Stock
                                </button>
                            @endif
                        @else
                            @can('manage-products')
                                <a href="{{ route('products.edit', $product) }}" class="text-xs text-blue-600 hover:underline font-medium">Edit Product</a>
                            @endcan
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                    <span class="material-symbols-outlined text-4xl text-gray-300 mb-2">inventory_2</span>
                    <p class="text-sm text-gray-500">No products yet.</p>
                    @if ($isAdmin && Auth::user()->can('manage-products'))
                        <a href="{{ route('products.create') }}" class="inline-block mt-3 text-sm text-blue-600 hover:underline">Add First Product &rarr;</a>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- List View (default for admin, shows stock) --}}
        <div x-show="viewMode === 'list'" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Price</th>
                            @if ($isAdmin)
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            @endif
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                            @if ($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-gray-400 text-xl">inventory_2</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ $product->name }}</p>
                                            <p class="text-xs text-gray-500 max-w-[200px] truncate">{{ $product->description ?? 'No description' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">₦{{ number_format($product->unit_price, 2) }}</td>
                                @if ($isAdmin)
                                    <td class="px-4 py-3 text-sm {{ $product->stock_quantity <= 0 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">{{ $product->stock_quantity }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $product->enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $product->enabled ? 'Active' : 'Disabled' }}
                                        </span>
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-right">
                                    @if ($isAdmin && ! $memberId && Auth::user()->can('manage-products'))
                                        <a href="{{ route('products.edit', $product) }}" class="text-xs text-blue-600 hover:underline font-medium">Edit</a>
                                    @else
                                        @if ($product->stock_quantity > 0 && $product->enabled)
                                            <div x-data="{ added: false, loading: false }" class="inline">
                                                <form method="POST" action="{{ route('cart.add') }}" class="inline" @submit.prevent="
                                                    loading = true;
                                                    fetch('{{ route('cart.add') }}', {
                                                        method: 'POST',
                                                        headers: {
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                            'X-Requested-With': 'XMLHttpRequest',
                                                            'Content-Type': 'application/x-www-form-urlencoded',
                                                        },
                                                        body: new URLSearchParams(new FormData($el))
                                                    }).then(() => {
                                                        loading = false;
                                                        added = true;
                                                    });
                                                ">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    @if ($memberId)
                                                        <input type="hidden" name="member_id" value="{{ $memberId }}">
                                                    @endif
                                                    <button type="submit" :disabled="loading || added" :class="added ? 'text-green-600 cursor-not-allowed' : 'text-blue-600'" class="text-xs hover:underline font-medium disabled:opacity-60 disabled:cursor-not-allowed" x-text="added ? 'Added to Cart ✓' : (loading ? 'Adding...' : 'Add to Cart')"></button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Out of stock</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 5 : 3 }}" class="px-4 py-8 text-center text-sm text-gray-500">No products yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $products->links() }}

            <x-show-all-toggle />
        </div>
    </div>
</x-app-layout>
