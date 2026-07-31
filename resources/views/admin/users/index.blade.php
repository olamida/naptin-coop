<x-app-layout title="User Management">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-[#0F172A]">User Management</h2>
            <a href="{{ route('admin.users.create') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-lg">person_add</span>
                Add User
            </a>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-slate-500 text-xs uppercase tracking-wider">User</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-500 text-xs uppercase tracking-wider">Role</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-500 text-xs uppercase tracking-wider">Joined</th>
                        <th class="text-right px-5 py-3 font-medium text-slate-500 text-xs uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-slate-100 rounded-[10px] flex items-center justify-center text-xs font-bold text-slate-600">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-[#0F172A]">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                @foreach ($user->roles as $role)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-700">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="px-5 py-3 text-slate-500 text-xs">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-slate-400 hover:text-[#0F172A] transition">
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
                                            <button type="button" onclick="deleteConfirm('delete-user-{{ $user->id }}')" class="text-slate-400 hover:text-red-600 transition">
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
