<x-app-layout title="Compile Payroll">
    <div class="max-w-xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('payroll.index') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Compile Payroll</h2>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <p class="text-sm text-slate-600 mb-6">
                Compile payroll for a specific month and year. This will calculate expected deductions (member's preferred savings amount or 10% fallback, 5% share contribution, and loan repayments) for all active members.
            </p>

            <div class="space-y-4">
                <form method="POST" action="{{ route('payroll.compile.store') }}" class="p-4 bg-slate-50 rounded-[10px] border border-slate-200 space-y-4">
                    @csrf
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800">Standard Compile</h4>
                            <p class="text-xs text-slate-500">Export → upload actuals — requires salary dept. follow-up</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <select name="year" required class="px-2 py-1.5 border border-slate-300 rounded-[8px] text-xs outline-none">
                                @for ($y = date('Y') + 1; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <select name="month_number" required class="px-2 py-1.5 border border-slate-300 rounded-[8px] text-xs outline-none">
                                @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                                    <option value="{{ $index + 1 }}" {{ date('m') == $index + 1 ? 'selected' : '' }}>{{ $month }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-1.5 rounded-[8px] text-xs font-medium transition whitespace-nowrap">
                                Compile
                            </button>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('payroll.compile-and-lock') }}" class="p-4 bg-emerald-50 rounded-[10px] border border-emerald-200 space-y-4">
                    @csrf
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-emerald-800">Compile & Lock</h4>
                            <p class="text-xs text-emerald-600">Auto-calculate all deductions and mark completed immediately. No upload step needed.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <select name="year" required class="px-2 py-1.5 border border-emerald-300 rounded-[8px] text-xs outline-none">
                                @for ($y = date('Y') + 1; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <select name="month_number" required class="px-2 py-1.5 border border-emerald-300 rounded-[8px] text-xs outline-none">
                                @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                                    <option value="{{ $index + 1 }}" {{ date('m') == $index + 1 ? 'selected' : '' }}>{{ $month }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-1.5 rounded-[8px] text-xs font-medium transition whitespace-nowrap">
                                Compile & Lock
                            </button>
                        </div>
                    </div>
                </form>

                <div class="flex">
                    <a href="{{ route('payroll.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
