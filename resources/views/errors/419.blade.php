@php
    $company = \App\Models\Company::instance();
    $branding = app(\App\Services\BrandingService::class);
    $loginLogo = $branding->getLogo('header');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $branding->getThemeColor() }}">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ $branding->get('favicon', 'favicon-32x32.png') ?? asset('favicon.ico') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ $branding->get('favicon', 'favicon-180x180.png') ?? asset('icon-192.png') }}">
    <title>Session Expired - {{ $company->name ?? 'NAPTIN Cooperative' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-amber-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl text-amber-600">timer_off</span>
            </div>

            @if ($loginLogo)
                <img src="{{ $loginLogo }}" alt="{{ $company->name }}" class="h-12 mx-auto mb-4 object-contain">
            @endif

            <h1 class="text-xl font-bold text-[#0F172A] mb-2">Page Expired</h1>
            <p class="text-sm text-slate-500 mb-6">
                Your session has expired for security reasons. This happens when a form is left open too long or the page is reopened from the browser history.
            </p>

            <div class="space-y-3">
                <a href="{{ auth()->check() ? url()->previous() : route('login') }}"
                   onclick="event.preventDefault(); window.location.reload();"
                   class="w-full inline-flex items-center justify-center gap-2 bg-[#0F172A] hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-[10px] transition text-sm">
                    <span class="material-symbols-outlined text-lg">refresh</span>
                    Refresh &amp; Try Again
                </a>
                @guest
                    <a href="{{ route('login') }}"
                       class="w-full inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-2.5 px-4 rounded-[10px] transition text-sm">
                        <span class="material-symbols-outlined text-lg">login</span>
                        Go to Login
                    </a>
                @endguest
            </div>

            <p class="text-[11px] text-slate-400 mt-6">If this keeps happening, clear your browser cookies and try again.</p>
        </div>
    </div>
</body>
</html>
