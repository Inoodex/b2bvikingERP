<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Notification</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 32px 24px;
        }
        .content p {
            margin-bottom: 24px;
            font-size: 16px;
        }
        .booking-details {
            background-color: #f1f5f9;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 32px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #64748b;
            font-size: 14px;
            text-transform: uppercase;
        }
        .detail-value {
            font-weight: 700;
            color: #1e293b;
            font-size: 15px;
            text-align: right;
        }
        .variant-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px dashed #cbd5e1;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px;
            text-align: center;
            font-size: 14px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
        .btn {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New  Booking</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $booking->vendor->shop_name }}</strong>,</p>
            <p>A new booking has been created from <strong>{{ $settings->site_name ?? config('app.name') }}</strong>. Please find the details below:</p>
            
            <div class="booking-details">
                <div class="detail-row">
                    <span class="detail-label">Booking No:</span>
                    <span class="detail-value">{{ $booking->booking_no }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Product:</span>
                    <span class="detail-value">{{ $booking->product->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Quantity:</span>
                    <span class="detail-value">{{ $booking->qty }} {{ $booking->unit->name ?? 'units' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ $booking->created_at->format('M d, Y') }}</span>
                </div>

                @if($booking->variant_info)
                <div class="variant-info">
                    <span class="detail-label" style="display: block; margin-bottom: 10px;">Variant Breakdown:</span>
                    @foreach($booking->variant_info as $variant => $qty)
                    <div class="detail-row" style="border-bottom: 1px dotted #e2e8f0;">
                        <span class="detail-label">{{ $variant }}:</span>
                        <span class="detail-value">{{ $qty }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            @if($booking->description)
            <p><strong>Note:</strong><br>
            {{ $booking->description }}</p>
            @endif

            <p>Please review these details and let us know if you have any questions.</p>
            
            <div style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <p style="font-size: 14px; color: #64748b; margin-bottom: 4px;">Best Regards,</p>
                <p style="font-weight: 700; color: #1e293b; margin: 0;">{{ $settings->site_name ?? config('app.name') }}</p>
                @if($settings->contact_email)
                    <p style="font-size: 13px; color: #64748b; margin: 2px 0;">Email: {{ $settings->contact_email }}</p>
                @endif
                @if($settings->address)
                    <p style="font-size: 13px; color: #64748b; margin: 2px 0;">Address: {{ $settings->address }}</p>
                @endif
            </div>

            <!-- <div style="text-align: center;">
                <a href="{{ url('/') }}" class="btn">View in Dashboard</a>
            </div> -->
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $settings->site_name ?? config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
