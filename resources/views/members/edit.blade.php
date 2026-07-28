<x-app-layout title="Edit Member">
    <div class="max-w-3xl space-y-6">
        <x-breadcrumb :items="[['label' => 'Members', 'url' => route('members.index')], ['label' => 'Edit Member']]" />
        <div class="flex items-center gap-3">
            <a href="{{ route('members.show', $member) }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <h2 class="text-2xl font-bold text-gray-800">Edit Member</h2>
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

        <form method="POST" action="{{ route('members.update', $member) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            @csrf
            @method('PUT')

            <fieldset class="space-y-4">
                <legend class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Personal Information</legend>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
                        <div x-data="{ preview: '{{ $member->photo_url }}', removed: false }" class="text-center">
                            <template x-if="preview && !removed">
                                <div class="mb-3 relative inline-block">
                                    <img :src="preview" class="w-28 h-28 rounded-full object-cover border-4 border-gray-200">
                                    <button type="button" x-on:click="preview = null; removed = true; $refs.removeInput.value = '1'"
                                            class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center shadow">x</button>
                                </div>
                            </template>
                            <template x-if="!preview || removed">
                                <div>
                                    <div class="w-28 h-28 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3 border-4 border-dashed border-gray-300">
                                        <span class="material-symbols-outlined text-gray-400 text-3xl">person</span>
                                    </div>
                                    <input type="file" name="photo" accept="image/*" x-on:change="preview = URL.createObjectURL($event.target.files[0]); removed = false"
                                           class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                            </template>
                            <input type="hidden" name="remove_photo" value="0" x-ref="removeInput">
                            <p class="text-[11px] text-gray-400 mt-1">JPG, PNG. Max 2MB</p>
                        </div>
                    </div>
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $member->first_name) }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $member->last_name) }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $member->middle_name) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select name="gender" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">Select</option>
                                <option value="male" {{ old('gender', $member->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $member->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $member->date_of_birth?->format('Y-m-d')) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NIN</label>
                            <input type="text" name="nin" value="{{ old('nin', $member->nin) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Contact Information</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $member->email) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $member->phone) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('address', $member->address) }}</textarea>
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Employment Information</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Staff ID *</label>
                        <input type="text" name="staff_id" value="{{ old('staff_id', $member->staff_id) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Region *</label>
                        <select name="region_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            @foreach ($regions as $region)
                                <option value="{{ $region->id }}" {{ old('region_id', $member->region_id) == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employment Date</label>
                        <input type="date" name="employment_date" value="{{ old('employment_date', $member->employment_date?->format('Y-m-d')) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Retirement Date</label>
                        <input type="date" name="retirement_date" value="{{ old('retirement_date', $member->retirement_date?->format('Y-m-d')) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Grade Level</label>
                        <input type="text" name="grade_level" value="{{ old('grade_level', $member->grade_level) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State of Origin</label>
                        <input type="text" name="state_of_origin" value="{{ old('state_of_origin', $member->state_of_origin) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Salary (₦)</label>
                        <input type="number" step="0.01" name="monthly_salary" value="{{ old('monthly_salary', $member->monthly_salary) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Savings (₦)</label>
                        <input type="number" step="0.01" name="monthly_savings" value="{{ old('monthly_savings', $member->monthly_savings) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                            placeholder="Preferred monthly savings amount">
                        <p class="text-[11px] text-gray-400 mt-1">Member's preferred amount for monthly payroll deduction</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            @foreach (\App\Enums\MemberStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ old('status', $member->status) === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </fieldset>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                    Update Member
                </button>
                <a href="{{ route('members.show', $member) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
