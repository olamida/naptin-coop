<x-app-layout title="New Loan Application">
    <div class="max-w-xl space-y-6" x-data="loanForm()">
        <x-breadcrumb :items="[['label' => 'Loans', 'url' => route('loans.index')], ['label' => 'New Loan']]" />
        <div class="flex items-center gap-3">
            <a href="{{ route('loans.index') }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <h2 class="text-2xl font-bold text-gray-800">New Loan Application</h2>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('loans.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Member *</label>
                <div x-data="memberSearch(@json($members->map(fn($m) => ['id' => $m->id, 'first_name' => $m->first_name, 'last_name' => $m->last_name, 'staff_id' => $m->staff_id])))" x-init="init()">
                    <div class="relative">
                        <input type="text" x-model="search" @input="filterMembers()" @click="showDropdown = true" @click.away="showDropdown = false"
                            placeholder="Type to search members..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <input type="hidden" name="member_id" :value="selectedId">
                        <div x-show="showDropdown && filteredMembers.length > 0" x-transition
                            class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="m in filteredMembers" :key="m.id">
                                <div @click="selectMember(m); selectedMember = m.id" class="px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm border-b border-gray-50 last:border-0"
                                    :class="selectedId == m.id ? 'bg-blue-50 font-medium' : ''">
                                    <span x-text="m.first_name + ' ' + m.last_name"></span>
                                    <span class="text-xs text-gray-400 ml-1" x-text="'(' + m.staff_id + ')'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Loan Product</label>
                <select name="loan_product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                    x-model="selectedProduct" @change="onProductChange()">
                    <option value="">-- Select a Loan Product (optional) --</option>
                    @foreach ($loanProducts as $product)
                        <option value="{{ $product->id }}" data-product='@json($product)'
                            {{ old('loan_product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->interest_rate }}% - {{ $product->max_term_months }}mo)
                        </option>
                    @endforeach
                </select>
                @if ($loanProducts->count() > 0)
                    <p class="text-[11px] text-gray-400 mt-1">Selecting a product will auto-fill the loan parameters below.</p>
                @endif
                <div x-show="selectedProduct" x-transition x-cloak class="mt-2">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs space-y-1">
                        <p class="font-medium text-blue-800" x-text="'Loan Product: ' + (productData.name || '')"></p>
                        <p class="text-blue-700" x-show="productData.min_amount">
                            Amount range: ₦<span x-text="Number(productData.min_amount || 0).toLocaleString()"></span> — ₦<span x-text="Number(productData.max_amount || 0).toLocaleString()"></span>
                        </p>
                        <p class="text-blue-700" x-show="productData.max_term_months">
                            Max tenure: <span x-text="productData.max_term_months || ''"></span> months
                        </p>
                        <p class="text-blue-700" x-show="productData.max_loans_per_member">
                            Max active loans per member: <span x-text="productData.max_loans_per_member || ''"></span>
                        </p>
                        <p class="text-blue-700" x-show="productData.max_total_amount_per_member">
                            Max total outstanding per member: ₦<span x-text="Number(productData.max_total_amount_per_member || 0).toLocaleString()"></span>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₦) *</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        x-model="amount" @input="calculateMonthly()">
                    <p class="text-[11px] text-gray-400 mt-1" x-show="maxAmount > 0">
                        Max: ₦<span x-text="Number(maxAmount).toLocaleString()"></span>
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
                        ₦<span x-text="monthlyRepayment.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">0.00</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Auto-calculated: Amount / Tenure</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                <textarea name="purpose" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('purpose') }}</textarea>
            </div>

            {{-- Guarantors Section --}}
            <div x-show="requiresGuarantors" x-transition x-cloak>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-3">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-amber-600 text-lg">group_add</span>
                        <h4 class="text-sm font-semibold text-amber-800">Guarantors Required</h4>
                    </div>
                    <p class="text-xs text-amber-700">This loan product requires guarantors. Select at least one member to guarantee this loan. Guarantors will be notified and must accept before the loan can be approved.</p>
                </div>

                <label class="block text-sm font-medium text-gray-700 mb-1">Select Guarantors *</label>
                <select name="guarantor_ids[]" multiple size="5"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" {{ in_array($member->id, old('guarantor_ids', [])) ? 'selected' : '' }}
                            :disabled="selectedMember == '{{ $member->id }}'">
                            {{ $member->first_name }} {{ $member->last_name }} ({{ $member->staff_id }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple guarantors. The borrower cannot be their own guarantor.</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                    Submit Application
                </button>
                <a href="{{ route('loans.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        const loanProducts = @json($loanProducts);

        function loanForm() {
            return {
                selectedMember: '{{ old('member_id') }}',
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
</x-app-layout>
