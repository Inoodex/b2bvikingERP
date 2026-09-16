<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $settings->site_name ?? 'B2B Viking' }} — Purchase Value vs Last Year Comparison</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #222; line-height: 1.4; padding: 15px; }

        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #28549e; padding-bottom: 8px; }
        .header h1 { margin: 0; color: #28549e; font-size: 17px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header .sub { font-size: 12px; font-weight: bold; margin-top: 2px; color: #333; }
        .header .info { font-size: 9px; color: #666; margin-top: 3px; }

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
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-danger { background-color: #f8d7da; color: #721c24; }
        .badge-light { background-color: #e9ecef; color: #495057; }

        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #888; border-top: 1px solid #eee; padding-top: 6px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $settings->site_name ?? 'B2B Viking ERP' }}</h1>
        <div class="sub">Purchase Value vs Last Year (YoY Comparative Analysis) Report</div>
        <div class="info">
            Generated: {{ now()->format('d M Y, h:i A') }} | Comparison: Year {{ $comparison['current_year'] }} vs Year {{ $comparison['last_year'] }}
        </div>
    </div>

    <!-- Executive KPI Grid -->
    @php
        $variance = $comparison['current_year_value'] - $comparison['last_year_value'];
        $growth = $comparison['growth_percentage'];
    @endphp
    <table class="summary-grid">
        <tr>
            <td class="summary-cell">
                <div class="lbl">Year {{ $comparison['current_year'] }} Spend</div>
                <div class="val val-primary">{{ $currencyIcon }}{{ number_format($comparison['current_year_value'], 2) }}</div>
                <div class="sub-val">Active evaluation year</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Year {{ $comparison['last_year'] }} Spend</div>
                <div class="val">{{ $currencyIcon }}{{ number_format($comparison['last_year_value'], 2) }}</div>
                <div class="sub-val">Prior benchmark year</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Net Annual Variance</div>
                <div class="val {{ $variance >= 0 ? 'val-danger' : 'val-success' }}">
                    {{ $variance >= 0 ? '+' : '-' }}{{ $currencyIcon }}{{ number_format(abs($variance), 2) }}
                </div>
                <div class="sub-val">{{ $variance >= 0 ? 'Spend increase' : 'Spend reduction' }}</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Annual YoY Growth %</div>
                <div class="val {{ $growth >= 0 ? 'val-danger' : 'val-success' }}">
                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 2) }}%
                </div>
                <div class="sub-val">{{ $growth >= 0 ? 'Procurement expansion' : 'Procurement contraction' }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">12-Month Detailed Comparative Spend Matrix</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22%;">Month</th>
                <th class="tr" style="width: 20%;">Year {{ $comparison['last_year'] }} Benchmark</th>
                <th class="tr" style="width: 20%;">Year {{ $comparison['current_year'] }} Spend</th>
                <th class="tr" style="width: 22%;">Variance Amount</th>
                <th class="tc" style="width: 16%;">YoY Growth Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comparison['monthly_matrix'] ?? [] as $row)
                <tr>
                    <td class="font-bold">{{ $row['month'] }}</td>
                    <td class="tr">{{ $currencyIcon }}{{ number_format($row['last_year_value'], 2) }}</td>
                    <td class="tr font-bold">{{ $currencyIcon }}{{ number_format($row['current_year_value'], 2) }}</td>
                    <td class="tr {{ $row['variance_amount'] >= 0 ? 'val-danger' : 'val-success' }}">
                        {{ $row['variance_amount'] >= 0 ? '+' : '-' }}{{ $currencyIcon }}{{ number_format(abs($row['variance_amount']), 2) }}
                    </td>
                    <td class="tc">
                        @if($row['growth_percentage'] > 0)
                            <span class="badge badge-danger">+{{ number_format($row['growth_percentage'], 1) }}%</span>
                        @elseif($row['growth_percentage'] < 0)
                            <span class="badge badge-success">{{ number_format($row['growth_percentage'], 1) }}%</span>
                        @else
                            <span class="badge badge-light">0.0%</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #eef2f8; font-weight: bold;">
                <td>TOTAL (Full Year)</td>
                <td class="tr">{{ $currencyIcon }}{{ number_format($comparison['last_year_value'], 2) }}</td>
                <td class="tr val-primary">{{ $currencyIcon }}{{ number_format($comparison['current_year_value'], 2) }}</td>
                <td class="tr {{ $variance >= 0 ? 'val-danger' : 'val-success' }}">
                    {{ $variance >= 0 ? '+' : '-' }}{{ $currencyIcon }}{{ number_format(abs($variance), 2) }}
                </td>
                <td class="tc">
                    <span class="badge {{ $growth >= 0 ? 'badge-danger' : 'badge-success' }}" style="font-size: 9px;">
                        {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 2) }}%
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ $settings->site_name ?? 'B2B Viking' }} ERP &copy; {{ date('Y') }}. Confidential Enterprise Financial & Procurement Report.
    </div>

</body>
</html>
