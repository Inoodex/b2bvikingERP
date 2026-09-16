<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $settings->site_name ?? 'B2B Viking' }} — Total Purchase Value Periodic Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #222; line-height: 1.4; padding: 15px; }

        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #28549e; padding-bottom: 8px; }
        .header h1 { margin: 0; color: #28549e; font-size: 17px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header .sub { font-size: 12px; font-weight: bold; margin-top: 2px; color: #333; }
        .header .info { font-size: 9px; color: #666; margin-top: 3px; }

        .vendor-card { background: #f4f6f9; border: 1px solid #d2d6de; padding: 8px 12px; margin-bottom: 12px; border-radius: 4px; }
        .vendor-card .name { font-size: 13px; font-weight: bold; color: #28549e; }
        .vendor-card .details { font-size: 9px; color: #555; margin-top: 2px; }

        .summary-grid { width: 100%; margin-bottom: 14px; border-collapse: separate; border-spacing: 6px; }
        .summary-cell { width: 25%; padding: 10px 8px; border: 1px solid #d2d6de; border-radius: 4px; background: #fafbfc; text-align: center; vertical-align: top; }
        .summary-cell .lbl { font-size: 8px; color: #666; text-transform: uppercase; font-weight: bold; }
        .summary-cell .val { font-size: 14px; font-weight: bold; margin-top: 3px; }
        .summary-cell .sub-val { font-size: 8px; color: #777; margin-top: 2px; }
        .val-primary { color: #28549e; }
        .val-success { color: #28a745; }
        .val-danger { color: #dc3545; }
        .val-info { color: #17a2b8; }

        .section-title { font-size: 11px; font-weight: bold; margin: 10px 0 6px; padding: 3px 0; border-bottom: 1px solid #ccc; color: #333; text-transform: uppercase; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.data-table th { background: #28549e; color: #fff; border: 1px solid #28549e; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        table.data-table td { border: 1px solid #e1e4e8; padding: 5px 8px; vertical-align: middle; font-size: 9px; }
        table.data-table tr:nth-child(even) td { background: #f9fafb; }
        
        .tc { text-align: center; }
        .tr { text-align: right; }
        .font-bold { font-weight: bold; }

        .badge { display: inline-block; padding: 2px 5px; font-size: 8px; font-weight: bold; border-radius: 2px; }
        .badge-po { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
        .badge-share { background-color: #e8f0fe; color: #1a73e8; border: 1px solid #d2e3fc; }

        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #888; border-top: 1px solid #eee; padding-top: 6px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $settings->site_name ?? 'B2B Viking ERP' }}</h1>
        <div class="sub">Total Purchase Value (Periodic Analysis) Report</div>
        <div class="info">
            Generated: {{ now()->format('d M Y, h:i A') }}
            @if(!empty($filters['start_date'])) | From: {{ $filters['start_date'] }} @endif
            @if(!empty($filters['end_date'])) | To: {{ $filters['end_date'] }} @endif
            @if(isset($selectedVendor)) | Vendor: {{ $selectedVendor->shop_name ?? $selectedVendor->name }} @else | All Suppliers @endif
        </div>
    </div>

    @isset($selectedVendor)
        <div class="vendor-card">
            <div class="name">{{ $selectedVendor->shop_name ?? $selectedVendor->name }}</div>
            <div class="details">
                Email: {{ $selectedVendor->email ?? '—' }} | Phone: {{ $selectedVendor->phone ?? '—' }} | Country: {{ $selectedVendor->country ?? 'Denmark' }}
                @if($selectedVendor->currency) | Billing Currency: {{ $selectedVendor->currency->currency_code }} ({{ $selectedVendor->currency->currency_icon }}) @endif
            </div>
        </div>
    @endisset

    <!-- Executive KPI Grid -->
    @php
        $paidPct = ($summary['total_purchase_value'] > 0) ? round(($summary['total_paid_value'] / $summary['total_purchase_value']) * 100, 1) : 0;
        $duePct = ($summary['total_purchase_value'] > 0) ? round(($summary['total_due_value'] / $summary['total_purchase_value']) * 100, 1) : 0;
        $grandTotal = $summary['total_purchase_value'] ?? 0;
    @endphp
    <table class="summary-grid">
        <tr>
            <td class="summary-cell">
                <div class="lbl">POs Issued</div>
                <div class="val val-primary">{{ number_format($summary['total_pos']) }}</div>
                <div class="sub-val">Procurement orders</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Total Purchase Value</div>
                <div class="val val-primary">{{ $currencyIcon }}{{ number_format($summary['total_purchase_value'], 2) }}</div>
                <div class="sub-val">Gross committed spend</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Amount Disbursed (Paid)</div>
                <div class="val val-success">{{ $currencyIcon }}{{ number_format($summary['total_paid_value'], 2) }}</div>
                <div class="sub-val">{{ $paidPct }}% settled</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Outstanding Balance (Due)</div>
                <div class="val val-danger">{{ $currencyIcon }}{{ number_format($summary['total_due_value'], 2) }}</div>
                <div class="sub-val">{{ $duePct }}% pending payment</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Periodic Spend & Order Breakdown</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22%;">Period / Month</th>
                <th class="tc" style="width: 12%;">Total POs</th>
                <th class="tr" style="width: 16%;">Gross Subtotal</th>
                <th class="tr" style="width: 14%;">Discount Saved</th>
                <th class="tr" style="width: 14%;">Tax / VAT</th>
                <th class="tr" style="width: 16%;">Net Total Spend</th>
                <th class="tc" style="width: 6%;">Share %</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
                @php
                    $sharePct = ($grandTotal > 0) ? round(($row->net_total / $grandTotal) * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="font-bold">{{ $row->period }}</td>
                    <td class="tc">
                        <span class="badge badge-po">{{ number_format($row->po_count) }}</span>
                    </td>
                    <td class="tr">{{ $currencyIcon }}{{ number_format($row->subtotal, 2) }}</td>
                    <td class="tr">
                        @if($row->discount > 0)
                            {{ $currencyIcon }}{{ number_format($row->discount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="tr">
                        @if($row->tax > 0)
                            {{ $currencyIcon }}{{ number_format($row->tax, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="tr font-bold val-primary">{{ $currencyIcon }}{{ number_format($row->net_total, 2) }}</td>
                    <td class="tc">
                        <span class="badge badge-share">{{ $sharePct }}%</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="tc" style="padding: 20px; color: #888;">
                        No procurement records found for the selected period.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($reportData) > 0)
            <tfoot>
                <tr style="background: #eef2f8; font-weight: bold;">
                    <td>GRAND TOTAL</td>
                    <td class="tc">{{ number_format($reportData->sum('po_count')) }}</td>
                    <td class="tr">{{ $currencyIcon }}{{ number_format($reportData->sum('subtotal'), 2) }}</td>
                    <td class="tr">
                        @if($reportData->sum('discount') > 0)
                            {{ $currencyIcon }}{{ number_format($reportData->sum('discount'), 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="tr">
                        @if($reportData->sum('tax') > 0)
                            {{ $currencyIcon }}{{ number_format($reportData->sum('tax'), 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="tr val-primary" style="font-size: 10px;">{{ $currencyIcon }}{{ number_format($reportData->sum('net_total'), 2) }}</td>
                    <td class="tc">100.0%</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        {{ $settings->site_name ?? 'B2B Viking' }} ERP &copy; {{ date('Y') }}. Confidential Enterprise Financial & Procurement Report.
    </div>

</body>
</html>
