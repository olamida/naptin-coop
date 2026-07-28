<x-app-layout title="{{ $member->first_name }} {{ $member->last_name }} - Loans">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('members.show', $member) }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Loan Records</h2>
                <p class="text-sm text-gray-500">{{ $member->full_name }} &middot; {{ $member->staff_id }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Loan #</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Outstanding</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($loans as $loan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs">{{ $loan->loan_number }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ ucfirst($loan->type) }}</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($loan->amount, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($loan->outstanding, 2) }}</td>
                            <td class="px-4 py-3">
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
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('loans.show', $loan) }}" class="text-blue-600 hover:underline text-xs">View</a>
                                @if ($loan->status === 'disbursed' || $loan->status === 'repaying')
                                    <a href="{{ route('receipts.loan-disbursement', $loan) }}" target="_blank" class="text-gray-500 hover:text-gray-700">
                                        <span class="material-symbols-outlined text-[16px]">print</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No loans found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $loans->links() }}
    </div>
</x-app-layout>
