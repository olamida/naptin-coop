<x-app-layout title="Import Purchases">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.data-import') }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <h2 class="text-2xl font-bold text-gray-800">Import Purchase Orders</h2>
        </div>

        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.data-import') }}" class="hover:text-cyan-600 transition">Data Import</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-gray-800 font-medium">Import Purchase Orders</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-blue-800">Expected Column Headers</h3>
                    <a href="{{ route('purchases.download-template') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">download</span>
                        Download Template
                    </a>
                </div>
                <p class="text-xs text-blue-700 mb-2">Your Excel file must have these column headers in the first row:</p>
                <code class="block bg-blue-100 rounded p-2 text-[11px] text-blue-900 font-mono">
                    staff_id, product_name, quantity, unit_price, payment_date, notes
                </code>
                <p class="text-xs text-blue-600 mt-2"><strong>Required:</strong> staff_id, product_name</p>
                <p class="text-xs text-blue-600"><strong>Quantity:</strong> defaults to 1 if not provided</p>
                <p class="text-xs text-blue-600"><strong>Unit Price:</strong> defaults to the product's listed price if not provided</p>
                <p class="text-xs text-blue-600">Products are matched by name (partial match). All imports are recorded as salary deduction payment type.</p>
            </div>

            <form action="{{ route('purchases.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Excel File</label>
                        <input type="file" name="import_file" accept=".xlsx,.xls,.csv" required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>

                    @if ($errors->has('import_file'))
                        <p class="text-red-600 text-sm">{{ $errors->first('import_file') }}</p>
                    @endif

                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                            Import Purchases
                        </button>
                        <a href="{{ route('purchases.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
