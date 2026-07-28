<x-app-layout title="Create User">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-gray-800">Create User</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                    <div x-data="{ show: false }" class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                    <div x-data="{ show: false }" class="relative">
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" required
                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                    <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Select role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                        Create User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
