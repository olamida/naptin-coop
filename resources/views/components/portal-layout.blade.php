@props(['title' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="NAPTIN Coop">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <title>{{ $title ?? 'My Account' }} - NAPTIN Cooperative</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .sidebar-link { transition: all 0.15s ease; }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); }
        .sidebar-link.active { background: rgba(255,255,255,0.15); color: #fff; font-weight: 500; }
        .sidebar-link.active::before { content: ''; position: absolute; left: 0; top: 6px; bottom: 6px; width: 3px; border-radius: 0 3px 3px 0; background: #60a5fa; }
        .sidebar-link { position: relative; }
        .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px -5px rgba(0,0,0,0.1); }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @media print {
            aside, header, .no-print { display: none !important; }
            .flex-1.flex.flex-col { overflow: visible !important; }
            .flex.h-screen { display: block !important; overflow: visible !important; }
            main { padding: 0 !important; overflow: visible !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans" x-data="{ sidebarOpen: false, isOffline: !navigator.onLine }"
      x-init="window.addEventListener('online', () => isOffline = false); window.addEventListener('offline', () => isOffline = true)">
    {{-- Offline Banner --}}
    <div x-show="isOffline" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-y-full" x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="-translate-y-full"
         class="fixed top-0 left-0 right-0 z-[200] bg-orange-500 text-white px-4 py-2.5 text-sm font-medium flex items-center justify-center gap-2 shadow-lg" style="display: none;">
        <span class="material-symbols-outlined text-lg">wifi_off</span>
        <span>You are currently offline. Some features may be unavailable.</span>
        <button @click="window.location.reload()" class="ml-2 underline hover:no-underline font-semibold">Retry</button>
    </div>
    {{-- Top Navigation Bar --}}
    <div class="bg-gray-900 text-gray-300 text-xs">
        <div class="max-w-full mx-auto px-4 lg:px-6 flex items-center justify-between h-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="hover:text-white transition flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">home</span>
                    Homepage
                </a>
                <a href="{{ route('shop') }}" class="hover:text-white transition flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">storefront</span>
                    Shop
                </a>
                <a href="{{ route('about') }}" class="hover:text-white transition hidden sm:flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">info</span>
                    About
                </a>
            </div>
            <div class="flex items-center gap-4">
                @if (in_array(Auth::user()->getRoleNames()->first() ?? '', ['super-admin', 'admin', 'secretary', 'treasurer', 'loan-officer', 'teller']))
                    <a href="{{ route('dashboard') }}" class="hover:text-white transition">Admin Dashboard</a>
                @endif
                <span class="text-gray-600">|</span>
                <span class="text-gray-500">{{ now()->format('D, M d, Y') }}</span>
            </div>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden" :class="isOffline ? 'pt-10' : ''" style="transition: padding-top 0.3s ease;">
        {{-- Mobile Overlay --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-30 lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

        <aside class="w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white flex flex-col shadow-xl fixed inset-y-0 left-0 z-40 transform transition-transform duration-300 lg:relative lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="p-5 border-b border-blue-700/50">
                <div class="flex items-center gap-3">
                    @if ($company->logo_path && $company->logo_url)
                        <img src="{{ $company->logo_url }}" alt="{{ $company->name }}"
                             class="w-10 h-10 rounded-xl object-contain shadow-lg" style="background: rgba(255,255,255,0.15); padding: 2px;">
                    @else
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-xl">account_balance</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-sm font-bold tracking-tight">{{ $company->name ?? 'NAPTIN Staff Thrift' }}</h1>
                        <p class="text-[10px] text-blue-200 tracking-wider uppercase">{{ $company->slogan ?? 'My Account' }}</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
                @php
                    $navItems = [
                        ['route' => 'portal.dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
                        ['route' => 'portal.savings', 'icon' => 'savings', 'label' => 'My Savings'],
                        ['route' => 'portal.loans', 'icon' => 'account_balance', 'label' => 'My Loans'],
                        ['route' => 'portal.shares', 'icon' => 'trending_up', 'label' => 'My Shares'],
                        ['route' => 'portal.purchases', 'icon' => 'receipt_long', 'label' => 'My Purchases'],
                        ['route' => 'portal.guarantors', 'icon' => 'group_add', 'label' => 'Guarantors'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs($item['route'] . '*') ? 'active' : 'text-blue-100' }}">
                        <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-4 lg:px-6 h-14 flex items-center justify-between shadow-sm flex-shrink-0">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">menu</span>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h2>
                </div>

                <div class="flex items-center gap-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition">
                        <span class="material-symbols-outlined text-[20px]">home</span>
                        <span class="hidden sm:inline">Home</span>
                    </a>

                    <a href="{{ route('portal.products') }}" class="relative flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition">
                        <span class="material-symbols-outlined text-[20px]">storefront</span>
                        <span class="hidden sm:inline">Shop</span>
                        @if ($hasNewProducts)
                            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-emerald-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center px-1 uppercase">New</span>
                        @endif
                    </a>

                    <a href="{{ route('portal.cart') }}" id="cart-badge-link" class="relative flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                        <span id="cart-badge" class="{{ $cartCount > 0 ? '' : 'hidden' }} absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    </a>

                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="relative flex items-center px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition">
                            <span class="material-symbols-outlined text-[20px]">notifications</span>
                            @if ($unreadCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                            <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-800">Notifications</p>
                                @if ($unreadCount > 0)
                                    <form method="POST" action="{{ route('portal.notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse ($recentNotifications as $notification)
                                    @php
                                        $data = $notification->data;
                                        $isUnread = is_null($notification->read_at);
                                        $icon = match($data['type'] ?? '') {
                                            'loan_status' => 'account_balance',
                                            'guarantor_request' => 'group_add',
                                            'withdrawal_status' => 'savings',
                                            'deposit_recorded' => 'savings',
                                            'share_purchased' => 'trending_up',
                                            'dividend_declared' => 'diversity_3',
                                            'payroll_compiled' => 'payments',
                                            'member_registered' => 'person_add',
                                            'broadcast' => 'campaign',
                                            default => 'notifications',
                                        };
                                    @endphp
                                    <div class="px-4 py-3 border-b border-gray-50 last:border-0 {{ $isUnread ? 'bg-blue-50/50' : '' }} hover:bg-gray-50 transition cursor-pointer"
                                         onclick="if(!this.querySelector('form')) window.location='{{ route('portal.notifications') }}'">
                                        <div class="flex items-start gap-3">
                                            <span class="material-symbols-outlined text-lg text-gray-400 mt-0.5">{{ $icon }}</span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs {{ $isUnread ? 'text-gray-900 font-medium' : 'text-gray-600' }} leading-snug">{{ $data['message'] ?? 'Notification' }}</p>
                                                <p class="text-[10px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if ($isUnread)
                                                <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center">
                                        <span class="material-symbols-outlined text-3xl text-gray-300">notifications_off</span>
                                        <p class="text-xs text-gray-400 mt-2">No notifications</p>
                                    </div>
                                @endforelse
                            </div>
                            <div class="px-4 py-2 border-t border-gray-100">
                                <a href="{{ route('portal.notifications') }}" class="block text-center text-xs text-blue-600 hover:text-blue-800 font-medium">View all notifications</a>
                            </div>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-gray-200 mx-1"></div>

                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center gap-2 hover:bg-gray-100 rounded-lg px-2 py-1.5 transition">
                            @if (Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}"
                                     class="w-8 h-8 rounded-full object-cover border border-gray-200">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-xs font-bold">
                                    {{ Auth::user()->initials }}
                                </div>
                            @endif
                            <div class="text-left hidden sm:block">
                                <p class="text-sm font-medium text-gray-700 leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-gray-400 leading-tight">Member</p>
                            </div>
                            <span class="material-symbols-outlined text-gray-400 text-lg">expand_more</span>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <span class="material-symbols-outlined text-lg">person</span>
                                My Profile
                            </a>
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                        <span class="material-symbols-outlined text-lg">logout</span>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">

    @if (session('success'))
                    <div class="fade-in mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="fade-in mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="{{ asset('vendor/js/alpine-components.js') }}"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset("sw.js") }}');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            function disableFormsOffline() {
                const isOffline = !navigator.onLine;
                document.querySelectorAll('form').forEach(function(form) {
                    if (form.method && form.method.toUpperCase() !== 'GET') {
                        const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                        submitBtns.forEach(function(btn) {
                            btn.disabled = isOffline;
                            if (isOffline) {
                                btn.dataset.wasDisabled = 'true';
                            } else {
                                delete btn.dataset.wasDisabled;
                            }
                        });
                    }
                });
            }
            disableFormsOffline();
            window.addEventListener('online', disableFormsOffline);
            window.addEventListener('offline', disableFormsOffline);
        });

        function updateCartBadge(count) {
            const badge = document.getElementById('cart-badge');
            if (!badge) return;
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('cart-updated', function(e) {
                updateCartBadge(e.detail.cart_count);
            });

            document.addEventListener('click', function(e) {
                const form = e.target.closest('form[data-cart]');
                if (!form) return;
                if (!navigator.onLine) {
                    e.preventDefault();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'You are offline. Please check your connection.', type: 'error' } }));
                    return;
                }
                e.preventDefault();

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        updateCartBadge(data.cart_count);
                        document.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                    }
                });
            });
        });
    </script>

    @if (session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('toast', { detail: { message: {!! json_encode(session('success')) !!}, type: 'success' } })))</script>
    @endif
    @if ($errors->any())
        <script>document.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('toast', { detail: { message: {!! json_encode($errors->first()) !!}, type: 'error' } })))</script>
    @endif

    @stack('scripts')
</body>
</html>
