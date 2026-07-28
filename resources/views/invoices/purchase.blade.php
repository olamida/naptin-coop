<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: #1f2937; background: #f9fafb; padding: 2rem; }
        .invoice { max-width: 800px; margin: 0 auto; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 2rem; }
        .header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
        .header p { font-size: 0.8rem; opacity: 0.8; }
        .invoice-title { text-align: center; padding: 1.5rem; border-bottom: 2px solid #e5e7eb; }
        .invoice-title h2 { font-size: 1.25rem; font-weight: 700; color: #1e40af; }
        .invoice-title .inv-number { font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem; }
        .content { padding: 2rem; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        .info-block h3 { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-bottom: 0.5rem; }
        .info-block p { font-size: 0.85rem; line-height: 1.6; }
        .info-block .label { color: #6b7280; font-size: 0.75rem; }
        .info-block .value { font-weight: 500; }
        table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; }
        th { background: #f3f4f6; padding: 0.75rem 1rem; text-align: left; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
        td { padding: 0.75rem 1rem; font-size: 0.85rem; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .totals { margin-left: auto; width: 300px; }
        .totals .row { display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.85rem; }
        .totals .row.total { border-top: 2px solid #1e40af; font-weight: 700; font-size: 1rem; color: #1e40af; padding-top: 0.75rem; margin-top: 0.25rem; }
        .footer { background: #f9fafb; padding: 1.5rem 2rem; border-top: 1px solid #e5e7eb; text-align: center; }
        .footer p { font-size: 0.75rem; color: #9ca3af; line-height: 1.6; }
        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #dbeafe; color: #1e40af; }
        .status-active { background: #fef3c7; color: #92400e; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 6rem; font-weight: 800; color: rgba(0,0,0,0.03); pointer-events: none; z-index: 0; text-transform: uppercase; letter-spacing: 0.2em; }
        @media print {
            body { background: white; padding: 0; }
            .invoice { border: none; border-radius: 0; box-shadow: none; }
            .no-print { display: none !important; }
            .watermark { display: none; }
        }
    </style>
</head>
<body>
    <div class="watermark">NAPTIN COOPERATIVE</div>

    <div class="no-print" style="max-width: 800px; margin: 0 auto 1rem; text-align: right;">
        <button onclick="window.print()" style="background: #1e40af; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 500;">
            Print Invoice
        </button>
        <a href="{{ route('products.orders') }}" style="color: #6b7280; text-decoration: none; font-size: 0.85rem; margin-left: 1rem;">Back to Orders</a>
    </div>

    <div class="invoice">
        <div class="header">
            @php $company = \App\Models\Company::instance(); @endphp
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.25rem;">
                @if ($company->logo_path)
                    <img src="{{ $company->logo_url }}" alt="Logo" style="height: 56px; width: 56px; object-fit: contain; border-radius: 8px; background: white; padding: 4px; flex-shrink: 0;">
                @endif
                <div>
                    <h1>{{ $company->name }}</h1>
                    <p>Staff Cooperative Purchase Invoice</p>
                </div>
            </div>
        </div>

        <div class="invoice-title">
            <h2>PURCHASE INVOICE</h2>
            <div class="inv-number">{{ $order->order_number }}</div>
        </div>

        <div class="content">
            <div class="info-grid">
                <div class="info-block">
                    <h3>Bill To</h3>
                    <p class="value">{{ $order->member->first_name ?? 'N/A' }} {{ $order->member->last_name ?? '' }}</p>
                    <p><span class="label">Staff ID:</span> <span class="value">{{ $order->member->staff_id ?? 'N/A' }}</span></p>
                    <p><span class="label">Region:</span> <span class="value">{{ $order->member->region->name ?? 'N/A' }}</span></p>
                    <p><span class="label">Phone:</span> <span class="value">{{ $order->member->phone ?? 'N/A' }}</span></p>
                    <p><span class="label">Email:</span> <span class="value">{{ $order->member->email ?? 'N/A' }}</span></p>
                </div>
                <div class="info-block">
                    <h3>Invoice Details</h3>
                    <p><span class="label">Date:</span> <span class="value">{{ $order->created_at->format('d M Y') }}</span></p>
                    <p><span class="label">Payment Type:</span> <span class="value">{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</span></p>
                    <p><span class="label">Status:</span>
                        <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                    </p>
                    @if ($order->collected_at)
                        <p><span class="label">Collected:</span> <span class="value">{{ $order->collected_at->format('d M Y') }}</span></p>
                    @endif
                    @if ($order->approvedBy)
                        <p><span class="label">Approved By:</span> <span class="value">{{ $order->approvedBy->name }}</span></p>
                    @endif
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="value">{{ $order->product->name ?? 'N/A' }}</td>
                        <td>{{ $order->product->description ?? '-' }}</td>
                        <td class="text-right">{{ $order->quantity }}</td>
                        <td class="text-right">₦{{ number_format($order->unit_price, 2) }}</td>
                        <td class="text-right" style="font-weight: 600;">₦{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="totals">
                <div class="row">
                    <span>Subtotal</span>
                    <span>₦{{ number_format($order->total_amount, 2) }}</span>
                </div>
                @if ($order->payment_type === 'hire_purchase' && $order->monthly_repayment > 0)
                    <div class="row">
                        <span>Amount Paid</span>
                        <span>₦{{ number_format($order->amount_paid, 2) }}</span>
                    </div>
                    <div class="row">
                        <span>Monthly Repayment</span>
                        <span>₦{{ number_format($order->monthly_repayment, 2) }}</span>
                    </div>
                @endif
                <div class="row total">
                    <span>Total Amount</span>
                    <span>₦{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>{{ $company->name }}</strong></p>
            <p>This is a computer-generated invoice. For inquiries, contact the cooperative office.</p>
            @if ($company->footer_note)
                <p style="margin-top: 0.5rem; font-style: italic;">{{ $company->footer_note }}</p>
            @endif
            <p style="margin-top: 0.5rem;">Generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>
</body>
</html>
