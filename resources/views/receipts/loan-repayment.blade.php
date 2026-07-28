<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Repayment Receipt - {{ $repayment->reference_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-area { box-shadow: none; border: none; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-8">
    <div class="no-print mb-6 text-center">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
            Print Receipt
        </button>
        <a href="javascript:window.close()" class="ml-3 text-gray-500 hover:text-gray-700 text-sm">Close</a>
    </div>

    <div class="print-area bg-white rounded-xl shadow-lg max-w-lg w-full p-8">
        @php $company = \App\Models\Company::instance(); @endphp
        <div class="text-center border-b border-gray-200 pb-6 mb-6">
            @if ($company->logo_path)
                <img src="{{ $company->logo_url }}" alt="Logo" class="h-14 w-14 mx-auto mb-3 object-contain rounded-lg p-1 border border-gray-100">
            @endif
            <h1 class="text-xl font-bold text-gray-900">{{ $company->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Loan Repayment Receipt</p>
        </div>

        <div class="space-y-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Receipt No:</span>
                <span class="font-mono font-semibold text-gray-900">{{ $repayment->reference_number }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Date:</span>
                <span class="text-gray-900">{{ $repayment->payment_date->format('d M Y, h:i A') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Member:</span>
                <span class="text-gray-900">{{ $repayment->loan->member->full_name }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Staff ID:</span>
                <span class="text-gray-900">{{ $repayment->loan->member->staff_id }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Loan Number:</span>
                <span class="font-mono text-gray-900">{{ $repayment->loan->loan_number }}</span>
            </div>

            <div class="border-t border-dashed border-gray-300 my-4"></div>

            <div class="flex justify-between text-lg">
                <span class="font-semibold text-gray-700">Amount Paid:</span>
                <span class="font-bold text-emerald-600">₦{{ number_format($repayment->amount_paid, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Payment Method:</span>
                <span class="text-gray-900 capitalize">{{ str_replace('_', ' ', $repayment->payment_method) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Principal Component:</span>
                <span class="text-gray-900">₦{{ number_format($repayment->principal_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Interest Component:</span>
                <span class="text-gray-900">₦{{ number_format($repayment->interest_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Outstanding Balance:</span>
                <span class="font-semibold text-gray-900">₦{{ number_format($repayment->loan->outstanding_balance, 2) }}</span>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 text-center text-xs text-gray-400">
            <p>Generated {{ now()->format('d M Y, h:i A') }} &middot; {{ $company->name }}</p>
        </div>
    </div>
</body>
</html>
