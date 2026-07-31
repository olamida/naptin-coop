<x-portal-layout title="Apply for Loan">
    <div class="max-w-3xl mx-auto space-y-6" x-data="loanWizard()">
        <x-breadcrumb :items="[
            ['label' => 'My Loans', 'url' => route('portal.loans')],
            ['label' => 'Apply for Loan'],
        ]" />

        <div class="flex items-center gap-3">
            <a href="{{ route('portal.loans') }}" class="text-slate-500 hover:text-slate-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Apply for Loan</h2>
        </div>

        {{-- Stepper --}}
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4 sm:p-6">
            <div class="flex items-center">
                <template x-for="(s, i) in steps" :key="s.num">
                    <div class="flex items-center" :class="i === steps.length - 1 ? 'flex-none' : 'flex-1'">
                        <button type="button" @click="s.num <= step ? step = s.num : null"
                                class="flex flex-col items-center focus:outline-none">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-all"
                                 :class="s.num < step ? 'bg-emerald-500 text-white' : s.num === step ? 'bg-[#0F172A] text-white ring-4 ring-slate-100' : 'bg-slate-200 text-slate-500'">
                                <template x-if="s.num < step">
                                    <span class="material-symbols-outlined text-lg">check</span>
                                </template>
                                <template x-if="s.num >= step">
                                    <span class="material-symbols-outlined text-lg" x-text="s.icon"></span>
                                </template>
                            </div>
                            <span class="mt-2 text-xs font-medium whitespace-nowrap"
                                  :class="s.num === step ? 'text-[#0F172A] font-semibold' : s.num < step ? 'text-emerald-600' : 'text-slate-400'"
                                  x-text="s.label"></span>
                        </button>
                        <template x-if="i < steps.length - 1">
                            <div class="flex-1 h-0.5 mx-2 mb-5 rounded" :class="s.num < step ? 'bg-emerald-400' : 'bg-slate-200'"></div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <form method="POST" action="{{ route('portal.loan-apply.store') }}">
            @csrf

            {{-- Hidden fields synced from wizard state --}}
            <input type="hidden" name="loan_product_id" :value="selectedProduct">
            <input type="hidden" name="type" value="regular">
            <input type="hidden" name="amount" :value="amountRaw">
            <input type="hidden" name="interest_rate" :value="interestRate">
            <input type="hidden" name="tenure_months" :value="tenureMonths">
            <template x-for="id in selectedGuarantors" :key="id">
                <input type="hidden" name="guarantor_ids[]" :value="id">
            </template>

            {{-- ============ STEP 1: PRODUCT + AMOUNT ============ --}}
            <div x-show="step === 1" x-cloak x-transition.opacity class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-[#0F172A]">Choose your loan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pick a product and set how much you need.</p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-slate-300">tune</span>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-[10px] p-3 flex items-center gap-3">
                    <span class="material-symbols-outlined text-blue-600">person</span>
                    <div>
                        <p class="text-sm font-medium text-[#0F172A]">Applying as: {{ $member->full_name }}</p>
                        <p class="text-xs text-blue-600">{{ $member->staff_id_display }} &middot; {{ $member->region->name ?? '' }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Loan Product</label>
                    <select name="" class="w-full px-3 py-2.5 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-[#0F172A] outline-none"
                        x-model="selectedProduct" @change="onProductChange()">
                        <option value="">-- Select a Loan Product --</option>
                        @foreach ($loanProducts as $product)
                            <option value="{{ $product->id }}" {{ old('loan_product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->interest_rate }}% &middot; up to {{ $product->max_term_months }} months)
                            </option>
                        @endforeach
                    </select>

                    <div x-show="selectedProduct" x-transition x-cloak class="mt-3">
                        <div class="bg-slate-50 border border-slate-200 rounded-[10px] p-3.5 text-xs space-y-1.5">
                            <p class="font-medium text-[#0F172A] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-slate-400">info</span>
                                <span x-text="productData.name || ''"></span>
                            </p>
                            <p class="text-slate-700" x-show="productData.description" x-text="productData.description"></p>
                            <p class="text-slate-700" x-show="productData.min_amount">
                                Range: &#8358;<span x-text="Number(productData.min_amount || 0).toLocaleString()"></span> —
                                &#8358;<span x-text="Number(productData.max_amount || 0).toLocaleString()"></span>
                            </p>
                            <p class="text-slate-700" x-show="productData.processing_fee_pct">
                                Processing fee: <span x-text="productData.processing_fee_pct"></span>%
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Eligibility --}}
                <div x-show="selectedProduct" x-transition x-cloak
                     class="bg-gradient-to-r from-[#0F172A] to-slate-700 text-white rounded-[16px] p-4 sm:p-5 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-white/60">Your savings balance</p>
                        <p class="mt-1 text-xl sm:text-2xl font-mono font-bold">&#8358;<span x-text="Number(savingsBalance).toLocaleString()"></span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-wider text-white/60">Max eligible (3&times; savings)</p>
                        <p class="mt-1 text-xl sm:text-2xl font-mono font-bold text-emerald-300">&#8358;<span x-text="Number(maxEligible).toLocaleString()"></span></p>
                        <p class="text-[10px] text-white/50 mt-0.5" title="3 × savings balance, capped at product maximum">Formula &rarr; 3 &times; savings, capped at product max</p>
                    </div>
                </div>

                <div x-show="selectedProduct" x-transition x-cloak class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-sm font-medium text-slate-700">Amount</label>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-slate-400">&#8358;</span>
                                <input type="text" x-model="amountDisplay" @input="onAmountInput($event)" @blur="onAmountBlur"
                                    inputmode="decimal" autocomplete="off"
                                    class="w-40 px-3 py-2 text-right border rounded-[10px] text-sm font-mono focus:ring-2 focus:ring-[#0F172A] outline-none transition"
                                    :class="amountError ? 'border-red-400 bg-red-50' : amountRaw > 0 ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-300'">
                            </div>
                        </div>
                        <input type="range" x-model.number="amountRaw" @input="syncFromRange()"
                            :min="minAmount > 0 ? minAmount : 0" :max="maxEligible > 0 ? maxEligible : 1" step="1000"
                            class="w-full accent-[#0F172A]" :disabled="!selectedProduct">
                        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
                            <span>&#8358;<span x-text="Number(minAmount || 0).toLocaleString()"></span></span>
                            <span class="text-slate-500">&#8358;<span x-text="Number(maxEligible).toLocaleString()"></span> max</span>
                        </div>
                        <p x-show="amountError" x-text="amountError" class="text-[11px] text-red-600 font-medium mt-1.5"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tenure</label>
                        <div class="flex items-center gap-3">
                            <input type="range" x-model.number="tenureMonths" @input="watchTenure()" :max="maxTenure" min="1" step="1"
                                class="w-full accent-[#0F172A]">
                            <span class="w-20 text-right text-sm font-mono font-semibold text-[#0F172A]" x-text="tenureMonths + ' mo'"></span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" @click="next()"
                        class="px-6 py-2.5 rounded-[10px] text-sm font-medium transition"
                        :class="canNext() ? 'bg-[#0F172A] hover:bg-slate-800 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                        Continue &rarr;
                    </button>
                </div>
            </div>

            {{-- ============ STEP 2: GUARANTORS ============ --}}
            <div x-show="step === 2" x-cloak x-transition.opacity class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-[#0F172A]">Guarantors</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Select members who will guarantee this loan.</p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-slate-300">group_add</span>
                </div>

                <div x-show="!requiresGuarantors" class="bg-emerald-50 border border-emerald-200 rounded-[10px] p-4 flex items-start gap-3">
                    <span class="material-symbols-outlined text-emerald-600">verified_user</span>
                    <div>
                        <p class="text-sm font-medium text-emerald-800">No guarantors required</p>
                        <p class="text-xs text-emerald-700 mt-0.5">This loan product does not require guarantors. You can continue.</p>
                    </div>
                </div>

                <template x-if="requiresGuarantors">
                    <div class="space-y-4">
                        <div class="bg-amber-50 border border-amber-200 rounded-[10px] p-4 flex items-start gap-3">
                            <span class="material-symbols-outlined text-amber-600">group_add</span>
                            <div>
                                <p class="text-sm font-semibold text-amber-800">Guarantors Required</p>
                                <p class="text-xs text-amber-700 mt-0.5">Each member can guarantee up to &#8358;<span x-text="Number(guarantorLimit).toLocaleString()"></span> in total. They must accept before the loan can be approved.</p>
                            </div>
                        </div>

                        <div class="relative">
                            <span class="material-symbols-outlined text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-lg">search</span>
                            <input type="text" x-model="guarantorSearch"
                                placeholder="Search by name or staff ID..."
                                class="w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-[#0F172A] outline-none">
                        </div>

                        <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                            <template x-for="g in filteredGuarantors" :key="g.id">
                                <label class="flex items-center gap-3 p-3 border rounded-[10px] cursor-pointer transition"
                                    :class="selectedGuarantors.includes(g.id) ? 'border-emerald-300 bg-emerald-50/60' : guarantorDisabled(g) ? 'border-slate-100 opacity-50 cursor-not-allowed' : 'border-slate-200 hover:border-slate-300'">
                                    <input type="checkbox"
                                        :checked="selectedGuarantors.includes(g.id)"
                                        @change="toggleGuarantor(g.id)"
                                        :disabled="guarantorDisabled(g)"
                                        class="w-4 h-4 accent-[#0F172A] rounded">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-800 truncate" x-text="g.name"></p>
                                        <p class="text-xs text-slate-500 font-mono" x-text="g.staff"></p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-xs text-slate-500">Currently guaranteeing
                                            <span class="font-mono font-semibold text-amber-600">&#8358;<span x-text="Number(g.exposure).toLocaleString()"></span></span>
                                        </p>
                                        <p class="text-[10px] mt-0.5"
                                            :class="guarantorDisabled(g) ? 'text-red-500' : 'text-emerald-600'"
                                            x-text="guarantorDisabled(g) ? 'Exceeds capacity limit' : 'Capacity left: ₦' + Number(Math.max(guarantorLimit - g.exposure, 0)).toLocaleString()"></p>
                                    </div>
                                </label>
                            </template>
                            <p x-show="filteredGuarantors.length === 0" class="text-center text-sm text-slate-400 py-6">No members match your search.</p>
                        </div>

                        <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-[10px] px-4 py-2.5">
                            <p class="text-xs text-slate-600">
                                <span x-text="selectedGuarantors.length"></span> selected
                                <template x-if="requiresGuarantors">
                                    <span>(at least 1 required)</span>
                                </template>
                            </p>
                            <p class="text-xs text-slate-400" x-show="selectedGuarantors.length === 0 && amountRaw > 0">
                                Add guarantors to continue
                            </p>
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="back()" class="px-6 py-2.5 rounded-[10px] text-sm font-medium border border-slate-300 hover:bg-slate-50 transition">
                        &larr; Back
                    </button>
                    <button type="button" @click="next()"
                        class="px-6 py-2.5 rounded-[10px] text-sm font-medium transition"
                        :class="canNext() ? 'bg-[#0F172A] hover:bg-slate-800 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                        Continue &rarr;
                    </button>
                </div>
            </div>

            {{-- ============ STEP 3: REPAYMENT PREVIEW ============ --}}
            <div x-show="step === 3" x-cloak x-transition.opacity class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-[#0F172A]">Repayment Preview</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Drag the tenure to see how your monthly payment changes.</p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-slate-300">receipt_long</span>
                </div>

                <div>
                    <label class="flex items-center justify-between text-sm font-medium text-slate-700 mb-1.5">
                        Tenure
                        <span class="text-sm font-mono font-bold text-[#0F172A]" x-text="tenureMonths + ' months'"></span>
                    </label>
                    <input type="range" x-model.number="tenureMonths" @input="watchTenure()" :max="maxTenure" min="1" step="1"
                        class="w-full accent-[#0F172A]">
                    <div class="flex justify-between text-[11px] text-slate-400 mt-1">
                        <span>1 mo</span>
                        <span x-text="maxTenure + ' mo'"></span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-[#0F172A] text-white rounded-[16px] p-4">
                        <p class="text-[10px] uppercase tracking-wider text-white/60">Monthly Repayment</p>
                        <p class="mt-1 text-lg sm:text-xl font-mono font-bold">&#8358;<span x-text="formatMoney(monthlyRepayment)"></span></p>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-[16px] p-4">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">Total Interest</p>
                        <p class="mt-1 text-lg sm:text-xl font-mono font-bold text-amber-600">&#8358;<span x-text="formatMoney(totalInterest)"></span></p>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-[16px] p-4">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">Total Payable</p>
                        <p class="mt-1 text-lg sm:text-xl font-mono font-bold text-emerald-600">&#8358;<span x-text="formatMoney(totalPayable)"></span></p>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-[16px] p-4">
                    <p class="text-xs font-semibold text-slate-700 mb-2">Balance over time</p>
                    <div style="position: relative; height: 200px;">
                        <canvas x-ref="amortChart"></canvas>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-slate-700">Amortization schedule</p>
                        <button type="button" @click="showAllPayments = !showAllPayments"
                            class="text-xs text-blue-600 hover:underline font-medium"
                            x-text="showAllPayments ? 'Show less' : 'Show all ' + amortization.length + ' payments'"></button>
                    </div>
                    <div class="overflow-x-auto rounded-[10px] border border-slate-200 max-h-72 overflow-y-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold">#</th>
                                    <th class="px-3 py-2 text-right font-semibold">Principal</th>
                                    <th class="px-3 py-2 text-right font-semibold">Interest</th>
                                    <th class="px-3 py-2 text-right font-semibold">Payment</th>
                                    <th class="px-3 py-2 text-right font-semibold">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="row in visibleAmortization" :key="row.n">
                                    <tr>
                                        <td class="px-3 py-2 text-slate-400" x-text="row.n"></td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatMoney(row.principal)"></td>
                                        <td class="px-3 py-2 text-right font-mono text-amber-600" x-text="formatMoney(row.interest)"></td>
                                        <td class="px-3 py-2 text-right font-mono font-semibold" x-text="formatMoney(row.total)"></td>
                                        <td class="px-3 py-2 text-right font-mono text-slate-500" x-text="formatMoney(row.balance)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="back()" class="px-6 py-2.5 rounded-[10px] text-sm font-medium border border-slate-300 hover:bg-slate-50 transition">
                        &larr; Back
                    </button>
                    <button type="button" @click="next()" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2.5 rounded-[10px] text-sm font-medium transition">
                        Review Application &rarr;
                    </button>
                </div>
            </div>

            {{-- ============ STEP 4: REVIEW + SUBMIT ============ --}}
            <div x-show="step === 4" x-cloak x-transition.opacity class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-[#0F172A]">Review &amp; Submit</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Confirm everything looks right before submitting.</p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-slate-300">fact_check</span>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center py-2.5 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Product</span>
                        <span class="text-sm font-semibold text-[#0F172A]" x-text="productData.name || '-'"></span>
                    </div>
                    <div class="flex justify-between items-center py-2.5 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Amount</span>
                        <span class="text-sm font-mono font-semibold text-[#0F172A]">&#8358;<span x-text="formatMoney(amountRaw)"></span></span>
                    </div>
                    <div class="flex justify-between items-center py-2.5 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Interest rate</span>
                        <span class="text-sm font-mono font-semibold" x-text="interestRate + '%'"></span>
                    </div>
                    <div class="flex justify-between items-center py-2.5 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Tenure</span>
                        <span class="text-sm font-mono font-semibold" x-text="tenureMonths + ' months'"></span>
                    </div>
                    <div class="flex justify-between items-center py-2.5 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Monthly repayment</span>
                        <span class="text-sm font-mono font-bold text-[#0F172A]">&#8358;<span x-text="formatMoney(monthlyRepayment)"></span></span>
                    </div>
                    <div class="flex justify-between items-center py-2.5 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Total interest</span>
                        <span class="text-sm font-mono font-semibold text-amber-600">&#8358;<span x-text="formatMoney(totalInterest)"></span></span>
                    </div>
                    <div class="flex justify-between items-center py-2.5 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Total payable</span>
                        <span class="text-sm font-mono font-bold text-emerald-600">&#8358;<span x-text="formatMoney(totalPayable)"></span></span>
                    </div>
                    <div class="flex justify-between items-center py-2.5">
                        <span class="text-sm text-slate-500">Guarantors</span>
                        <span class="text-sm font-medium text-[#0F172A]">
                            <template x-if="selectedGuarantors.length === 0"><span class="text-slate-400">None (not required)</span></template>
                            <template x-for="id in selectedGuarantors" :key="id">
                                <span class="inline-block bg-slate-100 text-slate-700 rounded-full px-2 py-0.5 text-[11px] font-mono ml-1"
                                      x-text="guarantorName(id)"></span>
                            </template>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Purpose (optional)</label>
                    <textarea name="purpose" rows="3" x-model="purpose"
                        class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-[#0F172A] outline-none"
                        placeholder="State the reason for this loan application"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="back()" class="px-6 py-2.5 rounded-[10px] text-sm font-medium border border-slate-300 hover:bg-slate-50 transition">
                        &larr; Back
                    </button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">send</span>
                        Submit Application
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        const loanProducts = @json($loanProducts);
        const guarantorsList = @json($guarantorList);

        function loanWizard() {
            return {
                steps: [
                    { num: 1, label: 'Amount', icon: 'tune' },
                    { num: 2, label: 'Guarantors', icon: 'group_add' },
                    { num: 3, label: 'Repayment', icon: 'receipt_long' },
                    { num: 4, label: 'Review', icon: 'fact_check' },
                ],
                step: {{ old('loan_product_id') ? 4 : 1 }},
                products: loanProducts,
                guarantors: guarantorsList,
                savingsBalance: {{ $savingsBalance }},
                guarantorLimit: {{ $guarantorLimit }},
                selectedProduct: '{{ old('loan_product_id') }}',
                productData: {},
                amountRaw: {{ max(old('amount', 0), 0) }},
                amountDisplay: '',
                amountError: '',
                maxEligible: 0,
                minAmount: 0,
                maxTenure: 12,
                interestRate: {{ old('interest_rate', 5) }},
                tenureMonths: {{ old('tenure_months', 12) }},
                monthlyRepayment: 0,
                totalInterest: 0,
                totalPayable: 0,
                amortization: [],
                balances: [],
                showAllPayments: false,
                requiresGuarantors: false,
                guarantorSearch: '',
                selectedGuarantors: @json(old('guarantor_ids', [])),
                purpose: '{{ old('purpose') }}',
                chart: null,

                init() {
                    if (this.amountRaw > 0) {
                        this.amountDisplay = formatMoney(this.amountRaw);
                    }
                    if (this.selectedProduct) {
                        this.$nextTick(() => {
                            this.onProductChange();
                            if (this.step === 4) this.$nextTick(() => this.renderChart());
                        });
                    } else {
                        this.calculateRepayment();
                    }
                },

                get filteredGuarantors() {
                    const q = (this.guarantorSearch || '').toLowerCase().trim();
                    if (!q) return this.guarantors;
                    return this.guarantors.filter(g =>
                        g.name.toLowerCase().includes(q) || (g.staff || '').toLowerCase().includes(q)
                    );
                },

                get visibleAmortization() {
                    return this.showAllPayments ? this.amortization : this.amortization.slice(0, 8);
                },

                onProductChange() {
                    const p = this.products.find(p => p.id == this.selectedProduct);
                    if (!p) {
                        this.productData = {};
                        this.requiresGuarantors = false;
                        this.maxEligible = 0;
                        this.minAmount = 0;
                        return;
                    }
                    this.productData = p;
                    this.interestRate = p.interest_rate;
                    this.maxTenure = p.max_term_months;
                    this.minAmount = p.min_amount || 0;
                    this.requiresGuarantors = !!p.requires_guarantors;
                    if (!this.tenureMonths || this.tenureMonths > p.max_term_months) {
                        this.tenureMonths = p.max_term_months;
                    }
                    const bySavings = Math.floor(this.savingsBalance * 3);
                    this.maxEligible = Math.min(p.max_amount, Math.max(bySavings, 0));
                    this.validateAmount();
                    this.calculateRepayment();
                },

                syncFromRange() {
                    this.amountDisplay = this.amountRaw ? formatMoney(this.amountRaw) : '';
                    this.validateAmount();
                    this.calculateRepayment();
                },

                onAmountInput(event) {
                    let raw = event.target.value.replace(/[^0-9.]/g, '');
                    const dots = raw.match(/\./g);
                    if (dots && dots.length > 1) raw = raw.replace(/\.+$/, '');
                    this.amountRaw = parseFloat(raw) || 0;
                    this.amountDisplay = raw;
                    this.validateAmount();
                    this.calculateRepayment();
                },

                onAmountBlur() {
                    if (this.amountRaw > 0) {
                        this.amountDisplay = formatMoney(this.amountRaw);
                    }
                },

                validateAmount() {
                    this.amountError = '';
                    const val = this.amountRaw;
                    if (val <= 0) return;
                    if (this.maxEligible > 0 && val > this.maxEligible) {
                        this.amountError = 'Maximum eligible is \u20A6' + Number(this.maxEligible).toLocaleString() + ' (based on 3\u00D7 savings)';
                        return;
                    }
                    if (this.minAmount > 0 && val < this.minAmount) {
                        this.amountError = 'Minimum amount is \u20A6' + Number(this.minAmount).toLocaleString();
                    }
                },

                calculateRepayment() {
                    const amount = this.amountRaw || 0;
                    const months = parseInt(this.tenureMonths) || 1;
                    const rate = parseFloat(this.interestRate) || 0;
                    this.monthlyRepayment = (amount * (1 + rate / 100)) / months;
                    this.totalInterest = (amount * rate) / 100;
                    this.totalPayable = amount + this.totalInterest;
                    this.buildAmortization();
                },

                buildAmortization() {
                    const months = parseInt(this.tenureMonths) || 1;
                    const amount = this.amountRaw || 0;
                    const rate = parseFloat(this.interestRate) || 0;
                    const monthly = this.monthlyRepayment;
                    const interestPct = rate / 100;
                    const rows = [];
                    const balances = [amount];
                    let balance = amount;
                    for (let i = 1; i <= months; i++) {
                        const interest = interestPct > 0 ? monthly * (interestPct / (1 + interestPct)) : 0;
                        const principal = monthly - interest;
                        balance = Math.max(0, balance - principal);
                        rows.push({
                            n: i,
                            principal: principal,
                            interest: interest,
                            total: monthly,
                            balance: balance,
                        });
                        balances.push(balance);
                    }
                    this.amortization = rows;
                    this.balances = balances;
                },

                toggleGuarantor(id) {
                    const g = this.guarantors.find(x => x.id == id);
                    if (!g) return;
                    if (this.selectedGuarantors.includes(id)) {
                        this.selectedGuarantors = this.selectedGuarantors.filter(x => x != id);
                        return;
                    }
                    if ((g.exposure + this.amountRaw) > this.guarantorLimit) return;
                    this.selectedGuarantors = [...this.selectedGuarantors, id];
                },

                guarantorDisabled(g) {
                    if (this.selectedGuarantors.includes(g.id)) return false;
                    return (g.exposure + this.amountRaw) > this.guarantorLimit;
                },

                guarantorName(id) {
                    const g = this.guarantors.find(x => x.id == id);
                    if (!g) return '';
                    return g.name.split(' ').map(w => w[0] || '').join('').slice(0, 2).toUpperCase();
                },

                canNext() {
                    if (this.step === 1) {
                        return this.selectedProduct && this.amountRaw > 0 && !this.amountError;
                    }
                    if (this.step === 2) {
                        if (this.requiresGuarantors) return this.selectedGuarantors.length > 0;
                        return true;
                    }
                    return true;
                },

                next() {
                    if (!this.canNext()) return;
                    this.step++;
                    if (this.step === 3) {
                        this.$nextTick(() => this.renderChart());
                    }
                },

                back() {
                    this.step = Math.max(1, this.step - 1);
                },

                watchTenure() {
                    this.calculateRepayment();
                    if (this.step === 3) {
                        this.$nextTick(() => this.renderChart());
                    }
                },

                renderChart() {
                    if (this.chart) { this.chart.destroy(); this.chart = null; }
                    const canvas = this.$refs.amortChart;
                    if (!canvas || typeof Chart === 'undefined') return;
                    this.chart = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: this.balances.map((_, i) => 'M' + i),
                            datasets: [{
                                label: 'Outstanding Balance',
                                data: this.balances,
                                borderColor: '#0F172A',
                                backgroundColor: 'rgba(16, 185, 129, 0.08)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 0,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        callback: (v) => '\u20A6' + Math.round(v).toLocaleString()
                                    }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                },
            };
        }

        function formatMoney(value) {
            const n = Number(value) || 0;
            const parts = n.toFixed(2).split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.join('.');
        }
    </script>
    @endpush
</x-portal-layout>
