<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Statement — {{ $loan->loan_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #1f2937; background: #f9fafb; padding: 2rem; }
        .statement { max-width: 850px; margin: 0 auto; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 2rem; }
        .header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
        .header p { font-size: 0.8rem; opacity: 0.8; }
        .doc-title { text-align: center; padding: 1.5rem; border-bottom: 2px solid #e5e7eb; }
        .doc-title h2 { font-size: 1.25rem; font-weight: 700; color: #1e40af; }
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
        .progress-section { margin-bottom: 2rem; }
        .progress-bar { width: 100%; background: #e5e7eb; border-radius: 9999px; height: 10px; overflow: hidden; margin-bottom: 0.5rem; }
        .progress-fill { height: 100%; border-radius: 9999px; transition: width 0.3s; }
        .progress-fill.green { background: #059669; }
        .progress-fill.orange { background: #d97706; }
        .progress-fill.red { background: #dc2626; }
        .progress-text { display: flex; justify-content: space-between; font-size: 0.75rem; color: #6b7280; }
        h3.section-title { font-size: 0.9rem; font-weight: 600; color: #374151; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
        th { background: #f3f4f6; padding: 0.6rem 0.75rem; text-align: left; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
        td { padding: 0.6rem 0.75rem; font-size: 0.8rem; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .status-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .status-disbursed { background: #dbeafe; color: #1e40af; }
        .status-repaying { background: #dbeafe; color: #1e40af; }
        .status-defaulted { background: #fee2e2; color: #991b1b; }
        .guarantor-row { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; }
        .guarantor-avatar { width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700; color: #6b7280; flex-shrink: 0; }
        .guarantor-info { flex: 1; font-size: 0.8rem; }
        .guarantor-info .name { font-weight: 500; }
        .guarantor-info .detail { font-size: 0.7rem; color: #9ca3af; }
        .totals { margin-left: auto; width: 320px; }
        .totals .row { display: flex; justify-content: space-between; padding: 0.4rem 0; font-size: 0.85rem; }
        .totals .row.total { border-top: 2px solid #1e40af; font-weight: 700; font-size: 1rem; color: #1e40af; padding-top: 0.6rem; margin-top: 0.25rem; }
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
            <button onclick="window.print()" style="background: #1e40af; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 500;">
                Print Statement
            </button>
            <a href="{{ route('loans.show', $loan) }}" style="color: #6b7280; text-decoration: none; font-size: 0.85rem; margin-left: 1rem;">Back to Loan</a>
            <a href="{{ route('loans.index') }}" style="color: #6b7280; text-decoration: none; font-size: 0.85rem; margin-left: 1rem;">All Loans</a>
        </div>

        <div class="statement">
        <div class="header">
            @php $company = \App\Models\Company::instance(); @endphp
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                @if ($receiptLogo = app(\App\Services\BrandingService::class)->getLogo('pdf'))
                    <img src="{{ $receiptLogo }}" alt="Logo" style="height: 56px; width: 56px; object-fit: contain; border-radius: 8px; background: white; padding: 4px; flex-shrink: 0;">
                @elseif ($company->logo_path)
                    <img src="{{ $company->logo_url }}" alt="Logo" style="height: 56px; width: 56px; object-fit: contain; border-radius: 8px; background: white; padding: 4px; flex-shrink: 0;">
                @endif
                <div>
                    <h1>{{ $company->name }}</h1>
                    <p>Loan Account Statement</p>
                </div>
            </div>
        </div>

        <div class="doc-title">
            <h2>LOAN STATEMENT</h2>
            <div class="doc-number">{{ $loan->loan_number }}</div>
        </div>

        <div class="content">
            {{-- Loan & Member Info --}}
            <div class="info-grid">
                <div class="info-block">
                    <h3>Borrower Information</h3>
                    <p class="value">{{ $loan->member->first_name ?? 'N/A' }} {{ $loan->member->last_name ?? '' }}</p>
                    <p><span class="label">Staff ID:</span> <span class="value">{{ $loan->member->staff_id_display ?? 'N/A' }}</span></p>
                    <p><span class="label">Region:</span> <span class="value">{{ $loan->member->region->name ?? 'N/A' }}</span></p>
                    <p><span class="label">Phone:</span> <span class="value">{{ $loan->member->phone ?? 'N/A' }}</span></p>
                    <p><span class="label">Email:</span> <span class="value">{{ $loan->member->email ?? 'N/A' }}</span></p>
                    <p><span class="label">Monthly Salary:</span> <span class="value">₦{{ number_format($loan->member->monthly_salary ?? 0, 2) }}</span></p>
                </div>
                <div class="info-block">
                    <h3>Loan Details</h3>
                    <p><span class="label">Loan Number:</span> <span class="value">{{ $loan->loan_number }}</span></p>
                    <p><span class="label">Loan Product:</span> <span class="value">{{ $loan->loanProduct?->name ?? ucfirst($loan->type) }}</span></p>
                    <p><span class="label">Status:</span>
                        @php
                            $statusClass = match($loan->status) {
                                'completed' => 'status-completed',
                                'defaulted' => 'status-defaulted',
                                'repaying' => 'status-repaying',
                                'disbursed' => 'status-disbursed',
                                'pending' => 'status-pending',
                                default => '',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ ucfirst($loan->status) }}</span>
                    </p>
                    <p><span class="label">Application Date:</span> <span class="value">{{ $loan->application_date?->format('d M Y') ?? 'N/A' }}</span></p>
                    <p><span class="label">Disbursement Date:</span> <span class="value">{{ $loan->disbursement_date?->format('d M Y') ?? 'N/A' }}</span></p>
                    <p><span class="label">Maturity Date:</span> <span class="value {{ $loan->isOverdue() ? 'color: #dc2626; font-weight: 600;' : '' }}">{{ $loan->maturity_date?->format('d M Y') ?? 'N/A' }}{{ $loan->isOverdue() ? ' (' . $loan->daysOverdue() . ' days overdue)' : '' }}</span></p>
                    <p><span class="label">Approved By:</span> <span class="value">{{ $loan->approvedBy?->name ?? 'N/A' }}</span></p>
                </div>
            </div>

            {{-- Summary Stat Cards --}}
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-label">Loan Amount</div>
                    <div class="stat-value blue">₦{{ number_format($loan->amount, 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Repaid</div>
                    <div class="stat-value green">₦{{ number_format($loan->total_repaid, 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Outstanding</div>
                    <div class="stat-value orange">₦{{ number_format($loan->outstanding, 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Monthly Repayment</div>
                    <div class="stat-value">₦{{ number_format($loan->monthly_repayment, 2) }}</div>
                </div>
            </div>

            {{-- Loan Terms --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 2rem; padding: 1rem; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                <div class="info-block">
                    <p><span class="label">Principal</span></p>
                    <p class="value">₦{{ number_format($loan->amount, 2) }}</p>
                </div>
                <div class="info-block">
                    <p><span class="label">Interest Rate</span></p>
                    <p class="value">{{ $loan->interest_rate }}%</p>
                </div>
                <div class="info-block">
                    <p><span class="label">Tenure</span></p>
                    <p class="value">{{ $loan->tenure_months }} months</p>
                </div>
            </div>

            {{-- Repayment Progress --}}
            @php
                $repaidPercent = $loan->amount > 0 ? round(($loan->total_repaid / $loan->amount) * 100, 1) : 0;
                $barColor = $repaidPercent >= 100 ? 'green' : ($repaidPercent >= 50 ? 'orange' : 'red');
            @endphp
            <div class="progress-section">
                <h3 class="section-title">Repayment Progress</h3>
                <div class="progress-bar">
                    <div class="progress-fill {{ $barColor }}" style="width: {{ min($repaidPercent, 100) }}%"></div>
                </div>
                <div class="progress-text">
                    <span>₦{{ number_format($loan->total_repaid, 2) }} repaid</span>
                    <span>{{ $repaidPercent }}%</span>
                    <span>₦{{ number_format($loan->outstanding, 2) }} remaining</span>
                </div>
            </div>

            {{-- Purpose --}}
            @if ($loan->purpose)
                <div style="margin-bottom: 2rem;">
                    <h3 class="section-title">Purpose of Loan</h3>
                    <p style="font-size: 0.85rem; color: #374151; line-height: 1.6;">{{ $loan->purpose }}</p>
                </div>
            @endif

            {{-- Repayment History --}}
            <h3 class="section-title">Repayment History</h3>
            @if ($loan->repayments->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Method</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Principal</th>
                            <th class="text-right">Interest</th>
                            <th class="text-right">Balance After</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loan->repayments as $index => $repayment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $repayment->payment_date->format('d M Y') }}</td>
                                <td style="font-family: monospace; font-size: 0.7rem;">{{ $repayment->reference }}</td>
                                <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $repayment->payment_method) }}</td>
                                <td class="text-right" style="font-weight: 600; color: #059669;">₦{{ number_format($repayment->amount, 2) }}</td>
                                <td class="text-right">₦{{ number_format($repayment->principal_portion, 2) }}</td>
                                <td class="text-right">₦{{ number_format($repayment->interest_portion, 2) }}</td>
                                <td class="text-right" style="font-weight: 500;">₦{{ number_format($repayment->outstanding_after, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="totals">
                    <div class="row">
                        <span>Total Paid</span>
                        <span>₦{{ number_format($loan->total_repaid, 2) }}</span>
                    </div>
                    <div class="row">
                        <span>Principal Portion</span>
                        <span>₦{{ number_format($loan->repayments->sum('principal_portion'), 2) }}</span>
                    </div>
                    <div class="row">
                        <span>Interest Portion</span>
                        <span>₦{{ number_format($loan->repayments->sum('interest_portion'), 2) }}</span>
                    </div>
                    <div class="row">
                        <span>Remaining Balance</span>
                        <span style="color: #d97706;">₦{{ number_format($loan->outstanding, 2) }}</span>
                    </div>
                </div>
            @else
                <div style="text-align: center; padding: 2rem; color: #9ca3af; font-size: 0.85rem; background: #f9fafb; border-radius: 8px; border: 1px dashed #e5e7eb;">
                    No repayments recorded yet.
                </div>
            @endif

            {{-- Guarantors --}}
            @if ($loan->guarantors->isNotEmpty())
                <h3 class="section-title" style="margin-top: 1.5rem;">Guarantors</h3>
                <div style="margin-bottom: 1.5rem;">
                    @foreach ($loan->guarantors as $guarantor)
                        @php
                            $gColors = [
                                'pending' => '#d97706',
                                'accepted' => '#059669',
                                'declined' => '#dc2626',
                            ];
                        @endphp
                        <div class="guarantor-row">
                            <div class="guarantor-avatar">
                                {{ strtoupper(substr($guarantor->member->first_name ?? '?', 0, 1) . substr($guarantor->member->last_name ?? '?', 0, 1)) }}
                            </div>
                            <div class="guarantor-info">
                                <div class="name">{{ $guarantor->member->first_name ?? 'Deleted' }} {{ $guarantor->member->last_name ?? '' }}</div>
                                <div class="detail">{{ $guarantor->member->staff_id_display ?? 'N/A' }}</div>
                            </div>
                            <span class="status-badge" style="background: {{ $gColors[$guarantor->status->value] ?? '#e5e7eb' }}20; color: {{ $gColors[$guarantor->status->value] ?? '#6b7280' }};">
                                {{ $guarantor->status->label() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="footer">
            <p><strong>{{ $company->name }}</strong></p>
            <p>This is a computer-generated loan statement. For inquiries, contact the cooperative office.</p>
            @if ($company->footer_note)
                <p style="margin-top: 0.5rem; font-style: italic;">{{ $company->footer_note }}</p>
            @endif
            <p style="margin-top: 0.5rem;">Generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>  <!-- close statement -->
    </div>  <!-- close wrapper -->
</body>
</html>
