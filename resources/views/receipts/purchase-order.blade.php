<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Receipt - {{ $order->order_number }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-area { box-shadow: none; border: none; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-lg mx-auto">
        <div class="no-print mb-6 text-center">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                Print Receipt
            </button>
            <a href="javascript:window.close()" class="ml-3 text-gray-500 hover:text-gray-700 text-sm">Close</a>
        </div>

        <div class="print-area bg-white rounded-xl shadow-lg w-full p-8">
        @php $company = \App\Models\Company::instance(); @endphp
        <div class="text-center border-b border-gray-200 pb-6 mb-6">
            @if ($company->logo_path)
                <img src="{{ $company->logo_url }}" alt="Logo" class="h-14 w-14 mx-auto mb-3 object-contain rounded-lg p-1 border border-gray-100">
            @endif
            <h1 class="text-xl font-bold text-gray-900">{{ $company->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Product Purchase Receipt</p>
        </div>

        <div class="space-y-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Order No:</span>
                <span class="font-mono font-semibold text-gray-900">{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Date:</span>
                <span class="text-gray-900">{{ $order->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Member:</span>
                <span class="text-gray-900">{{ $order->member->full_name }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Staff ID:</span>
                <span class="text-gray-900">{{ $order->member->staff_id_display }}</span>
            </div>

            <div class="border-t border-dashed border-gray-300 my-4"></div>

            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Product:</span>
                    <span class="text-gray-900 font-medium">{{ $order->product->name }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Quantity:</span>
                    <span class="text-gray-900">{{ $order->quantity }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Unit Price:</span>
                    <span class="text-gray-900">₦{{ number_format($order->unit_price, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Payment Type:</span>
                    <span class="text-gray-900 capitalize">{{ str_replace('_', ' ', $order->payment_type) }}</span>
                </div>
                @if ($order->payment_type === 'hire_purchase')
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Monthly Repayment:</span>
                        <span class="text-gray-900">₦{{ number_format($order->monthly_repayment, 2) }}</span>
                    </div>
                @endif
            </div>

            <div class="flex justify-between text-lg mt-4">
                <span class="font-semibold text-gray-700">Total Amount:</span>
                <span class="font-bold text-blue-600">₦{{ number_format($order->total_amount, 2) }}</span>
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Status:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if ($order->status === 'completed') bg-emerald-100 text-emerald-700
                    @elseif ($order->status === 'approved') bg-blue-100 text-blue-700
                    @elseif ($order->status === 'active') bg-amber-100 text-amber-700
                    @else bg-gray-100 text-gray-700
                    @endif">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 text-center text-xs text-gray-400">
            <p>Generated {{ now()->format('d M Y, h:i A') }} &middot; {{ $company->name }}</p>
        </div>
    </div>  <!-- close print-area -->
    </div>  <!-- close wrapper -->
</body>
</html>
