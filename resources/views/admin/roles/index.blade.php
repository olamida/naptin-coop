<x-app-layout title="Roles & Permissions">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Roles & Permissions</h2>
                <p class="text-xs text-slate-500 mt-1">{{ $roles->count() }} roles &middot; {{ $permissions->count() }} permissions</p>
            </div>
            <a href="{{ route('admin.roles.create') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                + New Role
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-slate-500 text-xs uppercase tracking-wider">Role</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-500 text-xs uppercase tracking-wider">Permissions</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-500 text-xs uppercase tracking-wider">Users</th>
                        <th class="text-right px-6 py-3 font-semibold text-slate-500 text-xs uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($roles as $role)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold
                                        {{ $role->name === 'super-admin' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $role->name === 'admin' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $role->name === 'secretary' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $role->name === 'treasurer' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $role->name === 'loan-officer' ? 'bg-purple-100 text-purple-700' : '' }}
                                        {{ $role->name === 'teller' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                        {{ $role->name === 'member' ? 'bg-gray-100 text-gray-600' : '' }}">
                                        {{ strtoupper(substr($role->name, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-[#0F172A]">{{ $role->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-md">
                                    @forelse ($role->permissions->take(8) as $perm)
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">{{ $perm->name }}</span>
                                    @empty
                                        <span class="text-xs text-gray-400">No permissions</span>
                                    @endforelse
                                    @if ($role->permissions->count() > 8)
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-xs">+{{ $role->permissions->count() - 8 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $role->users_count ?? $role->users()->count() }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                                    @if ($role->name !== 'super-admin')
                                        <form id="delete-role-{{ $role->id }}" method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" onclick="deleteConfirm('delete-role-{{ $role->id }}')" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
