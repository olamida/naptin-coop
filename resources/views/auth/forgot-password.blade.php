<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>Forgot Password - NAPTIN Cooperative</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    @php $company = \App\Models\Company::instance(); @endphp
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-6">
                @if ($company->logo_url)
                    <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="h-14 mx-auto mb-3 object-contain">
                @else
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-3xl">lock_reset</span>
                    </div>
                @endif
                <h1 class="text-2xl font-bold text-gray-800">Forgot Password?</h1>
                <p class="text-sm text-gray-500 mt-2">Enter your email address and we'll send you a link to reset your password.</p>
            </div>

            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
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
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition">
                    Send Reset Link
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
