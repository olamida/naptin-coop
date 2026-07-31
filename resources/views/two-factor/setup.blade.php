<x-app-layout title="Set Up Two-Factor Authentication">
    <div class="min-h-[80vh] flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-[16px] shadow-lg border border-slate-200 p-8">
                <div class="text-center mb-6">
                    <span class="material-symbols-outlined text-5xl text-[#0F172A] mb-3">security</span>
                    <h1 class="text-xl font-bold text-[#0F172A]">Set Up Two-Factor Authentication</h1>
                    <p class="text-sm text-slate-500 mt-1">Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
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

                <div class="flex justify-center mb-6">
                    <div class="bg-white p-4 rounded-[16px] border border-slate-200 shadow-sm">
                        {!! $qrSvg !!}
                    </div>
                </div>

                <div class="mb-4 text-center">
                    <p class="text-xs text-slate-400 mb-1">Or enter this code manually:</p>
                    <code class="text-sm font-mono bg-slate-100 px-3 py-1.5 rounded-lg text-slate-700 select-all">{{ $secret }}</code>
                </div>

                <form method="POST" action="{{ route('two-factor.setup.confirm') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Verify Code</label>
                        <input type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="off" required
                               class="w-full px-3 py-3 text-center text-2xl tracking-[0.5em] border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="000000">
                        <p class="text-xs text-slate-400 mt-1">Enter the 6-digit code from your authenticator app.</p>
                    </div>
                    <button type="submit" class="w-full bg-[#0F172A] hover:bg-slate-800 text-white py-2.5 rounded-[10px] text-sm font-medium transition">
                        Enable Two-Factor Authentication
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
