<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Statement — {{ $account->account_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #1f2937; background: #f9fafb; padding: 2rem; }
        .statement { max-width: 850px; margin: 0 auto; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #065f46, #059669); color: white; padding: 2rem; }
        .header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
        .header p { font-size: 0.8rem; opacity: 0.8; }
        .doc-title { text-align: center; padding: 1.5rem; border-bottom: 2px solid #e5e7eb; }
        .doc-title h2 { font-size: 1.25rem; font-weight: 700; color: #065f46; }
        .doc-title .doc-number { font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem; }
        .content { padding: 2rem; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        .info-block h3 { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-bottom: 0.5rem; }
        .info-block p { font-size: 0.85rem; line-height: 1.7; }
        .info-block .label { color: #6b7280; font-size: 0.75rem; }
        .info-block .value { font-weight: 500; }
        .stat-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; text-align: center; }
        .stat-card .stat-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-bottom: 0.25rem; }
        .stat-card .stat-value { font-size: 1.1rem; font-weight: 700; }
        .stat-card .stat-value.green { color: #059669; }
        .stat-card .stat-value.blue { color: #2563eb; }
        .stat-card .stat-value.orange { color: #d97706; }
        .stat-card .stat-value.red { color: #dc2626; }
        h3.section-title { font-size: 0.9rem; font-weight: 600; color: #374151; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
        th { background: #f3f4f6; padding: 0.6rem 0.75rem; text-align: left; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
        td { padding: 0.6rem 0.75rem; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .status-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 600; }
        .status-deposit { background: #d1fae5; color: #065f46; }
        .status-withdrawal { background: #fee2e2; color: #991b1b; }
        .status-interest { background: #dbeafe; color: #1e40af; }
        .totals { margin-left: auto; width: 320px; }
        .totals .row { display: flex; justify-content: space-between; padding: 0.4rem 0; font-size: 0.85rem; }
        .totals .row.total { border-top: 2px solid #059669; font-weight: 700; font-size: 1rem; color: #059669; padding-top: 0.6rem; margin-top: 0.25rem; }
        .footer { background: #f9fafb; padding: 1.5rem 2rem; border-top: 1px solid #e5e7eb; text-align: center; }
        .footer p { font-size: 0.75rem; color: #9ca3af; line-height: 1.6; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 6rem; font-weight: 800; color: rgba(0,0,0,0.03); pointer-events: none; z-index: 0; text-transform: uppercase; letter-spacing: 0.2em; }
        @media print {
            body { background: white; padding: 0; }
            .statement { border: none; border-radius: 0; box-shadow: none; }
            .no-print { display: none !important; }
            .watermark { display: none; }
        }
    </style>
</head>
<body>
    <div class="watermark">NAPTIN COOPERATIVE</div>

    <div style="max-width: 850px; margin: 0 auto;">
        <div class="no-print" style="text-align: right; margin-bottom: 1rem;">
            <button onclick="window.print()" style="background: #059669; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 500;">
                Print Statement
            </button>
            <a href="{{ route('savings.accounts') }}" style="color: #6b7280; text-decoration: none; font-size: 0.85rem; margin-left: 1rem;">Back to Accounts</a>
        </div>

        <div class="statement">
        <div class="header">
            @php $company = \App\Models\Company::instance(); @endphp
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                @if ($company->logo_path)
                    <img src="{{ $company->logo_url }}" alt="Logo" style="height: 56px; width: 56px; object-fit: contain; border-radius: 8px; background: white; padding: 4px; flex-shrink: 0;">
                @endif
                <div>
                    <h1>{{ $company->name }}</h1>
                    <p>Savings Account Statement</p>
                </div>
            </div>
        </div>

        <div class="doc-title">
            <h2>SAVINGS STATEMENT</h2>
            <div class="doc-number">{{ $account->account_number }}</div>
        </div>

        <div class="content">
            {{-- Account & Member Info --}}
            <div class="info-grid">
                <div class="info-block">
                    <h3>Account Holder</h3>
                    <p class="value">{{ $account->member->first_name ?? 'N/A' }} {{ $account->member->last_name ?? '' }}</p>
                    <p><span class="label">Staff ID:</span> <span class="value">{{ $account->member->staff_id_display ?? 'N/A' }}</span></p>
                    <p><span class="label">Region:</span> <span class="value">{{ $account->member->region->name ?? 'N/A' }}</span></p>
                    <p><span class="label">Phone:</span> <span class="value">{{ $account->member->phone ?? 'N/A' }}</span></p>
                    <p><span class="label">Email:</span> <span class="value">{{ $account->member->email ?? 'N/A' }}</span></p>
                    <p><span class="label">Monthly Salary:</span> <span class="value">₦{{ number_format($account->member->monthly_salary ?? 0, 2) }}</span></p>
                </div>
                <div class="info-block">
                    <h3>Account Details</h3>
                    <p><span class="label">Account Number:</span> <span class="value" style="font-family: monospace;">{{ $account->account_number }}</span></p>
                    <p><span class="label">Status:</span> <span class="status-badge status-deposit">{{ ucfirst($account->status ?? 'active') }}</span></p>
                    <p><span class="label">Interest Rate:</span> <span class="value">{{ $account->interest_rate ?? 5 }}%</span></p>
                    <p><span class="label">Account Opened:</span> <span class="value">{{ $account->created_at->format('d M Y') }}</span></p>
                    <p><span class="label">Statement Generated:</span> <span class="value">{{ now()->format('d M Y, h:i A') }}</span></p>
                </div>
            </div>

            {{-- Summary Stat Cards --}}
            @php
                $totalDeposits = $account->transactions->where('type', 'deposit')->where('status', 'completed')->sum('amount');
                $totalWithdrawals = $account->transactions->where('type', 'withdrawal')->where('status', 'completed')->sum('amount');
                $totalInterest = $account->transactions->where('type', 'interest')->sum('amount');
                $txnCount = $account->transactions->count();
            @endphp
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-label">Current Balance</div>
                    <div class="stat-value green">₦{{ number_format($account->balance, 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Deposits</div>
                    <div class="stat-value blue">₦{{ number_format($totalDeposits, 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Withdrawals</div>
                    <div class="stat-value red">₦{{ number_format($totalWithdrawals, 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Transactions</div>
                    <div class="stat-value">{{ number_format($txnCount) }}</div>
                </div>
            </div>

            {{-- Monthly Contribution --}}
            @if ($account->member->monthly_savings > 0)
                <div style="margin-bottom: 2rem; padding: 1rem; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #166534; font-weight: 600;">Monthly Salary Deduction</span>
                    </div>
                    <p style="font-size: 1.1rem; font-weight: 700; color: #166534;">₦{{ number_format($account->member->monthly_savings, 2) }}/month</p>
                    <p style="font-size: 0.75rem; color: #16a34a; margin-top: 0.25rem;">10% of monthly salary (₦{{ number_format($account->member->monthly_salary ?? 0, 2) }})</p>
                </div>
            @endif

            {{-- Transaction History --}}
            <h3 class="section-title">Transaction History</h3>
            @if ($account->transactions->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Balance Before</th>
                            <th class="text-right">Balance After</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($account->transactions->sortByDesc('transaction_date') as $index => $txn)
                            @php
                                $typeClass = match($txn->type) {
                                    'deposit' => 'status-deposit',
                                    'withdrawal' => 'status-withdrawal',
                                    'interest' => 'status-interest',
                                    default => '',
                                };
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $txn->transaction_date?->format('d M Y') ?? $txn->created_at->format('d M Y') }}</td>
                                <td style="font-family: monospace; font-size: 0.7rem;">{{ $txn->reference }}</td>
                                <td><span class="status-badge {{ $typeClass }}">{{ ucfirst($txn->type) }}</span></td>
                                <td class="text-right" style="font-weight: 600; {{ $txn->type === 'deposit' ? 'color: #059669;' : ($txn->type === 'withdrawal' ? 'color: #dc2626;' : 'color: #2563eb;') }}">
                                    {{ $txn->type === 'withdrawal' ? '-' : '+' }}₦{{ number_format($txn->amount, 2) }}
                                </td>
                                <td class="text-right" style="font-family: monospace; font-size: 0.75rem;">₦{{ number_format($txn->balance_before, 2) }}</td>
                                <td class="text-right" style="font-family: monospace; font-size: 0.75rem; font-weight: 500;">₦{{ number_format($txn->balance_after, 2) }}</td>
                                <td style="font-size: 0.75rem; color: #6b7280; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $txn->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="totals">
                    <div class="row">
                        <span>Total Deposits</span>
                        <span style="color: #059669;">₦{{ number_format($totalDeposits, 2) }}</span>
                    </div>
                    <div class="row">
                        <span>Total Withdrawals</span>
                        <span style="color: #dc2626;">₦{{ number_format($totalWithdrawals, 2) }}</span>
                    </div>
                    @if ($totalInterest > 0)
                        <div class="row">
                            <span>Interest Earned</span>
                            <span style="color: #2563eb;">₦{{ number_format($totalInterest, 2) }}</span>
                        </div>
                    @endif
                    <div class="row total">
                        <span>Current Balance</span>
                        <span>₦{{ number_format($account->balance, 2) }}</span>
                    </div>
                </div>
            @else
                <div style="text-align: center; padding: 2rem; color: #9ca3af; font-size: 0.85rem; background: #f9fafb; border-radius: 8px; border: 1px dashed #e5e7eb;">
                    No transactions recorded yet.
                </div>
            @endif
        </div>

        <div class="footer">
            <p><strong>{{ $company->name }}</strong></p>
            <p>This is a computer-generated savings statement. For inquiries, contact the cooperative office.</p>
            @if ($company->footer_note)
                <p style="margin-top: 0.5rem; font-style: italic;">{{ $company->footer_note }}</p>
            @endif
            <p style="margin-top: 0.5rem;">Generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>  <!-- close statement -->
    </div>  <!-- close wrapper -->
</body>
</html>
