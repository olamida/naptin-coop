@props(['memberOptions' => [], 'name' => 'member_id', 'selectedId' => '', 'showBalance' => false, 'placeholder' => 'Type to search members...'])

<div x-data="memberSearch(@json($memberOptions))" x-init="init()" class="relative">
    <input type="text" x-model="search" @input="filterMembers()" @click="showDropdown = true" @click.away="showDropdown = false"
        placeholder="{{ $placeholder }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    <input type="hidden" name="{{ $name }}" :value="selectedId">
    <div x-show="showDropdown && filteredMembers.length > 0" x-transition
        class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
        <template x-for="m in filteredMembers" :key="m.id">
            <div @click="selectMember(m)" class="px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm border-b border-gray-50 last:border-0"
                :class="selectedId == m.id ? 'bg-blue-50 font-medium' : ''">
                <div class="flex items-center justify-between">
                    <span>
                        <span x-text="m.first_name + ' ' + m.last_name"></span>
                        <span class="text-xs text-gray-400 ml-1" x-text="'(' + m.staff_id_display + ')'"></span>
                    </span>
                    @if ($showBalance)
                        <span class="text-xs text-gray-500" x-text="'₦' + Number(m.balance).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                    @endif
                </div>
            </div>
        </template>
    </div>
</div>
