<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Purchase Receipt</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #1f2937; background: #f3f4f6; padding: 2rem; }
        .receipt-wrapper { max-width: 480px; margin: 0 auto; }
        .no-print { margin-bottom: 1.5rem; text-align: center; }
        .no-print button { background: #1e40af; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 500; }
        .no-print a { color: #6b7280; text-decoration: none; font-size: 0.85rem; margin-left: 1rem; }

        .receipt {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
            padding: 2rem;
        }

        .receipt-header { text-align: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
        .receipt-header img.logo { width: 56px; height: 56px; object-fit: contain; margin: 0 auto 0.75rem; border: 1px solid #f3f4f6; border-radius: 8px; padding: 4px; }
        .receipt-header h1 { font-size: 1.1rem; font-weight: 700; color: #111827; }
        .receipt-header .receipt-type { font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem; }

        .detail-row { display: flex; justify-content: space-between; align-items: baseline; font-size: 0.85rem; padding: 0.3rem 0; }
        .detail-row .label { color: #6b7280; }
        .detail-row .value { font-weight: 600; color: #111827; }
        .detail-row .value.mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }

        .divider { border-top: 1px dashed #d1d5db; margin: 1rem 0; }

        .amount-row { display: flex; justify-content: space-between; align-items: baseline; font-size: 1rem; padding: 0.35rem 0; }
        .amount-row .label { font-weight: 600; color: #374151; }
        .amount-row .value { font-weight: 700; }
        .amount-row .value.shares { color: #1d4ed8; }
        .amount-row .value.amount { color: #059669; }

        .receipt-footer { margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; text-align: center; font-size: 0.7rem; color: #9ca3af; line-height: 1.5; }

        @media print {
            body { background: white; padding: 0; }
            .receipt { box-shadow: none; border: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="receipt-wrapper">
        <div class="no-print">
            <button onclick="window.print()">Print Receipt</button>
            <a href="javascript:window.close()">Close</a>
        </div>

        <div class="receipt">
            @php $company = \App\Models\Company::instance(); @endphp
            <div class="receipt-header">
                @if ($receiptLogo = app(\App\Services\BrandingService::class)->getLogo('pdf'))
                    <img class="logo" src="{{ $receiptLogo }}" alt="Logo">
                @elseif ($company->logo_path)
                    <img class="logo" src="{{ $company->logo_url }}" alt="Logo">
                @endif
                <h1>{{ $company->name }}</h1>
                <p class="receipt-type">Share Purchase Receipt</p>
            </div>

            <div class="detail-row">
                <span class="label">Reference</span>
                <span class="value mono">{{ $transaction->reference }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Date</span>
                <span class="value">{{ $transaction->transaction_date->format('d M Y, h:i A') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Member</span>
                <span class="value">{{ $transaction->shareAccount->member->full_name }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Staff ID</span>
                <span class="value">{{ $transaction->shareAccount->member->staff_id_display }}</span>
            </div>

            <div class="divider"></div>

            <div class="amount-row">
                <span class="label">Shares Purchased</span>
                <span class="value shares">{{ number_format($transaction->shares) }} shares</span>
            </div>
            <div class="amount-row">
                <span class="label">Amount Paid</span>
                <span class="value amount">&#8358;{{ number_format($transaction->amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Total Shares Held</span>
                <span class="value">{{ number_format($transaction->shareAccount->total_shares) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Total Share Value</span>
                <span class="value">&#8358;{{ number_format($transaction->shareAccount->total_value, 2) }}</span>
            </div>

            <div class="receipt-footer">
                <p>Generated {{ now()->format('d M Y, h:i A') }} &middot; {{ $company->name }}</p>
            </div>
        </div>
    </div>
</body>
</html>
