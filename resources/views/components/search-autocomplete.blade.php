@props([
    'endpoint' => '',
    'name' => 'search',
    'value' => request('search'),
    'placeholder' => 'Search...',
    'minChars' => 2,
])

<div
    x-data="searchAutocomplete({
        endpoint: @js($endpoint),
        value: @js((string) $value),
        minChars: {{ (int) $minChars }},
    })"
    @keydown.escape="open = false"
    @click.outside="open = false"
    class="relative"
>
    <div class="relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
        <input
            type="text"
            :name="'{{ $name }}'"
            x-model="query"
            @input.debounce.300ms="fetchResults()"
            @keydown.down.prevent="move(1)"
            @keydown.up.prevent="move(-1)"
            @keydown.enter.prevent="onEnter()"
            autocomplete="off"
            placeholder="{{ $placeholder }}"
            class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        >
        <span x-show="loading" x-cloak class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 border-2 border-slate-300 border-t-blue-500 rounded-full animate-spin"></span>
    </div>

    <div x-show="open" x-cloak x-transition
         class="absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-[10px] shadow-lg max-h-60 overflow-y-auto">
        <template x-for="(r, i) in results" :key="r.id ?? i">
            <div @click="select(r)" @mouseenter="selected = i"
                 class="px-3 py-2.5 cursor-pointer hover:bg-slate-100 text-sm border-b border-slate-50 last:border-0 transition"
                 :class="selected === i ? 'bg-slate-100' : ''">
                <div class="flex items-center justify-between gap-3">
                    <span class="min-w-0 truncate font-medium text-[#0F172A]" x-text="r.label"></span>
                    <span class="text-xs text-slate-500 flex-shrink-0" x-text="r.sublabel || ''"></span>
                </div>
            </div>
        </template>
        <div x-show="!loading && query.trim().length >= 2 && results.length === 0"
             class="px-3 py-2.5 text-sm text-slate-400">No matches found.</div>
    </div>
</div>
