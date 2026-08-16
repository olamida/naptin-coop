<x-app-layout title="Loan Products">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-[#0F172A]">Loan Products</h2>
            <a href="{{ route('admin.loan-products.create') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">add</span>
                Add Product
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($products as $product)
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5 hover:shadow-md transition">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-[#0F172A]">{{ $product->name }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ ucfirst($product->repayment_method) }} | {{ $product->max_term_months }} months max</p>
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $product->enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $product->enabled ? 'Active' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs mb-4">
                        <div class="bg-slate-50 rounded-[10px] p-2">
                            <p class="text-slate-500">Min Amount</p>
                            <p class="font-semibold">₦{{ number_format($product->min_amount, 2) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-[10px] p-2">
                            <p class="text-slate-500">Max Amount</p>
                            <p class="font-semibold">₦{{ number_format($product->max_amount, 2) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-[10px] p-2">
                            <p class="text-slate-500">Interest Rate</p>
                            <p class="font-semibold">{{ $product->interest_rate }}%</p>
                        </div>
                        <div class="bg-slate-50 rounded-[10px] p-2">
                            <p class="text-slate-500">Processing Fee</p>
                            <p class="font-semibold">{{ $product->processing_fee_pct }}%</p>
                        </div>
                    </div>

                    <!-- Flexible Loan Policy Fields -->
                    <div class="grid grid-cols-2 gap-2 text-xs mb-4 p-3 bg-emerald-50 rounded-[10px] border border-emerald-100">
                        <div>
                            <p class="text-emerald-700 font-medium">Default Multiplier</p>
                            <p class="font-semibold text-emerald-900">{{ $product->default_multiplier }}x</p>
                        </div>
                        <div>
                            <p class="text-emerald-700 font-medium">Max Multiplier</p>
                            <p class="font-semibold text-emerald-900">{{ $product->max_multiplier }}x</p>
                        </div>
                        <div>
                            <p class="text-emerald-700 font-medium">Monthly Interest</p>
                            <p class="font-semibold text-emerald-900">{{ $product->interest_rate_monthly }}%</p>
                        </div>
                        <div>
                            <p class="text-emerald-700 font-medium">Min Tenure</p>
                            <p class="font-semibold text-emerald-900">{{ $product->min_tenure_months }} months</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-[10px] text-slate-500 mb-3 flex-wrap">
                        @if ($product->requires_guarantors)
                            <span class="px-1.5 py-0.5 bg-yellow-50 text-yellow-700 rounded">Guarantors Required</span>
                        @endif
                        @if ($product->requires_collateral)
                            <span class="px-1.5 py-0.5 bg-orange-50 text-orange-700 rounded">Collateral Required</span>
                        @endif
                        @if ($product->max_loans_per_member)
                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded">Max {{ $product->max_loans_per_member }} loan(s)/member</span>
                        @endif
                        @if ($product->max_total_amount_per_member)
                            <span class="px-1.5 py-0.5 bg-purple-50 text-purple-700 rounded">Max ₦{{ number_format($product->max_total_amount_per_member, 0) }}/member</span>
                        @endif
                        @if ($product->requires_guarantor)
                            <span class="px-1.5 py-0.5 bg-teal-50 text-teal-700 rounded">Guarantor Required</span>
                        @endif
                        @if ($product->min_guarantors)
                            <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 rounded">Min {{ $product->min_guarantors }} Guarantor(s)</span>
                        @endif
                        @if ($product->max_guarantors)
                            <span class="px-1.5 py-0.5 bg-cyan-50 text-cyan-700 rounded">Max {{ $product->max_guarantors }} Guarantors</span>
                        @endif
                        @if ($product->allow_multiplier_override)
                            <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 rounded">Allow Multiplier Override</span>
                        @endif
                        @if ($product->allow_deduction_cap_override)
                            <span class="px-1.5 py-0.5 bg-amber-50 text-amber-700 rounded">Allow Deduction Cap Override</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                        <a href="{{ route('admin.loan-products.edit', $product) }}" class="text-xs text-[#0F172A] hover:underline">Edit</a>
                        <form id="delete-loan-product-{{ $product->id }}" method="POST" action="{{ route('admin.loan-products.destroy', $product) }}">
                            @csrf @method('DELETE')
                            <button type="button" onclick="deleteConfirm('delete-loan-product-{{ $product->id }}')" class="text-xs text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-[16px] shadow-sm border border-slate-200 p-8 text-center">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">category</span>
                    <p class="text-sm text-slate-500">No loan products created yet.</p>
                    <a href="{{ route('admin.loan-products.create') }}" class="inline-block mt-3 text-sm text-[#0F172A] hover:underline">Create First Product &rarr;</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>