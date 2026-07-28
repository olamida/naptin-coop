<x-app-layout title="Withdraw">
    <div class="max-w-xl space-y-6">
        <x-breadcrumb :items="[['label' => 'Savings', 'url' => route('savings.index')], ['label' => 'Withdraw']]" />
        <div class="flex items-center gap-3">
            <a href="{{ route('savings.index') }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <h2 class="text-2xl font-bold text-gray-800">Savings Withdrawal</h2>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-amber-600 text-lg">info</span>
            </div>
            <div>
                <p class="text-sm font-medium text-amber-800">Withdrawal Approval Required</p>
                <p class="text-sm text-amber-700 mt-1">All withdrawal requests require approval by an administrator before funds are released. Your request will be reviewed and processed within 24 hours.</p>
            </div>
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

        <form method="POST" action="{{ route('savings.withdraw.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5" x-data="evidenceUpload()">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Member *</label>
                <div x-data="memberSearch(@json($members->map(fn($m) => ['id' => $m->id, 'first_name' => $m->first_name, 'last_name' => $m->last_name, 'staff_id' => $m->staff_id, 'balance' => $m->savingsAccount?->balance ?? 0])))" x-init="init()">
                    <div class="relative">
                        <input type="text" x-model="search" @input="filterMembers()" @click="showDropdown = true" @click.away="showDropdown = false"
                            placeholder="Type to search members..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <input type="hidden" name="member_id" :value="selectedId">
                        <div x-show="showDropdown && filteredMembers.length > 0" x-transition
                            class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="m in filteredMembers" :key="m.id">
                                <div @click="selectMember(m)" class="px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm border-b border-gray-50 last:border-0"
                                    :class="selectedId == m.id ? 'bg-blue-50 font-medium' : ''">
                                    <div class="flex items-center justify-between">
                                        <span>
                                            <span x-text="m.first_name + ' ' + m.last_name"></span>
                                            <span class="text-xs text-gray-400 ml-1" x-text="'(' + m.staff_id + ')'"></span>
                                        </span>
                                        <span class="text-xs text-gray-500" x-text="'Balance: ₦' + Number(m.balance).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₦) *</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required min="0.01"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason / Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Reason for withdrawal (helps with approval)">{{ old('notes') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Evidence (optional)</label>
                <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center hover:border-blue-300 transition">
                    <template x-if="!preview">
                        <label class="cursor-pointer">
                            <span class="material-symbols-outlined text-3xl text-gray-300">upload_file</span>
                            <p class="text-xs text-gray-500 mt-1">Click to upload supporting document (optional)</p>
                            <input type="file" name="payment_evidence" accept="image/*" class="hidden" x-on:change="handleFile($event)">
                        </label>
                    </template>
                    <template x-if="preview">
                        <div class="relative inline-block">
                            <img :src="preview" class="h-24 rounded-lg object-contain border border-gray-200">
                            <button type="button" @click="clearFile()" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs">
                                <span class="material-symbols-outlined text-[12px]">close</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                    Submit Withdrawal Request
                </button>
                <a href="{{ route('savings.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
