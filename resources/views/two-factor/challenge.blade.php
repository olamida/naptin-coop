<x-app-layout title="Two-Factor Authentication">
    <div class="min-h-[80vh] flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-[16px] shadow-lg border border-slate-200 p-8">
                <div class="text-center mb-6">
                    <span class="material-symbols-outlined text-5xl text-[#0F172A] mb-3">security</span>
                    <h1 class="text-xl font-bold text-[#0F172A]">Two-Factor Authentication</h1>
                    <p class="text-sm text-slate-500 mt-1">Enter the authentication code from your authenticator app.</p>
                </div>

                @if (session('info'))
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg text-sm mb-4">
                        {{ session('info') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('two-factor.challenge.verify') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Authentication Code</label>
                        <input type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required
                               class="w-full px-3 py-3 text-center text-2xl tracking-[0.5em] border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="000000">
                    </div>
                    <button type="submit" class="w-full bg-[#0F172A] hover:bg-slate-800 text-white py-2.5 rounded-[10px] text-sm font-medium transition">
                        Verify
                    </button>
                </form>

                <div class="mt-6 pt-4 border-t border-slate-200">
                    <details class="group">
                        <summary class="text-sm text-slate-500 hover:text-slate-700 cursor-pointer list-none flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-base">key</span>
                            Use a recovery code instead
                        </summary>
                        <form method="POST" action="{{ route('two-factor.recovery') }}" class="mt-3 space-y-3">
                            @csrf
                            <div>
                                <input type="text" name="code" placeholder="Recovery code" required
                                       class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <button type="submit" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2 rounded-[10px] text-sm font-medium transition">
                                Verify Recovery Code
                            </button>
                        </form>
                    </details>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
