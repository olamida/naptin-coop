@props([
    'endpoint' => null,
    'members' => [],
    'name' => 'member_id',
    'selectedId' => null,
    'placeholder' => 'Type to search members...',
    'showBalance' => false,
    'showShares' => false,
    'showAccount' => false,
    'minChars' => 1,
])

@php
    if (! $endpoint && $members instanceof \Illuminate\Support\Collection) {
        $members = $members->values()->all();
    }
    $source = $endpoint ?: $members;
@endphp

<div x-data="memberFormSearch({{ Js::from($source) }}, { minChars: {{ (int) $minChars }}, initialSelectedId: '{{ $selectedId }}' })" x-init="init()">
    <div class="relative">
        <input type="text" x-model="search" @input="filterMembers()" @click="showDropdown = true" @click.away="showDropdown = false"
               placeholder="{{ $placeholder }}"
               class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <input type="hidden" name="{{ $name }}" :value="selectedId">
        <button type="button" x-show="selectedId" @click="clearSelected()" title="Clear selection"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition" style="display: none;">
            <span class="material-symbols-outlined text-lg">close</span>
        </button>
        <div x-show="showDropdown" x-transition
             class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-[10px] shadow-lg max-h-60 overflow-y-auto">
            <div x-show="loading" class="px-3 py-2 text-sm text-slate-500">Searching...</div>
            <div x-show="!loading && search.trim().length > 0 && filteredMembers.length === 0" class="px-3 py-2 text-sm text-slate-400">No members found.</div>
            <template x-for="m in filteredMembers" :key="m.id">
                <div @click="selectMember(m)" class="px-3 py-2 cursor-pointer hover:bg-slate-100 text-sm border-b border-slate-50 last:border-0"
                     :class="selectedId == m.id ? 'bg-blue-50 font-medium' : ''">
                    <div class="flex items-center justify-between gap-3">
                        <span class="min-w-0 truncate">
                            <span x-text="m.first_name + ' ' + m.last_name"></span>
                            <span class="text-xs text-slate-400 ml-1" x-text="m.staff_id_display ? '(' + m.staff_id_display + ')' : (m.account_number ? '(' + m.account_number + ')' : '')"></span>
                        </span>
                        <span class="text-xs text-slate-500 flex-shrink-0">
                            <template x-if="Boolean('{{ $showAccount }}') && m.account_number">
                                <span x-text="m.account_number"></span>
                            </template>
                            <template x-if="Boolean('{{ $showBalance }}')">
                                <span x-text="'Bal: ₦' + Number(m.balance || 0).toLocaleString(undefined, {maximumFractionDigits: 2})"></span>
                            </template>
                            <template x-if="Boolean('{{ $showShares }}') && m.shares != null">
                                <span x-text="m.shares + ' shr'"></span>
                            </template>
                        </span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
