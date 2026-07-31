@php use Illuminate\Support\Facades\Session; @endphp
<x-app-layout title="Change Your Password">
    <div class="min-h-[80vh] flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-[16px] shadow-lg border border-slate-200 p-8">
                <div class="text-center mb-6">
                    <span class="material-symbols-outlined text-5xl text-[#0F172A] mb-3">lock_reset</span>
                    <h1 class="text-xl font-bold text-[#0F172A]">Change Your Password</h1>
                    <p class="text-sm text-slate-500 mt-1">Your password has been reset by an administrator. Please set a new password to continue.</p>
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

                <form method="POST" action="{{ route('password.force.update') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Current Password</label>
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" name="current_password" required
                                   class="w-full px-3 py-2 pr-10 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" required minlength="8"
                                   class="w-full px-3 py-2 pr-10 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" required minlength="8"
                                   class="w-full px-3 py-2 pr-10 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="text-xs text-slate-400 space-y-1">
                        <p class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">info</span> Minimum 8 characters</p>
                    </div>

                    <button type="submit" class="w-full bg-[#0F172A] hover:bg-slate-800 text-white py-2.5 rounded-[10px] text-sm font-medium transition">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
