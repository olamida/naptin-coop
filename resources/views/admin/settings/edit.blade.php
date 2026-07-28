<x-app-layout title="Company Settings">
    <div class="max-w-3xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Management', 'url' => route('admin.manage')], ['label' => 'Company Settings']]" />

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

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')

            {{-- Logo Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="logoUpload()">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Cooperative Logo</h3>
                <div class="flex items-center gap-6">
                    <div class="relative group">
                        <template x-if="preview">
                            <img :src="preview" alt="Logo" class="w-28 h-28 rounded-xl object-contain border-2 border-gray-200 bg-gray-50">
                        </template>
                        <template x-if="!preview && '{{ $company->logo_path ?? '' }}' !== ''">
                            <img src="{{ $company->logo_url }}" alt="Logo" class="w-28 h-28 rounded-xl object-contain border-2 border-gray-200 bg-gray-50">
                        </template>
                        <template x-if="!preview && '{{ $company->logo_path ?? '' }}' === ''">
                            <div class="w-28 h-28 rounded-xl bg-blue-50 border-2 border-dashed border-blue-300 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-400 text-3xl">image</span>
                            </div>
                        </template>
                        <label class="absolute inset-0 rounded-xl bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center cursor-pointer">
                            <span class="material-symbols-outlined text-white text-xl">photo_camera</span>
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/svg+xml" class="hidden"
                                   x-on:change="handleFile($event)">
                        </label>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-700">Upload logo</p>
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF or SVG. Max 2MB. Recommended: 300x300px</p>
                        <div class="flex items-center gap-2 mt-3">
                            <template x-if="hasFile">
                                <div class="flex items-center gap-2">
                                    <button type="button" x-on:click="clearFile()" class="text-xs text-gray-500 hover:text-gray-700">Clear</button>
                                </div>
                            </template>
                            @if ($company->logo_path)
                                <label class="flex items-center gap-1 text-xs text-red-600 hover:text-red-800 cursor-pointer">
                                    <input type="checkbox" name="remove_logo" value="1" class="hidden"
                                           x-model="removeLogo">
                                    <span class="material-symbols-outlined text-sm" x-text="removeLogo ? 'check_box' : 'check_box_outline_blank'"></span>
                                    Remove logo
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Company Info Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Company Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slogan</label>
                        <input type="text" name="slogan" value="{{ old('slogan', $company->slogan) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration Number</label>
                        <input type="text" name="registration_number" value="{{ old('registration_number', $company->registration_number) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" name="address" value="{{ old('address', $company->address) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                        <input type="url" name="website" value="{{ old('website', $company->website) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="https://example.com">
                    </div>
                </div>
            </div>

            {{-- Thrift & Loan Settings --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Thrift & Loan Settings</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Default Monthly Thrift (₦)</label>
                        <input type="number" name="thrift_amount" value="{{ old('thrift_amount', $company->thrift_amount) }}" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Membership Fee (₦)</label>
                        <input type="number" name="membership_fee" value="{{ old('membership_fee', $company->membership_fee) }}" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Savings Interest Rate (% p.a.)</label>
                        <input type="number" name="savings_interest_rate" value="{{ old('savings_interest_rate', $company->savings_interest_rate) }}" step="0.01" min="0" max="100" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Loan Interest Rate (% p.a.)</label>
                        <input type="number" name="loan_interest_rate" value="{{ old('loan_interest_rate', $company->loan_interest_rate) }}" step="0.01" min="0" max="100" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Loan Multiplier</label>
                        <input type="number" name="max_loan_multiplier" value="{{ old('max_loan_multiplier', $company->max_loan_multiplier) }}" min="1" max="20" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <p class="text-xs text-gray-400 mt-1">Max loan = savings × this multiplier</p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Footer Note</label>
                        <textarea name="footer_note" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('footer_note', $company->footer_note) }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Displayed on receipts and reports</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function logoUpload() {
            return {
                preview: null,
                hasFile: false,
                removeLogo: false,
                handleFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('File is too large. Maximum size is 2MB.');
                            event.target.value = '';
                            return;
                        }
                        this.preview = URL.createObjectURL(file);
                        this.hasFile = true;
                    }
                },
                clearFile() {
                    this.preview = null;
                    this.hasFile = false;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
