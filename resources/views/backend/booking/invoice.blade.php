<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Booking Invoice #{{ $targetBooking->booking_no }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        /* Web View Styling */
        @media screen {
            body {
                background-color: #f0f0f0;
                padding: 40px 0;
            }

            .container {
                max-width: 800px;
                background: #fff;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                border-radius: 4px;
                padding: 40px;
            }

            .no-print {
                margin-bottom: 20px;
                display: flex;
                justify-content: flex-end;
            }

            .btn {
                display: inline-block;
                padding: 8px 16px;
                margin-left: 10px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: bold;
                font-size: 13px;
                cursor: pointer;
                border: none;
            }

            .btn-print {
                background: #ffc107;
                color: #000;
            }

            .btn-download {
                background: #007bff;
                color: #fff;
            }

            .btn-back { display: none; }
            .btn-close {
                background: #6c757d;
                color: #fff;
            }
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: #fff;
            }

            .container {
                width: 100%;
                padding: 0;
                box-shadow: none;
            }
        }

        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .company-info {
            text-align: right;
            float: right;
            width: 60%;
        }

        .invoice-title {
            float: left;
            width: 40%;
        }

        .invoice-title h1 {
            margin: 0;
            color: #333;
            font-size: 28px;
            text-transform: uppercase;
        }

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        .details-box {
            margin-bottom: 30px;
        }

        .box-left {
            float: left;
            width: 48%;
        }

        .box-right {
            float: right;
            width: 48%;
            text-align: right;
        }

        .table-responsive {
            width: 100%;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f8f9fa;
            color: #333;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
            border-top: 2px solid #ddd;
        }

        .variant-tag {
            display: inline-block;
            padding: 2px 6px;
            background: #eee;
            border-radius: 3px;
            font-size: 11px;
            margin-right: 5px;
            margin-top: 3px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-danger {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-print">Print Now</button>
            <a href="{{ route('admin.bookings.download-pdf', $targetBooking->id) }}" class="btn btn-download">Download
                PDF</a>
            <button type="button" onclick="window.close(); if(!window.closed){ window.history.back(); }" class="btn btn-close">Close</button>
        </div>

        <div class="header clearfix">
            <div class="invoice-title">
                <h1>{{ $targetBooking->status == 'complete' ? 'ORDER PLACE' : 'ORDER PLACE' }}</h1>
                <p><strong>Order No:</strong> #{{ $targetBooking->booking_no }}</p>
                <div style="margin-top: 10px;">
                    <span
                        class="badge {{ $targetBooking->status == 'complete' ? 'badge-success' : ($targetBooking->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                        {{ ucfirst($targetBooking->status) }}
                    </span>
                </div>
            </div>
            <div class="company-info">
                <div style="margin-bottom: 6px;">
                    <img src="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}" alt="Logo" style="height: 40px; max-width: 160px; object-fit: contain;">
                </div>
                {{-- <h3>{{ $settings->site_name ?? 'Inventory Management System' }}</h3>
                <p>
                    {{ $settings->contact_email ?? '' }}<br>
                    {!! nl2br(e($settings->address ?? '')) !!}
                </p> --}}
            </div>
        </div>

        <div class="details-box clearfix">
            <div class="box-left">
                <h4>Vendor Details:</h4>
                <p>
                    <strong>{{ $targetBooking->vendor->shop_name }}</strong><br>
                    Phone: {{ $targetBooking->vendor->phone }}<br>
                    Address: {{ $targetBooking->vendor->address }}
                </p>
            </div>
            <div class="box-right">
                <h4>Order Details:</h4>
                <p>
                    <strong>Date:</strong> {{ $targetBooking->created_at->format('d M, Y h:i A') }}<br>
                    <strong>Shipping Method:</strong> {{ $targetBooking->shipping_method ?? 'N/A' }}<br>
                    <strong>Generated By:</strong> {{ Auth::user()->name }}
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 55%;">Product Details</th>
                        <th style="width: 20%; text-align: center;">Qty</th>
                        <th style="width: 20%; text-align: center;">Unit Price</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalQty = 0; @endphp
                    @foreach ($orderGroup as $index => $item)
                        @php $totalQty += $item->qty; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    @if($item->product && $item->product->thumb_image)
                                        <img src="{{ asset('storage/' . $item->product->thumb_image) }}" style="width: 50px; height: 50px; margin-right: 10px; border: 1px solid #eee; object-fit: cover;">
                                    @endif
                                    <div>
                                        <strong>{{ $item->product->name }}</strong><br>
                                        <span style="font-size: 11px; color: #666;">product no: {{ $item->product->product_number ?? 'N/A' }}</span>
                                        @if ($item->variant_info)
                                            <div style="margin-top: 5px;">
                                                @foreach ($item->variant_info as $name => $qty)
                                                    <span class="variant-tag">{{ $name }}: {{ $qty }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">{{ (float) $item->qty }}</td>
                            <td style="text-align: center;">{{ $item->unit->name ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right;">GRAND TOTAL QUANTITY</td>
                        <td style="text-align: center;">{{ (float) $totalQty }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if ($targetBooking->description)
            <div style="margin-bottom: 30px; background: #f8f9fa; padding: 15px; border-left: 4px solid #ddd;">
                <strong>Note/Description:</strong><br>
                {{ $targetBooking->description }}
            </div>
        @endif

        @if ($targetBooking->custom_fields && count($targetBooking->custom_fields) > 0)
            <div style="margin-top: 20px;">
                <strong>Additional Details:</strong>
                <table style="width: 100%; margin-top: 5px;">
                    @foreach ($targetBooking->custom_fields as $field)
                        @if (!empty($field['key']) || !empty($field['value']))
                            <tr>
                                <td width="30%" style="color: #666; border: none; padding: 4px 0;">
                                    {{ $field['key'] ?? 'N/A' }}:</td>
                                <td style="border: none; padding: 4px 0;">
                                    <strong>{{ $field['value'] ?? 'N/A' }}</strong></td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        @endif

        <div class="footer">
            {{-- <p>Generated by {{ $settings->site_name ?? 'Inventory Management System' }} on {{ date('d M, Y h:i A') }}
            </p> --}}
            <h3>{{ $settings->site_name ?? 'Inventory Management System' }}</h3>
                <p>
                    {{ $settings->contact_email ?? '' }}<br>
                    {!! nl2br(e($settings->address ?? '')) !!}
                </p>
        </div>
    </div>
</body>

</html>
