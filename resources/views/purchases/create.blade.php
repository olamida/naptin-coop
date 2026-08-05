<x-app-layout title="New Purchase Order">
    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Purchases', 'url' => route('purchases.index')],
            ['label' => 'New Order'],
        ]" />

        <div>
            <h2 class="text-2xl font-bold text-[#0F172A]">New Purchase Order</h2>
            <p class="text-sm text-slate-500 mt-1">Select a member to place an order for</p>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            @php
                $memberSearchData = \App\Models\Member::where('status', 'active')->orderBy('first_name')->get()->map(fn($m) => ['id' => $m->id, 'first_name' => $m->first_name, 'last_name' => $m->last_name, 'staff_id' => $m->staff_id, 'staff_id_display' => $m->staff_id_display]);
            @endphp
            <div x-data="{ selectedId: '{{ request('member_id') }}' }" class="max-w-lg">
                <label class="block text-sm font-medium text-slate-700 mb-2">Select Member</label>
                <x-member-combobox :members="$memberSearchData" :selected-id="request('member_id')" @member-selected="selectedId = $event.detail.member.id" />

                <div class="mt-4 flex gap-2">
                    <button type="button" @click="if(selectedId) window.location.href='{{ route('products.index') }}?member_id=' + selectedId; else alert('Please select a member first.')"
                        class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2 rounded-[10px] text-sm font-medium transition">
                        Continue to Products
                    </button>
                    <a href="{{ route('purchases.index') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-5 py-2 rounded-[10px] text-sm font-medium transition">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
