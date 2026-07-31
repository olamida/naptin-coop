<x-app-layout title="Purchases">
    <div class="space-y-6 fade-in">
        <x-breadcrumb :items="[['label' => 'Purchases']]" />

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A]">Purchases</h2>
                <p class="text-xs text-slate-500 mt-1">Track and manage member product orders</p>
            </div>
            <div class="flex items-center gap-2">
                @can('view-products')
                    <a href="{{ route('products.index') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-[10px] text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">storefront</span>
                        Products
                    </a>
                @endcan
                @can('view-purchase-orders')
                    <a href="{{ route('products.orders') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-[10px] text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                        Orders
                    </a>
                @endcan
                @can('create-purchase-orders')
                    <a href="{{ route('purchases.import') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-[10px] text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">upload_file</span>
                        Import
                    </a>
                    <a href="{{ route('purchases.create') }}" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                        New Order
                    </a>
                @endcan
            </div>
        </div>

        {{-- KPI Cards --}}
        @php
            $purchaseStats = $orders ?? collect();
            $totalOrders = $purchaseStats->count();
            $totalAmount = $purchaseStats->sum('total_amount');
            $pendingOrders = $purchaseStats->where('status', 'pending')->count();
            $completedOrders = $purchaseStats->where('status', 'completed')->count();
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Orders</p>
                <p class="mt-2 text-2xl font-bold text-[#0F172A]">{{ $totalOrders }}</p>
                <p class="text-xs text-slate-400 mt-1">All time purchases</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Value</p>
                <p class="mt-2 text-2xl font-mono font-bold text-[#0F172A] truncate" title="₦{{ number_format($totalAmount, 2) }}">₦{{ number_format($totalAmount, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Aggregate order value</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Pending</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ $pendingOrders }}</p>
                <p class="text-xs text-slate-400 mt-1">Awaiting approval</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Completed</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $completedOrders }}</p>
                <p class="text-xs text-slate-400 mt-1">Fulfilled orders</p>
            </div>
        </div>

        <form method="GET" class="bg-white rounded-[16px] border border-slate-200 shadow-sm p-5">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Member name, Staff ID, or Order #..."
                        class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="min-w-[180px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Member</label>
                    <select name="member_id" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">All Members</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>{{ $member->first_name }} {{ $member->last_name }} ({{ $member->staff_id_display }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-[10px] text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-2 rounded-[10px] text-sm font-medium transition">Filter</button>
                @if (request()->hasAny(['search', 'member_id', 'status']))
                    <a href="{{ route('purchases.index') }}" class="text-sm text-slate-500 hover:underline py-2">Clear</a>
                @endif
            </div>
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
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Payment</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ substr($order->order_group, 0, 8) }}...</td>
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('members.show', $order->member) }}" class="text-slate-800 font-medium hover:text-blue-600 transition">{{ $order->member->first_name }} {{ $order->member->last_name }}</a>
                                </td>
                                <td class="px-5 py-3.5 text-center text-slate-600">{{ $order->item_count }} item{{ $order->item_count > 1 ? 's' : '' }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-medium text-slate-800">₦{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full {{ $order->payment_type === 'cash' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                        {{ $order->payment_type === 'cash' ? 'Cash' : 'Hire Purchase' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <x-status-badge :status="$order->status" />
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('products.orders.show', $order->order_group) }}" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 transition">
                                        View
                                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">shopping_cart</span>
                                    <p class="text-sm">No purchase orders found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $orders->links() }}

        <x-show-all-toggle />
    </div>
</x-app-layout>