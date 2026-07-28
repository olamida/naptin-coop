<x-app-layout title="New Broadcast">
    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Management', 'url' => route('admin.manage')],
            ['label' => 'Broadcast Notifications', 'url' => route('admin.broadcasts.index')],
            ['label' => 'New Broadcast']
        ]" />

        <div>
            <h2 class="text-2xl font-bold text-gray-800">New Broadcast Notification</h2>
            <p class="text-sm text-gray-500 mt-1">Send an announcement to all {{ number_format($memberCount) }} active member(s)</p>
        </div>

        <form action="{{ route('admin.broadcasts.store') }}" method="POST" class="max-w-3xl">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        placeholder="e.g., Annual General Meeting Notice">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select name="category" id="category" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>General</option>
                            <option value="urgent" {{ old('category') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="meeting" {{ old('category') === 'meeting' ? 'selected' : '' }}>Meeting</option>
                            <option value="policy" {{ old('category') === 'policy' ? 'selected' : '' }}>Policy</option>
                            <option value="financial" {{ old('category') === 'financial' ? 'selected' : '' }}>Financial</option>
                            <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('category')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-red-500">*</span></label>
                        <select name="priority" id="priority" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                        @error('priority')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                    <textarea name="body" id="body" rows="8" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        placeholder="Type your announcement message here...">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">Max 5,000 characters</p>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm"
                    onclick="return confirm('Send this broadcast to {{ number_format($memberCount) }} active member(s)?')">
                    <span class="material-symbols-outlined text-lg">send</span>
                    Send Broadcast
                </button>
                <a href="{{ route('admin.broadcasts.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
