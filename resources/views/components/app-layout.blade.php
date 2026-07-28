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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('vendor/js/chart.umd.min.js') }}"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
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
                @if (Auth::user()->member_id ?? false)
                    <a href="{{ route('portal.dashboard') }}" class="hover:text-white transition">Member Portal</a>
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

        {{-- Sidebar --}}
        <aside class="w-64 bg-gradient-to-b from-gray-900 to-gray-800 text-white flex flex-col shadow-xl fixed inset-y-0 left-0 z-40 transform transition-transform duration-300 lg:relative lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="p-5 border-b border-gray-700/50">
                <div class="flex items-center gap-3">
                    @if ($company->logo_path && $company->logo_url)
                        <img src="{{ $company->logo_url }}" alt="{{ $company->name }}"
                             class="w-10 h-10 rounded-xl object-contain shadow-lg" style="background: rgba(255,255,255,0.1); padding: 2px;">
                    @else
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                            <span class="material-symbols-outlined text-white text-xl">account_balance</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-sm font-bold tracking-tight">{{ $company->name ?? 'NAPTIN Staff Thrift' }}</h1>
                        <p class="text-[10px] text-gray-400 tracking-wider uppercase">{{ $company->slogan ?? 'Cooperative Society' }}</p>
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
                        ['route' => 'shares.index', 'icon' => 'trending_up', 'label' => 'Shares', 'permission' => 'view-shares'],
                        ['route' => 'purchases.index', 'icon' => 'shopping_cart', 'label' => 'Purchases', 'permission' => 'view-products'],
                        ['route' => 'dividends.index', 'icon' => 'diversity_3', 'label' => 'Dividends', 'permission' => 'view-dividends'],
                        ['route' => 'payroll.index', 'icon' => 'payments', 'label' => 'Payroll', 'permission' => 'view-payroll'],
                        ['route' => 'reports.index', 'icon' => 'description', 'label' => 'Reports', 'permission' => 'view-reports'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @can($item['permission'])
                        <a href="{{ route($item['route']) }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs($item['route'] . '.*') ? 'active text-white font-medium' : 'text-gray-300' }}">
                            <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
                            {{ $item['label'] }}
                            @if (!empty($item['badge']) && $unreadCount > 0)
                                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold min-w-[18px] h-[18px] rounded-full flex items-center justify-center">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </a>
                    @endcan
                @endforeach

                @if (Auth::user()->member_id)
                    <div class="pt-3 mt-3 border-t border-gray-700/50">
                        <p class="px-3 py-1 text-[10px] text-gray-500 uppercase tracking-wider">Member Portal</p>
                        <a href="{{ route('portal.dashboard') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                            My Account
                        </a>
                    </div>
                @endif

                @can('manage-users')
                    <div class="pt-3 mt-3 border-t border-gray-700/50">
                        <p class="px-3 py-1 text-[10px] text-gray-500 uppercase tracking-wider">Administration</p>
                        <a href="{{ route('admin.manage') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('admin.manage') || request()->routeIs('admin.data-import') || request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.regions.*') ? 'active text-white font-medium' : 'text-gray-300' }}">
                            <span class="material-symbols-outlined text-[20px]">settings</span>
                            Management
                        </a>
                    </div>
                @endcan
            </nav>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Header --}}
            <header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-3 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">menu</span>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h2>
                </div>
                <div class="flex items-center gap-2 lg:gap-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition">
                        <span class="material-symbols-outlined text-[20px]">home</span>
                        <span class="hidden sm:inline">Home</span>
                    </a>
                    <div class="h-6 w-px bg-gray-200 hidden md:block"></div>
                    <span class="text-sm text-gray-500 hidden md:inline">{{ now()->format('D, M d, Y') }}</span>
                    <div class="h-6 w-px bg-gray-200 hidden md:block"></div>

                    {{-- Notification Bell --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            <span class="material-symbols-outlined text-xl">notifications</span>
                            @if ($unreadCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[9px] font-bold min-w-[16px] h-[16px] rounded-full flex items-center justify-center px-1">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
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
                                    <div class="px-4 py-3 border-b border-gray-50 last:border-0 {{ $isUnread ? 'bg-blue-50/50' : '' }} hover:bg-gray-50 transition cursor-pointer"
                                         onclick="if(!this.querySelector('form')) window.location='{{ route('admin.notifications.index') }}'">
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
                                <a href="{{ route('admin.notifications.index') }}" class="block text-center text-xs text-blue-600 hover:text-blue-800 font-medium">View all notifications</a>
                            </div>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-gray-200"></div>

                    {{-- User Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center gap-2 hover:bg-gray-50 rounded-lg px-2 py-1.5 transition">
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
                                <p class="text-[10px] text-gray-400 leading-tight">{{ ucfirst(Auth::user()->getRoleNames()->first() ?? '') }}</p>
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

            {{-- Page Content --}}
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

    @livewireScripts

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
    </script>

    {{-- Confirmation Modal --}}
    <div x-data="confirmModal()" x-on:confirm.window="open($event.detail.title, $event.detail.message, $event.detail.onConfirm)"
         x-show="open" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="cancel()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 z-10"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-red-600 text-xl">warning</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-800" x-text="title"></h3>
            </div>
            <p class="text-sm text-gray-600 mb-6" x-text="message"></p>
            <div class="flex items-center justify-end gap-3">
                <button x-on:click="cancel()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</button>
                <button x-on:click="confirm()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">Confirm</button>
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
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium"
             :class="type === 'success' ? 'bg-emerald-600 text-white' : type === 'error' ? 'bg-red-600 text-white' : 'bg-blue-600 text-white'">
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

    @stack('scripts')
</body>
</html>
