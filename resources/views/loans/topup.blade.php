<x-app-layout title="Top-up Loan {{ $loan->loan_number }}">
    <div class="max-w-xl space-y-6" x-data="topupForm()">
        <x-breadcrumb :items="[['label' => 'Loans', 'url' => route('loans.index')], ['label' => 'Loan ' . $loan->loan_number, 'url' => route('loans.show', $loan)], ['label' => 'Top-up']]" />
        <div class="flex items-center gap-3">
            <a href="{{ route('loans.show', $loan) }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Loan Top-up</h2>
        </div>

        <div class="bg-indigo-50 border border-indigo-200 rounded-[16px] p-4 flex items-start gap-4">
            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-blue-600 text-xl">add_circle</span>
            </div>
            <div class="flex-1">
                <p class="font-bold text-indigo-800">Top-up for Loan {{ $loan->loan_number }}</p>
                <p class="text-indigo-700 text-sm mt-1">
                    Member: {{ $loan->member->first_name }} {{ $loan->member->last_name }} ({{ $loan->member->staff_id_display }})<br>
                    Current Outstanding: ₦{{ number_format($loan->outstanding, 2) }} &middot; Current Monthly Repayment: ₦{{ number_format($loan->monthly_repayment, 2) }}
                </p>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('loans.topup.store', $loan) }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Loan Product</label>
                <select name="loan_product_id" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                    x-model="selectedProduct" @change="onProductChange()">
                    <option value="">-- Keep Current Product ({{ $loan->loanProduct?->name ?? 'None' }}) --</option>
                    @foreach ($loanProducts as $product)
                        <option value="{{ $product->id }}" data-product='@json($product)'
                            {{ old('loan_product_id', $loan->loan_product_id) == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->interest_rate }}% - {{ $product->max_term_months }}mo)
                        </option>
                    @endforeach
                </select>
                <div x-show="selectedProduct" x-transition x-cloak class="mt-2">
                    <div class="bg-blue-50 border border-blue-200 rounded-[10px] p-3 text-xs space-y-1">
                        <p class="font-medium text-blue-800" x-text="'Loan Product: ' + (productData.name || '')"></p>
                        <p class="text-blue-700" x-show="productData.min_amount">
                            Amount range: ₦<span x-text="Number(productData.min_amount || 0).toLocaleString()"></span> — ₦<span x-text="Number(productData.max_amount || 0).toLocaleString()"></span>
                        </p>
                        <p class="text-blue-700" x-show="productData.max_term_months">
                            Max tenure: <span x-text="productData.max_term_months || ''"></span> months
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Loan Type *</label>
                <select name="type" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="regular" {{ old('type', $loan->type) === 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="emergency" {{ old('type', $loan->type) === 'emergency' ? 'selected' : '' }}>Emergency</option>
                    <option value="educational" {{ old('type', $loan->type) === 'educational' ? 'selected' : '' }}>Educational</option>
                    <option value="special" {{ old('type', $loan->type) === 'special' ? 'selected' : '' }}>Special</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Top-up Amount (₦) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none z-10">₦</span>
                        <input type="text" name="amount_display" inputmode="decimal" autocomplete="off" required
                            class="w-full pl-10 pr-10 py-2 border rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none transition"
                            :class="amountError ? 'border-red-400 bg-red-50' : amountValid ? 'border-green-400 bg-green-50' : 'border-slate-300'"
                            x-model="amountDisplay" @input="onAmountInput($event)" @blur="onAmountBlur">
                        <span x-show="amountError" class="absolute right-3 top-1/2 -translate-y-1/2 text-red-500">
                            <span class="material-symbols-outlined text-lg">error</span>
                        </span>
                        <span x-show="amountValid && amountDisplay !== '' && !amountError" class="absolute right-3 top-1/2 -translate-y-1/2 text-green-500">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                        </span>
                    </div>
                    <div class="mt-1.5 space-y-0.5">
                        <p class="text-[11px] text-slate-400" x-show="maxAmount > 0">
                            Max: ₦<span x-text="Number(maxAmount).toLocaleString()"></span>
                        </p>
                        <p x-show="amountError" x-text="amountError" class="text-[11px] text-red-600 font-medium"></p>
                    </div>
                    <input type="hidden" name="amount" :value="amountRaw">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Interest Rate (%) *</label>
                    <input type="number" step="0.01" name="interest_rate" value="{{ old('interest_rate', $loan->interest_rate) }}" required min="0" max="100"
                        class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        x-model="interestRate" @input="calculateMonthly()">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tenure (Months) *</label>
                    <input type="number" name="tenure_months" value="{{ old('tenure_months', $loan->tenure_months) }}" required min="1" max="120"
                        class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        x-model="tenureMonths" @input="calculateMonthly()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Repayment (Top-up Only)</label>
                    <div class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-[10px] text-sm font-medium text-[#0F172A]">
                        ₦<span x-text="monthlyRepayment.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">0.00</span>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-[10px] p-3">
                <div class="flex items-center gap-2 text-xs text-amber-700">
                    <span class="material-symbols-outlined text-[16px]">info</span>
                    <span>This top-up will be a separate loan linked to <strong>{{ $loan->loan_number }}</strong>. It follows its own approval and repayment schedule.</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Purpose</label>
                <textarea name="purpose" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Reason for top-up...">{{ old('purpose', 'Top-up for loan ' . $loan->loan_number) }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">
                    Submit Top-up Application
                </button>
                <a href="{{ route('loans.show', $loan) }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        const loanProducts = @json($loanProducts);

        function topupForm() {
            return {
                selectedProduct: '{{ old('loan_product_id', $loan->loan_product_id) }}',
                amountRaw: {{ max(old('amount', 0), 0) }},
                amountDisplay: '',
                amountError: '',
                amountValid: false,
                interestRate: {{ old('interest_rate', $loan->interest_rate) }},
                tenureMonths: {{ old('tenure_months', $loan->tenure_months) }},
                monthlyRepayment: 0,
                maxAmount: 0,
                productData: {},

                init() {
                    if (this.amountRaw > 0) {
                        this.amountDisplay = formatMoney(this.amountRaw);
                    }
                    this.calculateMonthly();
                    if (this.selectedProduct) {
                        this.$nextTick(() => this.onProductChange());
                    }
                },

                onAmountInput(event) {
                    let raw = event.target.value.replace(/[^0-9.]/g, '');
                    const dots = raw.match(/\./g);
                    if (dots && dots.length > 1) raw = raw.replace(/\.+$/, '');
                    this.amountRaw = parseFloat(raw) || 0;
                    this.amountDisplay = raw ? formatMoney(this.amountRaw) : '';
                    this.validateAmount();
                    this.calculateMonthly();
                },

                onAmountBlur() {
                    if (this.amountDisplay) {
                        this.amountDisplay = formatMoney(this.amountRaw);
                    }
                },

                validateAmount() {
                    this.amountError = '';
                    this.amountValid = false;
                    const val = this.amountRaw;
                    if (val <= 0) return;
                    if (this.maxAmount > 0 && val > this.maxAmount) {
                        this.amountError = 'Maximum amount is ₦' + Number(this.maxAmount).toLocaleString();
                        return;
                    }
                    this.amountValid = true;
                },

                onProductChange() {
                    const product = loanProducts.find(p => p.id == this.selectedProduct);
                    if (product) {
                        this.productData = product;
                        this.interestRate = product.interest_rate;
                        this.tenureMonths = product.max_term_months;
                        this.maxAmount = product.max_amount;
                        this.validateAmount();
                        this.calculateMonthly();
                    } else {
                        this.productData = {};
                        this.maxAmount = 0;
                        this.amountError = '';
                        this.amountValid = false;
                    }
                },

                calculateMonthly() {
                    const amount = this.amountRaw || 0;
                    const months = parseInt(this.tenureMonths) || 1;
                    const rate = parseFloat(this.interestRate) || 0;
                    const totalWithInterest = amount * (1 + rate / 100);
                    this.monthlyRepayment = totalWithInterest / months;
                }
            }
        }

        function formatMoney(value) {
            if (!value && value !== 0) return '';
            const parts = Number(value).toFixed(2).split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.join('.');
        }
    </script>
    @endpush
</x-app-layout>
