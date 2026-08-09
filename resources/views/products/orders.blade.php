<x-app-layout title="Purchase Orders">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Products', 'url' => route('products.index')], ['label' => 'Orders']]" />
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Purchase Orders</h2>
                <p class="text-xs text-slate-500 mt-1">Manage and track all member purchase orders</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.orders.create') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                    New Order
                </a>
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-slate-200">
            <a href="{{ route('products.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 -mb-px transition">
                Products
            </a>
            <a href="{{ route('products.orders') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 border-[#0F172A] text-[#0F172A] -mb-px">
                Orders
            </a>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                <x-search-autocomplete :endpoint="route('purchases.search')" name="search" placeholder="Member name, Staff ID, or Order #..." />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Payment Type</label>
                <select name="payment_type" class="px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Types</option>
                    <option value="cash" {{ request('payment_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="hire_purchase" {{ request('payment_type') === 'hire_purchase' ? 'selected' : '' }}>Hire Purchase</option>
                </select>
            </div>
            <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2 rounded-[10px] text-sm font-medium transition">Filter</button>
            @if (request()->hasAny(['search', 'status', 'payment_type']))
                <a href="{{ route('products.orders') }}" class="text-sm text-slate-500 hover:underline py-2">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-[16px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order Group</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                            <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Items</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $order->order_group ?? $order->order_number }}</td>
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('members.show', $order->member) }}" class="text-slate-800 font-medium hover:text-blue-600 transition">{{ $order->member->first_name ?? '' }} {{ $order->member->last_name ?? '' }}</a>
                                </td>
                                <td class="px-5 py-3.5 text-center text-slate-600">{{ $order->item_count ?? 1 }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-medium text-slate-800">₦{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full border {{ $order->payment_type === 'cash' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-purple-50 text-purple-700 border-purple-200' }}">
                                        {{ $order->payment_type === 'cash' ? 'Cash' : 'Hire Purchase' }}
                                    </span>
                                    @if ($order->is_society_expense)
                                        <span class="px-2.5 py-1 text-[10px] font-medium rounded-full border bg-rose-50 text-rose-700 border-rose-200">
                                            Expense
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <x-status-badge :status="$order->status" />
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">{{ $order->created_at?->format('d M Y') }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('products.orders.show', $order->order_group ?? $order->order_number) }}" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 transition">
                                        View
                                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5">
                                    <x-empty-state icon="receipt_long" title="No orders found"
                                        message="Purchase orders for cash and hire-purchase sales will appear here."
                                        actionUrl="{{ route('products.orders.create') }}" actionLabel="Create an order" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $orders->links() }}
    </div>
</x-app-layout>