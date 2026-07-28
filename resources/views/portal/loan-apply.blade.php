<x-portal-layout title="Apply for Loan">
    <div class="max-w-xl space-y-6" x-data="loanForm()">
        <x-breadcrumb :items="[
            ['label' => 'My Loans', 'url' => route('portal.loans')],
            ['label' => 'Apply for Loan'],
        ]" />

        <div class="flex items-center gap-3">
            <a href="{{ route('portal.loans') }}" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-2xl font-bold text-gray-800">Apply for Loan</h2>
        </div>

        <form method="POST" action="{{ route('portal.loan-apply.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center gap-3">
                <span class="material-symbols-outlined text-blue-600">person</span>
                <div>
                    <p class="text-sm font-medium text-blue-800">Applying as: {{ $member->full_name }}</p>
                    <p class="text-xs text-blue-600">{{ $member->staff_id }} &middot; {{ $member->region->name ?? '' }}</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Loan Product</label>
                <select name="loan_product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                    x-model="selectedProduct" @change="onProductChange()">
                    <option value="">-- Select a Loan Product --</option>
                    @foreach ($loanProducts as $product)
                        <option value="{{ $product->id }}" data-product='@json($product)'
                            {{ old('loan_product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->interest_rate }}% - Max {{ $product->max_term_months }}mo)
                        </option>
                    @endforeach
                </select>
                <div x-show="selectedProduct" x-transition x-cloak class="mt-2">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs space-y-1">
                        <p class="font-medium text-blue-800" x-text="'Loan Product: ' + (productData.name || '')"></p>
                        <p class="text-blue-700" x-show="productData.min_amount">
                            Amount range: &#8358;<span x-text="Number(productData.min_amount || 0).toLocaleString()"></span> — &#8358;<span x-text="Number(productData.max_amount || 0).toLocaleString()"></span>
                        </p>
                        <p class="text-blue-700" x-show="productData.max_term_months">
                            Max tenure: <span x-text="productData.max_term_months || ''"></span> months
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Loan Type *</label>
                <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="regular" {{ old('type') === 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="emergency" {{ old('type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                    <option value="educational" {{ old('type') === 'educational' ? 'selected' : '' }}>Educational</option>
                    <option value="special" {{ old('type') === 'special' ? 'selected' : '' }}>Special</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (&#8358;) *</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        x-model="amount" @input="calculateMonthly()">
                    <p class="text-[11px] text-gray-400 mt-1" x-show="maxAmount > 0">
                        Max: &#8358;<span x-text="Number(maxAmount).toLocaleString()"></span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%) *</label>
                    <input type="number" step="0.01" name="interest_rate" value="{{ old('interest_rate', '5') }}" required min="0" max="100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        x-model="interestRate" @input="calculateMonthly()">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tenure (Months) *</label>
                    <input type="number" name="tenure_months" value="{{ old('tenure_months', '12') }}" required min="1" max="120"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        x-model="tenureMonths" @input="calculateMonthly()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Repayment</label>
                    <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-800">
                        &#8358;<span x-text="monthlyRepayment.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">0.00</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Auto-calculated: (Amount &times; (1 + Rate%)) / Tenure</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                <textarea name="purpose" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="State the reason for this loan application">{{ old('purpose') }}</textarea>
            </div>

            <div x-show="requiresGuarantors" x-transition x-cloak>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-3">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-amber-600 text-lg">group_add</span>
                        <h4 class="text-sm font-semibold text-amber-800">Guarantors Required</h4>
                    </div>
                    <p class="text-xs text-amber-700">This loan product requires guarantors. Select other members who will guarantee your loan. They will be notified and must accept before the loan can be approved.</p>
                </div>

                <label class="block text-sm font-medium text-gray-700 mb-1">Select Guarantors *</label>
                <select name="guarantor_ids[]" multiple size="5"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @foreach ($otherMembers as $m)
                        <option value="{{ $m->id }}" {{ in_array($m->id, old('guarantor_ids', [])) ? 'selected' : '' }}>
                            {{ $m->first_name }} {{ $m->last_name }} ({{ $m->staff_id }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple guarantors.</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                    Submit Application
                </button>
                <a href="{{ route('portal.loans') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        const loanProducts = @json($loanProducts);

        function loanForm() {
            return {
                selectedProduct: '{{ old('loan_product_id') }}',
                amount: {{ old('amount', 0) }},
                interestRate: {{ old('interest_rate', 5) }},
                tenureMonths: {{ old('tenure_months', 12) }},
                monthlyRepayment: 0,
                requiresGuarantors: false,
                maxAmount: 0,
                productData: {},

                init() {
                    this.calculateMonthly();
                    if (this.selectedProduct) {
                        this.$nextTick(() => this.onProductChange());
                    }
                },

                onProductChange() {
                    const product = loanProducts.find(p => p.id == this.selectedProduct);
                    if (product) {
                        this.productData = product;
                        this.interestRate = product.interest_rate;
                        this.tenureMonths = product.max_term_months;
                        this.maxAmount = product.max_amount;
                        this.requiresGuarantors = product.requires_guarantors;
                        this.calculateMonthly();
                    } else {
                        this.productData = {};
                        this.requiresGuarantors = false;
                        this.maxAmount = 0;
                    }
                },

                calculateMonthly() {
                    const amount = parseFloat(this.amount) || 0;
                    const months = parseInt(this.tenureMonths) || 1;
                    const rate = parseFloat(this.interestRate) || 0;
                    const totalWithInterest = amount * (1 + rate / 100);
                    this.monthlyRepayment = totalWithInterest / months;
                }
            }
        }
    </script>
    @endpush
</x-portal-layout>
