<x-app-layout title="New Loan Application">
    <div class="max-w-2xl mx-auto space-y-6" x-data="loanWizard()">
        <x-breadcrumb :items="[['label' => 'Loans', 'url' => route('loans.index')], ['label' => 'New Loan']]" />
        <div class="flex items-center gap-3">
            <a href="{{ route('loans.index') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <h2 class="text-2xl font-bold text-[#0F172A]">New Loan Application</h2>
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

        {{-- Stepper --}}
        <div class="flex items-center justify-between bg-white border border-slate-200 rounded-[16px] p-4 shadow-sm">
            <template x-for="(step, i) in steps" :key="i">
                <div class="flex items-center flex-1">
                    <button @click="goToStep(i)" class="flex items-center gap-2 text-xs font-medium transition"
                        :class="stepIndex === i ? 'text-[#0F172A]' : stepIndex > i ? 'text-emerald-600' : 'text-slate-400'">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold border-2 transition"
                            :class="stepIndex === i ? 'bg-[#0F172A] text-white border-[#0F172A]' : stepIndex > i ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white border-slate-300'"
                            x-text="i + 1"></span>
                        <span class="hidden sm:inline" x-text="step"></span>
                    </button>
                    <div class="flex-1 h-px mx-3 bg-slate-200 last:hidden"></div>
                </div>
            </template>
        </div>

        <form method="POST" action="{{ route('loans.store') }}">
            @csrf

            {{-- Step 1: Eligibility & Amount --}}
            <div x-show="stepIndex === 0" x-cloak class="bg-white rounded-[16px] border border-slate-200 p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-semibold text-[#0F172A] flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">verified</span>
                    Eligibility &amp; Amount
                </h3>

                @php
                    $membersJson = $members->map(fn($m) => [
                        'id' => $m->id, 'first_name' => $m->first_name, 'last_name' => $m->last_name,
                        'staff_id' => $m->staff_id, 'staff_id_display' => $m->staff_id_display,
                        'savings_balance' => (float) ($m->savingsAccount->balance ?? 0),
                        'active_loans' => $m->loans()->whereIn('status', ['disbursed', 'repaying'])->count(),
                        'active_outstanding' => (float) $m->loans()->whereIn('status', ['disbursed', 'repaying'])->sum('outstanding'),
                    ])->values();
                @endphp
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Member *</label>
                    <div x-data="memberSearch({{ $membersJson->toJson() }})" x-init="init()">
                        <div class="relative">
                            <input type="text" x-model="search" @input="filterMembers(); parent.selectedMemberData = null" @click="showDropdown = true" @click.away="showDropdown = false"
                                placeholder="Type to search members..." class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-[#0F172A] outline-none">
                            <input type="hidden" name="member_id" :value="selectedId">
                            <div x-show="showDropdown && filteredMembers.length > 0" x-transition
                                class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-[10px] shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="m in filteredMembers" :key="m.id">
                                    <div @click="selectMember(m); parent.selectEligibleMember(m)" class="px-3 py-2.5 cursor-pointer hover:bg-slate-50 text-sm border-b border-slate-50 last:border-0 flex items-center justify-between"
                                        :class="selectedId == m.id ? 'bg-slate-50 font-medium' : ''">
                                        <div>
                                            <span x-text="m.first_name + ' ' + m.last_name" class="font-medium text-slate-800"></span>
                                            <span class="text-xs text-slate-400 ml-1" x-text="'(' + m.staff_id_display + ')'"></span>
                                        </div>
                                        <span class="text-[10px] font-mono text-emerald-600" x-text="'₦' + Number(m.savings_balance).toLocaleString()"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Eligibility Info --}}
                <div x-show="selectedMemberData" x-cloak class="bg-emerald-50 border border-emerald-200 rounded-[10px] p-4">
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <p class="font-medium text-emerald-800" x-text="selectedMemberData?.first_name + ' ' + selectedMemberData?.last_name"></p>
                            <p class="text-xs text-emerald-600" x-text="'Savings: ₦' + Number(selectedMemberData?.savings_balance || 0).toLocaleString() + ' · Active loans: ' + (selectedMemberData?.active_loans || 0) + ' · Outstanding: ₦' + Number(selectedMemberData?.active_outstanding || 0).toLocaleString()"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-emerald-600">Max Eligible</p>
                            <p class="font-mono font-bold text-emerald-800" x-text="'₦' + Number(maxEligible).toLocaleString()"></p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Loan Product</label>
                        <select name="loan_product_id" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-[#0F172A] outline-none"
                            x-model="selectedProduct" @change="onProductChange()">
                            <option value="">-- Select a Loan Product --</option>
                            @foreach ($loanProducts as $product)
                                <option value="{{ $product->id }}" data-product='@json($product)'
                                    {{ old('loan_product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->interest_rate }}% - {{ $product->max_term_months }}mo)
                                </option>
                            @endforeach
                        </select>
                        <div x-show="selectedProduct" x-cloak class="mt-2 bg-slate-50 border border-slate-200 rounded-[10px] p-3 text-xs space-y-1">
                            <p class="font-medium text-[#0F172A]" x-text="'Product: ' + (productData.name || '')"></p>
                            <p class="text-slate-600" x-show="productData.min_amount">Range: ₦<span x-text="Number(productData.min_amount || 0).toLocaleString()"></span> — ₦<span x-text="Number(productData.max_amount || 0).toLocaleString()"></span></p>
                            <p class="text-slate-600" x-show="productData.max_term_months">Max tenure: <span x-text="productData.max_term_months || ''"></span> months</p>
                            <p class="text-slate-600" x-show="productData.requires_guarantors"><span class="text-amber-600 font-medium">Requires guarantors</span></p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Loan Type *</label>
                        <select name="type" required class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-[#0F172A] outline-none">
                            <option value="regular" {{ old('type') === 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="emergency" {{ old('type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                            <option value="educational" {{ old('type') === 'educational' ? 'selected' : '' }}>Educational</option>
                            <option value="special" {{ old('type') === 'special' ? 'selected' : '' }}>Special</option>
                        </select>
                    </div>
                </div>

                {{-- Amount Slider --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Loan Amount (₦) *</label>
                    <input type="range" min="1000" max="5000000" step="1000"
                        x-model.number="amountRaw" @input="onAmountChange()"
                        class="w-full h-2 bg-slate-200 rounded-full appearance-none cursor-pointer accent-[#0F172A]">
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-2">
                            <input type="text" :value="amountFormatted" @input="onAmountTyped($event)"
                                class="w-36 px-3 py-2 border border-slate-300 rounded-[10px] text-sm font-mono focus:ring-2 focus:ring-[#0F172A] outline-none text-right">
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400" x-show="maxAmount > 0">Max: ₦<span x-text="Number(maxAmount).toLocaleString()"></span></p>
                            <p x-show="amountError" x-text="amountError" class="text-[11px] text-red-600 font-medium"></p>
                        </div>
                    </div>
                    <input type="hidden" name="amount" :value="amountRaw">
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" @click="nextStep()" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">Continue</button>
                </div>
            </div>

            {{-- Step 2: Interest & Tenure --}}
            <div x-show="stepIndex === 1" x-cloak class="bg-white rounded-[16px] border border-slate-200 p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-semibold text-[#0F172A] flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">tune</span>
                    Interest &amp; Tenure
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Interest Rate (%) *</label>
                        <div class="relative">
                            <input type="range" min="0" max="30" step="0.5"
                                x-model.number="interestRate" @input="calculateRepayment(); updateSchedule()"
                                class="w-full h-2 bg-slate-200 rounded-full appearance-none cursor-pointer accent-[#0F172A]">
                            <div class="flex items-center justify-between mt-1">
                                <input type="number" step="0.01" name="interest_rate" required min="0" max="100"
                                    class="w-24 px-3 py-2 border border-slate-300 rounded-[10px] text-sm font-mono focus:ring-2 focus:ring-[#0F172A] outline-none"
                                    x-model.number="interestRate" @input="calculateRepayment(); updateSchedule()">
                                <span class="text-sm text-slate-500" x-text="interestRate + '%'"></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tenure (Months) *</label>
                        <div class="relative">
                            <input type="range" min="1" max="60" step="1"
                                x-model.number="tenureMonths" @input="calculateRepayment(); updateSchedule()"
                                class="w-full h-2 bg-slate-200 rounded-full appearance-none cursor-pointer accent-[#0F172A]">
                            <div class="flex items-center justify-between mt-1">
                                <input type="number" name="tenure_months" required min="1" max="120"
                                    class="w-24 px-3 py-2 border border-slate-300 rounded-[10px] text-sm font-mono focus:ring-2 focus:ring-[#0F172A] outline-none"
                                    x-model.number="tenureMonths" @input="calculateRepayment(); updateSchedule()">
                                <span class="text-sm text-slate-500" x-text="tenureMonths + ' months'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Repayment Summary --}}
                <div class="bg-slate-50 rounded-[10px] p-4 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-xs text-slate-500">Total with Interest</p>
                        <p class="text-lg font-mono font-bold text-[#0F172A]" x-text="'₦' + Number(totalWithInterest).toLocaleString(undefined, {minimumFractionDigits: 2})"></p>
                    </div>
                    <div class="border-x border-slate-200">
                        <p class="text-xs text-slate-500">Monthly Repayment</p>
                        <p class="text-lg font-mono font-bold text-emerald-600" x-text="'₦' + monthlyRepayment.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Total Interest</p>
                        <p class="text-lg font-mono font-bold text-amber-600" x-text="'₦' + Number(totalInterest).toLocaleString(undefined, {minimumFractionDigits: 2})"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Purpose</label>
                    <textarea name="purpose" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-[#0F172A] outline-none" placeholder="State the reason for this loan application">{{ old('purpose') }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="prevStep()" class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2">Back</button>
                    <button type="button" @click="nextStep()" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">Continue</button>
                </div>
            </div>

            {{-- Step 3: Guarantors --}}
            <div x-show="stepIndex === 2" x-cloak class="bg-white rounded-[16px] border border-slate-200 p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-semibold text-[#0F172A] flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">group_add</span>
                    Guarantors
                </h3>

                <div x-show="!requiresGuarantors" class="bg-slate-50 rounded-[10px] p-4 text-center">
                    <span class="material-symbols-outlined text-3xl text-slate-300">group_off</span>
                    <p class="text-sm text-slate-500 mt-1">This loan product does not require guarantors.</p>
                </div>

                <div x-show="requiresGuarantors" x-cloak>
                    <div class="bg-amber-50 border border-amber-200 rounded-[10px] p-4 mb-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="material-symbols-outlined text-amber-600">info</span>
                            <p class="text-sm font-medium text-amber-800">Guarantors Required</p>
                        </div>
                        <p class="text-xs text-amber-700">Select at least one guarantor. Guarantors will be notified and must accept before the loan is approved.</p>
                    </div>

                    <div x-data="guarantorSearch({{ $membersJson->toJson() }})" x-init="init()">
                        <div class="relative">
                            <input type="text" x-model="search" @input="filterMembers()" @click="showDropdown = true" @click.away="showDropdown = false"
                                placeholder="Search members to add as guarantor..."
                                class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-[#0F172A] outline-none">
                            <div x-show="showDropdown && filteredMembers.length > 0" x-transition
                                class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-[10px] shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="m in filteredMembers" :key="m.id">
                                    <div @click="addGuarantor(m)" class="px-3 py-2.5 cursor-pointer hover:bg-slate-50 text-sm border-b border-slate-50 last:border-0 flex items-center justify-between"
                                        x-show="!isSelected(m.id)">
                                        <div>
                                            <span x-text="m.first_name + ' ' + m.last_name" class="font-medium text-slate-800"></span>
                                            <span class="text-xs text-slate-400 ml-1" x-text="'(' + m.staff_id_display + ')'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="mt-3 space-y-2" x-show="selectedGuarantors.length > 0">
                            <p class="text-xs font-medium text-slate-500">Selected Guarantors (<span x-text="selectedGuarantors.length"></span>)</p>
                            <template x-for="(g, index) in selectedGuarantors" :key="g.id">
                                <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-[10px] px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#0F172A] text-white flex items-center justify-center text-xs font-bold" x-text="(g.first_name?.charAt(0) || '') + (g.last_name?.charAt(0) || '')"></div>
                                        <div>
                                            <p class="text-sm font-medium text-slate-800" x-text="g.first_name + ' ' + g.last_name"></p>
                                            <p class="text-xs text-slate-400" x-text="g.staff_id_display"></p>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeGuarantor(index)" class="text-slate-400 hover:text-rose-600 p-1 rounded-full hover:bg-rose-50 transition">
                                        <span class="material-symbols-outlined text-lg">close</span>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <p class="text-xs text-slate-400 mt-3" x-show="selectedGuarantors.length === 0">No guarantors selected yet. Search and select members above.</p>
                        <template x-for="g in selectedGuarantors" :key="g.id">
                            <input type="hidden" name="guarantor_ids[]" :value="g.id">
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="prevStep()" class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2">Back</button>
                    <button type="button" @click="nextStep()" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">Review</button>
                </div>
            </div>

            {{-- Step 4: Review & Submit --}}
            <div x-show="stepIndex === 3" x-cloak class="bg-white rounded-[16px] border border-slate-200 p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-semibold text-[#0F172A] flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">summarize</span>
                    Review &amp; Submit
                </h3>

                <div class="bg-slate-50 rounded-[10px] p-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Member</span><span class="font-medium text-slate-800" x-text="selectedMemberData?.first_name + ' ' + selectedMemberData?.last_name || 'Not selected'"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Loan Product</span><span class="font-medium text-slate-800" x-text="productData.name || 'None'"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Amount</span><span class="font-mono font-semibold text-[#0F172A]" x-text="'₦' + Number(amountRaw).toLocaleString()"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Interest Rate</span><span class="font-mono" x-text="interestRate + '%'"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Tenure</span><span class="font-mono" x-text="tenureMonths + ' months'"></span></div>
                    <div class="flex justify-between border-t border-slate-200 pt-3"><span class="text-slate-500">Monthly Repayment</span><span class="font-mono font-bold text-emerald-600" x-text="'₦' + monthlyRepayment.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Total Interest</span><span class="font-mono text-amber-600" x-text="'₦' + Number(totalInterest).toLocaleString(undefined, {minimumFractionDigits: 2})"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Total Repayment</span><span class="font-mono font-bold text-[#0F172A]" x-text="'₦' + Number(totalWithInterest).toLocaleString(undefined, {minimumFractionDigits: 2})"></span></div>
                    <div x-show="requiresGuarantors" class="flex justify-between"><span class="text-slate-500">Guarantors</span><span class="font-medium" x-text="selectedGuarantors.length + ' selected'"></span></div>
                </div>

                {{-- Amortization Preview --}}
                <div>
                    <details class="group">
                        <summary class="text-xs font-medium text-blue-600 hover:text-blue-800 cursor-pointer">View Amortization Schedule</summary>
                        <div class="mt-3 overflow-x-auto max-h-64 overflow-y-auto border border-slate-200 rounded-[10px]">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-50 sticky top-0">
                                    <tr class="border-b border-slate-200">
                                        <th class="text-left px-3 py-2 text-[10px] font-semibold text-slate-500 uppercase">#</th>
                                        <th class="text-left px-3 py-2 text-[10px] font-semibold text-slate-500 uppercase">Due Date</th>
                                        <th class="text-right px-3 py-2 text-[10px] font-semibold text-slate-500 uppercase">Principal</th>
                                        <th class="text-right px-3 py-2 text-[10px] font-semibold text-slate-500 uppercase">Interest</th>
                                        <th class="text-right px-3 py-2 text-[10px] font-semibold text-slate-500 uppercase">Total</th>
                                        <th class="text-right px-3 py-2 text-[10px] font-semibold text-slate-500 uppercase">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <template x-for="(row, i) in schedule" :key="i">
                                        <tr :class="i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50'">
                                            <td class="px-3 py-2 text-slate-500 font-mono" x-text="row.installment"></td>
                                            <td class="px-3 py-2 text-slate-600" x-text="row.dueDate"></td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-700" x-text="'₦' + Number(row.principal).toLocaleString()"></td>
                                            <td class="px-3 py-2 text-right font-mono text-amber-600" x-text="'₦' + Number(row.interest).toLocaleString()"></td>
                                            <td class="px-3 py-2 text-right font-mono font-medium text-slate-800" x-text="'₦' + Number(row.total).toLocaleString()"></td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-400" x-text="'₦' + Number(row.balance).toLocaleString()"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="prevStep()" class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2">Back</button>
                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-8 py-2.5 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">check</span>
                        Submit Application
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        const loanProducts = @json($loanProducts);

        function loanWizard() {
            return {
                stepIndex: 0,
                steps: ['Eligibility & Amount', 'Interest & Tenure', 'Guarantors', 'Review & Submit'],

                selectedMemberData: null,
                selectedProduct: '{{ old('loan_product_id') }}',
                amountRaw: {{ max(old('amount', 0), 0) }},
                amountError: '',
                interestRate: {{ old('interest_rate', 5) }},
                tenureMonths: {{ old('tenure_months', 12) }},
                monthlyRepayment: 0,
                totalWithInterest: 0,
                totalInterest: 0,
                requiresGuarantors: false,
                maxAmount: 0,
                minAmount: 0,
                productData: {},
                maxEligible: 0,
                selectedGuarantors: [],
                schedule: [],

                get amountFormatted() {
                    return this.amountRaw ? '₦' + Number(this.amountRaw).toLocaleString() : '₦0';
                },

                goToStep(i) { this.stepIndex = i; },

                nextStep() { if (this.stepIndex < 3) this.stepIndex++; },

                prevStep() { if (this.stepIndex > 0) this.stepIndex--; },

                selectEligibleMember(m) {
                    this.selectedMemberData = m;
                    this.maxEligible = Math.min(m.savings_balance * 3, 5000000);
                    if (this.maxAmount > 0) {
                        this.maxEligible = Math.min(this.maxEligible, this.maxAmount);
                    }
                    if (this.amountRaw > this.maxEligible) {
                        this.amountRaw = this.maxEligible;
                    }
                    this.onAmountChange();
                },

                onAmountChange() {
                    this.amountError = '';
                    if (this.maxAmount > 0 && this.amountRaw > this.maxAmount) {
                        this.amountRaw = this.maxAmount;
                    }
                    if (this.maxEligible > 0 && this.amountRaw > this.maxEligible) {
                        this.amountRaw = this.maxEligible;
                    }
                    this.calculateRepayment();
                },

                onAmountTyped(event) {
                    let val = event.target.value.replace(/[^0-9.]/g, '');
                    this.amountRaw = parseFloat(val) || 0;
                    this.onAmountChange();
                },

                onProductChange() {
                    const product = loanProducts.find(p => p.id == this.selectedProduct);
                    if (product) {
                        this.productData = product;
                        this.interestRate = product.interest_rate;
                        this.tenureMonths = product.max_term_months;
                        this.maxAmount = product.max_amount;
                        this.minAmount = product.min_amount || 0;
                        this.requiresGuarantors = product.requires_guarantors;
                        if (this.selectedMemberData) {
                            this.maxEligible = Math.min(
                                this.selectedMemberData.savings_balance * 3,
                                this.maxAmount,
                                5000000
                            );
                        }
                        this.onAmountChange();
                    } else {
                        this.productData = {};
                        this.requiresGuarantors = false;
                        this.maxAmount = 0;
                        this.minAmount = 0;
                    }
                },

                calculateRepayment() {
                    const amount = this.amountRaw || 0;
                    const months = parseInt(this.tenureMonths) || 1;
                    const rate = parseFloat(this.interestRate) || 0;
                    this.totalWithInterest = amount * (1 + rate / 100);
                    this.monthlyRepayment = this.totalWithInterest / months;
                    this.totalInterest = this.totalWithInterest - amount;
                },

                updateSchedule() {
                    const amount = this.amountRaw || 0;
                    const months = parseInt(this.tenureMonths) || 1;
                    const rate = parseFloat(this.interestRate) || 0;
                    const monthlyPrincipal = amount / months;
                    const monthlyInterest = (amount * (rate / 100)) / months;
                    const monthlyTotal = monthlyPrincipal + monthlyInterest;
                    let balance = amount;
                    const today = new Date();
                    this.schedule = [];
                    for (let i = 1; i <= months; i++) {
                        const due = new Date(today.getFullYear(), today.getMonth() + i, 1);
                        balance -= monthlyPrincipal;
                        this.schedule.push({
                            installment: i,
                            dueDate: due.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' }),
                            principal: Math.round(monthlyPrincipal),
                            interest: Math.round(monthlyInterest),
                            total: Math.round(monthlyTotal),
                            balance: Math.max(0, Math.round(balance))
                        });
                    }
                },

                init() {
                    if (this.amountRaw > 0) this.calculateRepayment();
                    if (this.selectedProduct) this.$nextTick(() => this.onProductChange());
                    this.updateSchedule();
                }
            };
        }

        function memberSearch(data) {
            return {
                search: '',
                showDropdown: false,
                members: data || [],
                filteredMembers: [],
                selectedId: '{{ old('member_id') }}',
                parent: null,
                init() {
                    this.parent = this.$el.closest('[x-data]').__x.$data;
                    this.filteredMembers = this.members;
                    if (this.selectedId) {
                        const m = this.members.find(x => x.id == this.selectedId);
                        if (m) {
                            this.search = m.first_name + ' ' + m.last_name;
                            this.parent.selectEligibleMember(m);
                        }
                    }
                },
                filterMembers() {
                    const q = this.search.toLowerCase();
                    this.filteredMembers = q ? this.members.filter(m =>
                        (m.first_name + ' ' + m.last_name).toLowerCase().includes(q) ||
                        (m.staff_id || '').toLowerCase().includes(q)
                    ) : this.members;
                },
                selectMember(m) {
                    this.selectedId = m.id;
                    this.search = m.first_name + ' ' + m.last_name;
                    this.showDropdown = false;
                }
            };
        }

        function guarantorSearch(data) {
            return {
                search: '',
                showDropdown: false,
                members: data || [],
                filteredMembers: [],
                selectedGuarantors: [],
                init() { this.filteredMembers = this.members; },
                filterMembers() {
                    const q = this.search.toLowerCase();
                    this.filteredMembers = q ? this.members.filter(m =>
                        (m.first_name + ' ' + m.last_name).toLowerCase().includes(q) ||
                        (m.staff_id || '').toLowerCase().includes(q)
                    ) : this.members;
                },
                isSelected(id) { return this.selectedGuarantors.some(g => g.id == id); },
                addGuarantor(m) {
                    if (!this.isSelected(m.id)) this.selectedGuarantors.push({...m});
                    this.search = '';
                    this.filteredMembers = this.members;
                    this.showDropdown = false;
                },
                removeGuarantor(index) { this.selectedGuarantors.splice(index, 1); }
            };
        }
    </script>
    @endpush
</x-app-layout>