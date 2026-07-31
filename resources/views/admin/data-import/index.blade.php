<x-app-layout title="Data Import & Upload">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.manage') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Data Import & Upload</h2>
                <p class="text-xs text-slate-500 mt-1">Bulk import data from Excel/CSV files into the system</p>
            </div>
        </div>

        {{-- Onboarding Wizard CTA --}}
        <div class="bg-gradient-to-r from-[#0F172A] to-[#1e3a5f] rounded-[16px] p-6 flex items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <span class="material-symbols-outlined text-4xl text-emerald-400 mt-1">rocket_launch</span>
                <div>
                    <h3 class="text-white font-semibold text-lg">New coop? Run the Unified Onboarding Wizard</h3>
                    <p class="text-slate-300 text-sm mt-1 leading-relaxed">
                        Upload one workbook with <strong class="text-white">members</strong>, <strong class="text-white">opening_savings</strong> and
                        <strong class="text-white">shares</strong> sheets. Members, opening balances and share allotments are
                        created atomically in a single batch.
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.onboarding') }}" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-[10px] text-sm font-medium transition flex-shrink-0">
                <span class="material-symbols-outlined text-[18px]">rocket_launch</span>
                Go to Onboarding
            </a>
        </div>

        {{-- Quick Info --}}
        <div class="bg-amber-50 border border-amber-200 rounded-[16px] p-5">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-amber-600 mt-0.5">info</span>
                <div class="text-sm text-amber-800 space-y-1">
                    <p class="font-semibold">Before you start importing:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-amber-700">
                        <li>Download the template file for each data type to see the expected format</li>
                        <li>All imports accept <code class="bg-amber-100 px-1 rounded text-xs">.xlsx</code>, <code class="bg-amber-100 px-1 rounded text-xs">.xls</code>, or <code class="bg-amber-100 px-1 rounded text-xs">.csv</code> files (max 10MB)</li>
                        <li>Members are matched by <strong>staff_id</strong> — ensure IDs are consistent across all imports</li>
                        <li>Import order matters: <strong>Members first</strong>, then Savings, Loans, Products, and Purchases</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Recommended Import Order --}}
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-[#0F172A] mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-slate-500">route</span>
                Recommended Import Order
            </h3>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">group</span> 1. Members
                </span>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">arrow_forward</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">savings</span> 2. Savings
                </span>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">arrow_forward</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-pink-100 text-pink-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">inventory_2</span> 3. Products
                </span>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">arrow_forward</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-cyan-100 text-cyan-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">shopping_cart</span> 4. Purchases
                </span>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">arrow_forward</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">account_balance</span> 5. Loan Repayments
                </span>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">arrow_forward</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-violet-100 text-violet-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">payments</span> 6. Payroll
                </span>
            </div>
        </div>

        {{-- Import Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach ($imports as $import)
                @php
                    $colorMap = [
                        'indigo' => ['bg' => 'bg-indigo-50', 'icon_bg' => 'bg-indigo-100 group-hover:bg-indigo-200', 'icon_text' => 'text-blue-600', 'badge' => 'bg-indigo-100 text-indigo-700', 'btn' => 'bg-[#0F172A] hover:bg-slate-800'],
                        'emerald' => ['bg' => 'bg-emerald-50', 'icon_bg' => 'bg-emerald-100 group-hover:bg-emerald-200', 'icon_text' => 'text-emerald-600', 'badge' => 'bg-emerald-100 text-emerald-700', 'btn' => 'bg-emerald-600 hover:bg-emerald-700'],
                        'amber' => ['bg' => 'bg-amber-50', 'icon_bg' => 'bg-amber-100 group-hover:bg-amber-200', 'icon_text' => 'text-amber-600', 'badge' => 'bg-amber-100 text-amber-700', 'btn' => 'bg-amber-600 hover:bg-amber-700'],
                        'pink' => ['bg' => 'bg-pink-50', 'icon_bg' => 'bg-pink-100 group-hover:bg-pink-200', 'icon_text' => 'text-pink-600', 'badge' => 'bg-pink-100 text-pink-700', 'btn' => 'bg-pink-600 hover:bg-pink-700'],
                        'cyan' => ['bg' => 'bg-cyan-50', 'icon_bg' => 'bg-cyan-100 group-hover:bg-cyan-200', 'icon_text' => 'text-cyan-600', 'badge' => 'bg-cyan-100 text-cyan-700', 'btn' => 'bg-cyan-600 hover:bg-cyan-700'],
                        'violet' => ['bg' => 'bg-violet-50', 'icon_bg' => 'bg-violet-100 group-hover:bg-violet-200', 'icon_text' => 'text-violet-600', 'badge' => 'bg-violet-100 text-violet-700', 'btn' => 'bg-violet-600 hover:bg-violet-700'],
                    ];
                    $c = $colorMap[$import['color']] ?? $colorMap['indigo'];
                @endphp

                <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden group hover:shadow-md transition">
                    {{-- Header --}}
                    <div class="p-5 pb-3">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl {{ $c['icon_bg'] }} flex items-center justify-center flex-shrink-0 transition">
                                <span class="material-symbols-outlined {{ $c['icon_text'] }} text-2xl">{{ $import['icon'] }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-[#0F172A]">{{ $import['title'] }}</h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $c['badge'] }}">
                                        {{ number_format($import['record_count']) }} {{ $import['record_label'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">{{ $import['description'] }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Column Details --}}
                    <div class="px-5 pb-3">
                        <div class="bg-slate-50 rounded-[10px] p-3 space-y-1.5">
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Columns:</span>
                                <code class="block text-[11px] text-slate-700 font-mono mt-0.5">{{ $import['columns'] }}</code>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Required:</span>
                                <span class="text-[11px] text-slate-700 ml-1">{{ $import['required'] }}</span>
                            </div>
                            @if (isset($import['note']))
                                <div class="flex items-start gap-1.5 pt-1">
                                    <span class="material-symbols-outlined text-[14px] text-slate-400 mt-0.5">lightbulb</span>
                                    <span class="text-[11px] text-slate-500">{{ $import['note'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="px-5 pb-5 flex items-center gap-3">
                        @if ($import['route'])
                            <a href="{{ $import['route'] }}" class="inline-flex items-center gap-2 {{ $c['btn'] }} text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                                <span class="material-symbols-outlined text-[16px]">upload_file</span>
                                Go to Import
                            </a>
                        @endif
                        @if (isset($import['extra_routes']))
                            @foreach ($import['extra_routes'] as $extra)
                                <a href="{{ $extra['route'] }}" class="inline-flex items-center gap-2 {{ $c['btn'] }} text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                                    <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                                    {{ $extra['label'] }}
                                </a>
                            @endforeach
                        @endif
                        @if ($import['template_route'])
                            <a href="{{ $import['template_route'] }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-[10px] text-sm font-medium transition">
                                <span class="material-symbols-outlined text-[16px]">download</span>
                                Download Template
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Recent Import Batches --}}
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-[#0F172A] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-slate-500">history</span>
                    Recent Import Batches
                </h3>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-medium">
                    {{ $recentBatches->count() }} recent
                </span>
            </div>

            @if ($recentBatches->isEmpty())
                <x-empty-state
                    icon="upload_file"
                    title="No imports yet"
                    message="Batches imported through the system will be recorded here with success and failure details."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <th class="pb-2 pr-3 font-semibold">Date</th>
                                <th class="pb-2 pr-3 font-semibold">Type</th>
                                <th class="pb-2 pr-3 font-semibold">File</th>
                                <th class="pb-2 pr-3 font-semibold text-right">Total</th>
                                <th class="pb-2 pr-3 font-semibold text-right">Success</th>
                                <th class="pb-2 pr-3 font-semibold text-right">Failed</th>
                                <th class="pb-2 pr-3 font-semibold">Status</th>
                                <th class="pb-2 font-semibold">Imported By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recentBatches as $batch)
                                @php
                                    $typeLabels = [
                                        'members' => 'Members',
                                        'savings' => 'Savings',
                                        'loan_repayments' => 'Loan Repayments',
                                        'products' => 'Products',
                                        'purchase_orders' => 'Purchase Orders',
                                        'payroll_deductions' => 'Payroll Deductions',
                                    ];
                                    $typeColors = [
                                        'members' => 'bg-indigo-100 text-indigo-700',
                                        'savings' => 'bg-emerald-100 text-emerald-700',
                                        'loan_repayments' => 'bg-amber-100 text-amber-700',
                                        'products' => 'bg-pink-100 text-pink-700',
                                        'purchase_orders' => 'bg-cyan-100 text-cyan-700',
                                        'payroll_deductions' => 'bg-violet-100 text-violet-700',
                                    ];
                                    $isFailed = $batch->status === 'failed';
                                @endphp
                                <tr class="hover:bg-slate-50" x-data="{ open: false }">
                                    <td class="py-2.5 pr-3 text-slate-600 whitespace-nowrap">{{ $batch->created_at->format('d M Y H:i') }}</td>
                                    <td class="py-2.5 pr-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $typeColors[$batch->type] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ $typeLabels[$batch->type] ?? ucfirst(str_replace('_', ' ', $batch->type)) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 pr-3 text-slate-600 max-w-[180px] truncate" title="{{ $batch->file_name }}">{{ $batch->file_name ?? '—' }}</td>
                                    <td class="py-2.5 pr-3 text-right text-slate-700 font-medium">{{ number_format($batch->total_rows) }}</td>
                                    <td class="py-2.5 pr-3 text-right text-emerald-600 font-medium">{{ number_format($batch->success) }}</td>
                                    <td class="py-2.5 pr-3 text-right {{ $batch->failed > 0 ? 'text-rose-600 font-medium' : 'text-slate-400' }}">
                                        {{ number_format($batch->failed) }}
                                    </td>
                                    <td class="py-2.5 pr-3">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $isFailed ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $isFailed ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                                            {{ $isFailed ? 'Failed' : 'Completed' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 pr-3 text-slate-600">{{ $batch->creator?->name ?? '—' }}</td>
                                    <td class="py-2.5 pl-2 text-right">
                                        @if (!empty($batch->errors))
                                            <button @click="open = !open" class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 hover:text-slate-800">
                                                <span x-text="open ? 'Hide errors' : 'Errors ({{ count($batch->errors) }})'"></span>
                                                <span class="material-symbols-outlined text-[14px]" :class="open ? 'rotate-180' : ''">expand_more</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @if (!empty($batch->errors))
                                    <tr x-show="open" x-cloak>
                                        <td colspan="9" class="py-2">
                                            <div class="bg-rose-50 border border-rose-200 rounded-[10px] p-3 space-y-1 max-h-40 overflow-y-auto">
                                                @foreach ($batch->errors as $error)
                                                    <div class="flex items-start gap-2 text-[12px] text-rose-700">
                                                        <span class="material-symbols-outlined text-[14px] mt-0.5">error</span>
                                                        <span>
                                                            @if (isset($error['row']))
                                                                <span class="font-mono font-semibold">Row {{ $error['row'] }}:</span>
                                                            @endif
                                                            {{ $error['error'] }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-[11px] text-slate-400">Batch ID shown in the success message on each import page matches the recorded batch.</p>
            @endif
        </div>
    </div>
</x-app-layout>
