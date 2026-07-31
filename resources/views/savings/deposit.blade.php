<x-app-layout title="Deposit">
    <div class="max-w-xl space-y-6">
        <x-breadcrumb :items="[['label' => 'Savings', 'url' => route('savings.index')], ['label' => 'Deposit']]" />
        <div class="flex items-center gap-3">
            <a href="{{ route('savings.index') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Savings Deposit</h2>
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

        <form method="POST" action="{{ route('savings.deposit.store') }}" enctype="multipart/form-data" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 space-y-5" x-data="moneyForm()">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Member *</label>
                <div x-data="memberSearch('{{ route('members.search.form') }}')" x-init="init()">
                    <div class="relative">
                        <input type="text" x-model="search" @input="filterMembers()" @click="showDropdown = true" @click.away="showDropdown = false"
                            placeholder="Type to search members..." class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <input type="hidden" name="member_id" :value="selectedId">
                        <div x-show="showDropdown" x-transition
                            class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-[10px] shadow-lg max-h-60 overflow-y-auto">
                            <div x-show="loading" class="px-3 py-2 text-sm text-slate-500">Searching...</div>
                            <div x-show="!loading && search.trim().length > 0 && filteredMembers.length === 0" class="px-3 py-2 text-sm text-slate-400">No members found.</div>
                            <template x-for="m in filteredMembers" :key="m.id">
                                <div @click="selectMember(m)" class="px-3 py-2 cursor-pointer hover:bg-slate-100 text-sm border-b border-slate-50 last:border-0"
                                    :class="selectedId == m.id ? 'bg-blue-50 font-medium' : ''">
                                    <span x-text="m.first_name + ' ' + m.last_name"></span>
                                    <span class="text-xs text-slate-400 ml-1" x-text="'(' + (m.account_number || 'No Account') + ')'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Amount (₦) *</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none z-10">₦</span>
                    <input type="text" name="amount_display" inputmode="decimal" autocomplete="off" required
                        class="w-full pl-10 pr-10 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        x-model="amountDisplay" @input="onAmountInput($event)" @blur="onAmountBlur">
                    <span x-show="amountDisplay && !amountError" class="absolute right-3 top-1/2 -translate-y-1/2 text-green-500">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                    </span>
                </div>
                <p x-show="amountError" x-text="amountError" class="text-[11px] text-red-600 font-medium mt-1"></p>
                <input type="hidden" name="amount" :value="amountRaw">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Payment Evidence (optional)</label>
                <div class="border-2 border-dashed border-slate-300 rounded-[10px] p-4 text-center hover:border-blue-300 transition">
                    <template x-if="!preview">
                        <label class="cursor-pointer">
                            <span class="material-symbols-outlined text-3xl text-slate-300">upload_file</span>
                            <p class="text-xs text-slate-500 mt-1">Click to upload receipt or payment proof (image)</p>
                            <input type="file" name="payment_evidence" accept="image/*" class="hidden" x-on:change="handleFile($event)">
                        </label>
                    </template>
                    <template x-if="preview">
                        <div class="relative inline-block">
                            <img :src="preview" class="h-24 rounded-[10px] object-contain border border-slate-200">
                            <button type="button" @click="clearFile()" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs">
                                <span class="material-symbols-outlined text-[12px]">close</span>
                            </button>
                        </div>
                    </template>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Optional. Upload payment receipt or transfer confirmation.</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">
                    Record Deposit
                </button>
                <a href="{{ route('savings.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function moneyForm() {
            return {
                amountRaw: 0,
                amountDisplay: '',
                amountError: '',
                preview: null,
                handleFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.size > 5 * 1024 * 1024) {
                            alert('File is too large. Maximum size is 5MB.');
                            event.target.value = '';
                            return;
                        }
                        this.preview = URL.createObjectURL(file);
                    }
                },
                clearFile() {
                    this.preview = null;
                    const input = document.querySelector('input[name="payment_evidence"]');
                    if (input) input.value = '';
                },
                onAmountInput(event) {
                    let raw = event.target.value.replace(/[^0-9.]/g, '');
                    const dots = raw.match(/\./g);
                    if (dots && dots.length > 1) raw = raw.replace(/\.+$/, '');
                    this.amountRaw = parseFloat(raw) || 0;
                    this.amountDisplay = raw ? formatMoney(this.amountRaw) : '';
                },
                onAmountBlur() {
                    if (this.amountDisplay) {
                        this.amountDisplay = formatMoney(this.amountRaw);
                    }
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
