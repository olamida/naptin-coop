<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>Reset Password - NAPTIN Cooperative</title>
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
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-green-600 text-3xl">lock</span>
                    </div>
                @endif
                <h1 class="text-2xl font-bold text-gray-800">Reset Password</h1>
                <p class="text-sm text-gray-500 mt-2">Enter your new password below.</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
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
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        {{ isset($email) ? 'readonly' : '' }}>
                </div>

                <div class="mb-4" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password" name="password" required
                            class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="mb-6" x-data="{ show: false }">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required
                            class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</body>
</html>
