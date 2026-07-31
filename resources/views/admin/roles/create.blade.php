<x-app-layout title="Create Role">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.roles.index') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Create New Role</h2>
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

        <form method="POST" action="{{ route('admin.roles.store') }}" class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6 space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Role Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                       placeholder="e.g. regional-manager">
                <p class="text-xs text-slate-400 mt-1">Use lowercase with hyphens (e.g. loan-officer)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-3">Permissions</label>
                <div class="space-y-4">
                    @foreach ($permissions as $group => $perms)
                        <div class="border border-slate-200 rounded-[10px] p-4">
                            <label class="flex items-center gap-2 mb-3 cursor-pointer">
                                <input type="checkbox" class="group-toggle rounded text-blue-600 focus:ring-blue-500" data-group="{{ $group }}">
                                <span class="text-sm font-semibold text-slate-700 capitalize">{{ str_replace('-', ' ', $group) }}</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2 ml-6">
                                @foreach ($perms as $perm)
                                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer hover:text-[#0F172A]">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                               class="perm-{{ $group }} rounded text-blue-600 focus:ring-blue-500">
                                        {{ $perm->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:text-[#0F172A]">Cancel</a>
                <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">
                    Create Role
                </button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('.group-toggle').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                var group = this.dataset.group;
                document.querySelectorAll('.perm-' + group).forEach(function(cb) {
                    cb.checked = toggle.checked;
                });
            });
        });
    </script>
</x-app-layout>
