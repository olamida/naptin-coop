<x-app-layout title="{{ $member->first_name }} {{ $member->last_name }} - Purchases">
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('members.show', $member) }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Purchase Records</h2>
                <p class="text-sm text-gray-500">{{ $member->full_name }} &middot; {{ $member->staff_id }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Order #</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Product</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Qty</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs">{{ $order->order_number }}</td>
                            <td class="px-4 py-3 font-medium">{{ $order->product->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-center">{{ $order->quantity }}</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-4 py-3 text-xs capitalize">{{ str_replace('_', ' ', $order->payment_type) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $order->status === 'approved' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->status === 'active' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('receipts.purchase-order', $order) }}" target="_blank" class="text-gray-500 hover:text-gray-700">
                                    <span class="material-symbols-outlined text-[16px]">print</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No purchases found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>
</x-app-layout>
