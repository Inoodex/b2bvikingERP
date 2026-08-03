<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statement of Account - {{ $statement['vendor']->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .logo { font-size: 22px; font-weight: bold; color: #2b4c7e; }
        .title { font-size: 18px; font-weight: bold; text-align: right; color: #555; text-transform: uppercase; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 6px; vertical-align: top; }
        .box { border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: #f9f9f9; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .items-table th { background: #2b4c7e; color: #fff; text-align: left; padding: 6px; font-size: 11px; }
        .items-table td { padding: 6px; border-bottom: 1px solid #eee; font-size: 11px; }
        .text-right { text-align: right; }
        .summary-table { width: 40%; float: right; margin-top: 20px; border-collapse: collapse; }
        .summary-table td { padding: 6px; border-bottom: 1px solid #ddd; }
        .footer { margin-top: 50px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; text-align: center; color: #777; }
        .signature-grid { width: 100%; margin-top: 60px; border-collapse: collapse; }
        .signature-grid td { width: 50%; text-align: center; font-weight: bold; border-top: 1px solid #999; padding-top: 5px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo">B2B Viking ERP</td>
            <td class="title">Supplier Statement of Account</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="50%">
                <div class="box">
                    <strong>SUPPLIER DETAILS:</strong><br>
                    <strong>{{ $statement['vendor']->name }}</strong><br>
                    Code: {{ $statement['vendor']->code ?? 'N/A' }}<br>
                    Phone: {{ $statement['vendor']->phone ?? 'N/A' }}<br>
                    Email: {{ $statement['vendor']->email ?? 'N/A' }}
                </div>
            </td>
            <td width="50%">
                <div class="box">
                    <strong>STATEMENT SUMMARY:</strong><br>
                    <strong>Statement Date:</strong> {{ date('d M Y') }}<br>
                    <table>
                        <tr>
                            <th>Total Billed:</th>
                            <td>{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($statement['total_billed'], 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Payments Received:</th>
                            <td>{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($statement['total_paid'], 2) }}</td>
                        </tr>
                        <tr>
                            <th>Debit Note Adjustments:</th>
                            <td>{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($statement['total_debit_notes'], 2) }}</td>
                        </tr>
                        <tr class="total">
                            <th>Net Balance Outstanding:</th>
                            <td>{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($statement['outstanding_balance'], 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Reference</th>
                <th class="text-right">Billed (Debit)</th>
                <th class="text-right">Paid / Claim (Credit)</th>
                <th class="text-right">Running Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statement['transactions'] as $tx)
                <tr>
                    <td>{{ $tx['date'] }}</td>
                    <td>{{ $tx['type'] }}</td>
                    <td><code>{{ $tx['reference'] }}</code></td>
                    <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($tx['debit'], 2) }}</td>
                    <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($tx['credit'], 2) }}</td>
                    <td class="text-right font-weight-bold">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($tx['running_balance'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td><strong>Total Outstanding Payable:</strong></td>
            <td class="text-right" style="font-size: 14px; color: red; font-weight: bold;">
                ${{ number_format($statement['outstanding_balance'], 2) }}
            </td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <table class="signature-grid">
        <tr>
            <td>Accounts Department Signature</td>
            <td>Supplier Acknowledgement & Stamp</td>
        </tr>
    </table>

    <div class="footer">
        Official Supplier Outstanding Acknowledgement Statement generated by B2B Viking ERP.
    </div>

</body>
</html>
