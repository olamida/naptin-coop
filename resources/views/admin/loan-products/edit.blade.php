<x-app-layout title="Edit Loan Product">
    <div class="max-w-2xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.loan-products.index') }}" class="text-slate-500 hover:text-slate-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-[#0F172A]">Edit: {{ $product->name }}</h2>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <form method="POST" action="{{ route('admin.loan-products.update', $product) }}" class="space-y-5">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Product Name *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" rows="2"
                                  class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('description', $product->description) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Min Amount (₦) *</label>
                        <input type="number" name="min_amount" value="{{ old('min_amount', $product->min_amount) }}" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Max Amount (₦) *</label>
                        <input type="number" name="max_amount" value="{{ old('max_amount', $product->max_amount) }}" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Interest Rate (%) *</label>
                        <input type="number" name="interest_rate" value="{{ old('interest_rate', $product->interest_rate) }}" step="0.01" min="0" max="100" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Processing Fee (%) *</label>
                        <input type="number" name="processing_fee_pct" value="{{ old('processing_fee_pct', $product->processing_fee_pct) }}" step="0.01" min="0" max="100"
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Repayment Method *</label>
                        <select name="repayment_method" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="flat" {{ old('repayment_method', $product->repayment_method) === 'flat' ? 'selected' : '' }}>Flat</option>
                            <option value="reducing_balance" {{ old('repayment_method', $product->repayment_method) === 'reducing_balance' ? 'selected' : '' }}>Reducing Balance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Max Term (months) *</label>
                        <input type="number" name="max_term_months" value="{{ old('max_term_months', $product->max_term_months) }}" min="1" max="120" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <!-- Flexible Loan Policy Fields -->
                <div class="border-t border-slate-200 pt-5 bg-emerald-50 rounded-[10px] p-4">
                    <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider mb-4">Flexible Loan Policy (Multiplier & Deduction Cap Settings)</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Default Savings Multiplier *</label>
                            <input type="number" name="default_multiplier" value="{{ old('default_multiplier', $product->default_multiplier) }}" step="0.1" min="0.1" max="10" required
                                   class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="e.g., 2.0">
                            <p class="text-[11px] text-slate-400 mt-1">Default multiplier applied to member's savings (e.g., 2.0 = 2x savings)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Maximum Multiplier *</label>
                            <input type="number" name="max_multiplier" value="{{ old('max_multiplier', $product->max_multiplier) }}" step="0.1" min="0.1" max="10" required
                                   class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="e.g., 4.0">
                            <p class="text-[11px] text-slate-400 mt-1">Absolute max multiplier even with EXCO override (must be >= default)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Interest Rate (%) *</label>
                            <input type="number" name="interest_rate_monthly" value="{{ old('interest_rate_monthly', $product->interest_rate_monthly) }}" step="0.01" min="0" max="100" required
                                   class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="e.g., 1.5">
                            <p class="text-[11px] text-slate-400 mt-1">Monthly interest rate for amortization calculation</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Min Tenure (months) *</label>
                            <input type="number" name="min_tenure_months" value="{{ old('min_tenure_months', $product->min_tenure_months) }}" min="1" max="120" required
                                   class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="e.g., 1">
                            <p class="text-[11px] text-slate-400 mt-1">Minimum loan tenure in months</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Per-Member Limits</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Max Active Loans per Member</label>
                            <input type="number" name="max_loans_per_member" value="{{ old('max_loans_per_member', $product->max_loans_per_member) }}" min="1"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="Leave empty for no limit">
                            <p class="text-[11px] text-slate-400 mt-1">Max active loans a member can have under this product</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Max Total Outstanding per Member (₦)</label>
                            <input type="number" name="max_total_amount_per_member" value="{{ old('max_total_amount_per_member', $product->max_total_amount_per_member) }}" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="Leave empty for no limit">
                            <p class="text-[11px] text-slate-400 mt-1">Total outstanding amount cap across all active loans</p>
                        </div>
                    </div>
                </div>

                <!-- Guarantor Settings -->
                <div class="border-t border-slate-200 pt-5 bg-blue-50 rounded-[10px] p-4">
                    <p class="text-xs font-semibold text-blue-700 uppercase tracking-wider mb-3">Guarantor Settings</p>
                    <div class="flex items-center gap-6 mb-3">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="requires_guarantor" value="1" {{ old('requires_guarantor', $product->requires_guarantor) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded">
                            Requires Guarantor
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Min Guarantors</label>
                            <input type="number" name="min_guarantors" value="{{ old('min_guarantors', $product->min_guarantors) }}" min="1" max="5"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="e.g., 1">
                            <p class="text-[11px] text-slate-400 mt-1">Minimum number of guarantors required</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Max Guarantors</label>
                            <input type="number" name="max_guarantors" value="{{ old('max_guarantors', $product->max_guarantors) }}" min="1" max="5"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                   placeholder="e.g., 3">
                            <p class="text-[11px] text-slate-400 mt-1">Maximum number of guarantors allowed</p>
                        </div>
                    </div>
                </div>

                <!-- Override Permissions -->
                <div class="border-t border-slate-200 pt-5 bg-amber-50 rounded-[10px] p-4">
                    <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider mb-3">Override Permissions</p>
                    <div class="flex items-center gap-6 flex-wrap">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="allow_multiplier_override" value="1" {{ old('allow_multiplier_override', $product->allow_multiplier_override) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded">
                            Allow Multiplier Override (EXCO can increase multiplier for special cases)
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="allow_deduction_cap_override" value="1" {{ old('allow_deduction_cap_override', $product->allow_deduction_cap_override) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded">
                            Allow Deduction Cap Override (EXCO can exceed 33.33% salary deduction)
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="requires_guarantors" value="1" {{ old('requires_guarantors', $product->requires_guarantors) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 rounded">
                        Requires Guarantors (Legacy)
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="requires_collateral" value="1" {{ old('requires_collateral', $product->requires_collateral) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 rounded">
                        Requires Collateral
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="enabled" value="1" {{ old('enabled', $product->enabled) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 rounded">
                        Enabled
                    </label>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">
                        Update Product
                    </button>
                    <a href="{{ route('admin.loan-products.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>