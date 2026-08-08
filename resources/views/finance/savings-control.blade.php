<x-app-layout title="Members Savings Control Report">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Finance', 'url' => route('finance.index')], ['label' => 'Savings Control Report']]" />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Members Savings Control Report</h2>
                <p class="text-xs text-slate-500 mt-1">Report 6 — member-by-member savings ledger with closing balances vs the 2001 control account.</p>
            </div>
            <a href="{{ route('finance.control-reconciliation') }}" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-[10px] text-sm font-medium transition">
                <span class="material-symbols-outlined text-lg">balance</span>
                Control Reconciliation
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] text-sm">
                @foreach ($errors->all() as $error) {{ $error }} @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Total Member Savings</p>
                <p class="text-2xl font-bold font-mono text-[#0F172A] mt-1">₦{{ number_format($subLedgerTotal, 2) }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Sum of savings account balances</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Ledger Control (2001)</p>
                <p class="text-2xl font-bold font-mono text-[#0F172A] mt-1">₦{{ number_format($ledgerBalance, 2) }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Members Savings Liability balance</p>
            </div>
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-5 {{ $controlVariance == 0 ? 'border-emerald-200' : 'border-red-200' }}">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Control Variance</p>
                <p class="text-2xl font-bold font-mono mt-1 {{ $controlVariance == 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $controlVariance > 0 ? '+' : '' }}₦{{ number_format($controlVariance, 2) }}</p>
                <p class="text-[11px] text-slate-500 mt-1">{{ $controlVariance == 0 ? 'Reconciled — sub-ledger matches the ledger.' : 'Mismatch — run Sync Opening Balances or review postings.' }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <p class="text-xs text-slate-500">Filter by transaction date range:</p>
            <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-center">
                <input type="date" name="from" value="{{ $from ?? '' }}" class="rounded-[10px] border-slate-300 text-sm focus:border-slate-400 focus:ring-slate-300">
                <input type="date" name="to" value="{{ $to ?? '' }}" class="rounded-[10px] border-slate-300 text-sm focus:border-slate-400 focus:ring-slate-300">
                <button type="submit" class="inline-flex items-center gap-2 bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition">
                    <span class="material-symbols-outlined text-lg">filter_alt</span>
                    Apply
                </button>
                <a href="{{ route('finance.savings-control') }}" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-[10px] text-sm font-medium transition">
                    Reset
                </a>
            </form>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50 text-left text-[10px] uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">Member</th>
                            <th class="px-4 py-3 text-right">Opening</th>
                            <th class="px-4 py-3 text-right">Deposits</th>
                            <th class="px-4 py-3 text-right">Withdrawals</th>
                            <th class="px-4 py-3 text-right">Interest</th>
                            <th class="px-4 py-3 text-right">Transfers</th>
                            <th class="px-4 py-3 text-right">Closing</th>
                            <th class="px-4 py-3 text-right">Ledger Variance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-[#0F172A]">{{ $row['member']?->full_name ?? 'Deleted member' }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $row['member']?->staff_id ?? '—' }} · {{ $row['account_number'] }}</p>
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-slate-600">₦{{ number_format($row['opening'], 2) }}</td>
                                <td class="px-4 py-3 text-right font-mono text-emerald-600">₦{{ number_format($row['deposits'], 2) }}</td>
                                <td class="px-4 py-3 text-right font-mono text-red-600">₦{{ number_format($row['withdrawals'], 2) }}</td>
                                <td class="px-4 py-3 text-right font-mono text-emerald-600">₦{{ number_format($row['interest'], 2) }}</td>
                                <td class="px-4 py-3 text-right font-mono text-indigo-600">₦{{ number_format($row['transfers'], 2) }}</td>
                                <td class="px-4 py-3 text-right font-mono font-semibold text-[#0F172A]">₦{{ number_format($row['closing'], 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($row['variance'] == 0)
                                        <span class="text-emerald-600 font-mono font-semibold">₦0.00</span>
                                    @else
                                        <span class="text-red-600 font-mono font-semibold">{{ $row['variance'] > 0 ? '+' : '' }}₦{{ number_format($row['variance'], 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">savings</span>
                                    <p class="text-sm text-slate-500">No savings accounts found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
