<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Payment Details</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        .header { text-align: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 12px; }
        .logo { height: 42px; margin-bottom: 6px; }
        .title { font-size: 18px; font-weight: bold; margin: 0; }
        .meta { font-size: 11px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        th { background: #f9fafb; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .02em; }
        .section-title { font-weight: bold; margin: 12px 0 6px; }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($logoData))
            <img src="{{ $logoData }}" class="logo" alt="Logo">
        @endif
        <p class="title">{{ $settings->site_name ?? 'Inventory Management System' }}</p>
        <div class="meta">{{ $settings->contact_email ?? '' }}</div>
        <div class="meta">{{ $settings->phone ?? '' }}</div>
        <div class="meta">{{ $settings->address ?? '' }}</div>
        <div class="meta">Vendor Payment Details • Generated: {{ $generatedAt->format('d M, Y h:i A') }}</div>
    </div>

    <div class="section-title">Purchase Info</div>
    <table>
        <tbody>
            <tr>
                <th style="width: 20%;">Invoice No</th>
                <td>{{ $payment->purchase->invoice_no ?? '-' }}</td>
                <th style="width: 20%;">Purchase Date</th>
                <td>{{ $payment->purchase?->date ?? '-' }}</td>
            </tr>
            <tr>
                <th>Vendor</th>
                <td>{{ $payment->purchase->vendor->shop_name ?? '-' }}</td>
                <th>Phone</th>
                <td>{{ $payment->purchase->vendor->phone ?? '-' }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td colspan="3">{{ $payment->purchase->vendor->address ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Payment Info</div>
    <table>
        <tbody>
            <tr>
                <th style="width: 20%;">Payment Date</th>
                <td>{{ $payment->created_at?->format('d M, Y h:i A') ?? '-' }}</td>
                <th style="width: 20%;">Method</th>
                <td>{{ strtoupper($payment->payment_method ?? '-') }}</td>
            </tr>
            <tr>
                <th>Transaction ID</th>
                <td>{{ $payment->transaction_id ?? '-' }}</td>
                <th>Amount</th>
                <td>{{ $settings->currency_icon }}{{ number_format($payment->amount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <th>Note</th>
                <td colspan="3">{{ $payment->note ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
