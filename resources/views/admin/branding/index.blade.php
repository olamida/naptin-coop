<x-app-layout title="Branding">
    @php
        $branding = app(\App\Services\BrandingService::class);
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

    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Management', 'url' => route('admin.manage')], ['label' => 'Branding']]" />

        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Branding Assets</h2>
                <p class="text-xs text-slate-500 mt-1">Manage the logos, heroes, and favicon that power every page — uploads are resized and cached automatically.</p>
            </div>
            <a href="{{ route('admin.branding.preview') }}"
               class="inline-flex items-center gap-2 bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2.5 rounded-[10px] text-sm font-medium transition">
                <span class="material-symbols-outlined text-lg">visibility</span>
                Preview Brand
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($meta as $key => $info)
                @php $asset = $assets->get($key); @endphp
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                    {{-- Preview --}}
                    <div class="{{ $isHero($key) ? 'h-40 bg-slate-800' : 'h-40 bg-slate-50' }} flex items-center justify-center p-4 border-b border-slate-100">
                        @if ($thumb($key))
                            <img src="{{ $thumb($key) }}" alt="{{ $info['label'] }}"
                                 class="{{ $isHero($key) ? 'w-full h-full object-cover rounded-[10px]' : 'max-h-28 max-w-28 object-contain' }}">
                        @else
                            <div class="flex flex-col items-center text-slate-300">
                                <span class="material-symbols-outlined text-4xl">image_not_supported</span>
                                <p class="text-xs mt-1">Not set</p>
                            </div>
                        @endif
                    </div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-[#0F172A]">{{ $info['label'] }}</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $info['description'] }}</p>
                            </div>
                            @if ($asset)
                                <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    SET
                                </span>
                            @else
                                <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-[10px] font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    EMPTY
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($info['usage'] as $usage)
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">{{ $usage }}</span>
                            @endforeach
                        </div>

                        @if ($info['recommended_size'])
                            <p class="mt-3 text-[11px] text-slate-400">Recommended: {{ $info['recommended_size'] }}</p>
                        @endif

                        {{-- Actions --}}
                        <form method="POST" action="{{ route('admin.branding.upload', $key) }}" enctype="multipart/form-data"
                              class="mt-4 flex items-center gap-2" x-data="{ name: '{{ $asset->file_path ?? '' }}' }">
                            @csrf
                            <label class="flex-1 cursor-pointer">
                                <input type="file" name="asset" accept="image/jpeg,image/png,image/gif,image/webp"
                                       class="sr-only" x-on:change="name = $event.target.files[0] ? $event.target.files[0].name : '{{ $asset->file_path ?? '' }}'">
                                <span class="flex items-center gap-2 w-full border border-slate-300 rounded-[10px] px-3 py-2 text-xs text-slate-500 hover:border-slate-400 transition">
                                    <span class="material-symbols-outlined text-[16px]">upload_file</span>
                                    <span class="truncate" x-text="name">{{ $asset->file_path ?? 'Choose an image…' }}</span>
                                </span>
                            </label>
                            <button type="submit"
                                    class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-[10px] text-xs font-medium transition">
                                Upload
                            </button>
                        </form>

                        @if ($asset)
                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <p class="text-[11px] text-slate-400 truncate">
                                    {{ $asset->file_type }} &middot; {{ number_format(strlen($asset->file_path)) }}<span class="hidden"> bytes</span>
                                    @if ($asset->uploader)
                                        &middot; by {{ $asset->uploader->name }}
                                    @endif
                                </p>
                                <div class="flex items-center gap-1">
                                    <form method="POST" action="{{ route('admin.branding.regenerate', $key) }}">
                                        @csrf
                                        <button type="submit" title="Regenerate size variants"
                                                class="p-1.5 rounded-[8px] text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                                            <span class="material-symbols-outlined text-[18px]">refresh</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.branding.destroy', $key) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Remove asset"
                                                onclick="return confirm('Remove this branding asset? All pages will fall back to the default placeholder.')"
                                                class="p-1.5 rounded-[8px] text-slate-400 hover:text-red-600 hover:bg-red-50 transition">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-slate-100 border border-slate-200 rounded-[16px] p-4 flex items-start gap-3">
            <span class="material-symbols-outlined text-slate-500 text-lg mt-0.5">info</span>
            <p class="text-xs text-slate-600 leading-relaxed">
                Missing assets fall back to the default placeholders. Uploading a favicon also refreshes the PWA home-screen icons
                (icon-192.png, icon-512.png) and browser favicon. Use <code class="bg-white px-1 rounded">php artisan branding:seed</code>
                to (re)seed from <code class="bg-white px-1 rounded">resources/branding/seed</code>.
            </p>
        </div>
    </div>
</x-app-layout>
