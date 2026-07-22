<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Order Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50;">New Order Received</h2>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>Order No:</strong> {{ $order->order_no }}</p>
        <p><strong>Customer:</strong> {{ $order->billing_name ?? 'N/A' }}</p>
        <p><strong>Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>
        <p><strong>Total Amount:</strong> {{ config('settings.currency_symbol', '$') }}{{ number_format($order->total_amount, 2) }}</p>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Product</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Qty</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items ?? [] as $item)
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $item->product_name }}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ $item->quantity }}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">{{ config('settings.currency_symbol', '$') }}{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-align: center; color: #7f8c8d; font-size: 12px; margin-top: 30px;">
        This is an automated notification from {{ config('app.name') }}.
    </p>
</body>
</html>
