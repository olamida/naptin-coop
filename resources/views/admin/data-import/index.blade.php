<x-app-layout title="Data Import & Upload">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.manage') }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Data Import & Upload</h2>
                <p class="text-sm text-gray-500 mt-1">Bulk import data from Excel/CSV files into the system</p>
            </div>
        </div>

        {{-- Quick Info --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-5">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-blue-600 mt-0.5">info</span>
                <div class="text-sm text-blue-800 space-y-1">
                    <p class="font-semibold">Before you start importing:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-blue-700">
                        <li>Download the template file for each data type to see the expected format</li>
                        <li>All imports accept <code class="bg-blue-100 px-1 rounded text-xs">.xlsx</code>, <code class="bg-blue-100 px-1 rounded text-xs">.xls</code>, or <code class="bg-blue-100 px-1 rounded text-xs">.csv</code> files (max 10MB)</li>
                        <li>Members are matched by <strong>staff_id</strong> — ensure IDs are consistent across all imports</li>
                        <li>Import order matters: <strong>Members first</strong>, then Savings, Loans, Products, and Purchases</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Recommended Import Order --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-gray-500">route</span>
                Recommended Import Order
            </h3>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">group</span> 1. Members
                </span>
                <span class="material-symbols-outlined text-gray-300 text-[18px]">arrow_forward</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">savings</span> 2. Savings
                </span>
                <span class="material-symbols-outlined text-gray-300 text-[18px]">arrow_forward</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-pink-100 text-pink-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">inventory_2</span> 3. Products
                </span>
                <span class="material-symbols-outlined text-gray-300 text-[18px]">arrow_forward</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-cyan-100 text-cyan-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">shopping_cart</span> 4. Purchases
                </span>
                <span class="material-symbols-outlined text-gray-300 text-[18px]">arrow_forward</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">account_balance</span> 5. Loan Repayments
                </span>
                <span class="material-symbols-outlined text-gray-300 text-[18px]">arrow_forward</span>
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
                        'indigo' => ['bg' => 'bg-indigo-50', 'icon_bg' => 'bg-indigo-100 group-hover:bg-indigo-200', 'icon_text' => 'text-indigo-600', 'badge' => 'bg-indigo-100 text-indigo-700', 'btn' => 'bg-indigo-600 hover:bg-indigo-700'],
                        'emerald' => ['bg' => 'bg-emerald-50', 'icon_bg' => 'bg-emerald-100 group-hover:bg-emerald-200', 'icon_text' => 'text-emerald-600', 'badge' => 'bg-emerald-100 text-emerald-700', 'btn' => 'bg-emerald-600 hover:bg-emerald-700'],
                        'amber' => ['bg' => 'bg-amber-50', 'icon_bg' => 'bg-amber-100 group-hover:bg-amber-200', 'icon_text' => 'text-amber-600', 'badge' => 'bg-amber-100 text-amber-700', 'btn' => 'bg-amber-600 hover:bg-amber-700'],
                        'pink' => ['bg' => 'bg-pink-50', 'icon_bg' => 'bg-pink-100 group-hover:bg-pink-200', 'icon_text' => 'text-pink-600', 'badge' => 'bg-pink-100 text-pink-700', 'btn' => 'bg-pink-600 hover:bg-pink-700'],
                        'cyan' => ['bg' => 'bg-cyan-50', 'icon_bg' => 'bg-cyan-100 group-hover:bg-cyan-200', 'icon_text' => 'text-cyan-600', 'badge' => 'bg-cyan-100 text-cyan-700', 'btn' => 'bg-cyan-600 hover:bg-cyan-700'],
                        'violet' => ['bg' => 'bg-violet-50', 'icon_bg' => 'bg-violet-100 group-hover:bg-violet-200', 'icon_text' => 'text-violet-600', 'badge' => 'bg-violet-100 text-violet-700', 'btn' => 'bg-violet-600 hover:bg-violet-700'],
                    ];
                    $c = $colorMap[$import['color']] ?? $colorMap['indigo'];
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-md transition">
                    {{-- Header --}}
                    <div class="p-5 pb-3">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl {{ $c['icon_bg'] }} flex items-center justify-center flex-shrink-0 transition">
                                <span class="material-symbols-outlined {{ $c['icon_text'] }} text-2xl">{{ $import['icon'] }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-gray-800">{{ $import['title'] }}</h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $c['badge'] }}">
                                        {{ number_format($import['record_count']) }} {{ $import['record_label'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $import['description'] }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Column Details --}}
                    <div class="px-5 pb-3">
                        <div class="bg-gray-50 rounded-lg p-3 space-y-1.5">
                            <div>
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Columns:</span>
                                <code class="block text-[11px] text-gray-700 font-mono mt-0.5">{{ $import['columns'] }}</code>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Required:</span>
                                <span class="text-[11px] text-gray-700 ml-1">{{ $import['required'] }}</span>
                            </div>
                            @if (isset($import['note']))
                                <div class="flex items-start gap-1.5 pt-1">
                                    <span class="material-symbols-outlined text-[14px] text-gray-400 mt-0.5">lightbulb</span>
                                    <span class="text-[11px] text-gray-500">{{ $import['note'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="px-5 pb-5 flex items-center gap-3">
                        @if ($import['route'])
                            <a href="{{ $import['route'] }}" class="inline-flex items-center gap-2 {{ $c['btn'] }} text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                <span class="material-symbols-outlined text-[16px]">upload_file</span>
                                Go to Import
                            </a>
                        @endif
                        @if (isset($import['extra_routes']))
                            @foreach ($import['extra_routes'] as $extra)
                                <a href="{{ $extra['route'] }}" class="inline-flex items-center gap-2 {{ $c['btn'] }} text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                    <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                                    {{ $extra['label'] }}
                                </a>
                            @endforeach
                        @endif
                        @if ($import['template_route'])
                            <a href="{{ $import['template_route'] }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition">
                                <span class="material-symbols-outlined text-[16px]">download</span>
                                Download Template
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
