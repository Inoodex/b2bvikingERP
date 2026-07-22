<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Payments</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        .header { text-align: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 12px; }
        .logo { height: 42px; margin-bottom: 6px; }
        .title { font-size: 18px; font-weight: bold; margin: 0; }
        .meta { font-size: 11px; color: #6b7280; }
        .section-title { font-weight: bold; margin: 12px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        th { background: #f9fafb; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .02em; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 10px; background: #eef2ff; color: #3730a3; }
        .summary { margin-top: 10px; font-size: 12px; }
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
        <div class="meta">Order Payment History • Generated: {{ $generatedAt->format('d M, Y h:i A') }}</div>
    </div>

    <div class="section-title">Order Info</div>
    <table>
        <tbody>
            <tr>
                <th style="width: 20%;">Order No</th>
                <td>{{ $order->order_no }}</td>
                <th style="width: 20%;">Order Date</th>
                <td>{{ $order->created_at?->format('d M, Y h:i A') ?? '-' }}</td>
            </tr>
            <tr>
                <th>Customer</th>
                <td>{{ $order->billing_name ?? '-' }}</td>
                <th>Phone</th>
                <td>{{ $order->billing_phone ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Payments</div>
    <table>
        <thead>
            <tr>
                <th style="width: 14%;">Date</th>
                <th style="width: 14%;">Method</th>
                <th style="width: 20%;">Trans ID</th>
                <th>Note</th>
                <th style="width: 12%;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->created_at?->format('d M, Y') }}</td>
                    <td><span class="badge">{{ strtoupper($payment->payment_method ?? '-') }}</span></td>
                    <td>{{ $payment->transaction_id ?? '-' }}</td>
                    <td>{{ $payment->note ?? '-' }}</td>
                    <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-right">No payments recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Total Records:</strong> {{ $summary['count'] }} |
        <strong>Total Paid:</strong> {{ number_format($summary['total_amount'], 2) }} |
        <strong>Order Paid:</strong> {{ number_format($summary['paid_amount'], 2) }} |
        <strong>Order Due:</strong> {{ number_format($summary['due_amount'], 2) }}
    </div>
</body>
</html>
