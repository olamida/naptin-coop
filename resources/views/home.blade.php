<x-public-layout title="Home">
    {{-- Hero Section --}}
    <section class="relative text-white hero-gradient">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
            <div class="max-w-2xl">
                <h1 class="text-4xl lg:text-5xl font-extrabold leading-tight mb-6">
                    {{ $company->slogan ?? 'Building Financial Independence Together' }}
                </h1>
                <p class="text-lg text-blue-100 leading-relaxed mb-8">
                    Join the NAPTIN Staff Thrift Cooperative Society and enjoy competitive savings rates, affordable loans, share dividends, and a wide range of products through our cooperative shop.
                </p>
                <div class="flex flex-wrap gap-4">
                        <a href="{{ route('shop') }}" class="bg-white text-blue-700 hover:bg-slate-100 px-6 py-3 rounded-[10px] font-semibold text-sm transition shadow-lg">
                        Browse Shop
                    </a>
                    @guest
                        <a href="{{ route('login') }}" class="border-2 border-white/40 text-white hover:bg-white/10 px-6 py-3 rounded-[10px] font-semibold text-sm transition">
                            Member Login
                        </a>
                    @else
                        @if (Auth::user()->member_id ?? false)
                            <a href="{{ route('portal.dashboard') }}" class="border-2 border-white/40 text-white hover:bg-white/10 px-6 py-3 rounded-[10px] font-semibold text-sm transition">
                                My Dashboard
                            </a>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- Quick Stats --}}
    <section class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                    <p class="text-2xl lg:text-3xl font-bold text-blue-600">{{ \App\Models\Member::where('status', 'active')->count() }}+</p>
                    <p class="text-sm text-slate-500 mt-1">Active Members</p>
                </div>
                <div>
                    <p class="text-2xl lg:text-3xl font-bold text-blue-600">{{ \App\Models\Region::count() }}</p>
                    <p class="text-sm text-slate-500 mt-1">Regional Centers</p>
                </div>
                <div>
                    <p class="text-2xl lg:text-3xl font-bold text-blue-600">{{ \App\Models\LoanProduct::count() }}</p>
                    <p class="text-sm text-slate-500 mt-1">Loan Products</p>
                </div>
                <div>
                    <p class="text-2xl lg:text-3xl font-bold text-blue-600">{{ \App\Models\Product::where('enabled', true)->count() }}</p>
                    <p class="text-sm text-slate-500 mt-1">Shop Products</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured Products --}}
    @if ($featuredProducts->count())
        <section class="bg-slate-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0F172A]">Featured Products</h2>
                        <p class="text-sm text-slate-500 mt-1">Browse items available in our cooperative shop</p>
                    </div>
                    <a href="{{ route('shop') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1 transition">
                        View All
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach ($featuredProducts as $product)
                        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition group">
                            <div class="h-44 bg-slate-100 flex items-center justify-center overflow-hidden">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <span class="material-symbols-outlined text-5xl text-gray-300">inventory_2</span>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-[#0F172A] text-sm mb-1">{{ $product->name }}</h3>
                                <p class="text-xs text-slate-500 line-clamp-2 mb-3">{{ $product->description ?? 'No description available' }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-[#0F172A]">₦{{ number_format($product->unit_price, 2) }}</span>
                                    <a href="{{ route('shop') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">View</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- About Summary --}}
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-2xl font-bold text-[#0F172A] mb-4">About Our Cooperative</h2>
                    <p class="text-slate-600 leading-relaxed mb-6">
                        The NAPTIN Staff Thrift Cooperative Society is dedicated to promoting the financial welfare of staff members through systematic savings, affordable credit facilities, and shared investment opportunities.
                    </p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                            <span class="text-sm text-slate-600">Competitive savings interest rates</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                            <span class="text-sm text-slate-600">Affordable loan products with flexible repayment</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                            <span class="text-sm text-slate-600">Annual dividend distributions</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                            <span class="text-sm text-slate-600">Wide range of products at cooperative prices</span>
                        </li>
                    </ul>
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-2 bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2.5 rounded-[10px] text-sm font-medium transition">
                        Learn More About Us
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </a>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-8">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white rounded-[16px] p-4 text-center shadow-sm">
                            <span class="material-symbols-outlined text-blue-600 text-3xl mb-2">savings</span>
                            <p class="text-sm font-semibold text-[#0F172A]">Savings</p>
                            <p class="text-xs text-slate-500">Grow your savings securely</p>
                        </div>
                        <div class="bg-white rounded-[16px] p-4 text-center shadow-sm">
                            <span class="material-symbols-outlined text-blue-600 text-3xl mb-2">account_balance</span>
                            <p class="text-sm font-semibold text-[#0F172A]">Loans</p>
                            <p class="text-xs text-slate-500">Access affordable credit</p>
                        </div>
                        <div class="bg-white rounded-[16px] p-4 text-center shadow-sm">
                            <span class="material-symbols-outlined text-blue-600 text-3xl mb-2">trending_up</span>
                            <p class="text-sm font-semibold text-[#0F172A]">Shares</p>
                            <p class="text-xs text-slate-500">Invest and earn dividends</p>
                        </div>
                        <div class="bg-white rounded-[16px] p-4 text-center shadow-sm">
                            <span class="material-symbols-outlined text-blue-600 text-3xl mb-2">storefront</span>
                            <p class="text-sm font-semibold text-[#0F172A]">Shop</p>
                            <p class="text-xs text-slate-500">Buy on cooperative terms</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact Quick Info --}}
    <section class="bg-slate-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold text-[#0F172A] mb-2">Get In Touch</h2>
            <p class="text-sm text-slate-500 mb-8">Have questions? We'd love to hear from you.</p>
            <div class="flex flex-wrap justify-center gap-6">
                @if ($company->phone)
                    <div class="flex items-center gap-3 bg-white px-6 py-4 rounded-[16px] shadow-sm border border-slate-200">
                        <span class="material-symbols-outlined text-blue-600">phone</span>
                        <div class="text-left">
                            <p class="text-xs text-slate-500">Call Us</p>
                            <p class="text-sm font-semibold text-[#0F172A]">{{ $company->phone }}</p>
                        </div>
                    </div>
                @endif
                @if ($company->email)
                    <div class="flex items-center gap-3 bg-white px-6 py-4 rounded-[16px] shadow-sm border border-slate-200">
                        <span class="material-symbols-outlined text-blue-600">mail</span>
                        <div class="text-left">
                            <p class="text-xs text-slate-500">Email Us</p>
                            <p class="text-sm font-semibold text-[#0F172A]">{{ $company->email }}</p>
                        </div>
                    </div>
                @endif
                @if ($company->address)
                    <div class="flex items-center gap-3 bg-white px-6 py-4 rounded-[16px] shadow-sm border border-slate-200">
                        <span class="material-symbols-outlined text-blue-600">location_on</span>
                        <div class="text-left">
                            <p class="text-xs text-slate-500">Visit Us</p>
                            <p class="text-sm font-semibold text-[#0F172A]">{{ $company->address }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-public-layout>
