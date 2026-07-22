<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Invoice #{{ $order->order_no }}</title>
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
        @media screen {
            body {
                background-color: #f0f0f0;
                padding: 40px 0;
            }
            .container {
                max-width: 900px;
                background: #fff;
                box-shadow: 0 0 15px rgba(0,0,0,0.1);
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
            .btn-print { background: #ffc107; color: #000; }
            .btn-download { background: #3abaf4; color: #fff; }
            .btn-back { background: #6777ef; color: #fff; }
            .btn-close { background: #6c757d; color: #fff; }
        }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .container { width: 100%; padding: 0; box-shadow: none; }
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
            width: 55%;
        }
        .invoice-title {
            float: left;
            width: 45%;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
            vertical-align: top;
        }
        .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
            border-top: 2px solid #ddd;
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
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-info { background-color: #17a2b8; }
        .badge-primary { background-color: #6777ef; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .text-right { text-align: right; }
        .image-cell {
            text-align: center;
        }
        .image-cell img {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }
        .image-empty {
            font-size: 11px;
            color: #999;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    @php
        $status = strtolower((string) $order->status);
        $statusClass = match($status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-info',
            'processing' => 'badge-primary',
            'shipped' => 'badge-primary',
            'completed' => 'badge-success',
            'rejected', 'cancelled' => 'badge-danger',
            default => 'badge-info',
        };
        $currency = $settings->currency_icon ?? '$';
    @endphp

    <div class="container">
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-print">Print Now</button>
            <a href="{{ route('admin.orders.download-invoice', $order->id) }}" class="btn btn-download">Download PDF</a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-back">Back to List</a>
            <button type="button" onclick="window.close(); if(!window.closed){ window.history.back(); }" class="btn btn-close">Close</button>
        </div>

        <div class="header clearfix">
            <div class="invoice-title">
                <h1>Outlet/Shop Order</h1>
                <p><strong>Order No:</strong> #{{ $order->order_no }}</p>
                <div style="margin-top: 10px;">
                    <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                </div>
            </div>
            <div class="company-info">
                <div>
                    <img src="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}" alt="Logo" style="height: 42px; max-width: 180px; object-fit: contain;">
                </div>
            </div>
        </div>

        <div class="details-box clearfix">
            <div class="box-left">
                <h4>Customer Details:</h4>
                <p>
                    <strong>{{ $order->billing_name }}</strong><br>
                    Outlet/Shop: {{ $order->billing_outlet_name ?: ($order->user->outlet_name ?? 'N/A') }}<br>
                    Phone: {{ $order->billing_phone }}<br>
                    Email: {{ $order->billing_email }}
                </p>
            </div>
            <div class="box-right">
                <h4>Order Details:</h4>
                <p>
                    <strong>Date:</strong> {{ $order->created_at?->format('d M, Y h:i A') }}<br>
                    <strong>Source:</strong> {{ $order->shipping_method ?: 'frontend_checkout' }}<br>
                    <strong>Status:</strong> {{ strtoupper($order->status) }}
                </p>
            </div>
        </div>

        <table>
            @php
                // Use issuedItems if available, otherwise fallback to order items
                $itemsToDisplay = isset($issuedItems) ? $issuedItems : $order->items;

                // Group items by category and sort alphabetically
                $groupedItems = $itemsToDisplay->groupBy(function($item) {
                    return $item->category_name ?: 'General';
                })->sortKeys();

                // Sort items within each category by product name (letter by letter)
                $sortedGroupedItems = $groupedItems->map(function($items) {
                    return $items->sortBy(function($item) {
                        return strtolower($item->product_name);
                    })->values();
                });

                $globalIndex = 0;
            @endphp

            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 45%;">Product</th>
                    <th style="width: 17%;">Variant</th>
                    <th style="width: 10%;" class="text-right">Qty</th>
                    <th style="width: 12%;" class="text-right">Unit Price</th>
                    <th style="width: 13%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sortedGroupedItems as $categoryName => $categoryItems)
                    <tr style="background-color: #e9ecef;">
                        <td colspan="6" style="padding: 8px 12px; font-weight: bold; text-transform: uppercase; font-size: 12px; color: #495057;">
                            {{ $categoryName }}
                        </td>
                    </tr>
                    @foreach($categoryItems as $item)
                    @php $globalIndex++; @endphp
                    <tr>
                        <td>{{ $globalIndex }}</td>
                        <td>
                            <strong>{{ $item->product_name }}</strong><br>
                            <span style="font-size: 11px; color: #666;">product no: {{ $item->product->product_number ?? 'N/A' }}</span><br>
                            <small>{{ $item->category_name ?: 'General' }}</small>
                        </td>
                        <td>{{ $item->variant_label ?: ($item->variant->name ?? 'Standard') }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
                @endforeach
                @php
                    $displaySubtotal = $issuedItems ? $issuedItems->sum('line_total') : ($order->subtotal_amount ?: $order->total_amount);
                    $displayGrandTotal = $issuedItems ? ($displaySubtotal - $order->discount_amount + $order->tax_amount) : $order->total_amount;
                    $displayPaid = (float) $order->paid_amount;
                    $displayDue = max(0, round($displayGrandTotal - $displayPaid, 2));
                @endphp

                <tr class="total-row">
                    <td colspan="5" class="text-right">Subtotal</td>
                    <td class="text-right">{{ $currency }}{{ number_format($displaySubtotal, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="5" class="text-right">Discount</td>
                    <td class="text-right">-{{ $currency }}{{ number_format($order->discount_amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="5" class="text-right">VAT</td>
                    <td class="text-right">{{ $currency }}{{ number_format($order->tax_amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="5" class="text-right">Grand Total</td>
                    <td class="text-right">{{ $currency }}{{ number_format($displayGrandTotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: right; border: none; padding: 5px 12px;">PAID TOTAL</td>
                    <td style="text-align: right; border-bottom: 1px solid #ddd; color: #28a745; font-weight: bold; padding: 5px 12px;">{{ $currency }}{{ number_format($displayPaid, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: right; border: none; font-weight: bold; padding: 8px 12px;">DUE BALANCE</td>
                    <td style="text-align: right; font-weight: bold; color: {{ $displayDue > 0 ? '#dc3545' : '#28a745' }}; font-size: 16px; padding: 8px 12px;">{{ $currency }}{{ number_format($displayDue, 2) }}</td>
                </tr>

            </tbody>
        </table>

        {{-- @if($hasSavedPiInfo)
            @include('backend.pi._packing_table', [
                'piInfo' => $piInfo,
                'piTotals' => $piTotals,
            ])
        @endif --}}

        @if($order->ship_different)
            <div style="margin-top: 15px; background: #f8f9fa; padding: 15px; border-left: 4px solid #6777ef;">
                <strong>Shipping Address:</strong><br>
                {{ $order->shipping_name ?: 'N/A' }} |
                {{ $order->shipping_phone ?: 'N/A' }} |
                {{ $order->shipping_email ?: 'N/A' }}<br>
                {{ $order->shipping_address ?: 'N/A' }},
                {{ $order->shipping_city ?: '' }} {{ $order->shipping_state ?: '' }},
                {{ $order->shipping_zip_code ?: '' }},
                {{ $order->shipping_country ?: '' }}
            </div>
        @endif

        <div class="footer">
            <div style="font-weight: bold;">{{ $settings->site_name ?? 'Inventory Management System' }}</div>
            <div>{{ $settings->contact_email ?? '' }}</div>
            <div>{!! nl2br(e($settings->address ?? '')) !!}</div>
        </div>
    </div>
</body>
</html>
