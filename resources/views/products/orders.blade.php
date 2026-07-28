<x-app-layout title="Purchase Orders">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Products', 'url' => route('products.index')], ['label' => 'Orders']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Purchase Orders</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.orders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                    New Order
                </a>
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-gray-200">
            <a href="{{ route('products.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 -mb-px transition">
                Products
            </a>
            <a href="{{ route('products.orders') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px">
                Orders
            </a>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Member name, Staff ID, or Order #..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Payment Type</label>
                <select name="payment_type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Types</option>
                    <option value="cash" {{ request('payment_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="hire_purchase" {{ request('payment_type') === 'hire_purchase' ? 'selected' : '' }}>Hire Purchase</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Filter</button>
            @if (request()->hasAny(['search', 'status', 'payment_type']))
                <a href="{{ route('products.orders') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Order Group</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Member</th>
                        <th class="text-center px-5 py-3 font-medium text-gray-600">Items</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600">Total</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Type</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Date</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs">{{ $order->order_group ?? $order->order_number }}</td>
                            <td class="px-5 py-3 font-medium">{{ $order->member->first_name ?? '' }} {{ $order->member->last_name ?? '' }}</td>
                            <td class="px-5 py-3 text-center">{{ $order->item_count ?? 1 }}</td>
                            <td class="px-5 py-3 text-right font-semibold">&#8358;{{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-5 py-3 text-xs capitalize">{{ str_replace('_', ' ', $order->payment_type) }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-gray-100 text-gray-600',
                                        'approved' => 'bg-blue-100 text-blue-700',
                                        'active' => 'bg-yellow-100 text-yellow-700',
                                        'completed' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $order->created_at?->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('products.orders.show', $order->order_group ?? $order->order_number) }}" class="text-blue-600 hover:underline text-xs font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-gray-500">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>
</x-app-layout>
