<x-app-layout title="Purchase Shares">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('shares.index') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Purchase Shares</h2>
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

        <form method="POST" action="{{ route('shares.purchase.store') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Member *</label>
                <div x-data="memberSearch(@json($memberOptions))" x-init="init()">
                    <div class="relative">
                        <input type="text" x-model="search" @input="filterMembers()" @click="showDropdown = true" @click.away="showDropdown = false"
                            placeholder="Type to search members..." class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <input type="hidden" name="member_id" :value="selectedId">
                        <div x-show="showDropdown && filteredMembers.length > 0" x-transition
                            class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-[10px] shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="m in filteredMembers" :key="m.id">
                                <div @click="selectMember(m)" class="px-3 py-2 cursor-pointer hover:bg-slate-100 text-sm border-b border-slate-50 last:border-0"
                                    :class="selectedId == m.id ? 'bg-slate-50 font-medium' : ''">
                                    <div class="flex items-center justify-between">
                                        <span>
                                            <span x-text="m.first_name + ' ' + m.last_name"></span>
                                            <span class="text-xs text-slate-400 ml-1" x-text="'(' + m.staff_id_display + ')'"></span>
                                        </span>
                                        <span class="text-xs text-slate-500" x-text="'Shares: ' + (m.shares || 0)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Number of Shares *</label>
                <input type="number" name="shares" value="{{ old('shares', '1') }}" required min="1"
                    class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <p class="text-xs text-slate-500 mt-1">Share price: ₦100.00 per share</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">
                    Purchase Shares
                </button>
                <a href="{{ route('shares.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
