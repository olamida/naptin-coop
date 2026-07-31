<x-app-layout title="Unified Onboarding Wizard">
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.data-import') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Unified Onboarding Wizard</h2>
        </div>

        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.data-import') }}" class="hover:text-blue-600 transition">Data Import</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-[#0F172A] font-medium">Onboarding Wizard</span>
        </div>

        <div class="bg-gradient-to-r from-[#0F172A] to-[#1e3a5f] rounded-[16px] p-6 text-white">
            <div class="flex items-start gap-4">
                <span class="material-symbols-outlined text-4xl mt-1">rocket_launch</span>
                <div>
                    <h3 class="font-semibold text-lg">One Excel file. Full onboarding.</h3>
                    <p class="text-sm text-slate-300 mt-1 leading-relaxed">
                        Upload a single workbook with <strong class="text-white">members</strong>, <strong class="text-white">opening_savings</strong> and
                        <strong class="text-white">shares</strong> sheets. Members are created with savings &amp; share accounts,
                        opening balances are posted, and share allotments are recorded — all in one atomic transaction.
                    </p>
                    <div class="flex items-center gap-2 mt-3 text-[12px] text-emerald-300">
                        <span class="material-symbols-outlined text-[14px]">verified</span>
                        All-or-nothing: if any row fails hard, the entire batch is rolled back.
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-[#0F172A]">Workbook Structure</h3>
                <a href="{{ route('admin.onboarding.template') }}" class="inline-flex items-center gap-1.5 text-xs font-medium bg-[#0F172A] text-white px-3 py-2 rounded-[10px] hover:bg-slate-800 transition">
                    <span class="material-symbols-outlined text-[14px]">download</span>
                    Download Template (.xlsx)
                </a>
            </div>

            <div class="space-y-3">
                <div class="bg-slate-50 rounded-[10px] p-4">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-[#0F172A]">
                            <span class="material-symbols-outlined text-[16px] text-indigo-600">group</span> members
                        </span>
                        <span class="text-[11px] text-slate-500">Required: staff_id, first_name, last_name, region</span>
                    </div>
                    <code class="block bg-white border border-slate-200 rounded p-2 text-[11px] text-slate-700 font-mono mt-2">
                        staff_id, first_name, last_name, middle_name, region, email, phone, gender, date_of_birth, employment_date, address, state_of_origin, nin, grade_level, monthly_salary, status, external_reference
                    </code>
                    <p class="text-[11px] text-slate-500 mt-1.5">Auto-creates a savings account (₦0) and share account (0 shares). Unknown regions are auto-created.</p>
                </div>

                <div class="bg-slate-50 rounded-[10px] p-4">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-[#0F172A]">
                            <span class="material-symbols-outlined text-[16px] text-emerald-600">savings</span> opening_savings
                        </span>
                        <span class="text-[11px] text-slate-500">Required: staff_id, amount</span>
                    </div>
                    <code class="block bg-white border border-slate-200 rounded p-2 text-[11px] text-slate-700 font-mono mt-2">
                        staff_id, amount, transaction_date, notes, external_reference
                    </code>
                    <p class="text-[11px] text-slate-500 mt-1.5">Posts the opening balance as a completed deposit (source: opening_balance).</p>
                </div>

                <div class="bg-slate-50 rounded-[10px] p-4">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-[#0F172A]">
                            <span class="material-symbols-outlined text-[16px] text-pink-600">token</span> shares
                        </span>
                        <span class="text-[11px] text-slate-500">Required: staff_id, shares</span>
                    </div>
                    <code class="block bg-white border border-slate-200 rounded p-2 text-[11px] text-slate-700 font-mono mt-2">
                        staff_id, shares, share_price, notes, external_reference
                    </code>
                    <p class="text-[11px] text-slate-500 mt-1.5">share_price defaults to the member's account price (₦100 if unset). Adds to total_shares and total_value.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <form action="{{ route('admin.onboarding.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select Onboarding Workbook</label>
                        <input type="file" name="import_file" accept=".xlsx,.xls" required
                               class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-[10px] file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    </div>

                    @if ($errors->has('import_file'))
                        <p class="text-red-600 text-sm">{{ $errors->first('import_file') }}</p>
                    @endif

                    <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
                        <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">
                            Run Onboarding
                        </button>
                        <a href="{{ route('admin.data-import') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
