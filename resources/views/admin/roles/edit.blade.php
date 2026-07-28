<x-app-layout title="Edit Role: {{ $role->name }}">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.roles.index') }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <h2 class="text-2xl font-bold text-gray-800">Edit Role: {{ $role->name }}</h2>
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

        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Permissions</label>
                <div class="space-y-4">
                    @foreach ($permissions as $group => $perms)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <label class="flex items-center gap-2 mb-3 cursor-pointer">
                                <input type="checkbox" class="group-toggle rounded text-blue-600 focus:ring-blue-500" data-group="{{ $group }}">
                                <span class="text-sm font-semibold text-gray-700 capitalize">{{ str_replace('-', ' ', $group) }}</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2 ml-6">
                                @foreach ($perms as $perm)
                                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-gray-800">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                               class="perm-{{ $group }} rounded text-blue-600 focus:ring-blue-500"
                                               {{ $role->hasPermissionTo($perm->name) ? 'checked' : '' }}>
                                        {{ $perm->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                    Update Role
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

        document.querySelectorAll('.group-toggle').forEach(function(toggle) {
            var group = toggle.dataset.group;
            var cbs = document.querySelectorAll('.perm-' + group);
            toggle.checked = cbs.length > 0 && Array.from(cbs).every(function(cb) { return cb.checked; });
        });
    </script>
</x-app-layout>
