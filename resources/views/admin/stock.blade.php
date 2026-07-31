<x-app-layout title="Stock Management">
    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Management', 'url' => route('admin.manage')],
            ['label' => 'Stock Management'],
        ]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Stock Management</h2>
                <p class="text-xs text-slate-500 mt-1">Adjust product stock levels</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-sm text-[#0F172A] hover:underline flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">store</span>
                View Products
            </a>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-medium text-slate-500 text-xs uppercase tracking-wider">Product</th>
                        <th class="px-5 py-3 text-right font-medium text-slate-500 text-xs uppercase tracking-wider">Current Stock</th>
                        <th class="px-5 py-3 text-left font-medium text-slate-500 text-xs uppercase tracking-wider">Adjust By</th>
                        <th class="px-5 py-3 text-left font-medium text-slate-500 text-xs uppercase tracking-wider">Reason</th>
                        <th class="px-5 py-3 text-right font-medium text-slate-500 text-xs uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50" x-data="{ show: false }">
                            <td class="px-5 py-3">
                                <p class="font-medium text-[#0F172A]">{{ $product->name }}</p>
                                <p class="text-xs text-slate-500">₦{{ number_format($product->unit_price, 2) }}</p>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="text-lg font-bold {{ $product->stock_quantity <= 5 ? 'text-red-600' : 'text-[#0F172A]' }}">
                                    {{ $product->stock_quantity }}
                                </span>
                                @if ($product->stock_quantity <= 5)
                                    <span class="ml-1 text-xs text-red-500">Low</span>
                                @endif
                            </td>
                            <td class="px-5 py-3" colspan="3">
                                <form method="POST" action="{{ route('admin.stock.update', $product) }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="number" name="adjustment" value="0" required
                                           class="w-20 px-2 py-1.5 border border-slate-300 rounded-[10px] text-sm text-center focus:ring-2 focus:ring-[#0F172A] outline-none"
                                           placeholder="+/-">
                                    <input type="text" name="reason" placeholder="Reason (optional)"
                                           class="w-40 px-2 py-1.5 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-[#0F172A] outline-none">
                                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-3 py-1.5 rounded-[10px] text-xs font-medium transition">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500 text-sm">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
