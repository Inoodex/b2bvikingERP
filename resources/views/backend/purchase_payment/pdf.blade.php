<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Voucher - {{ $payment->payment_no }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #2b4c7e; }
        .title { font-size: 20px; font-weight: bold; text-align: right; color: #555; text-transform: uppercase; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 6px; vertical-align: top; }
        .box { border: 1px solid #ddd; padding: 12px; border-radius: 4px; background: #f9f9f9; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .items-table th { background: #2b4c7e; color: #fff; text-align: left; padding: 8px; font-size: 12px; }
        .items-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .total-box { margin-top: 20px; float: right; width: 40%; }
        .footer { margin-top: 50px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 11px; text-align: center; color: #777; }
        .signature-grid { width: 100%; margin-top: 60px; border-collapse: collapse; }
        .signature-grid td { width: 33%; text-align: center; font-weight: bold; border-top: 1px solid #999; padding-top: 5px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo">B2B Viking ERP</td>
            <td class="title">Payment Voucher</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="50%">
                <div class="box">
                    <strong>SUPPLIER INFORMATION:</strong><br>
                    <strong>{{ $payment->vendor?->name }}</strong><br>
                    Phone: {{ $payment->vendor?->phone ?? 'N/A' }}<br>
                    Email: {{ $payment->vendor?->email ?? 'N/A' }}<br>
                    Address: {{ $payment->vendor?->address ?? 'N/A' }}
                </div>
            </td>
            <td width="50%">
                <div class="box">
                    <strong>VOUCHER DETAILS:</strong><br>
                    <strong>Voucher No:</strong> {{ $payment->payment_no ?? ('PAY-'.$payment->id) }}<br>
                    <strong>Date:</strong> {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : $payment->created_at->format('d M Y') }}<br>
                    <strong>PO Reference:</strong> {{ $payment->purchase?->po_no ?? 'N/A' }}<br>
                    <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description / Payment Allocation</th>
                <th>Transaction Ref</th>
                <th>Currency</th>
                <th class="text-right">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Payment towards Purchase Order <strong>{{ $payment->purchase?->po_no }}</strong>
                    @if($payment->bank_name)
                        <br><small>Bank: {{ $payment->bank_name }}</small>
                    @endif
                </td>
                <td><code>{{ $payment->transaction_id ?? 'N/A' }}</code></td>
                <td>{{ $payment->currency?->code ?? 'DKK' }}</td>
                <td class="text-right"><strong>{{ $payment->currency?->symbol ?? 'Kr.' }}{{ number_format($payment->amount, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <table class="total-box text-right" style="margin-bottom: 40px;">
        <tr>
            <td style="font-size: 16px; padding: 10px; background: #eef2f7; font-weight: bold;">
                Total Amount Paid: {{ $payment->currency?->symbol ?? 'Kr.' }}{{ number_format($payment->amount, 2) }}
            </td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    @if($payment->note)
    <div style="margin-top: 20px; font-style: italic; background: #fff8e7; padding: 10px; border-left: 3px solid #ffc107;">
        <strong>Note:</strong> {{ $payment->note }}
    </div>
    @endif

    <table class="signature-grid">
        <tr>
            <td>Prepared By</td>
            <td>Checked By</td>
            <td>Authorized Signature</td>
        </tr>
    </table>

    <div class="footer">
        This is an official computer-generated Payment Voucher produced by B2B Viking ERP.
    </div>

</body>
</html>
