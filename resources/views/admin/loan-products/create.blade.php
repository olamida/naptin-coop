<x-app-layout title="Create Loan Product">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.loan-products.index') }}" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-gray-800">Create Loan Product</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ route('admin.loan-products.store') }}" class="space-y-5">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                        <input type="text" name="slug" value="{{ old('slug') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="e.g., regular, emergency, educational">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Amount (₦) *</label>
                        <input type="number" name="min_amount" value="{{ old('min_amount', 0) }}" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Amount (₦) *</label>
                        <input type="number" name="max_amount" value="{{ old('max_amount', 0) }}" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%) *</label>
                        <input type="number" name="interest_rate" value="{{ old('interest_rate', 0) }}" step="0.01" min="0" max="100" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Processing Fee (%) *</label>
                        <input type="number" name="processing_fee_pct" value="{{ old('processing_fee_pct', 0) }}" step="0.01" min="0" max="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Repayment Method *</label>
                        <select name="repayment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="flat" {{ old('repayment_method') === 'flat' ? 'selected' : '' }}>Flat</option>
                            <option value="reducing_balance" {{ old('repayment_method') === 'reducing_balance' ? 'selected' : '' }}>Reducing Balance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Term (months) *</label>
                        <input type="number" name="max_term_months" value="{{ old('max_term_months', 12) }}" min="1" max="120" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="border-t border-gray-100 pt-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Per-Member Limits</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Active Loans per Member</label>
                            <input type="number" name="max_loans_per_member" value="{{ old('max_loans_per_member') }}" min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="Leave empty for no limit">
                            <p class="text-[11px] text-gray-400 mt-1">Max active loans a member can have under this product</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Total Outstanding per Member (₦)</label>
                            <input type="number" name="max_total_amount_per_member" value="{{ old('max_total_amount_per_member') }}" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="Leave empty for no limit">
                            <p class="text-[11px] text-gray-400 mt-1">Total outstanding amount cap across all active loans</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="requires_guarantors" value="1" {{ old('requires_guarantors') ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 rounded">
                        Requires Guarantors
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="requires_collateral" value="1" {{ old('requires_collateral') ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 rounded">
                        Requires Collateral
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="enabled" value="1" {{ old('enabled', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 rounded">
                        Enabled
                    </label>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                        Create Product
                    </button>
                    <a href="{{ route('admin.loan-products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
