<x-app-layout title="Upload Payroll Deductions - {{ $payroll->payroll_number }}">
    <div class="max-w-2xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('payroll.show', $payroll) }}" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-gray-800">Upload Payroll Deductions</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-100">
                <h3 class="text-sm font-semibold text-blue-800 mb-2">How it works</h3>
                <ol class="text-xs text-blue-700 space-y-1 list-decimal list-inside">
                    <li>Download the template Excel file below</li>
                    <li>Fill in the <strong>actual amounts</strong> deducted from each member's salary</li>
                    <li>Upload the completed file back here</li>
                    <li>The system will update each member's deduction status automatically</li>
                </ol>
            </div>

            <div class="flex items-center gap-4 mb-6">
                <a href="{{ route('payroll.download-template', $payroll) }}"
                   class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Download Template
                </a>
                <a href="{{ route('payroll.export-deductions', $payroll) }}"
                   class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                    <span class="material-symbols-outlined text-lg">table_chart</span>
                    Export Current Data
                </a>
            </div>

            <form method="POST" action="{{ route('payroll.upload-deductions', $payroll) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Excel File *</label>
                    <input type="file" name="deductions_file" accept=".xlsx,.xls,.csv" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: .xlsx, .xls, .csv (Max 10MB)</p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                        Upload & Process
                    </button>
                    <a href="{{ route('payroll.show', $payroll) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Current Deduction Status</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-gray-600 text-xs">Member</th>
                        <th class="text-right px-4 py-2 font-medium text-gray-600 text-xs">Expected</th>
                        <th class="text-right px-4 py-2 font-medium text-gray-600 text-xs">Actual</th>
                        <th class="text-left px-4 py-2 font-medium text-gray-600 text-xs">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($payroll->deductions->load('member') as $deduction)
                        <tr>
                            <td class="px-4 py-2 text-xs">{{ $deduction->member->staff_id }} - {{ $deduction->member->first_name }} {{ $deduction->member->last_name }}</td>
                            <td class="px-4 py-2 text-right text-xs">₦{{ number_format($deduction->total_expected, 2) }}</td>
                            <td class="px-4 py-2 text-right text-xs font-medium {{ $deduction->total_actual > 0 ? 'text-green-700' : 'text-gray-400' }}">₦{{ number_format($deduction->total_actual, 2) }}</td>
                            <td class="px-4 py-2 text-xs">
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $deduction->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($deduction->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
