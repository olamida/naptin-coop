<x-app-layout title="{{ $member->first_name }} {{ $member->last_name }} - Purchases">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('members.show', $member) }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Purchase Records</h2>
                <p class="text-sm text-slate-500">{{ $member->full_name }} &middot; {{ $member->staff_id_display }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order #</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Product</th>
                        <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3.5 font-mono text-xs">{{ $order->order_number }}</td>
                            <td class="px-5 py-3.5 font-medium">{{ $order->product->name ?? 'N/A' }}</td>
                            <td class="px-5 py-3.5 text-center">{{ $order->quantity }}</td>
                            <td class="px-5 py-3.5 text-right font-medium">₦{{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-5 py-3.5 text-xs capitalize">{{ str_replace('_', ' ', $order->payment_type) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $order->status === 'approved' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->status === 'active' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('receipts.purchase-order', $order) }}" target="_blank" class="text-slate-500 hover:text-slate-700">
                                    <span class="material-symbols-outlined text-[16px]">print</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500">No purchases found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>
</x-app-layout>
