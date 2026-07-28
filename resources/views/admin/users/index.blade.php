<x-app-layout title="User Management">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">User Management</h2>
            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">person_add</span>
                Add User
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">User</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Role</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Joined</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-xs font-bold text-gray-600">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                @foreach ($user->roles as $role)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-gray-400 hover:text-blue-600 transition">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="inline" onsubmit="return confirm('Reset password for {{ $user->name }}? A temporary password will be sent to their email.');">
                                            @csrf
                                            <button type="submit" class="text-gray-400 hover:text-orange-600 transition" title="Reset Password">
                                                <span class="material-symbols-outlined text-lg">lock_reset</span>
                                            </button>
                                        </form>
                                        <form id="delete-user-{{ $user->id }}" method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" onclick="deleteConfirm('delete-user-{{ $user->id }}')" class="text-gray-400 hover:text-red-600 transition">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</x-app-layout>
