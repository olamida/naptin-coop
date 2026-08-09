<x-app-layout title="Dividend {{ $dividend->dividend_number }}">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('dividends.index') }}" class="text-slate-500 hover:text-slate-700">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl font-bold text-[#0F172A]">Dividend {{ $dividend->dividend_number }}</h2>
            @php
                $statusColors = [
                    'draft' => 'bg-slate-100 text-slate-600',
                    'calculated' => 'bg-blue-100 text-blue-700',
                    'approved' => 'bg-yellow-100 text-yellow-700',
                    'completed' => 'bg-green-100 text-green-700',
                ];
            @endphp
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$dividend->status] ?? 'bg-slate-100' }}">{{ ucfirst($dividend->status) }}</span>
        </div>

        {{-- Dividend Progress Stepper --}}
        @php
            $divStepperCurrent = match($dividend->status) {
                'draft' => 1,
                'calculated' => 2,
                'approved' => 3,
                'completed' => 4,
                default => 1,
            };
        @endphp
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-[#0F172A] mb-4">Dividend Progress</h3>
            <x-stepper :steps="[
                ['label' => 'Declared', 'icon' => 'edit_document'],
                ['label' => 'Calculated', 'icon' => 'calculate'],
                ['label' => 'Approved', 'icon' => 'check_circle'],
                ['label' => 'Distributed', 'icon' => 'paid'],
            ]" :current="$divStepperCurrent" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Year</p>
                <p class="text-lg font-bold">{{ $dividend->year }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Total Profit</p>
                <p class="text-lg font-bold">₦{{ number_format($dividend->total_profit, 2) }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Total Distributed</p>
                <p class="text-lg font-bold text-green-700">₦{{ number_format($dividend->total_distributed, 2) }}</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Eligible Members</p>
                <p class="text-lg font-bold">{{ $dividend->eligible_members }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if ($dividend->status === 'draft' && $declarationPending > 0)
                <span class="px-3 py-2 text-xs font-medium rounded-[10px] bg-orange-100 text-orange-700">
                    Declaration awaits maker-checker approval ({{ $declarationPending }}/2)
                </span>
                <form method="POST" action="{{ route('dividends.approve-declaration', $dividend) }}" data-shortcut="approve">
                    @csrf
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                        Approve Declaration <span class="text-white/60">(A)</span>
                    </button>
                </form>
            @elseif ($dividend->status === 'draft' && $declarationApproved)
                <span class="px-3 py-2 text-xs font-medium rounded-[10px] bg-green-100 text-green-700">
                    Declaration approved
                </span>
            @endif
            @if ($dividend->status === 'draft')
                <form method="POST" action="{{ route('dividends.calculate', $dividend) }}">
                    @csrf
                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                        Calculate Distributions
                    </button>
                </form>
            @endif
            @if ($dividend->status === 'calculated')
                <form method="POST" action="{{ route('dividends.approve', $dividend) }}" data-shortcut="approve">
                    @csrf
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                        Approve Dividend <span class="text-white/60">(A)</span>
                    </button>
                </form>
            @endif
            @if ($dividend->status === 'approved')
                <form method="POST" action="{{ route('dividends.distribute', $dividend) }}">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                        Distribute Dividends
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-[#0F172A]">Distribution Details</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-5 py-3 font-medium text-slate-600">Member</th>
                            <th class="text-right px-5 py-3 font-medium text-slate-600">Shares</th>
                            <th class="text-right px-5 py-3 font-medium text-slate-600">Amount</th>
                            <th class="text-left px-5 py-3 font-medium text-slate-600">Status</th>
                            <th class="text-left px-5 py-3 font-medium text-slate-600">Paid At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($dividend->distributions as $dist)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium">{{ $dist->member->first_name ?? 'N/A' }} {{ $dist->member->last_name ?? '' }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($dist->share_count) }}</td>
                                <td class="px-5 py-3 text-right font-medium text-green-700">₦{{ number_format($dist->amount, 2) }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                        {{ $dist->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($dist->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-500 text-xs">{{ $dist->paid_at?->format('M d, Y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-500">No distributions calculated yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
