<x-app-layout title="Brand Preview">
    @php
        $company = \App\Models\Company::instance();
        $branding = app(\App\Services\BrandingService::class);
        $logo = $branding->getLogo('header');
        $sidebar = $branding->getLogo('sidebar');
        $pdf = $branding->getLogo('pdf');
        $favicon = $branding->get('favicon', 'favicon-32x32.png');
        $themeColor = $branding->getThemeColor();
        $secondary = $company->secondary_color ?: '#059669';
    @endphp

    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Management', 'url' => route('admin.manage')], ['label' => 'Branding', 'url' => route('admin.branding.index')], ['label' => 'Preview']]" />

        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Brand Preview</h2>
                <p class="text-xs text-slate-500 mt-1">A live preview of how the uploaded assets appear across the app.</p>
            </div>
            <a href="{{ route('admin.branding.index') }}"
               class="inline-flex items-center gap-2 bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2.5 rounded-[10px] text-sm font-medium transition">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Back to Branding
            </a>
        </div>

        {{-- Hero images --}}
        <div>
            <h3 class="text-sm font-semibold text-[#0F172A] mb-3">Hero Banners</h3>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                @foreach (['hero_fintech' => 'Admin / Fintech', 'hero_savings' => 'Savings / Member', 'hero_unity' => 'Unity / Homepage'] as $key => $context)
                    <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                        <div class="h-32 bg-slate-800 overflow-hidden">
                            @if ($hero = $branding->getHero(strtolower(str_replace(' / ', '_', $context))))
                                <img src="{{ $hero }}" alt="{{ $context }}" class="w-full h-full object-cover">
                            @else
                                <div class="h-full flex items-center justify-center text-slate-500 text-xs">Not set</div>
                            @endif
                        </div>
                        <div class="p-3 flex items-center justify-between">
                            <p class="text-xs font-medium text-[#0F172A]">{{ $context }}</p>
                            <a href="{{ route('admin.branding.index') }}" class="text-[11px] text-blue-600 hover:text-blue-800">Change</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Logos --}}
        <div>
            <h3 class="text-sm font-semibold text-[#0F172A] mb-3">Logos</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-medium text-[#0F172A] mb-3">Primary Logo (light bg)</p>
                    <div class="flex items-center gap-3 p-3 border border-slate-100 rounded-[10px] bg-white">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="Primary logo" class="w-10 h-10 object-contain">
                        @else
                            <div class="w-10 h-10 rounded-[16px] bg-[#0F172A] flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-xl">account_balance</span>
                            </div>
                        @endif
                        <p class="text-sm font-semibold text-[#0F172A]">{{ $company->name }}</p>
                    </div>
                    <div class="flex items-center gap-2 p-3 border border-slate-100 rounded-[10px] mt-2 bg-slate-50">
                        @if ($pdf)
                            <img src="{{ $pdf }}" alt="PDF logo" class="h-9 w-9 object-contain">
                        @endif
                        <p class="text-xs text-slate-500">Receipt / PDF (larger variant)</p>
                    </div>
                </div>

                <div class="bg-[#0F172A] rounded-[16px] shadow-sm p-5">
                    <p class="text-xs font-medium text-slate-300 mb-3">Sidebar Icon (dark bg)</p>
                    <div class="flex items-center gap-3 p-3 rounded-[10px] bg-white/10">
                        @if ($sidebar)
                            <img src="{{ $sidebar }}" alt="Sidebar icon" class="w-10 h-10 rounded-[16px] object-contain" style="background: rgba(255,255,255,0.1); padding: 2px;">
                        @else
                            <div class="w-10 h-10 rounded-[16px] bg-white/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-xl">account_balance</span>
                            </div>
                        @endif
                        <p class="text-sm font-bold text-white">{{ $company->name }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-medium text-[#0F172A] mb-3">Favicon &amp; PWA Icons</p>
                    <div class="flex items-end gap-4">
                        @if ($favicon)
                            <img src="{{ $favicon }}" alt="Favicon 32" class="w-8 h-8 object-contain border border-slate-200 rounded-[6px] p-0.5">
                        @else
                            <span class="material-symbols-outlined text-3xl text-slate-300">tab</span>
                        @endif
                        @if ($icon192 = $branding->get('favicon', 'favicon-192x192.png'))
                            <img src="{{ $icon192 }}" alt="PWA 192" class="w-12 h-12 object-contain rounded-[10px] border border-slate-200 p-0.5">
                        @endif
                        @if ($icon512 = $branding->get('favicon', 'favicon-512x512.png'))
                            <img src="{{ $icon512 }}" alt="PWA 512" class="w-16 h-16 object-contain rounded-[12px] border border-slate-200 p-1">
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-400 mt-3">Browser tab, home screen &amp; apple-touch icons.</p>
                </div>
            </div>
        </div>

        {{-- Theme colours --}}
        <div>
            <h3 class="text-sm font-semibold text-[#0F172A] mb-3">Theme Colours</h3>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-[10px] border border-slate-200" style="background: {{ $themeColor }};"></span>
                        <div>
                            <p class="text-xs font-medium text-[#0F172A]">Primary</p>
                            <p class="text-[11px] font-mono text-slate-400">{{ $themeColor }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-[10px] border border-slate-200" style="background: {{ $secondary }};"></span>
                        <div>
                            <p class="text-xs font-medium text-[#0F172A]">Secondary</p>
                            <p class="text-[11px] font-mono text-slate-400">{{ $secondary }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-[10px] border border-slate-200" style="background: #0F172A;"></span>
                        <div>
                            <p class="text-xs font-medium text-[#0F172A]">Slate / Sidebar</p>
                            <p class="text-[11px] font-mono text-slate-400">#0F172A</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.settings.edit') }}#theme" class="ml-auto text-xs text-blue-600 hover:text-blue-800 font-medium">Edit colours</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
