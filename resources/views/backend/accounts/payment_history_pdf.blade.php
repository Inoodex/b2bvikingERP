<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        .header { text-align: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 12px; }
        .logo { height: 42px; margin-bottom: 6px; }
        .title { font-size: 18px; font-weight: bold; margin: 0; }
        .meta { font-size: 11px; color: #6b7280; }
        .filters { margin: 10px 0 14px; font-size: 11px; color: #374151; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        th { background: #f9fafb; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .02em; }
        .text-right { text-align: right; }
        .muted { color: #6b7280; }
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
        <div class="meta">Payment History • Generated: {{ $generatedAt->format('d M, Y h:i A') }}</div>
    </div>

    <div class="filters">
        <strong>Filters:</strong>
        <span>From: {{ $filters['start_date'] ?: 'All' }}</span> |
        <span>To: {{ $filters['end_date'] ?: 'All' }}</span> |
        <span>Method: {{ $filters['method'] ?: 'All' }}</span> |
        <span>Search: {{ $filters['search'] ?: 'N/A' }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 12%;">Order No</th>
                <th style="width: 18%;">Customer</th>
                <th style="width: 12%;">Phone</th>
                <th style="width: 12%;">Method</th>
                <th style="width: 12%;">Trans ID</th>
                <th style="width: 10%;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->created_at?->format('d M, Y') }}</td>
                    <td>{{ $payment->order->order_no ?? '-' }}</td>
                    <td>{{ $payment->order->billing_name ?? '-' }}</td>
                    <td>{{ $payment->order->billing_phone ?? '-' }}</td>
                    <td><span class="badge">{{ strtoupper($payment->payment_method) }}</span></td>
                    <td>{{ $payment->transaction_id ?? '-' }}</td>
                    <td class="text-right">{{$settings->currency_icon}}{{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-right muted">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Total Records:</strong> {{ $summary['count'] }} |
        <strong>Total Amount:</strong>{{$settings->currency_icon}} {{ number_format($summary['total_amount'], 2) }}
    </div>
</body>
</html>
