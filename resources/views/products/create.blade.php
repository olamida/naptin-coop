<x-app-layout title="Add Product">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="text-slate-500 hover:text-slate-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-[#0F172A]">Add Product</h2>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div x-data="{ preview: null }" class="text-center">
                    <div x-show="preview" class="mb-3">
                        <img :src="preview" class="w-32 h-32 rounded-[16px] object-contain mx-auto border-2 border-slate-200 bg-slate-50">
                    </div>
                    <div x-show="!preview" class="w-32 h-32 rounded-[16px] bg-slate-100 flex items-center justify-center mx-auto mb-3 border-2 border-dashed border-slate-300">
                        <span class="material-symbols-outlined text-slate-400 text-3xl">image</span>
                    </div>
                    <input type="file" name="image" accept="image/*" x-on:change="preview = URL.createObjectURL($event.target.files[0])"
                           class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-[10px] file:border-0 file:text-sm file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    <p class="text-[11px] text-slate-400 mt-1">Product image. JPG, PNG. Max 2MB</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Unit Price (₦) *</label>
                        <input type="number" name="unit_price" value="{{ old('unit_price', 0) }}" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Stock Quantity *</label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">
                        Add Product
                    </button>
                    <a href="{{ route('products.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
