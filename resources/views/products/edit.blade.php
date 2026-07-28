<x-app-layout title="Edit Product">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-gray-800">Edit: {{ $product->name }}</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf @method('PUT')
                <div x-data="{ preview: '{{ $product->image_url }}', removed: false }" class="text-center">
                    <template x-if="preview && !removed">
                        <div class="mb-3 relative inline-block">
                            <img :src="preview" class="w-32 h-32 rounded-xl object-cover border-2 border-gray-200">
                            <button type="button" x-on:click="preview = null; removed = true; $refs.removeInput.value = '1'"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center shadow">x</button>
                        </div>
                    </template>
                    <template x-if="!preview || removed">
                        <div>
                            <div class="w-32 h-32 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3 border-2 border-dashed border-gray-300">
                                <span class="material-symbols-outlined text-gray-400 text-3xl">image</span>
                            </div>
                            <input type="file" name="image" accept="image/*" x-on:change="preview = URL.createObjectURL($event.target.files[0]); removed = false"
                                   class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </template>
                    <input type="hidden" name="remove_image" value="0" x-ref="removeInput">
                    <p class="text-[11px] text-gray-400 mt-1">JPG, PNG. Max 2MB</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (₦) *</label>
                        <input type="number" name="unit_price" value="{{ old('unit_price', $product->unit_price) }}" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="enabled" value="1" {{ old('enabled', $product->enabled) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded">
                            Enabled
                        </label>
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                        Update Product
                    </button>
                    <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
