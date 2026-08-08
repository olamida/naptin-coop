<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} — {{ $company->name }}</title>
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 10px; color: #1e293b; margin: 0; }
        .header { border-bottom: 2px solid #0F172A; padding-bottom: 8px; margin-bottom: 12px; }
        .header .company { font-size: 17px; font-weight: bold; color: #0F172A; margin: 0 0 2px; }
        .header .meta { font-size: 9px; color: #475569; }
        .header .report-title { font-size: 13px; font-weight: bold; margin-top: 6px; color: #0F172A; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 3px 6px; text-align: left; word-wrap: break-word; }
        th { background: #0F172A; color: #ffffff; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { font-size: 9px; }
        td.num { text-align: right; white-space: nowrap; }
        tr.totals td { font-weight: bold; background: #f1f5f9; }
        .footer { margin-top: 14px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        .footer .qr { float: right; margin-left: 10px; }
        .footer .hash { font-family: 'DejaVu Sans Mono', monospace; font-size: 8px; color: #64748b; word-break: break-all; }
        .footer .note { font-size: 8px; color: #64748b; margin-top: 4px; }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <p class="company">{{ $company->name }}</p>
        <div class="meta">
            @if($company->registration_number) RC {{ $company->registration_number }} &middot; @endif
            {{ $company->address ?? 'Cooperative Society' }}
        </div>
        <div class="report-title">{{ $title }}</div>
        <div class="meta">
            @if(! empty($subtitle)) {{ $subtitle }} &middot; @endif
            @if(! empty($from) && ! empty($to)) Period: {{ $from }} &rarr; {{ $to }} &middot; @endif
            @if(! empty($as_of)) As of {{ $as_of }} &middot; @endif
            @if(! empty($period)) Period: {{ $period }} &middot; @endif
            Generated {{ $generated_at->format('M d, Y h:i A') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    @foreach ($headings as $i => $heading)
                        @php
                            $isMoney = in_array($i, $moneyColumns, true) && isset($row[$i]) && $row[$i] !== '';
                        @endphp
                        <td class="{{ $isMoney ? 'num' : '' }}">
                            @if ($isMoney)
                                &#8358;{{ number_format((float) $row[$i], 2) }}
                            @else
                                {{ $row[$i] ?? '' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <img class="qr" src="{{ $qr }}" width="110" height="110" alt="Report QR">
        <div class="note"><strong>Report integrity hash:</strong></div>
        <div class="hash">{{ $hash }}</div>
        <div class="note">Scan the QR code (or compare the hash above) to verify this report matches the system data it was generated from.</div>
        <div class="note">{{ $company->footer_note ?? '' }}</div>
        <div class="clear"></div>
    </div>
</body>
</html>
