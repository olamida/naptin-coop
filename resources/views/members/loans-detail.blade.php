<x-app-layout title="{{ $member->first_name }} {{ $member->last_name }} - Loans">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('members.show', $member) }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Loan Records</h2>
                <p class="text-sm text-slate-500">{{ $member->full_name }} &middot; {{ $member->staff_id_display }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Loan #</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Outstanding</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($loans as $loan)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3.5 font-mono text-xs">{{ $loan->loan_number }}</td>
                            <td class="px-5 py-3.5 text-slate-700">{{ ucfirst($loan->type) }}</td>
                            <td class="px-5 py-3.5 text-right">₦{{ number_format($loan->amount, 2) }}</td>
                            <td class="px-5 py-3.5 text-right font-medium">₦{{ number_format($loan->outstanding, 2) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $loan->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $loan->status === 'repaying' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $loan->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $loan->status === 'approved' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $loan->status === 'disbursed' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $loan->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('loans.show', $loan) }}" class="text-blue-600 hover:underline text-xs">View</a>
                                @if ($loan->status === 'disbursed' || $loan->status === 'repaying')
                                    <a href="{{ route('receipts.loan-disbursement', $loan) }}" target="_blank" class="text-slate-500 hover:text-slate-700">
                                        <span class="material-symbols-outlined text-[16px]">print</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">No loans found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $loans->links() }}
    </div>
</x-app-layout>
