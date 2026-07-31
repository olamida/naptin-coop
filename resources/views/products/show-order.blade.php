<x-app-layout title="Order Details">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('products.orders') }}" class="text-slate-500 hover:text-slate-700">&larr;</a>
                <div>
                    <h2 class="text-2xl font-bold text-[#0F172A]">Order {{ $orderGroup }}</h2>
                    <p class="text-sm text-slate-500">{{ $orders->first()->member->full_name ?? '' }} &middot; {{ $orders->count() }} item(s)</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('receipts.purchase-order', $orders->first()) }}" target="_blank" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-[10px] text-sm flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-lg">print</span>
                    Print Receipt
                </a>
                <a href="{{ route('invoices.purchase.show', $orders->first()) }}" target="_blank" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-[10px] text-sm flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-lg">receipt</span>
                    Invoice
                </a>
            </div>
        </div>

        {{-- Purchase Order Progress Stepper --}}
        @php
            $firstOrder = $orders->first();
            $poStepperCurrent = match($firstOrder->status) {
                'pending' => 1,
                'approved' => 2,
                'active' => 3,
                'completed' => 4,
                default => 1,
            };
            if ($firstOrder->payment_type === 'cash') {
                $poSteps = [
                    ['label' => 'Ordered', 'icon' => 'shopping_cart'],
                    ['label' => 'Approved', 'icon' => 'check_circle'],
                    ['label' => 'Collected', 'icon' => 'inventory_2'],
                ];
                if ($poStepperCurrent >= 2) $poStepperCurrent = min($poStepperCurrent, 3);
            } else {
                $poSteps = [
                    ['label' => 'Ordered', 'icon' => 'shopping_cart'],
                    ['label' => 'Approved', 'icon' => 'check_circle'],
                    ['label' => 'Active', 'icon' => 'play_circle'],
                    ['label' => 'Completed', 'icon' => 'task_alt'],
                ];
            }
        @endphp
        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-[#0F172A] mb-4">Order Progress</h3>
            <x-stepper :steps="$poSteps" :current="$poStepperCurrent" />
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Product</th>
                        <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Price</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Subtotal</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($orders as $order)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium">{{ $order->product->name ?? 'N/A' }}</td>
                            <td class="px-5 py-3 text-center">{{ $order->quantity }}</td>
                            <td class="px-5 py-3 text-right">&#8358;{{ number_format($order->unit_price, 2) }}</td>
                            <td class="px-5 py-3 text-right font-semibold">&#8358;{{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $order->status === 'approved' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->status === 'active' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <td colspan="3" class="px-5 py-3 text-right font-semibold text-slate-700">Total:</td>
                        <td class="px-5 py-3 text-right font-bold text-lg">&#8358;{{ number_format($orders->sum('total_amount'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="flex items-center gap-3">
            @if ($orders->first()->status === 'pending')
                <form method="POST" action="{{ route('products.orders.approve', $orders->first()) }}">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-[10px] text-sm font-medium">Approve Order</button>
                </form>
            @endif
            @if (in_array($orders->first()->status, ['approved', 'active']))
                <form method="POST" action="{{ route('products.orders.collect', $orders->first()) }}">
                    @csrf
                    <button type="submit" class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-[10px] text-sm font-medium">Mark as Collected</button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
