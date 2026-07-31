@props(['searchUrl' => null, 'newMemberUrl' => ''])

<div x-data="commandPalette({ searchUrl: '{{ $searchUrl ?? route('command.search') }}', newMemberUrl: '{{ $newMemberUrl }}' })"
     @keydown.meta.k.window.prevent="open()"
     @keydown.ctrl-k.window.prevent="open()"
     @keydown.window="handleGlobalKey($event)"
     @open-command-palette.window="open()">

    {{-- Command Palette Modal --}}
    <div x-show="open" x-cloak x-trap.noscroll="open" @keydown="handleKey($event)"
         class="fixed inset-0 z-[150] flex items-start justify-center pt-[12vh]"
         role="dialog" aria-modal="true" aria-label="Search">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="close()"></div>

        <div class="relative w-full max-w-xl bg-white rounded-[16px] shadow-2xl border border-slate-200 overflow-hidden">
            <div class="flex items-center gap-3 px-4 border-b border-slate-100">
                <span class="material-symbols-outlined text-slate-400 flex-shrink-0">search</span>
                <input x-ref="input" type="text" x-model="query" @input.debounce.200ms="search()"
                       placeholder="Search members, loans, references, orders... or press Enter for actions"
                       class="flex-1 py-4 text-sm outline-none border-0 bg-transparent placeholder:text-slate-400">
                <button @click="close()" class="flex-shrink-0 text-slate-400 hover:text-slate-600 transition" aria-label="Close search">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="max-h-80 overflow-y-auto">
                <template x-if="loading">
                    <div class="flex items-center justify-center py-8">
                        <svg class="animate-spin h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                    </div>
                </template>

                <template x-if="!loading && groups.length === 0 && query.length > 0">
                    <div class="text-center py-10 text-sm text-slate-400">
                        <span class="material-symbols-outlined text-3xl text-slate-300 block">search_off</span>
                        <p class="mt-2">No results for "<span x-text="query"></span>"</p>
                    </div>
                </template>

                <template x-for="group in groups" :key="group.key">
                    <div class="py-1">
                        <div class="px-4 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm" x-text="group.icon || 'search'"></span>
                            <span x-text="group.label"></span>
                            <span class="text-slate-300" x-text="'(' + group.items.length + ')'"></span>
                        </div>
                        <template x-for="item in group.items" :key="item.url">
                            <button @click="goto(item)" @mouseenter="selected = flat.findIndex(f => f.item === item)"
                                    :class="isSelected(item) ? 'bg-slate-50 border-l-2 border-[#0F172A]' : 'border-l-2 border-transparent'"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-left transition">
                                <span class="material-symbols-outlined text-lg text-slate-400 flex-shrink-0" x-text="item.icon || 'north_east'"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate" x-text="item.name"></p>
                                    <p class="text-[11px] text-slate-400 truncate" x-text="item.sub"></p>
                                </div>
                                <span class="text-[10px] text-slate-300 opacity-0" :class="isSelected(item) ? 'opacity-100' : ''">&rarr;</span>
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            <div class="px-4 py-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
                <span class="flex items-center gap-4">
                    <span class="flex items-center gap-1"><kbd class="bg-slate-100 px-1.5 py-0.5 rounded font-mono">↑↓</kbd> Navigate</span>
                    <span class="flex items-center gap-1"><kbd class="bg-slate-100 px-1.5 py-0.5 rounded font-mono">↵</kbd> Open</span>
                    <span class="flex items-center gap-1"><kbd class="bg-slate-100 px-1.5 py-0.5 rounded font-mono">Esc</kbd> Close</span>
                    <span class="flex items-center gap-1"><kbd class="bg-slate-100 px-1.5 py-0.5 rounded font-mono">/</kbd> Search</span>
                </span>
                <span x-text="flat.length + ' result' + (flat.length === 1 ? '' : 's')"></span>
            </div>
        </div>
    </div>
</div>
