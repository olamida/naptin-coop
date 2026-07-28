<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Certificate — {{ $account->member->first_name ?? '' }} {{ $account->member->last_name ?? '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: #1f2937; background: #f3f4f6; padding: 2rem; }
        .certificate-wrapper { max-width: 900px; margin: 0 auto; }
        .no-print { margin-bottom: 1.5rem; text-align: right; }
        .no-print button { background: #1e40af; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 500; }
        .no-print a { color: #6b7280; text-decoration: none; font-size: 0.85rem; margin-left: 1rem; }

        .certificate {
            background: white;
            border: 3px solid #1e40af;
            border-radius: 4px;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }
        .certificate::before {
            content: '';
            position: absolute;
            inset: 8px;
            border: 1px solid #93c5fd;
            border-radius: 2px;
            pointer-events: none;
        }
        .certificate::after {
            content: 'NAPTIN COOPERATIVE';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 5rem;
            font-weight: 800;
            color: rgba(30, 64, 175, 0.03);
            text-transform: uppercase;
            letter-spacing: 0.3em;
            pointer-events: none;
            white-space: nowrap;
        }

        .cert-header { text-align: center; margin-bottom: 2rem; position: relative; z-index: 1; }
        .cert-header .logo-area {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .cert-header .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        .cert-header .org-name { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; color: #1e40af; }
        .cert-header .org-sub { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.2em; color: #6b7280; margin-top: 0.25rem; }

        .cert-title {
            text-align: center;
            margin: 2rem 0;
            position: relative;
            z-index: 1;
        }
        .cert-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }
        .cert-title .cert-number {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.5rem;
            font-family: monospace;
            letter-spacing: 0.05em;
        }
        .cert-title .cert-line {
            width: 120px;
            height: 2px;
            background: #1e40af;
            margin: 0.75rem auto 0;
        }

        .cert-body { position: relative; z-index: 1; margin-bottom: 2rem; }
        .cert-body .cert-text {
            text-align: center;
            font-size: 0.95rem;
            line-height: 1.8;
            color: #374151;
            max-width: 600px;
            margin: 0 auto 2rem;
        }
        .cert-body .member-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e40af;
            text-decoration: underline;
            text-decoration-color: #93c5fd;
            text-underline-offset: 6px;
        }
        .cert-body .staff-id { font-weight: 600; color: #1e40af; }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .details-grid .detail-item { display: flex; flex-direction: column; gap: 0.15rem; }
        .details-grid .detail-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; }
        .details-grid .detail-value { font-size: 0.9rem; font-weight: 500; color: #1f2937; }

        .share-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 2rem 0;
        }
        .share-box {
            text-align: center;
            padding: 1.25rem;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
        }
        .share-box .share-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            margin-bottom: 0.5rem;
        }
        .share-box .share-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e40af;
        }
        .share-box .share-sub { font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem; }

        .cert-declaration {
            text-align: center;
            font-size: 0.85rem;
            line-height: 1.8;
            color: #4b5563;
            margin: 2rem 0;
            padding: 1rem 2rem;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2rem;
            margin-top: 2.5rem;
            position: relative;
            z-index: 1;
        }
        .sig-block { text-align: center; }
        .sig-line {
            width: 100%;
            height: 1px;
            background: #374151;
            margin-bottom: 0.5rem;
            margin-top: 2rem;
        }
        .sig-name { font-size: 0.85rem; font-weight: 600; color: #1f2937; }
        .sig-title { font-size: 0.7rem; color: #6b7280; margin-top: 0.15rem; }

        .cert-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid #1e40af;
            position: relative;
            z-index: 1;
        }
        .cert-footer p { font-size: 0.7rem; color: #9ca3af; line-height: 1.6; }
        .cert-footer .date-line { font-size: 0.8rem; color: #6b7280; margin-top: 0.5rem; }

        .corner-ornament {
            position: absolute;
            width: 40px;
            height: 40px;
            z-index: 1;
        }
        .corner-ornament.top-left { top: 16px; left: 16px; border-top: 2px solid #93c5fd; border-left: 2px solid #93c5fd; }
        .corner-ornament.top-right { top: 16px; right: 16px; border-top: 2px solid #93c5fd; border-right: 2px solid #93c5fd; }
        .corner-ornament.bottom-left { bottom: 16px; left: 16px; border-bottom: 2px solid #93c5fd; border-left: 2px solid #93c5fd; }
        .corner-ornament.bottom-right { bottom: 16px; right: 16px; border-bottom: 2px solid #93c5fd; border-right: 2px solid #93c5fd; }

        @media print {
            body { background: white; padding: 0; }
            .certificate { border-width: 2px; }
            .no-print { display: none !important; }
            .share-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="certificate-wrapper">
        <div class="no-print">
            <button onclick="window.print()">Print Certificate</button>
            <a href="{{ route('shares.accounts') }}">Share Accounts</a>
            <a href="{{ route('portal.shares') }}">My Shares</a>
        </div>

        <div class="certificate">
            <div class="corner-ornament top-left"></div>
            <div class="corner-ornament top-right"></div>
            <div class="corner-ornament bottom-left"></div>
            <div class="corner-ornament bottom-right"></div>

            {{-- Header --}}
            <div class="cert-header">
                <div class="logo-area">
                    <div class="logo-icon">&#9878;</div>
                    <div>
                        <div class="org-name">NAPTIN Staff Thrift Cooperative</div>
                        <div class="org-sub">Cooperative Society</div>
                    </div>
                </div>
            </div>

            {{-- Title --}}
            <div class="cert-title">
                <h1>Share Certificate</h1>
                <div class="cert-number">CERT/SHR/{{ $account->member->staff_id ?? 'N/A' }}/{{ now()->format('Y') }}</div>
                <div class="cert-line"></div>
            </div>

            {{-- Body --}}
            <div class="cert-body">
                <div class="cert-text">
                    This is to certify that
                    <br>
                    <span class="member-name">{{ $account->member->first_name ?? 'N/A' }} {{ $account->member->middle_name ?? '' }} {{ $account->member->last_name ?? '' }}</span>
                    <br>
                    <span class="staff-id">Staff ID: {{ $account->member->staff_id ?? 'N/A' }}</span>
                    <br><br>
                    is a registered member of the NAPTIN Staff Thrift Cooperative Society and holds the following shares:
                </div>

                {{-- Member Details --}}
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Full Name</span>
                        <span class="detail-value">{{ $account->member->first_name ?? 'N/A' }} {{ $account->member->middle_name ?? '' }} {{ $account->member->last_name ?? '' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Staff ID</span>
                        <span class="detail-value">{{ $account->member->staff_id ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Region</span>
                        <span class="detail-value">{{ $account->member->region->name ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone</span>
                        <span class="detail-value">{{ $account->member->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value">{{ $account->member->email ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Account Status</span>
                        <span class="detail-value" style="text-transform: capitalize;">{{ $account->status }}</span>
                    </div>
                </div>

                {{-- Share Summary --}}
                <div class="share-summary">
                    <div class="share-box">
                        <div class="share-label">Total Shares</div>
                        <div class="share-value">{{ number_format($account->total_shares) }}</div>
                        <div class="share-sub">units</div>
                    </div>
                    <div class="share-box">
                        <div class="share-label">Price Per Share</div>
                        <div class="share-value">&#8358;{{ number_format($account->share_price, 2) }}</div>
                        <div class="share-sub">per unit</div>
                    </div>
                    <div class="share-box">
                        <div class="share-label">Total Share Value</div>
                        <div class="share-value" style="color: #059669;">&#8358;{{ number_format($account->total_value, 2) }}</div>
                        <div class="share-sub">portfolio value</div>
                    </div>
                </div>

                {{-- Declaration --}}
                <div class="cert-declaration">
                    The above-mentioned shares are fully paid up and held in accordance with the rules and regulations
                    of the NAPTIN Staff Thrift Cooperative Society. This certificate is issued as evidence of share ownership
                    and is not transferable without the approval of the Board of Directors.
                </div>

                {{-- Signatures --}}
                <div class="signatures">
                    <div class="sig-block">
                        <div class="sig-line"></div>
                        <div class="sig-name">Chairman</div>
                        <div class="sig-title">Board of Directors</div>
                    </div>
                    <div class="sig-block">
                        <div class="sig-line"></div>
                        <div class="sig-name">Secretary</div>
                        <div class="sig-title">Board of Directors</div>
                    </div>
                    <div class="sig-block">
                        <div class="sig-line"></div>
                        <div class="sig-name">Treasurer</div>
                        <div class="sig-title">Board of Directors</div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="cert-footer">
                <p><strong>NAPTIN Staff Thrift Cooperative Society</strong></p>
                <p>This is a computer-generated share certificate. For inquiries, contact the cooperative office.</p>
                <p class="date-line">Date of Issue: {{ now()->format('d M Y') }}</p>
                @if ($firstPurchase)
                    <p style="font-size: 0.7rem; color: #9ca3af; margin-top: 0.25rem;">First Share Purchase: {{ $firstPurchase->transaction_date->format('d M Y') }}</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
