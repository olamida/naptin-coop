<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F172A">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>Forgot Password - NAPTIN Cooperative</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    @php $company = \App\Models\Company::instance(); @endphp
    <div class="w-full max-w-md">
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-8">
            <div class="text-center mb-6">
                @if ($company->logo_url)
                    <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="h-14 mx-auto mb-3 object-contain">
                @else
                    <div class="w-16 h-16 bg-[#0F172A] rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-white text-3xl">lock_reset</span>
                    </div>
                @endif
                <h1 class="text-xl font-bold text-[#0F172A]">Forgot Password?</h1>
                <p class="text-sm text-slate-500 mt-2">Enter your email address and we'll send you a link to reset your password.</p>
            </div>

            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[10px] mb-6 text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">check_circle</span>
                    {{ session('status') }}
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

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-[10px] focus:ring-2 focus:ring-[#0F172A] focus:border-[#0F172A] outline-none transition text-sm">
                </div>

                <button type="submit" class="w-full bg-[#0F172A] hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-[10px] transition text-sm">
                    Send Reset Link
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-[#0F172A] font-medium inline-flex items-center gap-1 transition">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
