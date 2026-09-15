<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Supplier Statement of Account - {{ $statement['vendor']->shop_name ?? $statement['vendor']->name }}</title>
    <style>
        @page { margin: 25px 30px; size: a4 portrait; }
        body { font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 8.5pt; color: #2d3748; line-height: 1.3; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; border-bottom: 2px solid #2b4c7e; padding-bottom: 8px; }
        .company-title { font-size: 15pt; font-weight: bold; color: #1a365d; margin: 0; }
        .company-meta { font-size: 8pt; color: #4a5568; margin-top: 3px; line-height: 1.3; }
        .report-title { font-size: 13pt; font-weight: bold; color: #2b4c7e; text-align: right; text-transform: uppercase; margin: 0; }
        .report-meta { font-size: 8pt; color: #4a5568; text-align: right; margin-top: 3px; line-height: 1.3; }
        
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-grid td { vertical-align: top; }
        .card-box { border: 1px solid #cbd5e0; background-color: #f8fafc; border-radius: 4px; padding: 10px; font-size: 8pt; }
        .card-box-title { font-size: 8.5pt; font-weight: bold; color: #1a365d; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 6px; text-transform: uppercase; }

        .summary-mini-table { width: 100%; border-collapse: collapse; }
        .summary-mini-table td { padding: 2px 0; font-size: 8pt; }
        .summary-mini-table .total-line { border-top: 1px solid #cbd5e0; font-weight: bold; font-size: 8.5pt; color: #c53030; padding-top: 4px; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .data-table th { background-color: #2b4c7e; color: #ffffff; font-size: 8pt; font-weight: bold; padding: 6px 7px; text-align: left; border: 1px solid #2b4c7e; }
        .data-table td { font-size: 7.5pt; padding: 5px 7px; border-bottom: 1px solid #e2e8f0; border-left: 1px solid #edf2f7; border-right: 1px solid #edf2f7; vertical-align: middle; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tr { page-break-inside: avoid; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-danger { color: #c53030; font-weight: bold; }

        .signature-table { width: 100%; margin-top: 40px; border-collapse: collapse; page-break-inside: avoid; }
        .signature-table td { width: 50%; text-align: center; vertical-align: bottom; }
        .sig-line { width: 70%; margin: 0 auto 5px auto; border-top: 1px solid #718096; }
        .sig-title { font-size: 8pt; font-weight: bold; color: #2d3748; }
        .sig-sub { font-size: 7pt; color: #718096; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 7pt; color: #a0aec0; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 4px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="55%">
                <div class="company-title">{{ $company?->name ?? $settings?->site_name ?? 'B2B Viking ERP' }}</div>
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
                <div class="report-title">Statement of Account</div>
                <div class="report-meta">
                    <strong>Period:</strong> {{ ($fromDate && $toDate) ? ($fromDate . ' to ' . $toDate) : 'Full Account History' }}<br>
                    <strong>Statement Date:</strong> {{ ($generatedAt ?? now())->format('d M Y, h:i A') }}<br>
                    <strong>Prepared By:</strong> {{ $generatedBy ?? 'Administrator' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="info-grid">
        <tr>
            <td width="49%">
                <div class="card-box">
                    <div class="card-box-title">Supplier Details</div>
                    <strong>{{ $statement['vendor']->shop_name ?? $statement['vendor']->name }}</strong><br>
                    @if(!empty($statement['vendor']->contact_person))
                        Attn: {{ $statement['vendor']->contact_person }}<br>
                    @endif
                    Supplier Code: {{ $statement['vendor']->code ?? 'N/A' }}<br>
                    Phone: {{ $statement['vendor']->phone ?? 'N/A' }}<br>
                    Email: {{ $statement['vendor']->email ?? 'N/A' }}
                    @if(!empty($statement['vendor']->address))
                        <br>Address: {{ $statement['vendor']->address }}
                    @endif
                </div>
            </td>
            <td width="2%">&nbsp;</td>
            <td width="49%">
                <div class="card-box">
                    <div class="card-box-title">Statement Summary</div>
                    @php $currency = $settings?->currency_icon ?? 'kr. '; @endphp
                    <table class="summary-mini-table">
                        <tr>
                            <td>Total Invoiced / Billed:</td>
                            <td class="text-right font-bold">{{ $currency }}{{ number_format($statement['total_billed'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Total Settled / Paid:</td>
                            <td class="text-right font-bold" style="color: #276749;">{{ $currency }}{{ number_format($statement['total_paid'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Debit Notes / Claims:</td>
                            <td class="text-right font-bold" style="color: #c05621;">{{ $currency }}{{ number_format($statement['total_debit_notes'], 2) }}</td>
                        </tr>
                        <tr class="total-line">
                            <td>Net Balance Outstanding:</td>
                            <td class="text-right font-bold">{{ $currency }}{{ number_format($statement['outstanding_balance'], 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="10%">Type</th>
                <th width="18%">Voucher / Ref</th>
                <th width="16%">PO / Order Ref</th>
                <th width="14%" class="text-right">Billed (Debit)</th>
                <th width="15%" class="text-right">Paid / Claim (Credit)</th>
                <th width="15%" class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $currency = $settings?->currency_icon ?? 'kr. '; @endphp
            @forelse($statement['transactions'] as $tx)
                <tr>
                    <td>{{ $tx['date'] }}</td>
                    <td><span style="font-weight: 600;">{{ $tx['type'] }}</span></td>
                    <td><code>{{ $tx['reference'] }}</code></td>
                    <td>{{ $tx['po_no'] ?? '-' }}</td>
                    <td class="text-right">{{ (float)$tx['debit'] > 0 ? $currency . number_format($tx['debit'], 2) : '-' }}</td>
                    <td class="text-right" style="color: #276749;">{{ (float)$tx['credit'] > 0 ? $currency . number_format($tx['credit'], 2) : '-' }}</td>
                    <td class="text-right font-bold">{{ $currency }}{{ number_format($tx['running_balance'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #718096;">No statement transactions recorded for this supplier.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #edf2f7; font-weight: bold;">
                <td colspan="4" class="text-right">CLOSING OUTSTANDING PAYABLE:</td>
                <td class="text-right">{{ $currency }}{{ number_format($statement['total_billed'], 2) }}</td>
                <td class="text-right" style="color: #276749;">{{ $currency }}{{ number_format($statement['total_paid'] + $statement['total_debit_notes'], 2) }}</td>
                <td class="text-right text-danger font-bold">{{ $currency }}{{ number_format($statement['outstanding_balance'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Accounts Department</div>
                <div class="sig-sub">Authorized Signature & Seal</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Supplier Acknowledgement</div>
                <div class="sig-sub">Authorized Representative Stamp & Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Official Supplier Statement of Account • Confidential Financial Document • Produced by ERP System
    </div>

</body>
</html>
