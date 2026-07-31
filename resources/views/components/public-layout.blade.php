@props(['title' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F172A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="NAPTIN Coop">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <title>{{ $title ? $title . ' - ' : '' }}{{ $company->name ?? 'NAPTIN Cooperative' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-gradient { background: linear-gradient(135deg, #0F172A 0%, #1e3a5f 50%, #2563eb 100%); }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body class="bg-white">
    @php $company = \App\Models\Company::instance(); @endphp

    {{-- Top Navigation --}}
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    @if ($company->logo_path && $company->logo_url)
                        <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="h-9 w-9 rounded-[10px] object-contain">
                    @else
                        <div class="h-9 w-9 bg-[#0F172A] rounded-[10px] flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-xl">account_balance</span>
                        </div>
                    @endif
                    <a href="{{ route('home') }}" class="text-lg font-bold text-[#0F172A] hover:text-blue-600 transition">{{ $company->name ?? 'NAPTIN Cooperative' }}</a>
                </div>

                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="px-4 py-2 rounded-[10px] text-sm font-medium {{ request()->routeIs('home') ? 'bg-slate-100 text-[#0F172A]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }} transition">Home</a>
                    <a href="{{ route('shop') }}" class="px-4 py-2 rounded-[10px] text-sm font-medium {{ request()->routeIs('shop*') ? 'bg-slate-100 text-[#0F172A]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }} transition">Shop</a>
                    <a href="{{ route('about') }}" class="px-4 py-2 rounded-[10px] text-sm font-medium {{ request()->routeIs('about') ? 'bg-slate-100 text-[#0F172A]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }} transition">About Us</a>
                </div>

                <div class="hidden md:flex items-center gap-3">
                    @auth
                        @if (Auth::user()->member_id)
                            <a href="{{ route('portal.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-[#0F172A] transition">My Account</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-[#0F172A] transition">Dashboard</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-slate-600 hover:text-red-600 transition">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-[#0F172A] transition">Login</a>
                        <a href="{{ route('register') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">Get Started</a>
                    @endauth
                </div>

                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-[10px]">
                    <span class="material-symbols-outlined" x-text="mobileOpen ? 'close' : 'menu'">menu</span>
                </button>
            </div>
        </div>

        <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-slate-100 bg-white" style="display: none;">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2.5 rounded-[10px] text-sm font-medium {{ request()->routeIs('home') ? 'bg-slate-100 text-[#0F172A]' : 'text-slate-600' }}">Home</a>
                <a href="{{ route('shop') }}" class="block px-3 py-2.5 rounded-[10px] text-sm font-medium {{ request()->routeIs('shop*') ? 'bg-slate-100 text-[#0F172A]' : 'text-slate-600' }}">Shop</a>
                <a href="{{ route('about') }}" class="block px-3 py-2.5 rounded-[10px] text-sm font-medium {{ request()->routeIs('about') ? 'bg-slate-100 text-[#0F172A]' : 'text-slate-600' }}">About Us</a>
                <hr class="border-slate-100 my-2">
                @auth
                    @if (Auth::user()->member_id)
                        <a href="{{ route('portal.dashboard') }}" class="block px-3 py-2.5 rounded-[10px] text-sm font-medium text-slate-600">My Account</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="block px-3 py-2.5 rounded-[10px] text-sm font-medium text-slate-600">Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2.5 rounded-[10px] text-sm font-medium text-red-600">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2.5 rounded-[10px] text-sm font-medium text-slate-600">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Page Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-[#0F172A] text-slate-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        @if ($company->logo_path && $company->logo_url)
                            <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="h-10 w-10 rounded-[10px] object-contain bg-white/10 p-1">
                        @else
                            <div class="h-10 w-10 bg-blue-600 rounded-[10px] flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-xl">account_balance</span>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-white font-bold">{{ $company->name ?? 'NAPTIN Staff Thrift Cooperative' }}</h3>
                            <p class="text-xs text-slate-400">{{ $company->slogan ?? 'Building Financial Independence Together' }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-md">
                        {{ $company->footer_note ?? 'A cooperative society dedicated to the financial well-being of NAPTIN staff members through savings, loans, and shared growth.' }}
                    </p>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-sm hover:text-white transition">Home</a></li>
                        <li><a href="{{ route('shop') }}" class="text-sm hover:text-white transition">Shop</a></li>
                        <li><a href="{{ route('about') }}" class="text-sm hover:text-white transition">About Us</a></li>
                        <li><a href="{{ route('login') }}" class="text-sm hover:text-white transition">Member Login</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">Contact</h4>
                    <ul class="space-y-3">
                        @if ($company->address)
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-sm text-blue-400 mt-0.5">location_on</span>
                                <span class="text-sm">{{ $company->address }}</span>
                            </li>
                        @endif
                        @if ($company->phone)
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-blue-400">phone</span>
                                <span class="text-sm">{{ $company->phone }}</span>
                            </li>
                        @endif
                        @if ($company->email)
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-blue-400">mail</span>
                                <span class="text-sm">{{ $company->email }}</span>
                            </li>
                        @endif
                        @if ($company->website)
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-blue-400">language</span>
                                <a href="{{ $company->website }}" target="_blank" class="text-sm hover:text-white transition">{{ $company->website }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between flex-wrap gap-2">
                <p class="text-xs text-slate-500">&copy; {{ date('Y') }} {{ $company->name ?? 'NAPTIN Staff Thrift Cooperative' }}. All rights reserved.</p>
                @if ($company->registration_number)
                    <p class="text-xs text-slate-500">Reg: {{ $company->registration_number }}</p>
                @endif
            </div>
        </div>
    </footer>
</body>
</html>
