<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Product Request Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #3498db;">New Product Request</h2>
    </div>
    
    <div style="background-color: #eaf2f8; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>Request No:</strong> {{ $productRequest->request_no }}</p>
        <p><strong>Requested By:</strong> {{ $productRequest->user->name ?? 'N/A' }}</p>
        <p><strong>Date:</strong> {{ $productRequest->created_at->format('d M Y, h:i A') }}</p>
        <p><strong>Total Quantity:</strong> {{ $productRequest->total_qty }}</p>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Product</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productRequest->items ?? [] as $item)
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $item->product->name ?? 'N/A' }}</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ $item->qty }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-align: center; color: #7f8c8d; font-size: 12px; margin-top: 30px;">
        This is an automated notification from {{ config('app.name') }}.
    </p>
</body>
</html>
