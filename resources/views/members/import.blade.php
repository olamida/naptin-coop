<x-app-layout title="Import Members">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.data-import') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <h2 class="text-2xl font-bold text-[#0F172A]">Import Members</h2>
        </div>

        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.data-import') }}" class="hover:text-blue-600 transition">Data Import</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-[#0F172A] font-medium">Import Members</span>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
                <div class="bg-blue-50 border border-blue-200 rounded-[10px] p-4 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-blue-800">Expected Column Headers</h3>
                    <a href="{{ route('members.download-template') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">download</span>
                        Download Template
                    </a>
                </div>
                <p class="text-xs text-blue-700 mb-2">Your Excel file must have these column headers in the first row:</p>
                <code class="block bg-blue-100 rounded p-2 text-[11px] text-blue-900 font-mono">
                    staff_id, first_name, last_name, middle_name, region, email, phone, gender, date_of_birth, employment_date, address, state_of_origin, nin, grade_level, monthly_salary, status
                </code>
                <p class="text-xs text-blue-600 mt-2"><strong>Required:</strong> staff_id, first_name, last_name, region</p>
                <p class="text-xs text-blue-600"><strong>Status options:</strong> active, inactive, retired, suspended</p>
                <p class="text-xs text-blue-600"><strong>Gender options:</strong> male, female</p>
                <p class="text-xs text-blue-600">If a region name doesn't exist, it will be auto-created.</p>
            </div>

            <form action="{{ route('members.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select Excel File</label>
                        <input type="file" name="import_file" accept=".xlsx,.xls,.csv" required
                               class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-[10px] file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    </div>

                    @if ($errors->has('import_file'))
                        <p class="text-red-600 text-sm">{{ $errors->first('import_file') }}</p>
                    @endif

                    <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
                        <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-6 py-2 rounded-[10px] text-sm font-medium transition">
                            Import Members
                        </button>
                        <a href="{{ route('members.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
