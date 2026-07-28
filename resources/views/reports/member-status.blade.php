<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Status Report - {{ $member->first_name }} {{ $member->last_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: #1f2937; background: #f9fafb; padding: 2rem; }
        .report { max-width: 800px; margin: 0 auto; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 2rem; }
        .header h1 { font-size: 1.25rem; font-weight: 700; }
        .header p { font-size: 0.8rem; opacity: 0.8; margin-top: 0.25rem; }
        .member-info { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; padding: 1.5rem 2rem; border-bottom: 2px solid #e5e7eb; }
        .member-info .item { display: flex; gap: 0.5rem; font-size: 0.8rem; }
        .member-info .label { color: #9ca3af; min-width: 100px; }
        .member-info .value { font-weight: 500; }
        .content { padding: 2rem; }
        .section { margin-bottom: 2rem; }
        .section-title { font-size: 0.85rem; font-weight: 700; color: #1e40af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e5e7eb; }
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; }
        .stat-card .stat-label { font-size: 0.7rem; text-transform: uppercase; color: #9ca3af; letter-spacing: 0.05em; }
        .stat-card .stat-value { font-size: 1.1rem; font-weight: 700; margin-top: 0.25rem; }
        .stat-card .stat-value.green { color: #059669; }
        .stat-card .stat-value.blue { color: #1e40af; }
        .stat-card .stat-value.purple { color: #7c3aed; }
        .stat-card .stat-value.orange { color: #d97706; }
        .stat-card .stat-value.red { color: #dc2626; }
        table { width: 100%; border-collapse: collapse; margin-top: 0.75rem; }
        th { background: #f3f4f6; padding: 0.5rem 0.75rem; text-align: left; font-size: 0.65rem; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em; }
        td { padding: 0.5rem 0.75rem; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .summary-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1.25rem; margin-top: 1.5rem; }
        .summary-box h3 { font-size: 0.8rem; font-weight: 700; color: #1e40af; margin-bottom: 0.75rem; }
        .summary-row { display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.8rem; }
        .summary-row.total { border-top: 2px solid #1e40af; margin-top: 0.5rem; padding-top: 0.75rem; font-weight: 700; font-size: 0.95rem; color: #1e40af; }
        .footer { background: #f9fafb; padding: 1.5rem 2rem; border-top: 1px solid #e5e7eb; text-align: center; }
        .footer p { font-size: 0.7rem; color: #9ca3af; line-height: 1.6; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 5rem; font-weight: 800; color: rgba(0,0,0,0.02); pointer-events: none; z-index: 0; text-transform: uppercase; letter-spacing: 0.2em; }
        @media print {
            body { background: white; padding: 0; }
            .report { border: none; border-radius: 0; }
            .no-print { display: none !important; }
            .watermark { display: none; }
        }
    </style>
</head>
<body>
    <div class="watermark">NAPTIN COOPERATIVE</div>

    <div class="no-print" style="max-width: 800px; margin: 0 auto 1rem; text-align: right;">
        <button onclick="window.print()" style="background: #1e40af; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 500;">
            Print Report
        </button>
        <a href="{{ route('reports.index') }}" style="color: #6b7280; text-decoration: none; font-size: 0.85rem; margin-left: 1rem;">Back to Reports</a>
        <a href="{{ route('members.show', $member) }}" style="color: #6b7280; text-decoration: none; font-size: 0.85rem; margin-left: 1rem;">Member Profile</a>
    </div>

    <div class="report">
        <div class="header">
            @php $company = \App\Models\Company::instance(); @endphp
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                @if ($company->logo_path)
                    <img src="{{ $company->logo_url }}" alt="Logo"
                         style="height: 56px; width: 56px; object-fit: contain; border-radius: 8px; background: white; padding: 4px; flex-shrink: 0;">
                @else
                    <div style="height: 56px; width: 56px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span style="font-size: 1.5rem; color: white;">&#9733;</span>
                    </div>
                @endif
                <div>
                    <h1>{{ $company->name }}</h1>
                    @if ($company->slogan)
                        <p style="opacity: 0.9; font-size: 0.75rem; font-style: italic;">{{ $company->slogan }}</p>
                    @endif
                    <p>Member Status Report &mdash; Generated {{ now()->format('d M Y, h:i A') }}</p>
                </div>
            </div>
        </div>

        <div class="member-info">
            <div class="item"><span class="label">Name:</span> <span class="value">{{ $member->first_name }} {{ $member->middle_name ?? '' }} {{ $member->last_name }}</span></div>
            <div class="item"><span class="label">Staff ID:</span> <span class="value">{{ $member->staff_id }}</span></div>
            <div class="item"><span class="label">Region:</span> <span class="value">{{ $member->region->name ?? 'N/A' }}</span></div>
            <div class="item"><span class="label">Status:</span> <span class="value">{{ ucfirst($member->status) }}</span></div>
            <div class="item"><span class="label">Grade Level:</span> <span class="value">{{ $member->grade_level ?? 'N/A' }}</span></div>
            <div class="item"><span class="label">Monthly Salary:</span> <span class="value">₦{{ number_format($member->monthly_salary, 2) }}</span></div>
        </div>

        <div class="content">
            <div class="section">
                <div class="section-title">Account Balances</div>
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-label">Savings Balance</div>
                        <div class="stat-value green">₦{{ number_format($savingsBalance, 2) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Shares</div>
                        <div class="stat-value purple">{{ number_format($sharesCount) }} shares</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Share Value</div>
                        <div class="stat-value purple">₦{{ number_format($sharesValue, 2) }}</div>
                    </div>
                </div>
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-label">Loan Outstanding</div>
                        <div class="stat-value {{ $totalLoanOutstanding > 0 ? 'red' : 'green' }}">₦{{ number_format($totalLoanOutstanding, 2) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Loan Repaid</div>
                        <div class="stat-value green">₦{{ number_format($totalLoanRepaid, 2) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Purchase Outstanding</div>
                        <div class="stat-value {{ $totalPurchaseOutstanding > 0 ? 'orange' : 'green' }}">₦{{ number_format($totalPurchaseOutstanding, 2) }}</div>
                    </div>
                </div>
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total Loan Amount</div>
                        <div class="stat-value blue">₦{{ number_format($totalLoanAmount, 2) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Purchases</div>
                        <div class="stat-value orange">₦{{ number_format($totalPurchases, 2) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Purchase Paid</div>
                        <div class="stat-value green">₦{{ number_format($totalPurchasePaid, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Loan History</div>
                @if ($allLoans->isNotEmpty())
                    <table>
                        <thead>
                            <tr>
                                <th>Loan #</th>
                                <th>Amount</th>
                                <th class="text-right">Monthly Repayment</th>
                                <th class="text-right">Total Repaid</th>
                                <th class="text-right">Outstanding</th>
                                <th>Status</th>
                                <th>Applied</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allLoans as $loan)
                                @php
                                    $statusColors = [
                                        'pending' => 'background: #fef3c7; color: #92400e;',
                                        'approved' => 'background: #dbeafe; color: #1e40af;',
                                        'disbursed' => 'background: #d1fae5; color: #065f46;',
                                        'repaying' => 'background: #e0e7ff; color: #3730a3;',
                                        'completed' => 'background: #d1fae5; color: #065f46;',
                                        'rejected' => 'background: #fee2e2; color: #991b1b;',
                                        'defaulted' => 'background: #fecaca; color: #991b1b;',
                                    ];
                                @endphp
                                <tr>
                                    <td style="font-family: monospace; font-size: 0.75rem;">{{ $loan->loan_number }}</td>
                                    <td>₦{{ number_format($loan->amount, 2) }}</td>
                                    <td class="text-right">₦{{ number_format($loan->monthly_repayment, 2) }}</td>
                                    <td class="text-right" style="color: #059669;">₦{{ number_format($loan->total_repaid, 2) }}</td>
                                    <td class="text-right" style="color: #dc2626; font-weight: 600;">₦{{ number_format($loan->outstanding, 2) }}</td>
                                    <td><span style="font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 9999px; {{ $statusColors[$loan->status] ?? 'background: #f3f4f6; color: #374151;' }}">{{ ucfirst($loan->status) }}</span></td>
                                    <td style="font-size: 0.75rem; color: #6b7280;">{{ $loan->application_date?->format('d M Y') ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="font-size: 0.8rem; color: #9ca3af; padding: 1rem 0;">No loans on record.</p>
                @endif
            </div>

            <div class="section">
                <div class="section-title">Purchase History</div>
                @if ($allPurchases->isNotEmpty())
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Product</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Outstanding</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allPurchases as $purchase)
                                @php
                                    $poStatusColors = [
                                        'pending' => 'background: #fef3c7; color: #92400e;',
                                        'approved' => 'background: #d1fae5; color: #065f46;',
                                        'active' => 'background: #e0e7ff; color: #3730a3;',
                                        'completed' => 'background: #d1fae5; color: #065f46;',
                                        'cancelled' => 'background: #fee2e2; color: #991b1b;',
                                    ];
                                @endphp
                                <tr>
                                    <td style="font-family: monospace; font-size: 0.75rem;">{{ $purchase->order_number }}</td>
                                    <td>{{ $purchase->product->name ?? 'N/A' }}</td>
                                    <td class="text-right">₦{{ number_format($purchase->total_amount, 2) }}</td>
                                    <td class="text-right" style="color: #059669;">₦{{ number_format($purchase->amount_paid, 2) }}</td>
                                    <td class="text-right" style="color: #d97706; font-weight: 600;">₦{{ number_format($purchase->total_amount - $purchase->amount_paid, 2) }}</td>
                                    <td><span style="font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: {{ $purchase->payment_type === 'cash' ? '#d1fae5; color: #065f46' : '#fef3c7; color: #92400e' }};">{{ ucfirst(str_replace('_', ' ', $purchase->payment_type)) }}</span></td>
                                    <td><span style="font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 9999px; {{ $poStatusColors[$purchase->status] ?? 'background: #f3f4f6; color: #374151;' }}">{{ ucfirst($purchase->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="font-size: 0.8rem; color: #9ca3af; padding: 1rem 0;">No purchase orders on record.</p>
                @endif
            </div>

            <div class="section">
                <div class="section-title">Monthly Salary Deductions</div>
                <div class="summary-box">
                    <div class="summary-row" style="border-bottom: 1px solid #bfdbfe; margin-bottom: 0.5rem; padding-bottom: 0.5rem; font-weight: 600; font-size: 0.75rem; color: #6b7280;">
                        <span>Deduction Item</span>
                        <span>Amount</span>
                    </div>
                    <div class="summary-row" style="background: #eff6ff; margin: -0.5rem -0.5rem 0.5rem; padding: 0.5rem; border-radius: 4px; font-weight: 600;">
                        <span>Gross Monthly Salary</span>
                        <span>₦{{ number_format($member->monthly_salary, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Savings Contribution ({{ $savingsRate }}% of salary)</span>
                        <span>₦{{ number_format($expectedMonthlySavings, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Share Contribution (5% of salary)</span>
                        <span>₦{{ number_format($expectedMonthlyShares, 2) }}</span>
                    </div>

                    @if ($activeLoans->isNotEmpty())
                        <div class="summary-row" style="font-weight: 600; color: #374151; margin-top: 0.5rem;">
                            <span>Loan Repayments</span>
                            <span>₦{{ number_format($expectedLoanRepayment, 2) }}</span>
                        </div>
                        @foreach ($activeLoans as $loan)
                            <div class="summary-row" style="padding-left: 1rem; font-size: 0.75rem; color: #6b7280;">
                                <span>&#8226; {{ $loan->loan_number }} ({{ ucfirst($loan->loanProduct?->name ?? $loan->type) }})</span>
                                <span>₦{{ number_format($loan->monthly_repayment, 2) }}</span>
                            </div>
                        @endforeach
                    @endif

                    @php $activeHirePurchases = $activePurchases->where('payment_type', 'hire_purchase'); @endphp
                    @if ($activeHirePurchases->isNotEmpty())
                        <div class="summary-row" style="font-weight: 600; color: #374151; margin-top: 0.5rem;">
                            <span>Purchase Repayments</span>
                            <span>₦{{ number_format($expectedPurchaseRepayment, 2) }}</span>
                        </div>
                        @foreach ($activeHirePurchases as $purchase)
                            <div class="summary-row" style="padding-left: 1rem; font-size: 0.75rem; color: #6b7280;">
                                <span>&#8226; {{ $purchase->order_number }} &mdash; {{ $purchase->product->name ?? 'N/A' }}</span>
                                <span>₦{{ number_format($purchase->monthly_repayment, 2) }}</span>
                            </div>
                        @endforeach
                    @endif

                    <div class="summary-row total">
                        <span>Total Monthly Deduction</span>
                        <span>₦{{ number_format($totalMonthlyDeduction, 2) }}</span>
                    </div>
                    <div class="summary-row" style="color: #6b7280; font-size: 0.75rem; border-top: 1px dashed #bfdbfe; margin-top: 0.5rem; padding-top: 0.5rem;">
                        <span>Deduction as % of Salary</span>
                        <span>{{ $member->monthly_salary > 0 ? number_format(($totalMonthlyDeduction / $member->monthly_salary) * 100, 1) : '0.0' }}%</span>
                    </div>
                    @if ($member->monthly_salary > 0)
                        <div class="summary-row" style="color: #059669; font-size: 0.8rem; font-weight: 600; margin-top: 0.25rem;">
                            <span>Net Take-Home Pay</span>
                            <span>₦{{ number_format(max(0, $member->monthly_salary - $totalMonthlyDeduction), 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>{{ $company->name }}</strong></p>
            @if ($company->footer_note)
                <p style="margin-top: 0.5rem; font-style: italic;">{{ $company->footer_note }}</p>
            @endif
            <p style="margin-top: 0.5rem;">Report generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>
</body>
</html>
