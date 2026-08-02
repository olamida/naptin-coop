<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $branding->getThemeColor() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $company->name ?? 'NAPTIN Coop' }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" href="{{ $branding->get('favicon', 'favicon-32x32.png') ?? asset('favicon.ico') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ $branding->get('favicon', 'favicon-180x180.png') ?? asset('icon-192.png') }}">
    <title>Login - {{ $company->name ?? 'NAPTIN Cooperative' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-fade { transition: opacity 1s ease-in-out; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen"
      x-data="{ serverDown: false, hero: 0 }"
      x-init="setInterval(() => { fetch('/health').then(r => serverDown = false).catch(() => serverDown = true); }, 15000);
              fetch('/health').catch(() => serverDown = true);
              setInterval(() => { hero = hero === 0 ? 1 : 0; }, 7000);">
    {{-- Server Down Banner --}}
    <div x-show="serverDown" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-y-full" x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="-translate-y-full"
         class="fixed top-0 left-0 right-0 z-[200] bg-red-500 text-white px-4 py-2.5 text-sm font-medium flex items-center justify-center gap-2 shadow-lg" style="display: none;">
        <span class="material-symbols-outlined text-lg">cloud_off</span>
        <span>Server connection lost. Please check your local server.</span>
        <button @click="window.location.reload()" class="ml-2 underline hover:no-underline font-semibold">Retry</button>
    </div>

    @php
        $company = \App\Models\Company::instance();
        $branding = app(\App\Services\BrandingService::class);
        $heroA = $branding->getHero('login_admin');
        $heroB = $branding->getHero('login_member') ?? $heroA;
        $loginLogo = $branding->getLogo('header');
    @endphp

    <div class="min-h-screen flex flex-col lg:flex-row" :class="serverDown ? 'pt-10' : ''" style="transition: padding-top 0.3s ease;">
        {{-- Hero Panel --}}
        <div class="hidden lg:block lg:w-1/2 relative overflow-hidden">
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-cover bg-center hero-fade" :class="hero === 0 ? 'opacity-100' : 'opacity-0'" style="background-image: url('{{ $heroA }}');"></div>
                <div class="absolute inset-0 bg-cover bg-center hero-fade" :class="hero === 1 ? 'opacity-100' : 'opacity-0'" style="background-image: url('{{ $heroB }}');"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-[#0F172A]/95 via-[#0F172A]/75 to-[#2563eb]/40"></div>
            </div>
            <div class="relative h-full flex flex-col justify-between p-12 text-white">
                <div class="flex items-center gap-3">
                    @if ($loginLogo)
                        <img src="{{ $loginLogo }}" alt="{{ $company->name }}" class="w-12 h-12 rounded-[16px] object-contain shadow-lg" style="background: rgba(255,255,255,0.15); padding: 2px;">
                    @else
                        <div class="w-12 h-12 bg-white/10 rounded-[16px] flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-2xl">account_balance</span>
                        </div>
                    @endif
                    <div>
                        <p class="font-bold leading-tight">{{ $company->name ?? 'NAPTIN Staff Thrift Cooperative' }}</p>
                        <p class="text-xs text-slate-300 tracking-wide uppercase">{{ $company->slogan ?? 'Cooperative Society' }}</p>
                    </div>
                </div>

                <div>
                    <h1 class="text-3xl xl:text-4xl font-extrabold leading-tight mb-4">
                        Building financial independence together.
                    </h1>
                    <p class="text-slate-200 text-sm leading-relaxed max-w-md mb-8">
                        Save consistently, access affordable loans, earn share dividends, and shop at cooperative prices.
                    </p>
                    <div class="flex flex-wrap gap-3 text-xs text-slate-300">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10">
                            <span class="material-symbols-outlined text-[16px]">savings</span> Savings
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10">
                            <span class="material-symbols-outlined text-[16px]">account_balance</span> Loans
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10">
                            <span class="material-symbols-outlined text-[16px]">trending_up</span> Dividends
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10">
                            <span class="material-symbols-outlined text-[16px]">storefront</span> Shop
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-white/60" :class="hero === 0 ? 'bg-white' : ''"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-white/60" :class="hero === 1 ? 'bg-white' : ''"></span>
                </div>
            </div>
        </div>

        {{-- Login Form Panel --}}
        <div class="lg:w-1/2 flex items-center justify-center p-4 lg:p-8">
            <div class="w-full max-w-md">
                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-8">
                    <div class="text-center mb-8 lg:hidden">
                        @if ($loginLogo)
                            <img src="{{ $loginLogo }}" alt="{{ $company->name }}" class="h-16 mx-auto mb-3 object-contain">
                        @else
                            <div class="w-16 h-16 bg-[#0F172A] rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <span class="material-symbols-outlined text-white text-3xl">account_balance</span>
                            </div>
                        @endif
                        <h1 class="text-xl font-bold text-[#0F172A]">{{ $company->name }}</h1>
                        @if ($company->slogan)
                            <p class="text-sm text-slate-500 mt-1">{{ $company->slogan }}</p>
                        @endif
                    </div>

                    <div class="hidden lg:block text-center mb-8">
                        <h1 class="text-2xl font-bold text-[#0F172A]">Welcome Back</h1>
                        <p class="text-sm text-slate-500 mt-1">Sign in to your account to continue</p>
                    </div>

                    @if (session('success'))
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[10px] mb-6 text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-[10px] mb-6 text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">warning</span>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] mb-6 text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-[10px] focus:ring-2 focus:ring-[#0F172A] focus:border-[#0F172A] outline-none transition text-sm"
                            >
                        </div>

                        <div class="mb-4" x-data="{ show: false }">
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                            <div class="relative">
                                <input
                                    :type="show ? 'text' : 'password'"
                                    id="password"
                                    name="password"
                                    required
                                    class="w-full px-4 py-2.5 pr-10 border border-slate-300 rounded-[10px] focus:ring-2 focus:ring-[#0F172A] focus:border-[#0F172A] outline-none transition text-sm"
                                >
                                <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#0F172A] focus:ring-[#0F172A]">
                                <span class="text-sm text-slate-600">Remember me</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="text-sm text-[#0F172A] hover:text-slate-700 font-medium transition">
                                Forgot Password?
                            </a>
                        </div>

                        <button type="submit" class="w-full bg-[#0F172A] hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-[10px] transition text-sm">
                            Sign In
                        </button>
                    </form>

                    <div class="mt-6 pt-6 border-t border-slate-100 text-center space-y-2">
                        <a href="{{ route('register') }}" class="text-sm text-[#0F172A] hover:text-slate-700 font-medium inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">person_add</span>
                            Create an Account
                        </a>
                        <br>
                        <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-slate-700 font-medium inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">arrow_back</span>
                            Back to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
