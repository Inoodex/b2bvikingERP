<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Balance Sheet - {{ $dateRangeLabel }}</title>
    <style>
        @page { margin: 25px 30px; size: a4 portrait; }
        body { font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9pt; color: #2d3748; line-height: 1.35; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; border-bottom: 2px solid #2b4c7e; padding-bottom: 8px; }
        .company-title { font-size: 15pt; font-weight: bold; color: #1a365d; margin: 0; }
        .company-meta { font-size: 8pt; color: #4a5568; margin-top: 3px; line-height: 1.3; }
        .report-title { font-size: 13pt; font-weight: bold; color: #2b4c7e; text-align: right; text-transform: uppercase; margin: 0; }
        .report-meta { font-size: 8pt; color: #4a5568; text-align: right; margin-top: 3px; line-height: 1.3; }
        
        .status-box { width: 100%; margin-bottom: 12px; border-collapse: collapse; background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 4px; }
        .status-box td { padding: 7px 10px; font-size: 8.5pt; }

        .section-header { background-color: #2b4c7e; color: #ffffff; padding: 5px 10px; font-size: 9.5pt; font-weight: bold; text-transform: uppercase; margin-top: 10px; border-radius: 3px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; margin-top: 3px; }
        .data-table th { background-color: #edf2f7; color: #2d3748; font-size: 8.5pt; font-weight: bold; padding: 5px 8px; text-align: left; border: 1px solid #cbd5e0; }
        .data-table td { font-size: 8pt; padding: 5px 8px; border-bottom: 1px solid #e2e8f0; border-left: 1px solid #edf2f7; border-right: 1px solid #edf2f7; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tr { page-break-inside: avoid; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #276749; font-weight: bold; }
        .text-primary { color: #2b4c7e; font-weight: bold; }
        .text-danger { color: #c53030; font-weight: bold; }
        .font-bold { font-weight: bold; }

        .subtotal-row td { background-color: #edf2f7; font-size: 9pt; font-weight: bold; border-top: 1px solid #cbd5e0; border-bottom: 1px solid #cbd5e0; padding: 6px 8px; }
        .equation-box { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 15px; border: 2px solid #2b4c7e; }
        .equation-box td { padding: 10px 14px; font-size: 10pt; font-weight: bold; }

        .signature-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .signature-table td { width: 33.33%; text-align: center; vertical-align: bottom; }
        .sig-line { width: 75%; margin: 0 auto 5px auto; border-top: 1px solid #718096; }
        .sig-title { font-size: 8.5pt; font-weight: bold; color: #2d3748; }
        .sig-sub { font-size: 7.5pt; color: #718096; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 7.5pt; color: #a0aec0; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="55%">
                <div class="company-title">{{ $company?->name ?? $settings?->site_name ?? '' }}</div>
                <div class="company-meta">
                    @if(!empty($company?->address ?? $settings?->address))
                        {{ $company?->address ?? $settings?->address }}<br>
                    @endif
                    @php
                        $contactEmail = $company?->email ?? $settings?->contact_email;
                        $contactPhone = $company?->phone ?? $settings?->phone;
                    @endphp
                    @if($contactEmail)
                        Email: {{ $contactEmail }}
                    @endif
                    @if($contactEmail && $contactPhone)
                        &nbsp;|&nbsp;
                    @endif
                    @if($contactPhone)
                        Phone: {{ $contactPhone }}
                    @endif
                </div>
            </td>
            <td width="45%">
                <div class="report-title">Balance Sheet</div>
                <div class="report-meta">
                    <strong>{{ $dateRangeLabel }}</strong><br>
                    <strong>Statement:</strong> Financial Position (IFRS / GAAP)<br>
                    <strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }} (User: {{ $generatedBy ?? 'Administrator' }})
                </div>
            </td>
        </tr>
    </table>

    <table class="status-box">
        <tr>
            <td width="60%">
                <strong>Snapshot Date:</strong> {{ date('d F Y', strtotime($asOfDate)) }}
            </td>
            <td width="40%" class="text-right">
                @if($isBalanced)
                    <span class="text-success" style="font-size: 9pt;">✓ BALANCED (Assets = Liabilities + Equity)</span>
                @else
                    <span class="text-danger" style="font-size: 9pt;">⚠ VARIANCE: {{ number_format(abs($totalAssets - $totalLiabAndEquity), 2) }}</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- 1. Total Assets -->
    <div class="section-header">1. Assets</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Account Code</th>
                <th>Asset Account Name</th>
                <th width="25%" class="text-right">Balance ({{ $settings->currency_icon ?? 'kr.' }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assetsData as $row)
                <tr>
                    <td><span class="font-bold">{{ $row['code'] }}</span></td>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-right text-primary font-bold">{{ number_format($row['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: 10px; color: #a0aec0;">No active asset accounts found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="subtotal-row">
                <td colspan="2" class="text-right">Total Assets:</td>
                <td class="text-right text-primary font-bold">{{ ($settings->currency_icon ?? 'kr.') }} {{ number_format($totalAssets, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- 2. Liabilities -->
    <div class="section-header" style="background-color: #4a5568;">2. Liabilities</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Account Code</th>
                <th>Liability Account Name</th>
                <th width="25%" class="text-right">Balance ({{ $settings->currency_icon ?? 'kr.' }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($liabilitiesData as $row)
                <tr>
                    <td><span class="font-bold">{{ $row['code'] }}</span></td>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-right text-danger font-bold">{{ number_format($row['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: 10px; color: #a0aec0;">No active liability accounts found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="subtotal-row">
                <td colspan="2" class="text-right">Total Liabilities:</td>
                <td class="text-right text-danger font-bold">{{ ($settings->currency_icon ?? 'kr.') }} {{ number_format($totalLiabilities, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- 3. Owner's Equity -->
    <div class="section-header" style="background-color: #2c5282;">3. Owner's Equity</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Account Code</th>
                <th>Equity Account Name</th>
                <th width="25%" class="text-right">Balance ({{ $settings->currency_icon ?? 'kr.' }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($equityData as $row)
                <tr>
                    <td><span class="font-bold">{{ $row['code'] }}</span></td>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-right text-success font-bold">{{ number_format($row['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: 10px; color: #a0aec0;">No active equity accounts found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="subtotal-row">
                <td colspan="2" class="text-right">Total Equity:</td>
                <td class="text-right text-success font-bold">{{ ($settings->currency_icon ?? 'kr.') }} {{ number_format($totalEquity, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Balanced Accounting Equation Proof -->
    <table class="equation-box" style="background-color: #f7fafc;">
        <tr>
            <td width="50%" class="text-primary">
                TOTAL ASSETS: &nbsp; {{ ($settings->currency_icon ?? 'kr.') }} {{ number_format($totalAssets, 2) }}
            </td>
            <td width="50%" class="text-right {{ $isBalanced ? 'text-success' : 'text-danger' }}">
                TOTAL LIABILITIES & EQUITY: &nbsp; {{ ($settings->currency_icon ?? 'kr.') }} {{ number_format($totalLiabAndEquity, 2) }}
            </td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Prepared By</div>
                <div class="sig-sub">{{ auth()->user()->name ?? 'Accountant' }}</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Audited By</div>
                <div class="sig-sub">Senior Auditor</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Approved By</div>
                <div class="sig-sub">Chief Financial Officer</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        B2B Viking ERP — Core Financial Statements | Ephemeral Live Balance Sheet | Generated {{ now()->format('Y-m-d H:i:s') }}
    </div>

</body>
</html>
