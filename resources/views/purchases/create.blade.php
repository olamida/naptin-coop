<x-app-layout title="New Purchase Order">
    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Purchases', 'url' => route('purchases.index')],
            ['label' => 'New Order'],
        ]" />

        <div>
            <h2 class="text-2xl font-bold text-gray-800">New Purchase Order</h2>
            <p class="text-sm text-gray-500 mt-1">Select a member to place an order for</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form id="memberForm" class="max-w-lg">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Member</label>
                <select id="memberSelect" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Choose a member...</option>
                    @foreach (\App\Models\Member::where('status', 'active')->orderBy('first_name')->get() as $m)
                        <option value="{{ $m->id }}" {{ request('member_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->first_name }} {{ $m->last_name }} ({{ $m->staff_id }})
                        </option>
                    @endforeach
                </select>

                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                        Continue to Products
                    </button>
                    <a href="{{ route('purchases.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('memberForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var memberId = document.getElementById('memberSelect').value;
            if (memberId) {
                window.location.href = '{{ route("products.index") }}?member_id=' + memberId;
            } else {
                alert('Please select a member first.');
            }
        });
    </script>
    @endpush
</x-app-layout>
