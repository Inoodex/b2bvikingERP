<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer AR Aging Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .header-table { width: 100%; border-bottom: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 20px; }
        .company-title { font-size: 20px; font-weight: bold; color: #1e3a8a; }
        .report-title { font-size: 16px; font-weight: bold; color: #2563eb; text-align: right; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .report-table th { background-color: #f1f5f9; padding: 8px; font-size: 10px; border: 1px solid #cbd5e1; text-align: left; }
        .report-table td { padding: 8px; font-size: 10px; border: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-success { color: #16a34a; font-weight: bold; }
        .text-primary { color: #2563eb; font-weight: bold; }
        .footer { margin-top: 30px; font-size: 9px; color: #64748b; text-align: center; border-top: 1px solid #cbd5e1; padding-top: 10px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                <div class="company-title">{{ $generalSetting->site_name ?? 'B2B Viking ERP' }}</div>
                <div>Executive Accounts Receivable (AR) Portfolio Analysis</div>
            </td>
            <td class="report-title">
                CUSTOMER AR AGING REPORT<br>
                <small style="font-size: 10px; font-weight: normal; color: #64748b;">Generated: {{ date('Y-m-d H:i:s') }}</small>
            </td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th>B2B Customer</th>
                <th>Phone</th>
                <th class="text-center">Invoices</th>
                <th class="text-right">Current (0-30 D)</th>
                <th class="text-right">31-60 Days</th>
                <th class="text-right">61-90 Days</th>
                <th class="text-right">90+ Days (Critical)</th>
                <th class="text-right">Total Dues</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agingData as $row)
                <tr>
                    <td><strong>{{ $row['customer_name'] }}</strong></td>
                    <td>{{ $row['phone'] }}</td>
                    <td class="text-center">{{ $row['invoice_count'] }}</td>
                    <td class="text-right text-success">kr. {{ number_format((float)$row['current_0_30'], 2) }}</td>
                    <td class="text-right">kr. {{ number_format((float)$row['days_31_60'], 2) }}</td>
                    <td class="text-right">kr. {{ number_format((float)$row['days_61_90'], 2) }}</td>
                    <td class="text-right text-danger">kr. {{ number_format((float)$row['over_90'], 2) }}</td>
                    <td class="text-right text-primary">kr. {{ number_format((float)$row['total_due'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL PORTFOLIO DUES:</td>
                <td class="text-right text-success">kr. {{ number_format((float)$totals['current_0_30'], 2) }}</td>
                <td class="text-right">kr. {{ number_format((float)$totals['days_31_60'], 2) }}</td>
                <td class="text-right">kr. {{ number_format((float)$totals['days_61_90'], 2) }}</td>
                <td class="text-right text-danger">kr. {{ number_format((float)$totals['over_90'], 2) }}</td>
                <td class="text-right text-primary">kr. {{ number_format((float)$totals['total_due'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Confidential Report — Produced by B2B Viking ERP System Financial Engine.
    </div>

</body>
</html>
