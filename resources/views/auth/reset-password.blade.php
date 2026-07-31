<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F172A">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>Reset Password - NAPTIN Cooperative</title>
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
                        <span class="material-symbols-outlined text-white text-3xl">lock</span>
                    </div>
                @endif
                <h1 class="text-xl font-bold text-[#0F172A]">Reset Password</h1>
                <p class="text-sm text-slate-500 mt-2">Enter your new password below.</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] mb-6 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-[10px] focus:ring-2 focus:ring-[#0F172A] focus:border-[#0F172A] outline-none transition text-sm"
                        {{ isset($email) ? 'readonly' : '' }}>
                </div>

                <div class="mb-4" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password" name="password" required
                            class="w-full px-4 py-2.5 pr-10 border border-slate-300 rounded-[10px] focus:ring-2 focus:ring-[#0F172A] focus:border-[#0F172A] outline-none transition text-sm">
                        <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="mb-6" x-data="{ show: false }">
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required
                            class="w-full px-4 py-2.5 pr-10 border border-slate-300 rounded-[10px] focus:ring-2 focus:ring-[#0F172A] focus:border-[#0F172A] outline-none transition text-sm">
                        <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#0F172A] hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-[10px] transition text-sm">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</body>
</html>
