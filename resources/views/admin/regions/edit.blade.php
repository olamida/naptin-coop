<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-[#0F172A]">Edit Regional Center</h2>
            <p class="text-slate-500 text-sm mt-1">Update {{ $region->name }} details</p>
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

        <form action="{{ route('admin.regions.update', $region) }}" method="POST" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Region Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $region->name) }}" required
                           class="w-full border border-slate-300 rounded-[10px] px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-slate-700 mb-1">Region Code *</label>
                    <input type="text" name="code" id="code" value="{{ old('code', $region->code) }}" required maxlength="20"
                           class="w-full border border-slate-300 rounded-[10px] px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <div>
                <label for="zone" class="block text-sm font-medium text-slate-700 mb-1">Zone</label>
                <input type="text" name="zone" id="zone" value="{{ old('zone', $region->zone) }}"
                       class="w-full border border-slate-300 rounded-[10px] px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label for="headquarters" class="block text-sm font-medium text-slate-700 mb-1">Headquarters</label>
                <input type="text" name="headquarters" id="headquarters" value="{{ old('headquarters', $region->headquarters) }}"
                       class="w-full border border-slate-300 rounded-[10px] px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                <textarea name="address" id="address" rows="2"
                          class="w-full border border-slate-300 rounded-[10px] px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('address', $region->address) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $region->phone) }}"
                           class="w-full border border-slate-300 rounded-[10px] px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $region->email) }}"
                           class="w-full border border-slate-300 rounded-[10px] px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.regions.index') }}" class="px-4 py-2.5 text-sm text-slate-700 hover:text-[#0F172A]">Cancel</a>
                <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2.5 rounded-[10px] text-sm font-medium">
                    Update Region
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
