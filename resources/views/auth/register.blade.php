<x-public-layout title="Register as Member">
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 py-12 px-4">
        <div class="max-w-lg mx-auto">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <span class="material-symbols-outlined text-white text-3xl">person_add</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Member Registration</h1>
                <p class="text-gray-500 text-sm mt-2">Join NAPTIN Staff Thrift Cooperative Society</p>
            </div>

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
                    <p class="font-medium mb-1">Please fix the following errors:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data"
                  class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-5">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                            placeholder="First name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                            placeholder="Last name">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        placeholder="Middle name (optional)">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        placeholder="you@example.com">
                    <p class="text-[11px] text-gray-400 mt-1">Your login credentials will be sent to this email after approval.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        placeholder="08012345678">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Staff ID <span class="text-red-500">*</span></label>
                        <input type="text" name="staff_id" value="{{ old('staff_id') }}" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                            placeholder="e.g. STF001">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Region <span class="text-red-500">*</span></label>
                        <select name="region_id" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Select region</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                    {{ $region->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select name="gender" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Select gender</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIN (National Identification Number)</label>
                    <input type="text" name="nin" value="{{ old('nin') }}"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        placeholder="11-digit NIN (optional)">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
                    <input type="file" name="photo" accept="image/*"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[11px] text-gray-400 mt-1">Max 2MB. JPG, PNG, or WebP.</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-start gap-2 text-xs text-blue-700">
                        <span class="material-symbols-outlined text-[16px] mt-0.5">info</span>
                        <span>After registration, your account will be reviewed by an administrator. You will receive an email with your login credentials once approved.</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg text-sm font-medium transition shadow-sm">
                        Submit Registration
                    </button>
                    <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>
</x-public-layout>
