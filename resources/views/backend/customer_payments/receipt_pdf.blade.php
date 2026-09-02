<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->payment_no }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; margin: 0; padding: 20px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #2b4c7e; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #2b4c7e; }
        .title { font-size: 20px; font-weight: bold; text-align: right; color: #2b4c7e; text-transform: uppercase; }
        .subtitle { font-size: 12px; color: #777; text-align: right; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .info-table td { padding: 6px; vertical-align: top; }
        .box { border: 1px solid #ddd; padding: 12px; border-radius: 6px; background: #fdfdfd; min-height: 110px; }
        .box-title { font-size: 12px; font-weight: bold; color: #2b4c7e; text-transform: uppercase; margin-bottom: 6px; border-bottom: 1px solid #eee; padding-bottom: 4px; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        .items-table th { background: #2b4c7e; color: #fff; text-align: left; padding: 10px 8px; font-size: 12px; }
        .items-table td { padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 12px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-container { width: 100%; margin-top: 10px; }
        .total-box { float: right; width: 45%; border-collapse: collapse; }
        .total-box td { padding: 8px 12px; }
        .total-box .grand-total { background: #eef2f7; font-size: 16px; font-weight: bold; color: #2b4c7e; border-top: 2px solid #2b4c7e; }
        .notes-box { margin-top: 20px; background: #fffbe6; padding: 12px; border-left: 4px solid #faad14; border-radius: 4px; font-size: 12px; }
        .signature-grid { width: 100%; margin-top: 70px; border-collapse: collapse; }
        .signature-grid td { width: 33%; text-align: center; font-size: 12px; color: #555; border-top: 1px dashed #999; padding-top: 6px; }
        .footer { margin-top: 50px; border-top: 1px solid #eee; padding-top: 10px; font-size: 11px; text-align: center; color: #888; }
        .badge-success { background: #52c41a; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo" style="width: 50%;">
                {{ $settings->site_name ?? 'B2B Viking ERP' }}
            </td>
            <td style="width: 50%;">
                <div class="title">Official Payment Receipt</div>
                <div class="subtitle">Voucher # {{ $payment->payment_no }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="50%">
                <div class="box">
                    <div class="box-title">Received From (Customer)</div>
                    <strong>{{ $payment->user ? ($payment->user->outlet_name ?: $payment->user->name) : 'Guest / Cash Customer' }}</strong><br>
                    @if($payment->user)
                        Phone: {{ $payment->user->phone ?? 'N/A' }}<br>
                        Email: {{ $payment->user->email ?? 'N/A' }}<br>
                        Address: {{ $payment->user->address ?? 'N/A' }}
                    @endif
                </div>
            </td>
            <td width="50%">
                <div class="box">
                    <div class="box-title">Receipt Information</div>
                    <strong>Receipt No:</strong> {{ $payment->payment_no }}<br>
                    <strong>Receipt Date:</strong> {{ $payment->payment_date ? date('d M, Y', strtotime($payment->payment_date)) : date('d M, Y', strtotime($payment->created_at)) }}<br>
                    <strong>Payment Method:</strong> {{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}<br>
                    <strong>Status:</strong> <span class="badge-success">POSTED & CLEARED</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Description / Allocation</th>
                <th style="width: 25%;">Reference / Cheque No</th>
                <th class="text-right" style="width: 30%;">Amount Received</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($payment->invoice)
                        Customer payment towards Sales Invoice <strong>#{{ $payment->invoice->invoice_no }}</strong>
                        <br><small style="color: #666;">Remaining Due: kr. {{ number_format((float)$payment->invoice->due_amount, 2) }}</small>
                    @elseif($payment->order)
                        Customer payment towards Order <strong>#{{ $payment->order->order_no }}</strong>
                    @else
                        Customer Advance Deposit / Account Clearance
                    @endif
                </td>
                <td>{{ $payment->reference_no ?: 'N/A' }}</td>
                <td class="text-right">
                    <strong>kr. {{ number_format((float)$payment->amount, 2) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="total-container">
        <table class="total-box">
            <tr>
                <td class="text-right"><strong>Total Amount Received:</strong></td>
                <td class="text-right grand-total">kr. {{ number_format((float)$payment->amount, 2) }}</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    @if($payment->notes)
    <div class="notes-box">
        <strong>Notes / Remarks:</strong> {{ $payment->notes }}
    </div>
    @endif

    <table class="signature-grid">
        <tr>
            <td>Received By: <strong>{{ $payment->creator ? $payment->creator->name : 'System' }}</strong></td>
            <td>Accounts Verifier</td>
            <td>Customer Signature / Stamp</td>
        </tr>
    </table>

    <div class="footer">
        This is a computer-generated official receipt issued by {{ $settings->site_name ?? 'B2B Viking ERP' }}. All amounts are in Danish Krone (DKK).
    </div>

</body>
</html>
