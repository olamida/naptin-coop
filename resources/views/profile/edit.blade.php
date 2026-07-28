<x-app-layout title="My Profile">
    <div class="max-w-2xl mx-auto space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Profile Photo Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Profile Photo</h3>
            <div x-data="profilePhoto()" class="flex items-center gap-6">
                <div class="relative group">
                    <template x-if="preview">
                        <img :src="preview" alt="Profile" class="w-24 h-24 rounded-full object-cover border-2 border-gray-200">
                    </template>
                    <template x-if="!preview && '{{ $user->avatar_url }}'">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover border-2 border-gray-200">
                    </template>
                    <template x-if="!preview && !'{{ $user->avatar_url }}'">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                            {{ $user->initials }}
                        </div>
                    </template>
                    <label class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center cursor-pointer">
                        <span class="material-symbols-outlined text-white text-xl">photo_camera</span>
                        <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden"
                               x-on:change="handleFileChange($event)">
                    </label>
                </div>

                <div class="flex-1">
                    <div class="text-sm text-gray-500 space-y-1">
                        <p class="font-medium text-gray-700">Upload a new photo</p>
                        <p>JPG, PNG, GIF or WebP. Max 2MB.</p>
                    </div>
                    <div class="flex items-center gap-2 mt-3">
                        <template x-if="hasFile">
                            <div class="flex items-center gap-2">
                                <button type="button" x-on:click="uploadPhoto()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-xs font-medium transition">
                                    Save Photo
                                </button>
                                <button type="button" x-on:click="cancelUpload()" class="text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                            </div>
                        </template>
                        @if ($user->profile_photo_path)
                            <button type="button" x-on:click="removePhoto()" class="text-xs text-red-600 hover:text-red-800">Remove Photo</button>
                        @endif
                    </div>
                </div>
            </div>
            <form id="profilePhotoForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="hidden">
                @csrf @method('PUT')
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">
                <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/jpeg,image/png,image/gif,image/webp">
            </form>
            @if ($user->profile_photo_path)
                <form id="removePhotoForm" method="POST" action="{{ route('profile.update') }}" class="hidden">
                    @csrf @method('PUT')
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <input type="hidden" name="remove_photo" value="1">
                </form>
            @endif
        </div>

        {{-- Personal Info Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Personal Information</h3>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div class="pt-2 text-sm text-gray-500">
                    <p>Role: <span class="font-medium text-gray-700">{{ ucfirst($user->getRoleNames()->first() ?? 'None') }}</span></p>
                    <p>Member since: <span class="font-medium text-gray-700">{{ $user->created_at->format('d M Y') }}</span></p>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Password Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Change Password</h3>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <div x-data="{ show: false }" class="relative">
                        <input :type="show ? 'text' : 'password'" name="current_password"
                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" name="password"
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" name="password_confirmation"
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-400">Leave password fields blank to keep your current password.</p>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function profilePhoto() {
            return {
                preview: null,
                hasFile: false,
                handleFileChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('File is too large. Maximum size is 2MB.');
                            event.target.value = '';
                            return;
                        }
                        this.preview = URL.createObjectURL(file);
                        this.hasFile = true;
                        const input = document.getElementById('profilePhotoInput');
                        input.files = event.target.files;
                    }
                },
                uploadPhoto() {
                    document.getElementById('profilePhotoForm').submit();
                },
                cancelUpload() {
                    this.preview = null;
                    this.hasFile = false;
                    document.getElementById('profilePhotoInput').value = '';
                },
                removePhoto() {
                    if (confirm('Remove your profile photo?')) {
                        document.getElementById('removePhotoForm').submit();
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
