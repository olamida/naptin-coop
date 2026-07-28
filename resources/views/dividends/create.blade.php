<x-app-layout title="Declare Dividend">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('dividends.index') }}" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-gray-800">Declare Dividend</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm text-gray-600 mb-6">Create a new dividend record for the year. After creation, you can calculate distributions based on share holdings.</p>

            <form method="POST" action="{{ route('dividends.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year *</label>
                    <select name="year" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @for ($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Profit to Distribute (₦) *</label>
                    <input type="number" name="total_profit" value="{{ old('total_profit') }}" step="0.01" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                           placeholder="e.g., 5000000">
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                        Create Dividend
                    </button>
                    <a href="{{ route('dividends.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
