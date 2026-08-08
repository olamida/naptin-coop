<x-app-layout title="Loan Aging & Provisioning">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance', 'url' => route('finance.index')], ['label' => 'Loan Aging']]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Loan Aging & Provisioning</h2>
                <p class="text-xs text-slate-500 mt-1">IFRS 9 / CBN classification &middot; Period {{ $report['period'] }} &middot; {{ auth()->user()->name }}</p>
            </div>
            <div class="flex gap-2">
                <x-report-export-buttons route="finance.export.loan-aging" />
                <button onclick="window.print()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">print</span> Print
                </button>
                <form method="POST" action="{{ route('finance.provision.calculate') }}">
                    @csrf
                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">fact_check</span> Calculate Provision
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-[10px] text-sm">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">
                @foreach ($errors->all() as $error) {{ $error }} @endforeach
            </div>
        @endif

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <p class="text-xs text-slate-500">Total Portfolio (Outstanding)</p>
                <p class="text-xl font-mono font-semibold text-[#0F172A] mt-1">₦{{ number_format($report['total_outstanding'], 2) }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <p class="text-xs text-slate-500">Required Provision</p>
                <p class="text-xl font-mono font-semibold text-amber-600 mt-1">₦{{ number_format($report['total_provision'], 2) }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <p class="text-xs text-slate-500">Provision Coverage</p>
                <p class="text-xl font-mono font-semibold {{ $report['coverage_ratio'] >= 100 ? 'text-green-600' : 'text-slate-700' }} mt-1">{{ number_format($report['coverage_ratio'], 2) }}%</p>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs text-slate-500">
                        <th class="px-5 py-3">Loan</th>
                        <th class="px-5 py-3">Member</th>
                        <th class="px-5 py-3 text-right">Outstanding</th>
                        <th class="px-5 py-3 text-right">Days Past Due</th>
                        <th class="px-5 py-3">Classification</th>
                        <th class="px-5 py-3 text-right">Rate</th>
                        <th class="px-5 py-3 text-right">Provision</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($report['rows'] as $row)
                        @php
                            $class = $row['classification'];
                            $badge = match ($class) {
                                'Performing' => 'bg-green-100 text-green-700',
                                'Pass & Watch' => 'bg-emerald-50 text-emerald-700',
                                'Substandard' => 'bg-amber-100 text-amber-700',
                                'Doubtful' => 'bg-orange-100 text-orange-700',
                                default => 'bg-red-100 text-red-700',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-2.5 font-mono text-xs text-slate-500">{{ $row['loan_number'] }}</td>
                            <td class="px-5 py-2.5 text-slate-700">{{ $row['member'] }}</td>
                            <td class="px-5 py-2.5 text-right font-mono">₦{{ number_format($row['outstanding'], 2) }}</td>
                            <td class="px-5 py-2.5 text-right font-mono {{ $row['days_past_due'] > 0 ? 'text-amber-600' : 'text-slate-600' }}">{{ $row['days_past_due'] }}</td>
                            <td class="px-5 py-2.5"><span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $badge }}">{{ $class }}</span></td>
                            <td class="px-5 py-2.5 text-right font-mono">{{ $row['rate'] * 100 }}%</td>
                            <td class="px-5 py-2.5 text-right font-mono font-semibold">₦{{ number_format($row['provision'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400 text-sm">No active loans with outstanding balance.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-semibold">
                        <td colspan="2" class="px-5 py-3 text-slate-700">Total</td>
                        <td class="px-5 py-3 text-right font-mono">₦{{ number_format($report['total_outstanding'], 2) }}</td>
                        <td colspan="3"></td>
                        <td class="px-5 py-3 text-right font-mono text-amber-600">₦{{ number_format($report['total_provision'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-[16px] p-4 text-xs text-slate-500">
            <strong class="text-slate-700">CBN buckets:</strong>
            Performing 0–30 days (1%) &middot; Pass &amp; Watch 31–60 (25%) &middot; Substandard 61–90 (50%) &middot; Doubtful 91–180 (75%) &middot; Lost &gt;180 (100%).
            Clicking <strong>Calculate Provision</strong> posts the net movement to the ledger (Debit Loan Loss Expense 5004 / Credit Loan Loss Provision 1205).
        </div>
    </div>
</x-app-layout>
