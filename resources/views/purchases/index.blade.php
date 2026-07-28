<x-app-layout title="Purchases">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Purchases']]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Purchases</h2>
                <p class="text-sm text-gray-500 mt-1">Track and manage member product orders</p>
            </div>
            <div class="flex items-center gap-2">
                @can('view-products')
                    <a href="{{ route('products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">storefront</span>
                        Products
                    </a>
                @endcan
                @can('view-purchase-orders')
                    <a href="{{ route('products.orders') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                        Purchase Orders
                    </a>
                @endcan
                @can('create-purchase-orders')
                    <a href="{{ route('purchases.import') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">upload_file</span>
                        Import
                    </a>
                    <a href="{{ route('purchases.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                        New Order
                    </a>
                @endcan
            </div>
        </div>

        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-gray-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Member name, Staff ID, or Order #..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="min-w-[180px]">
                    <label class="block text-xs text-gray-500 mb-1">Member</label>
                    <select name="member_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">All Members</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>{{ $member->first_name }} {{ $member->last_name }} ({{ $member->staff_id }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Filter</button>
                @if (request()->hasAny(['search', 'member_id', 'status']))
                    <a href="{{ route('purchases.index') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
                @endif
            </div>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Order Group</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Member</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Items</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600">Total</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Payment</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Date</th>
                        <th class="text-right px-5 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ substr($order->order_group, 0, 8) }}...</td>
                            <td class="px-5 py-3 text-gray-800 font-medium">{{ $order->member->first_name }} {{ $order->member->last_name }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $order->item_count }} item{{ $order->item_count > 1 ? 's' : '' }}</td>
                            <td class="px-5 py-3 text-right font-medium text-gray-900">₦{{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 text-[10px] font-medium rounded-full {{ $order->payment_type === 'cash' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">
                                    {{ ucfirst($order->payment_type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 text-[10px] font-medium rounded-full
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $order->status === 'approved' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('products.orders.show', $order->order_group) }}" class="text-blue-600 hover:underline text-xs font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-gray-500">
                                <span class="material-symbols-outlined text-4xl text-gray-300 mb-2 block">shopping_cart</span>
                                No purchase orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}

        <x-show-all-toggle />
    </div>
</x-app-layout>
