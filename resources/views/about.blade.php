<x-public-layout title="About Us">
    {{-- Hero --}}
    <section class="hero-gradient text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl lg:text-4xl font-extrabold mb-4">About Our Cooperative</h1>
            <p class="text-lg text-blue-100 max-w-2xl mx-auto">
                {{ $company->slogan ?? 'Building Financial Independence Together' }}
            </p>
        </div>
    </section>

    {{-- Mission & Vision --}}
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">flag</span>
                        Our Mission
                    </h2>
                    <p class="text-gray-600 leading-relaxed">
                        To promote the welfare and financial well-being of NAPTIN staff members through
                        systematic savings mobilization, provision of affordable credit facilities, and
                        investment in products and services that enhance the quality of life of our members.
                    </p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">visibility</span>
                        Our Vision
                    </h2>
                    <p class="text-gray-600 leading-relaxed">
                        To be the leading staff cooperative society, recognized for financial strength,
                        member satisfaction, and innovative products that empower every staff member
                        to achieve their financial goals and build a secure future.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- What We Offer --}}
    <section class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-10">What We Offer</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-2xl">savings</span>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Savings Account</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Build your savings with competitive interest rates. Members can make deposits
                        and withdrawals with ease, and watch their savings grow over time.
                    </p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-green-600 text-2xl">account_balance</span>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Affordable Loans</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Access various loan products including regular, emergency, educational, and special loans
                        with flexible repayment terms and competitive interest rates.
                    </p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-purple-600 text-2xl">trending_up</span>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Share Holdings</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Own a piece of the cooperative through share purchases. Earn annual dividends
                        based on the cooperative's performance and your shareholding.
                    </p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-orange-600 text-2xl">storefront</span>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Cooperative Shop</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Access a wide range of products through our cooperative shop.
                        Enjoy cash purchases or convenient hire purchase payment plans.
                    </p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-red-600 text-2xl">diversity_3</span>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Annual Dividends</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Share in the cooperative's success. Annual profits are distributed
                        proportionally to members based on their shareholdings.
                    </p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-teal-600 text-2xl">payments</span>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Salary Deductions</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Convenient monthly salary deductions for savings contributions, loan repayments,
                        and purchase installments. No need for manual payments each month.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Membership --}}
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">How to Join</h2>
                <p class="text-gray-600 mb-8">Becoming a member of the NAPTIN Staff Thrift Cooperative Society is straightforward.</p>
                <div class="grid md:grid-cols-3 gap-6 text-left">
                    <div class="text-center">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center text-lg font-bold mx-auto mb-3">1</div>
                        <h4 class="font-semibold text-gray-800 text-sm mb-1">Apply</h4>
                        <p class="text-xs text-gray-500">Complete the membership application form at your regional center</p>
                    </div>
                    <div class="text-center">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center text-lg font-bold mx-auto mb-3">2</div>
                        <h4 class="font-semibold text-gray-800 text-sm mb-1">Pay Membership Fee</h4>
                        <p class="text-xs text-gray-500">Pay the required membership fee and start your monthly thrift contributions</p>
                    </div>
                    <div class="text-center">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center text-lg font-bold mx-auto mb-3">3</div>
                        <h4 class="font-semibold text-gray-800 text-sm mb-1">Get Started</h4>
                        <p class="text-xs text-gray-500">Receive your member credentials and start accessing all cooperative benefits</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact --}}
    <section class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Contact Us</h2>
            <p class="text-sm text-gray-500 mb-8">We're here to answer your questions about membership and services.</p>
            <div class="flex flex-wrap justify-center gap-6">
                @if ($company->phone)
                    <div class="flex items-center gap-3 bg-white px-6 py-4 rounded-xl shadow-sm border border-gray-100">
                        <span class="material-symbols-outlined text-blue-600">phone</span>
                        <div class="text-left">
                            <p class="text-xs text-gray-500">Call Us</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $company->phone }}</p>
                        </div>
                    </div>
                @endif
                @if ($company->email)
                    <div class="flex items-center gap-3 bg-white px-6 py-4 rounded-xl shadow-sm border border-gray-100">
                        <span class="material-symbols-outlined text-blue-600">mail</span>
                        <div class="text-left">
                            <p class="text-xs text-gray-500">Email Us</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $company->email }}</p>
                        </div>
                    </div>
                @endif
                @if ($company->address)
                    <div class="flex items-center gap-3 bg-white px-6 py-4 rounded-xl shadow-sm border border-gray-100">
                        <span class="material-symbols-outlined text-blue-600">location_on</span>
                        <div class="text-left">
                            <p class="text-xs text-gray-500">Visit Us</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $company->address }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-public-layout>
