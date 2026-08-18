<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt #{{ $customerPayment->payment_no }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; margin: 0; padding: 20px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .logo { font-size: 22px; font-weight: bold; color: #1a56db; text-transform: uppercase; }
        .title { font-size: 20px; font-weight: bold; text-align: right; color: #111827; }
        .badge-posted { background-color: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { width: 50%; vertical-align: top; padding: 10px; background-color: #f9fafb; border: 1px solid #e5e7eb; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 30px; }
        .details-table th, .details-table td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        .details-table th { background-color: #f3f4f6; color: #374151; font-weight: bold; }
        .amount-box { background-color: #eff6ff; border: 2px solid #2563eb; padding: 15px; text-align: center; border-radius: 6px; margin-bottom: 30px; }
        .amount-val { font-size: 26px; font-weight: bold; color: #1d4ed8; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                <div class="logo">B2B VIKING ERP</div>
                <div>Official Payment Receipt Voucher</div>
            </td>
            <td style="text-align: right;">
                <div class="title">RECEIPT #{{ $customerPayment->payment_no }}</div>
                <div style="margin-top: 5px;"><span class="badge-posted">POSTED & OFFICIAL</span></div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td>
                <strong>RECEIVED FROM (CUSTOMER):</strong><br>
                <strong>{{ $customerPayment->user ? ($customerPayment->user->outlet_name ?: $customerPayment->user->name) : 'Guest / Cash' }}</strong><br>
                Email: {{ $customerPayment->user ? $customerPayment->user->email : 'N/A' }}<br>
                Phone: {{ $customerPayment->user ? $customerPayment->user->phone : 'N/A' }}
            </td>
            <td>
                <strong>PAYMENT DETAILS:</strong><br>
                <strong>Date:</strong> {{ $customerPayment->payment_date ? $customerPayment->payment_date->format('d M, Y') : '-' }}<br>
                <strong>Method:</strong> {{ strtoupper(str_replace('_', ' ', $customerPayment->payment_method)) }}<br>
                <strong>Ref / Cheque No:</strong> {{ $customerPayment->reference_no ?: 'N/A' }}
            </td>
        </tr>
    </table>

    <div class="amount-box">
        <div style="font-size: 12px; color: #4b5563; text-uppercase; font-weight: bold;">AMOUNT RECEIVED</div>
        <div class="amount-val">kr. {{ number_format((float)$customerPayment->amount, 2) }}</div>
    </div>

    <table class="details-table">
        <thead>
            <tr>
                <th>Description / Invoice Reference</th>
                <th style="text-align: right;">Total Invoice</th>
                <th style="text-align: right;">Amount Applied</th>
                <th style="text-align: right;">Remaining Due</th>
            </tr>
        </thead>
        <tbody>
            @if($customerPayment->invoice)
                <tr>
                    <td>Sales Invoice #{{ $customerPayment->invoice->invoice_no }}</td>
                    <td style="text-align: right;">kr. {{ number_format((float)$customerPayment->invoice->total_amount, 2) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #059669;">kr. {{ number_format((float)$customerPayment->amount, 2) }}</td>
                    <td style="text-align: right; color: #dc2626;">kr. {{ number_format((float)$customerPayment->invoice->due_amount, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td>Customer Advance / Deposit Payment</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right; font-weight: bold; color: #059669;">kr. {{ number_format((float)$customerPayment->amount, 2) }}</td>
                    <td style="text-align: right;">-</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if($customerPayment->notes)
        <div style="margin-bottom: 20px; font-size: 11px;">
            <strong>Internal Notes:</strong> {{ $customerPayment->notes }}
        </div>
    @endif

    <table style="width: 100%; margin-top: 60px;">
        <tr>
            <td style="width: 50%; text-align: center;">
                ________________________<br>
                <strong>Customer Signature</strong>
            </td>
            <td style="width: 50%; text-align: center;">
                ________________________<br>
                <strong>Authorized Accountant Signature</strong>
            </td>
        </tr>
    </table>

    <div class="footer">
        Computer Generated Official B2B Payment Receipt. Valid without physical stamp. Printed on {{ date('Y-m-d H:i:s') }}.
    </div>

</body>
</html>
