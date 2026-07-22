<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Booking #{{ $targetBooking->booking_no }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #007bff;
            font-size: 24px;
        }

        .info-row {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-col {
            width: 50%;
            float: left;
        }

        .clear {
            clear: both;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .badge-success {
            background-color: #28a745;
            color: #fff;
        }

        .badge-danger {
            background-color: #dc3545;
            color: #fff;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }

        .table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .variant-item {
            display: inline-block;
            padding: 2px 6px;
            background: #eee;
            border-radius: 3px;
            font-size: 11px;
            margin-right: 5px;
            margin-top: 3px;
        }

        .summary-box {
            float: right;
            width: 250px;
            margin-top: 20px;
            border: 1px solid #dee2e6;
            padding: 15px;
            background: #fdfdfd;
        }
    </style>
</head>

<body>
    @php
        // Logo optimization
        $logoData = $settings->optimized_logo ?? null;
        if (!$logoData) {
            $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
            $logoFullPath = public_path(ltrim($logoPath, '/'));
            if (is_file($logoFullPath)) {
                $ext = strtolower(pathinfo($logoFullPath, PATHINFO_EXTENSION) ?: 'png');
                $mime = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) ? $ext : 'png';
                $logoData = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoFullPath));
            }
        }
    @endphp
    <div class="container">
        <div class="header">
            @if($logoData)
                <div style="margin-bottom: 10px;">
                    <img src="{{ $logoData }}" alt="Logo" style="height: 46px; max-width: 180px; object-fit: contain;">
                </div>
            @endif
            <div style="font-size: 18px; font-weight: bold;">ORDER PLACE DETAILS</div>
        </div>

        <div class="info-row">
            <div class="info-col">
                <strong>Vendor Information:</strong><br>
                {{ $targetBooking->vendor->shop_name }}<br>
                {{ $targetBooking->vendor->address }}<br>
                {{ $targetBooking->vendor->phone }}
            </div>
            <div class="info-col text-right">
                <strong>Booking No:</strong> #{{ $targetBooking->booking_no }}<br>
                <strong>Date:</strong> {{ $targetBooking->created_at->format('d M, Y h:i A') }}<br>
                <strong>Shipping Method:</strong> {{ $targetBooking->shipping_method ?? 'N/A' }}<br>
                {{-- <strong>Status:</strong>
                <span
                    class="badge {{ strtolower($targetBooking->status) == 'complete' ? 'badge-success' : (strtolower($targetBooking->status) == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                    {{ ucfirst($targetBooking->status) }}
                </span> --}}
            </div>
            <div class="clear"></div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th width="30">#</th>
                    <th>Product Details</th>
                    <th width="80" class="text-center">Quantity</th>
                    <th width="80" class="text-center">Unit</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; @endphp
                @foreach ($orderGroup as $index => $item)
                    @php $totalQty += $item->qty; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                @if($item->product && $item->product->optimized_image)
                                    <img src="{{ $item->product->optimized_image }}" style="width: 50px; height: 50px; margin-right: 10px; border: 1px solid #eee; object-fit: cover;">
                                @endif
                                <div>
                                    <div style="font-weight: bold;">{{ $item->product->name }}</div>
                                    <div style="font-size: 11px; color: #666;">product no: {{ $item->product->product_number ?? 'N/A' }}</div>
                                    @if ($item->variant_info)
                                        @foreach ($item->variant_info as $name => $qty)
                                            <span class="variant-item">{{ $name }}: {{ $qty }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center">{{ (float) $item->qty }}</td>
                        <td class="text-center">{{ $item->unit->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($targetBooking->description)
            <div style="margin-top: 30px;">
                <strong>Description / Note:</strong><br>
                <div style="padding: 10px; background: #f9f9f9; border: 1px solid #eee; margin-top: 5px;">
                    {{ $targetBooking->description }}
                </div>
            </div>
        @endif

        @if ($targetBooking->custom_fields && count($targetBooking->custom_fields) > 0)
            <div style="margin-top: 20px;">
                <strong>Additional Details:</strong><br>
                <table style="width: 100%; margin-top: 5px;">
                    @foreach ($targetBooking->custom_fields as $field)
                        @if (!empty($field['key']) || !empty($field['value']))
                            <tr>
                                <td width="30%" style="color: #666;">{{ $field['key'] ?? 'N/A' }}:</td>
                                <td><strong>{{ $field['value'] ?? 'N/A' }}</strong></td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        @endif

        <div class="summary-box">
            <div style="font-size: 14px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Grand Total</div>
            <div style="font-size: 24px; font-weight: bold; color: #007bff; margin-top: 5px;">
                {{ (float) $totalQty }} <small style="font-size: 14px; color: #666;">(Total Qty)</small>
            </div>
        </div>
        <div class="clear"></div>

        <div class="footer">
            <div style="font-weight: bold;">{{ $settings->site_name ?? 'Inventory Management System' }}</div>
            <div>{{ $settings->contact_email ?? '' }}</div>
            <div>{!! nl2br(e($settings->address ?? '')) !!}</div>
        </div>
    </div>
</body>

</html>
