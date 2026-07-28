<x-app-layout title="Purchase Shares">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('shares.index') }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <h2 class="text-2xl font-bold text-gray-800">Purchase Shares</h2>
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

        <form method="POST" action="{{ route('shares.purchase.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Member *</label>
                <select name="member_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select Member</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                            {{ $member->first_name }} {{ $member->last_name }} — Current Shares: {{ number_format($member->shareAccount?->total_shares ?? 0) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Shares *</label>
                <input type="number" name="shares" value="{{ old('shares', '1') }}" required min="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <p class="text-xs text-gray-500 mt-1">Share price: ₦100.00 per share</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                    Purchase Shares
                </button>
                <a href="{{ route('shares.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
