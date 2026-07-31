<x-app-layout title="Recovery Codes">
    <div class="min-h-[80vh] flex items-center justify-center">
        <div class="w-full max-w-lg">
            <div class="bg-white rounded-[16px] shadow-lg border border-slate-200 p-8">
                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="text-center mb-6">
                    <span class="material-symbols-outlined text-5xl text-[#0F172A] mb-3">key</span>
                    <h1 class="text-xl font-bold text-[#0F172A]">Recovery Codes</h1>
                    <p class="text-sm text-slate-500 mt-1">
                        @if ($show)
                            Save these recovery codes in a secure place. Each code can only be used once.
                        @else
                            Your recovery codes are hidden for security. Enter your password to view or regenerate them.
                        @endif
                    </p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($show && count($codes))
                    <div class="bg-slate-50 rounded-[10px] p-4 mb-4">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($codes as $code)
                                <code class="font-mono text-sm text-[#0F172A] bg-white px-3 py-2 rounded-lg border border-slate-200 select-all text-center">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg text-sm mb-4 flex items-start gap-2">
                        <span class="material-symbols-outlined text-lg flex-shrink-0">warning</span>
                        <span>Store these codes securely. If you lose access to your authenticator app, these codes are your only way to access your account.</span>
                    </div>
                @endif

                <div class="space-y-3">
                    @if (!$show)
                        <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password to View Codes</label>
                                <input type="password" name="confirm_password" required
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <button type="submit" class="w-full bg-[#0F172A] hover:bg-slate-800 text-white py-2.5 rounded-[10px] text-sm font-medium transition">
                                View Recovery Codes
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password to Regenerate</label>
                                <input type="password" name="confirm_password" required
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <button type="submit" class="w-full bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 py-2.5 rounded-[10px] text-sm font-medium transition">
                                Generate New Recovery Codes
                            </button>
                        </form>
                        <div class="text-center">
                            <a href="{{ route('two-factor.challenge') }}" class="text-sm text-blue-600 hover:text-blue-800">Continue to Dashboard</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
