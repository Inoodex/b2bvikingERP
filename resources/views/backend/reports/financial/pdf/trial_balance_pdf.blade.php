<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Trial Balance Statement - {{ $dateRangeLabel }}</title>
    <style>
        @page { margin: 25px 30px; size: a4 portrait; }
        body { font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9pt; color: #2d3748; line-height: 1.35; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; border-bottom: 2px solid #2b4c7e; padding-bottom: 8px; }
        .company-title { font-size: 15pt; font-weight: bold; color: #1a365d; margin: 0; }
        .company-meta { font-size: 8pt; color: #4a5568; margin-top: 3px; line-height: 1.3; }
        .report-title { font-size: 13pt; font-weight: bold; color: #2b4c7e; text-align: right; text-transform: uppercase; margin: 0; }
        .report-meta { font-size: 8pt; color: #4a5568; text-align: right; margin-top: 3px; line-height: 1.3; }
        
        .status-box { width: 100%; margin-bottom: 12px; border-collapse: collapse; background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 4px; }
        .status-box td { padding: 7px 10px; font-size: 8.5pt; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .data-table th { background-color: #2b4c7e; color: #ffffff; font-size: 8.5pt; font-weight: bold; padding: 6px 8px; text-align: left; border: 1px solid #2b4c7e; }
        .data-table td { font-size: 8pt; padding: 5px 8px; border-bottom: 1px solid #e2e8f0; border-left: 1px solid #edf2f7; border-right: 1px solid #edf2f7; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tr { page-break-inside: avoid; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #276749; font-weight: bold; }
        .text-info { color: #2b6cb0; font-weight: bold; }
        .text-danger { color: #c53030; font-weight: bold; }
        .font-bold { font-weight: bold; }

        .badge-type { font-size: 7pt; text-transform: uppercase; padding: 2px 5px; background: #edf2f7; border: 1px solid #cbd5e0; border-radius: 3px; color: #4a5568; }

        .total-row td { background-color: #edf2f7; font-size: 9pt; font-weight: bold; border-top: 2px solid #cbd5e0; border-bottom: 2px solid #a0aec0; padding: 7px 8px; }

        .signature-table { width: 100%; margin-top: 35px; border-collapse: collapse; page-break-inside: avoid; }
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
                <div class="report-title">Trial Balance Statement</div>
                <div class="report-meta">
                    <strong>Period:</strong> {{ $dateRangeLabel }}<br>
                    <strong>Accounting Standard:</strong> IFRS / GAAP<br>
                    <strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }} (User: {{ $generatedBy ?? 'Administrator' }})
                </div>
            </td>
        </tr>
    </table>

    <table class="status-box">
        <tr>
            <td width="60%">
                <strong>Reporting Period:</strong> {{ $dateRangeLabel }} &nbsp;|&nbsp;
                <strong>Active Accounts:</strong> {{ count($reportData) }}
            </td>
            <td width="40%" class="text-right">
                @if($isBalanced)
                    <span class="text-success" style="font-size: 9pt;">✓ BALANCED (Total Debits = Total Credits)</span>
                @else
                    <span class="text-danger" style="font-size: 9pt;">⚠ OUT OF BALANCE (Variance: {{ number_format(abs($totalDebitSum - $totalCreditSum), 2) }})</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Account Code</th>
                <th>Account Name</th>
                <th width="15%">Type</th>
                <th width="20%" class="text-right">Debit Balance (DR)</th>
                <th width="20%" class="text-right">Credit Balance (CR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
                <tr>
                    <td><span class="font-bold">{{ $row['account_code'] }}</span></td>
                    <td><strong>{{ $row['account_name'] }}</strong></td>
                    <td><span class="badge-type">{{ $row['account_type'] }}</span></td>
                    <td class="text-right text-success">
                        {{ $row['debit'] > 0 ? ($settings->currency_icon ?? 'kr.') . ' ' . number_format($row['debit'], 2) : '—' }}
                    </td>
                    <td class="text-right text-info">
                        {{ $row['credit'] > 0 ? ($settings->currency_icon ?? 'kr.') . ' ' . number_format($row['credit'], 2) : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #a0aec0;">
                        No account balances found for the selected period.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right font-bold">Total Balanced Summary:</td>
                <td class="text-right text-success">{{ ($settings->currency_icon ?? 'kr.') }} {{ number_format($totalDebitSum, 2) }}</td>
                <td class="text-right text-info">{{ ($settings->currency_icon ?? 'kr.') }} {{ number_format($totalCreditSum, 2) }}</td>
            </tr>
        </tfoot>
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
        B2B Viking ERP — Core Financial Statements | Ephemeral Live Trial Balance | Generated {{ now()->format('Y-m-d H:i:s') }}
    </div>

</body>
</html>
