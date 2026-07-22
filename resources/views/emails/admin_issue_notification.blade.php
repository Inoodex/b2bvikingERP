<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Stock Issue Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #e67e22;">New Stock Issue Created</h2>
    </div>
    
    <div style="background-color: #fdf5e6; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>Issue No:</strong> {{ $issue->issue_no }}</p>
        <p><strong>Issued To (Outlet):</strong> {{ $issue->outlet->outlet_name ?? $issue->outlet->name ?? 'N/A' }}</p>
        <p><strong>Date:</strong> {{ $issue->created_at->format('d M Y, h:i A') }}</p>
        <p><strong>Total Quantity:</strong> {{ $issue->total_qty }}</p>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Product</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Variant</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issue->items ?? [] as $item)
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $item->product->name ?? 'N/A' }}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ $item->variant->name ?? 'Standard' }}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-align: center; color: #7f8c8d; font-size: 12px; margin-top: 30px;">
        This is an automated notification from {{ config('app.name') }}.
    </p>
</body>
</html>
