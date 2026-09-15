<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>General Ledger - {{ $accountLabel }}</title>
    <style>
        @page { margin: 25px 30px; size: a4 landscape; }
        body { font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9pt; color: #2d3748; line-height: 1.3; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; border-bottom: 2px solid #2b4c7e; padding-bottom: 8px; }
        .company-title { font-size: 16pt; font-weight: bold; color: #1a365d; margin: 0; }
        .company-meta { font-size: 8pt; color: #4a5568; margin-top: 3px; line-height: 1.3; }
        .report-title { font-size: 14pt; font-weight: bold; color: #2b4c7e; text-align: right; text-transform: uppercase; margin: 0; }
        .report-meta { font-size: 8pt; color: #4a5568; text-align: right; margin-top: 3px; line-height: 1.3; }
        
        .filter-badge-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 4px; }
        .filter-badge-table td { padding: 6px 10px; font-size: 8.5pt; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .data-table th { background-color: #2b4c7e; color: #ffffff; font-size: 8.5pt; font-weight: bold; padding: 6px 8px; text-align: left; border: 1px solid #2b4c7e; }
        .data-table td { font-size: 8pt; padding: 5px 8px; border-bottom: 1px solid #e2e8f0; border-left: 1px solid #edf2f7; border-right: 1px solid #edf2f7; vertical-align: top; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tr { page-break-inside: avoid; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #276749; font-weight: bold; }
        .text-info { color: #2b6cb0; font-weight: bold; }
        .font-bold { font-weight: bold; }

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
                <div class="report-title">General Ledger Report</div>
                <div class="report-meta">
                    <strong>Account:</strong> {{ $accountLabel }}<br>
                    <strong>Period:</strong> {{ $dateRangeLabel }}<br>
                    <strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }} (User: {{ $generatedBy ?? 'Administrator' }})
                </div>
            </td>
        </tr>
    </table>

    <table class="filter-badge-table">
        <tr>
            <td width="40%">
                <strong>Account Scope:</strong> {{ $accountLabel }}
            </td>
            <td width="30%">
                <strong>Period:</strong> {{ $dateRangeLabel }}
            </td>
            <td width="30%" class="text-right">
                <strong>Total Transactions:</strong> {{ number_format($lines->count()) }} records
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">Entry Date</th>
                <th width="12%">Voucher No</th>
                <th width="24%">Account Code & Name</th>
                <th width="28%">Narration & Reference</th>
                <th width="13%" class="text-right">Debit (DR)</th>
                <th width="13%" class="text-right">Credit (CR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lines as $row)
                <tr>
                    <td>{{ $row->journalEntry?->entry_date ? $row->journalEntry->entry_date->format('Y-m-d') : 'N/A' }}</td>
                    <td><span class="font-bold">{{ $row->journalEntry?->entry_no ?? 'N/A' }}</span></td>
                    <td>
                        <span class="font-bold">{{ $row->account?->account_code }}</span> — {{ $row->account?->account_name }}
                    </td>
                    <td>
                        {{ $row->journalEntry?->narration ?? 'N/A' }}
                        @if($row->journalEntry?->reference_type)
                            <br><span style="font-size: 7.5pt; color: #718096;">Ref: {{ class_basename($row->journalEntry->reference_type) }} #{{ $row->journalEntry->reference_id }}</span>
                        @endif
                    </td>
                    <td class="text-right text-success">
                        {{ $row->debit > 0 ? ($settings->currency_icon ?? 'kr.') . ' ' . number_format((float)$row->debit, 2) : '—' }}
                    </td>
                    <td class="text-right text-info">
                        {{ $row->credit > 0 ? ($settings->currency_icon ?? 'kr.') . ' ' . number_format((float)$row->credit, 2) : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #a0aec0;">
                        No journal entries or transactions found for the specified period.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">Total Period Movement:</td>
                <td class="text-right text-success">{{ ($settings->currency_icon ?? 'kr.') }} {{ number_format($totalDebit, 2) }}</td>
                <td class="text-right text-info">{{ ($settings->currency_icon ?? 'kr.') }} {{ number_format($totalCredit, 2) }}</td>
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
                <div class="sig-title">Verified By</div>
                <div class="sig-sub">Internal Audit</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Approved By</div>
                <div class="sig-sub">Chief Financial Officer</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        B2B Viking ERP — Core Financial Statements | Ephemeral Live Ledger Document | Generated {{ now()->format('Y-m-d H:i:s') }}
    </div>

</body>
</html>
