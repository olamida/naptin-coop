<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create Regional Center</h2>
            <p class="text-gray-500 text-sm mt-1">Add a new regional office location for the cooperative</p>
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

        <form action="{{ route('admin.regions.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Region Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="e.g. North Central">
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Region Code *</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required maxlength="20"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="e.g. NC-01">
                </div>
            </div>

            <div>
                <label for="zone" class="block text-sm font-medium text-gray-700 mb-1">Zone</label>
                <input type="text" name="zone" id="zone" value="{{ old('zone') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="e.g. Northern Zone">
            </div>

            <div>
                <label for="headquarters" class="block text-sm font-medium text-gray-700 mb-1">Headquarters</label>
                <input type="text" name="headquarters" id="headquarters" value="{{ old('headquarters') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="e.g. Abuja Office">
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea name="address" id="address" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Full address of the regional center">{{ old('address') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="+234 ...">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="region@naptin.coop">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.regions.index') }}" class="px-4 py-2.5 text-sm text-gray-700 hover:text-gray-900">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium">
                    Create Region
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
