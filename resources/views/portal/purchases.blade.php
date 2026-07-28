<x-portal-layout title="My Purchases">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'My Purchases']]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">My Purchases</h2>
                <p class="text-sm text-gray-500 mt-1">View your order history</p>
            </div>
            <a href="{{ route('portal.products') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                New Order
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-gray-600 text-xs">Order</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600 text-xs">Product</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600 text-xs">Qty</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600 text-xs">Total</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600 text-xs">Payment</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600 text-xs">Status</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600 text-xs">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $order->order_group }}</td>
                            <td class="px-5 py-3 text-xs font-medium text-gray-800">{{ $order->product->name ?? 'N/A' }}</td>
                            <td class="px-5 py-3 text-right text-xs text-gray-600">{{ $order->quantity }}</td>
                            <td class="px-5 py-3 text-right font-medium text-xs text-gray-900">₦{{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $order->payment_type === 'cash' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $order->status === 'approved' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-500">
                                <span class="material-symbols-outlined text-4xl text-gray-300 mb-2 block">shopping_cart</span>
                                No purchases yet.
                                <a href="{{ route('portal.products') }}" class="block mt-2 text-sm text-blue-600 hover:underline">Browse Products &rarr;</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>
</x-portal-layout>
