<x-app-layout title="Company Settings">
    <div x-data="settingsPage()" class="max-w-4xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Management', 'url' => route('admin.manage')], ['label' => 'Company Settings']]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Company Settings</h2>
                <p class="text-xs text-slate-500 mt-1">Manage your cooperative's branding, content, and financial configuration</p>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            {{-- Tabs --}}
            <div class="border-b border-slate-200 bg-slate-50/50">
                <nav class="flex overflow-x-auto" role="tablist">
                    <button @click="tab = 'branding'" :class="tab === 'branding' ? 'border-b-2 border-[#0F172A] text-[#0F172A]' : 'text-slate-500 hover:text-slate-700'" class="px-5 py-3.5 text-sm font-medium whitespace-nowrap transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">palette</span>
                        Branding
                    </button>
                    <button @click="tab = 'contact'" :class="tab === 'contact' ? 'border-b-2 border-[#0F172A] text-[#0F172A]' : 'text-slate-500 hover:text-slate-700'" class="px-5 py-3.5 text-sm font-medium whitespace-nowrap transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">contact_mail</span>
                        Contact & Social
                    </button>
                    <button @click="tab = 'content'" :class="tab === 'content' ? 'border-b-2 border-[#0F172A] text-[#0F172A]' : 'text-slate-500 hover:text-slate-700'" class="px-5 py-3.5 text-sm font-medium whitespace-nowrap transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">article</span>
                        Content
                    </button>
                    <button @click="tab = 'financial'" :class="tab === 'financial' ? 'border-b-2 border-[#0F172A] text-[#0F172A]' : 'text-slate-500 hover:text-slate-700'" class="px-5 py-3.5 text-sm font-medium whitespace-nowrap transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">payments</span>
                        Financial
                    </button>
                    <button @click="tab = 'modules'" :class="tab === 'modules' ? 'border-b-2 border-[#0F172A] text-[#0F172A]' : 'text-slate-500 hover:text-slate-700'" class="px-5 py-3.5 text-sm font-medium whitespace-nowrap transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">toggle_on</span>
                        Modules
                    </button>
                </nav>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="p-6">
                @csrf @method('PUT')

                {{-- TAB: Branding --}}
                <div x-show="tab === 'branding'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Logo --}}
                        <div class="lg:col-span-2">
                            <div class="bg-slate-50 rounded-[16px] p-5 border border-slate-200" x-data="logoUpload()">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h4 class="text-sm font-semibold text-[#0F172A]">Cooperative Logo</h4>
                                        <p class="text-xs text-slate-500 mt-0.5">JPG, PNG, GIF, SVG or WebP. Max 2MB. 300x300px recommended.</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-5">
                                    <div class="relative group flex-shrink-0">
                                        <template x-if="preview">
                                            <img :src="preview" alt="Logo preview" class="w-24 h-24 rounded-[16px] object-contain border-2 border-slate-200 bg-white">
                                        </template>
                                        <template x-if="!preview && '{{ $company->logo_path ?? '' }}' !== ''">
                                            <img src="{{ $company->logo_url }}" alt="Current logo" class="w-24 h-24 rounded-[16px] object-contain border-2 border-slate-200 bg-white">
                                        </template>
                                        <template x-if="!preview && '{{ $company->logo_path ?? '' }}' === ''">
                                            <div class="w-24 h-24 rounded-[16px] bg-white border-2 border-dashed border-slate-300 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-slate-300 text-3xl">image</span>
                                            </div>
                                        </template>
                                        <label class="absolute inset-0 rounded-xl bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center cursor-pointer">
                                            <span class="material-symbols-outlined text-white text-xl">photo_camera</span>
                                            <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" class="hidden" x-on:change="handleFile($event)">
                                        </label>
                                    </div>
                                    <div class="flex-1 space-y-2">
                                        <p class="text-sm font-medium text-slate-700">{{ $company->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $company->slogan ?? 'No slogan set' }}</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <template x-if="hasFile">
                                                <button type="button" x-on:click="clearFile()" class="text-xs text-slate-500 hover:text-slate-700 flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-sm">close</span> Clear selection
                                                </button>
                                            </template>
                                            @if ($company->logo_path)
                                                <label class="flex items-center gap-1.5 text-xs text-red-600 hover:text-red-800 cursor-pointer select-none">
                                                    <input type="checkbox" name="remove_logo" value="1" class="hidden" x-model="removeLogo">
                                                    <span class="material-symbols-outlined text-sm" x-text="removeLogo ? 'check_box' : 'check_box_outline_blank'"></span>
                                                    Remove current logo
                                                </label>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Theme Color Preview --}}
                        <div class="bg-slate-50 rounded-[16px] p-5 border border-slate-200">
                            <h4 class="text-sm font-semibold text-[#0F172A] mb-3">Live Preview</h4>
                            <div class="space-y-3">
                                <div class="rounded-[10px] px-4 py-3 text-white text-xs font-medium text-center" :style="'background: ' + (themeColor || '#2563eb')">
                                    Primary Color
                                </div>
                                <div class="rounded-[10px] px-4 py-3 text-white text-xs font-medium text-center" :style="'background: ' + (secondaryColor || '#059669')">
                                    Secondary Color
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Theme Colors --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                        <div class="bg-slate-50 rounded-[16px] p-5 border border-slate-200">
                            <label class="block text-sm font-semibold text-[#0F172A] mb-3">Primary Theme Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="theme_color" value="{{ old('theme_color', $company->theme_color ?? '#2563eb') }}"
                                       class="w-10 h-10 rounded-[10px] cursor-pointer border border-slate-300 p-0.5"
                                       x-model="themeColor">
                                <input type="text" x-model="themeColor" class="flex-1 px-3 py-2 border border-slate-300 rounded-[10px] text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <p class="text-xs text-slate-400 mt-2">Used for headers, buttons, and links throughout the site</p>
                        </div>
                        <div class="bg-slate-50 rounded-[16px] p-5 border border-slate-200">
                            <label class="block text-sm font-semibold text-[#0F172A] mb-3">Secondary / Accent Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="secondary_color" value="{{ old('secondary_color', $company->secondary_color ?? '#059669') }}"
                                       class="w-10 h-10 rounded-[10px] cursor-pointer border border-slate-300 p-0.5"
                                       x-model="secondaryColor">
                                <input type="text" x-model="secondaryColor" class="flex-1 px-3 py-2 border border-slate-300 rounded-[10px] text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <p class="text-xs text-slate-400 mt-2">Used for accents, badges, success states</p>
                        </div>
                    </div>

                    {{-- Banner --}}
                    <div class="mt-6 bg-slate-50 rounded-[16px] p-5 border border-slate-200" x-data="bannerUpload()">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-sm font-semibold text-[#0F172A]">Hero Banner Image</h4>
                                <p class="text-xs text-slate-500 mt-0.5">JPG, PNG or WebP. Max 5MB. 1920x600px recommended for the homepage hero.</p>
                            </div>
                        </div>
                        <div class="relative rounded-[16px] overflow-hidden bg-slate-100 border-2 border-dashed border-slate-300" :class="preview || bannerExists ? '' : 'h-44 flex items-center justify-center'">
                            <template x-if="preview">
                                <img :src="preview" alt="Banner preview" class="w-full h-44 object-cover">
                            </template>
                            <template x-if="!preview && bannerExists">
                                <img src="{{ $company->banner_url }}" alt="Current banner" class="w-full h-44 object-cover">
                            </template>
                            <template x-if="!preview && !bannerExists">
                                <div class="text-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-300">panorama</span>
                                    <p class="text-sm text-slate-400 mt-2">No banner image set</p>
                                </div>
                            </template>
                            <label class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition flex items-center justify-center cursor-pointer">
                                <div class="text-center text-white">
                                    <span class="material-symbols-outlined text-3xl">photo_camera</span>
                                    <p class="text-sm font-medium">Upload Banner</p>
                                </div>
                                <input type="file" name="banner" accept="image/jpeg,image/png,image/webp" class="hidden" x-on:change="handleFile($event)">
                            </label>
                        </div>
                        <div class="flex items-center gap-3 mt-3">
                            <template x-if="hasFile">
                                <button type="button" x-on:click="clearFile()" class="text-xs text-slate-500 hover:text-slate-700 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">close</span> Clear selection
                                </button>
                            </template>
                            @if ($company->banner_path)
                                <label class="flex items-center gap-1.5 text-xs text-red-600 hover:text-red-800 cursor-pointer select-none">
                                    <input type="checkbox" name="remove_banner" value="1" class="hidden" x-model="removeBanner">
                                    <span class="material-symbols-outlined text-sm" x-text="removeBanner ? 'check_box' : 'check_box_outline_blank'"></span>
                                    Remove current banner
                                </label>
                            @endif
                        </div>
                    </div>

                    {{-- Company Identity --}}
                    <div class="mt-6 bg-slate-50 rounded-[16px] p-5 border border-slate-200">
                        <h4 class="text-sm font-semibold text-[#0F172A] mb-4">Company Identity</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 lg:col-span-1">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Company Name</label>
                                <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div class="col-span-2 lg:col-span-1">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Slogan / Tagline</label>
                                <input type="text" name="slogan" value="{{ old('slogan', $company->slogan) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                       placeholder="Building Financial Independence Together">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Registration Number</label>
                                <input type="text" name="registration_number" value="{{ old('registration_number', $company->registration_number) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Website</label>
                                <input type="url" name="website" value="{{ old('website', $company->website) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                       placeholder="https://example.com">
                            </div>
                        </div>
                    </div>

                    {{-- Branding Assets --}}
                    <div class="mt-6">
                        @php $branding = app(\App\Services\BrandingService::class); @endphp
                        <div class="flex items-center justify-between flex-wrap gap-2 mb-4">
                            <div>
                                <h4 class="text-sm font-semibold text-[#0F172A]">Branding Assets</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Favicon, heroes, logo and round icon used across the site — uploads are resized and cached automatically.</p>
                            </div>
                            <a href="{{ route('admin.branding.preview') }}"
                               class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-800">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                Preview Brand
                            </a>
                        </div>

                        @php
                            $brandingMeta = \App\Services\BrandingService::META;
                            $thumb = function (string $key) use ($branding): ?string {
                                return match ($key) {
                                    'favicon' => $branding->get('favicon', 'favicon-180x180.png'),
                                    'logo_primary' => $branding->get('logo_primary', 'logo-128x128.png'),
                                    'icon_round' => $branding->get('icon_round', 'icon-128x128.png'),
                                    default => $branding->get($key, 'hero-640.webp') ?? $branding->get($key),
                                };
                            };
                            $isHero = fn (string $key) => str_starts_with($key, 'hero_');
                        @endphp

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            @foreach ($brandingMeta as $key => $info)
                                @php $asset = $assets->get($key); @endphp
                                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden"
                                     x-data="brandingAsset('{{ $key }}', '{{ route('admin.branding.upload', $key) }}', '{{ route('admin.branding.regenerate', $key) }}', '{{ route('admin.branding.destroy', $key) }}')">
                                    <div class="{{ $isHero($key) ? 'h-32 bg-slate-800' : 'h-32 bg-slate-50' }} flex items-center justify-center p-3 border-b border-slate-100">
                                        @if ($thumb($key))
                                            <img src="{{ $thumb($key) }}" alt="{{ $info['label'] }}"
                                                 class="{{ $isHero($key) ? 'w-full h-full object-cover rounded-[8px]' : 'max-h-20 max-w-20 object-contain' }}">
                                        @else
                                            <div class="flex flex-col items-center text-slate-300">
                                                <span class="material-symbols-outlined text-3xl">image_not_supported</span>
                                                <p class="text-[11px] mt-1">Not set</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h3 class="text-sm font-semibold text-[#0F172A]">{{ $info['label'] }}</h3>
                                                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">{{ $info['description'] }}</p>
                                            </div>
                                            @if ($asset)
                                                <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-semibold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>SET
                                                </span>
                                            @else
                                                <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-semibold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>EMPTY
                                                </span>
                                            @endif
                                        </div>
                                        @if ($info['recommended_size'])
                                            <p class="mt-2 text-[10px] text-slate-400">Recommended: {{ $info['recommended_size'] }}</p>
                                        @endif
                                        <div class="mt-3 flex items-center gap-2">
                                            <label class="flex-1 cursor-pointer">
                                                <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="sr-only"
                                                       x-ref="file" @change="onFileChange($event)">
                                                <span class="flex items-center gap-2 w-full border border-slate-300 rounded-[10px] px-3 py-1.5 text-[11px] text-slate-500 hover:border-slate-400 transition">
                                                    <span class="material-symbols-outlined text-[14px]">upload_file</span>
                                                    <span class="truncate" x-text="fileName ? fileName : '{{ $asset->file_path ?? 'Choose an image…' }}'">{{ $asset->file_path ?? 'Choose an image…' }}</span>
                                                </span>
                                            </label>
                                            <button type="button" @click="upload()" :disabled="busy || !fileName"
                                                    class="shrink-0 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-3.5 py-1.5 rounded-[10px] text-[11px] font-medium transition">
                                                Upload
                                            </button>
                                        </div>
                                        @if ($asset)
                                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                                                <p class="text-[10px] text-slate-400 truncate">
                                                    {{ $asset->file_type ?? 'image' }}
                                                    @if ($asset->uploader)
                                                        &middot; by {{ $asset->uploader->name }}
                                                    @endif
                                                </p>
                                                <div class="flex items-center gap-1">
                                                    <button type="button" @click="regenerate()" :disabled="busy" title="Regenerate size variants"
                                                            class="p-1.5 rounded-[8px] text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                                                        <span class="material-symbols-outlined text-[18px]">refresh</span>
                                                    </button>
                                                    <button type="button" @click="remove()" :disabled="busy" title="Remove asset"
                                                            class="p-1.5 rounded-[8px] text-slate-400 hover:text-red-600 hover:bg-red-50 transition">
                                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-[16px] p-3 mt-4 flex items-start gap-2">
                            <span class="material-symbols-outlined text-slate-500 text-base mt-0.5">info</span>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Missing assets fall back to default placeholders. Uploading a favicon also refreshes the PWA home-screen icons and browser favicon.
                                Use <code class="bg-white px-1 rounded">php artisan branding:seed</code> to (re)seed from <code class="bg-white px-1 rounded">resources/branding/seed</code>.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- TAB: Contact & Social --}}
                <div x-show="tab === 'contact'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="bg-slate-50 rounded-[16px] p-5 border border-slate-200">
                        <h4 class="text-sm font-semibold text-[#0F172A] mb-4">Contact Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $company->email) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                       placeholder="info@naptincoop.org">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                       placeholder="+234 XXX XXX XXXX">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Physical Address</label>
                                <textarea name="address" rows="2"
                                          class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                          placeholder="Head office address">{{ old('address', $company->address) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Opening Hours</label>
                                <input type="text" name="opening_hours" value="{{ old('opening_hours', $company->opening_hours) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                       placeholder="Mon - Fri: 8:00 AM - 5:00 PM">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-slate-50 rounded-[16px] p-5 border border-slate-200">
                        <h4 class="text-sm font-semibold text-[#0F172A] mb-4">Social Media Links</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-blue-600 text-sm">thumb_up</span> Facebook
                                </label>
                                <input type="url" name="facebook" value="{{ old('facebook', $company->facebook) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                       placeholder="https://facebook.com/naptincoop">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sky-500 text-sm">alternate_email</span> Twitter / X
                                </label>
                                <input type="url" name="twitter" value="{{ old('twitter', $company->twitter) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                       placeholder="https://twitter.com/naptincoop">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-pink-500 text-sm">photo_camera</span> Instagram
                                </label>
                                <input type="url" name="instagram" value="{{ old('instagram', $company->instagram) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                       placeholder="https://instagram.com/naptincoop">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-blue-700 text-sm">work</span> LinkedIn
                                </label>
                                <input type="url" name="linkedin" value="{{ old('linkedin', $company->linkedin) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                       placeholder="https://linkedin.com/company/naptincoop">
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-3">These links will appear in the website footer</p>
                    </div>
                </div>

                {{-- TAB: Content --}}
                <div x-show="tab === 'content'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="space-y-6">
                        <div class="bg-slate-50 rounded-[16px] p-5 border border-slate-200">
                            <h4 class="text-sm font-semibold text-[#0F172A] mb-4">About the Cooperative</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Short Description</label>
                                    <p class="text-xs text-slate-400 mb-2">A brief description for search engines and social previews (meta description). Max 300 characters.</p>
                                    <textarea name="description" rows="3" maxlength="300"
                                              class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                              placeholder="A cooperative society dedicated to the financial well-being of NAPTIN staff members..."
                                              x-model="descriptionText">{{ old('description', $company->description) }}</textarea>
                                    <p class="text-xs text-slate-400 mt-1 text-right"><span x-text="descriptionText.length">0</span>/300</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Our Story / History</label>
                                    <p class="text-xs text-slate-400 mb-2">Displayed on the About page. Tell your cooperative's story.</p>
                                    <textarea name="short_history" rows="6"
                                              class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                              placeholder="Founded in...">{{ old('short_history', $company->short_history) }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 rounded-[16px] p-5 border border-slate-200">
                            <h4 class="text-sm font-semibold text-[#0F172A] mb-4">Footer & Legal</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Footer Note</label>
                                    <p class="text-xs text-slate-400 mb-2">Displayed at the bottom of receipts, reports, and the website footer.</p>
                                    <textarea name="footer_note" rows="2"
                                              class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('footer_note', $company->footer_note) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB: Financial --}}
                <div x-show="tab === 'financial'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="bg-slate-50 rounded-[16px] p-5 border border-slate-200">
                        <h4 class="text-sm font-semibold text-[#0F172A] mb-4">Financial Configuration</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Default Monthly Thrift (₦)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₦</span>
                                    <input type="number" name="thrift_amount" value="{{ old('thrift_amount', $company->thrift_amount) }}" step="0.01" min="0" required
                                           class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Default thrift contribution per member</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Membership Fee (₦)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₦</span>
                                    <input type="number" name="membership_fee" value="{{ old('membership_fee', $company->membership_fee) }}" step="0.01" min="0" required
                                           class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Savings Interest Rate (% p.a.)</label>
                                <div class="relative">
                                    <input type="number" name="savings_interest_rate" value="{{ old('savings_interest_rate', $company->savings_interest_rate) }}" step="0.01" min="0" max="100" required
                                           class="w-full pr-8 pl-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Loan Interest Rate (% p.a.)</label>
                                <div class="relative">
                                    <input type="number" name="loan_interest_rate" value="{{ old('loan_interest_rate', $company->loan_interest_rate) }}" step="0.01" min="0" max="100" required
                                           class="w-full pr-8 pl-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Max Loan Multiplier</label>
                                <div class="relative">
                                    <input type="number" name="max_loan_multiplier" value="{{ old('max_loan_multiplier', $company->max_loan_multiplier) }}" min="1" max="20" required
                                           class="w-full pr-12 pl-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">× savings</span>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Max loan amount = savings balance × this multiplier</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Auto-Approve Deposit Limit (₦)</label>
                                <div class="relative">
                                    <input type="number" name="auto_approve_deposit_limit" value="{{ old('auto_approve_deposit_limit', $company->auto_approve_deposit_limit) }}" step="0.01" min="0"
                                           class="w-full pr-8 pl-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₦</span>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Portal deposits with evidence up to this limit are approved automatically</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB: Modules --}}
                <div x-show="tab === 'modules'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="bg-slate-50 rounded-[16px] p-5 border border-slate-200">
                        <h4 class="text-sm font-semibold text-[#0F172A] mb-1">Module Toggles</h4>
                        <p class="text-xs text-slate-500 mb-5">Disable a module to hide it from the sidebar and block access to its pages. Existing data is never deleted.</p>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-4 bg-white border border-slate-200 rounded-[16px] p-4">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-slate-500">trending_up</span>
                                    <div>
                                        <p class="text-sm font-medium text-[#0F172A]">Shares</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Share accounts, purchases, certificates and the member portal "My Shares" page.</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                    <input type="checkbox" name="shares_enabled" value="1" class="sr-only peer"
                                           {{ $company->shares_enabled ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer transition peer-checked:bg-[#0F172A] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-slate-300 after:rounded-full after:h-5 after:w-5 after:transition peer-checked:after:translate-x-full"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between gap-4 bg-white border border-slate-200 rounded-[16px] p-4">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-slate-500">diversity_3</span>
                                    <div>
                                        <p class="text-sm font-medium text-[#0F172A]">Dividends</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Annual dividend declaration, calculation, approval and distribution.</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                    <input type="checkbox" name="dividends_enabled" value="1" class="sr-only peer"
                                           {{ $company->dividends_enabled ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer transition peer-checked:bg-[#0F172A] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-slate-300 after:rounded-full after:h-5 after:w-5 after:transition peer-checked:after:translate-x-full"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-between pt-6 border-t border-slate-200 mt-6">
                    <p class="text-xs text-slate-400" x-text="'Last updated: {{ $company->updated_at ? $company->updated_at->format('M d, Y \a\t h:i A') : 'Never' }}'"></p>
                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2.5 rounded-[10px] text-sm font-medium transition shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">save</span>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function settingsPage() {
            return {
                tab: 'branding',
                themeColor: '{{ old('theme_color', $company->theme_color ?? '#2563eb') }}',
                secondaryColor: '{{ old('secondary_color', $company->secondary_color ?? '#059669') }}',
                descriptionText: '{{ old('description', $company->description) }}',
            }
        }

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

        function bannerUpload() {
            return {
                preview: null,
                hasFile: false,
                removeBanner: false,
                bannerExists: '{{ $company->banner_path ? 'true' : 'false' }}' === 'true',
                handleFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.size > 5 * 1024 * 1024) {
                            alert('File is too large. Maximum size is 5MB.');
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

        function brandingAsset(key, uploadUrl, regenerateUrl, removeUrl) {
            return {
                fileName: '',
                busy: false,
                _file: null,
                onFileChange(event) {
                    const file = event.target.files[0];
                    if (file && file.size > 10 * 1024 * 1024) {
                        alert('File is too large. Maximum size is 10MB.');
                        this.fileName = '';
                        this._file = null;
                        event.target.value = '';
                        return;
                    }
                    this._file = file;
                    this.fileName = file ? file.name : '';
                },
                _token() {
                    const input = document.querySelector('input[name="_token"]');
                    return input ? input.value : (document.querySelector('meta[name="csrf-token"]') || {}).content;
                },
                async upload() {
                    if (this.busy || !this._file) return;
                    const fd = new FormData();
                    fd.append('_token', this._token());
                    fd.append('asset', this._file);
                    this.busy = true;
                    try {
                        const res = await fetch(uploadUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                        if (res.ok) {
                            window.location.reload();
                        } else {
                            const data = await res.json().catch(() => ({}));
                            alert(data.message || 'Upload failed. Check the file type and size.');
                        }
                    } finally {
                        this.busy = false;
                    }
                },
                async regenerate() {
                    if (this.busy) return;
                    const fd = new FormData();
                    fd.append('_token', this._token());
                    this.busy = true;
                    try {
                        const res = await fetch(regenerateUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                        if (res.ok) {
                            window.location.reload();
                        } else {
                            alert('Regeneration failed. Upload an asset first.');
                        }
                    } finally {
                        this.busy = false;
                    }
                },
                async remove() {
                    if (this.busy) return;
                    if (!confirm('Remove this branding asset? All pages will fall back to the default placeholder.')) return;
                    const fd = new FormData();
                    fd.append('_token', this._token());
                    fd.append('_method', 'DELETE');
                    this.busy = true;
                    try {
                        const res = await fetch(removeUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                        if (res.ok) {
                            window.location.reload();
                        } else {
                            alert('Removal failed.');
                        }
                    } finally {
                        this.busy = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>