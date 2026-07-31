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
    <title>{{ $title ?? 'NAPTIN Cooperative' }}</title>
    <script>
        (function () {
            try {
                var t = localStorage.getItem('app-theme');
                var d = t ? t === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (d) document.documentElement.classList.add('dark');
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('vendor/js/chart.umd.min.js') }}"></script>
    <style>
        html { overflow: hidden; }
        .sidebar-link { transition: all 0.15s ease; }
        .sidebar-link:hover { background: rgba(255,255,255,0.08); }
        .sidebar-link.active { background: rgba(255,255,255,0.12); border-right: 3px solid #3b82f6; }
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
    @livewireStyles
</head>
<body class="bg-slate-50 font-sans" x-data="{ sidebarOpen: false, serverDown: false }"
      x-init="setInterval(() => { fetch('/health').then(r => serverDown = false).catch(() => serverDown = true); }, 15000);
              fetch('/health').catch(() => serverDown = true);">
    {{-- Server Connection Banner --}}
    <div x-show="serverDown" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-y-full" x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="-translate-y-full"
         class="fixed top-0 left-0 right-0 z-[200] bg-red-500 text-white px-4 py-2.5 text-sm font-medium flex items-center justify-center gap-2 shadow-lg" style="display: none;">
        <span class="material-symbols-outlined text-lg">cloud_off</span>
        <span>Server connection lost. Please check your local server.</span>
        <button @click="window.location.reload()" class="ml-2 underline hover:no-underline font-semibold">Retry</button>
    </div>
    <div class="flex h-screen overflow-hidden" :class="serverDown ? 'pt-10' : ''" style="transition: padding-top 0.3s ease;">
        {{-- Mobile Overlay --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-30 lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

        {{-- Sidebar --}}
        <aside class="w-64 bg-[#0F172A] bg-linear-to-b from-[#0F172A] to-[#1a2332] text-white flex flex-col shadow-xl fixed inset-y-0 left-0 z-40 transform transition-transform duration-300 lg:relative lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="p-5 border-b border-white/10">
                <div class="flex items-center gap-3">
                    @if ($company->logo_path && $company->logo_url)
                        <img src="{{ $company->logo_url }}" alt="{{ $company->name }}"
                             class="w-10 h-10 rounded-[16px] object-contain shadow-lg" style="background: rgba(255,255,255,0.1); padding: 2px;">
                    @else
                        <div class="w-10 h-10 bg-[#0F172A] rounded-[16px] flex items-center justify-center shadow-lg">
                            <span class="material-symbols-outlined text-white text-xl">account_balance</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-sm font-bold tracking-tight">{{ $company->name ?? 'NAPTIN Staff Thrift' }}</h1>
                        <p class="text-[10px] text-slate-400 tracking-wider uppercase">{{ $company->slogan ?? 'Cooperative Society' }}</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
                @php
                    $navItems = [
                        ['route' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard', 'permission' => 'view-dashboard'],
                        ['route' => 'members.index', 'icon' => 'group', 'label' => 'Members', 'permission' => 'view-members'],
                        ['route' => 'savings.index', 'icon' => 'savings', 'label' => 'Savings', 'permission' => 'view-savings'],
                        ['route' => 'loans.index', 'icon' => 'account_balance', 'label' => 'Loans', 'permission' => 'view-loans'],
                        ['route' => 'purchases.index', 'icon' => 'shopping_cart', 'label' => 'Purchases', 'permission' => 'view-products'],
                        ['route' => 'dividends.index', 'icon' => 'diversity_3', 'label' => 'Dividends', 'permission' => 'view-dividends'],
                        ['route' => 'shares.index', 'icon' => 'trending_up', 'label' => 'Shares', 'permission' => 'view-shares'],
                        ['route' => 'payroll.index', 'icon' => 'payments', 'label' => 'Payroll', 'permission' => 'view-payroll'],
                        ['route' => 'reports.index', 'icon' => 'description', 'label' => 'Reports', 'permission' => 'view-reports'],
                        ['route' => 'ledger.accounts', 'icon' => 'account_balance', 'label' => 'Ledger', 'permission' => 'manage-users'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @can($item['permission'])
                        <a href="{{ route($item['route']) }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-[10px] text-sm {{ request()->routeIs($item['route'] . '.*') ? 'active text-white font-medium' : 'text-slate-300' }}">
                            <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
                            {{ $item['label'] }}
                            @if (!empty($item['badge']) && $unreadCount > 0)
                                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold min-w-[18px] h-[18px] rounded-full flex items-center justify-center">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </a>
                    @endcan
                @endforeach

                @if (Auth::user()->member_id)
                    <div class="pt-3 mt-3 border-t border-white/10">
                        <p class="px-3 py-1 text-[10px] text-slate-500 uppercase tracking-wider">Member Portal</p>
                        <a href="{{ route('portal.dashboard') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-[10px] text-sm text-slate-300">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                            My Account
                        </a>
                    </div>
                @endif


            </nav>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Header --}}
            <header class="bg-white border-b border-slate-200 px-4 lg:px-6 py-3 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-[10px] transition">
                        <span class="material-symbols-outlined text-xl">menu</span>
                    </button>
                    <h2 class="text-lg font-semibold text-[#0F172A]">{{ $title ?? 'Dashboard' }}</h2>
                </div>
                <div class="flex items-center gap-2 lg:gap-4">
                    <button onclick="AppTheme.toggle()" type="button" title="Toggle dark mode"
                            class="flex items-center justify-center p-2 rounded-[10px] text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition">
                        <span class="material-symbols-outlined text-[20px] dark:hidden">dark_mode</span>
                        <span class="material-symbols-outlined text-[20px] hidden dark:block">light_mode</span>
                    </button>
                    <button @click="window.dispatchEvent(new CustomEvent('open-command-palette'))"
                            class="flex items-center gap-2 px-3 py-2 rounded-[10px] text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-blue-600 transition">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                        <span class="hidden md:inline">Search</span>
                        <kbd class="hidden sm:inline bg-slate-100 px-1.5 py-0.5 rounded text-[10px] font-mono text-slate-500">⌘K</kbd>
                    </button>
                    <a href="{{ route('home') }}" class="flex items-center gap-1.5 px-3 py-2 rounded-[10px] text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-blue-600 transition">
                        <span class="material-symbols-outlined text-[20px]">home</span>
                        <span class="hidden sm:inline">Home</span>
                    </a>
                    <a href="{{ route('cart.index') }}" id="cart-badge-link" class="relative flex items-center gap-1.5 px-3 py-2 rounded-[10px] text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-blue-600 transition">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                        <span id="cart-badge" class="{{ $cartCount > 0 ? '' : 'hidden' }} absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    </a>
                    <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                    <span class="text-sm text-slate-500 hidden md:inline">{{ now()->format('D, M d, Y') }}</span>
                    <div class="h-6 w-px bg-slate-200 hidden md:block"></div>

                    {{-- Notification Bell --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-[10px] transition">
                            <span class="material-symbols-outlined text-xl">notifications</span>
                            @if ($unreadCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[9px] font-bold min-w-[16px] h-[16px] rounded-full flex items-center justify-center px-1">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-[16px] shadow-lg border border-slate-100 py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                                <p class="text-sm font-semibold text-[#0F172A]">Notifications</p>
                                @if ($unreadCount > 0)
                                    <form method="POST" action="{{ route('admin.notifications.mark-all') }}">
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
                                            'admin_password_reset' => 'lock_reset',
                                            'deposit_recorded' => 'savings',
                                            'share_purchased' => 'trending_up',
                                            'dividend_declared' => 'diversity_3',
                                            'payroll_compiled' => 'payments',
                                            'member_registered' => 'person_add',
                                            'broadcast' => 'campaign',
                                            default => 'notifications',
                                        };
                                    @endphp
                                    <div class="px-4 py-3 border-b border-slate-50 last:border-0 {{ $isUnread ? 'bg-slate-50/80' : '' }} hover:bg-slate-50 transition cursor-pointer"
                                         onclick="if(!this.querySelector('form')) window.location='{{ route('admin.notifications.index') }}'">
                                        <div class="flex items-start gap-3">
                                            <span class="material-symbols-outlined text-lg text-slate-400 mt-0.5">{{ $icon }}</span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs {{ $isUnread ? 'text-[#0F172A] font-medium' : 'text-slate-600' }} leading-snug">{{ $data['message'] ?? 'Notification' }}</p>
                                                <p class="text-[10px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if ($isUnread)
                                                <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center">
                                        <span class="material-symbols-outlined text-3xl text-slate-300">notifications_off</span>
                                        <p class="text-xs text-slate-400 mt-2">No notifications</p>
                                    </div>
                                @endforelse
                            </div>
                            <div class="px-4 py-2 border-t border-slate-100">
                                <a href="{{ route('admin.notifications.index') }}" class="block text-center text-xs text-blue-600 hover:text-blue-800 font-medium">View all notifications</a>
                            </div>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-slate-200"></div>

                    {{-- User Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center gap-2 hover:bg-slate-50 rounded-[10px] px-2 py-1.5 transition">
                            @if (Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}"
                                     class="w-8 h-8 rounded-full object-cover border border-slate-200">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#0F172A] to-slate-700 flex items-center justify-center text-white text-xs font-bold">
                                    {{ Auth::user()->initials }}
                                </div>
                            @endif
                            <div class="text-left hidden sm:block">
                                <p class="text-sm font-medium text-slate-700 leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-slate-400 leading-tight">{{ ucfirst(Auth::user()->getRoleNames()->first() ?? '') }}</p>
                            </div>
                            <span class="material-symbols-outlined text-slate-400 text-lg">expand_more</span>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-[16px] shadow-lg border border-slate-100 py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-sm font-medium text-[#0F172A]">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                                <span class="material-symbols-outlined text-lg">person</span>
                                My Profile
                            </a>
                            @can('manage-users')
                                <a href="{{ route('admin.manage') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                                    <span class="material-symbols-outlined text-lg">settings</span>
                                    Settings
                                </a>
                            @endcan
                            <div class="border-t border-slate-100 mt-1 pt-1">
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

            {{-- Page Content --}}
            <main class="flex-1 overflow-auto p-4 lg:p-6">
                @if (session('success'))
                    <div class="fade-in mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-[10px] text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="fade-in mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">
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

    @livewireScripts

    <script src="{{ asset('vendor/js/alpine-components.js') }}"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset("sw.js") }}').then((reg) => {
                    reg.addEventListener('updatefound', () => {
                        const newWorker = reg.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                newWorker.postMessage({ type: 'SKIP_WAITING' });
                            }
                        });
                    });
                });
            });
            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (refreshing) return;
                refreshing = true;
                window.location.reload();
            });
        }
    </script>

    {{-- Confirmation Modal --}}
    <div x-data="confirmModal()" x-on:confirm.window="open($event.detail.title, $event.detail.message, $event.detail.onConfirm)"
         x-show="open" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="cancel()"></div>
        <div class="relative bg-white rounded-[16px] shadow-2xl max-w-md w-full mx-4 p-6 z-10"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-red-600 text-xl">warning</span>
                </div>
                <h3 class="text-lg font-semibold text-[#0F172A]" x-text="title"></h3>
            </div>
            <p class="text-sm text-slate-600 mb-6" x-text="message"></p>
            <div class="flex items-center justify-end gap-3">
                <button x-on:click="cancel()" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-[10px] transition">Cancel</button>
                <button x-on:click="confirm()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-[10px] transition">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        function confirmModal() {
            return {
                open: false,
                title: '',
                message: '',
                onConfirm: null,
                open(title, message, onConfirm) {
                    this.title = title;
                    this.message = message;
                    this.onConfirm = onConfirm;
                    this.open = true;
                },
                cancel() {
                    this.open = false;
                },
                confirm() {
                    if (this.onConfirm) this.onConfirm();
                    this.open = false;
                }
            }
        }

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
        });

        function deleteConfirm(formId) {
            window.dispatchEvent(new CustomEvent('confirm', {
                detail: {
                    title: 'Confirm Delete',
                    message: 'Are you sure you want to delete this item? This action cannot be undone.',
                    onConfirm: () => document.getElementById(formId).submit()
                }
            }));
        }
    </script>

    {{-- Toast Notification --}}
    <div x-data="{ toasts: [], show: false, message: '', type: 'success' }"
         x-on:toast.window="message = $event.detail.message; type = $event.detail.type || 'success'; show = true; setTimeout(() => show = false, 4000)"
         x-show="show" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 max-w-sm">
        <div class="flex items-center gap-3 px-4 py-3 rounded-[16px] shadow-lg text-sm font-medium"
             :class="type === 'success' ? 'bg-emerald-600 text-white' : type === 'error' ? 'bg-red-600 text-white' : 'bg-[#0F172A] text-white'">
            <span class="material-symbols-outlined text-lg" x-text="type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'"></span>
            <span x-text="message"></span>
        </div>
    </div>

    @if (session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('toast', { detail: { message: {!! json_encode(session('success')) !!}, type: 'success' } })))</script>
    @endif
    @if ($errors->any())
        <script>document.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('toast', { detail: { message: {!! json_encode($errors->first()) !!}, type: 'error' } })))</script>
    @endif

    <x-command-palette :new-member-url="auth()->user()?->can('create-members') ? route('members.create') : ''" />

    @stack('scripts')
</body>
</html>
