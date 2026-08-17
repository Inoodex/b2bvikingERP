<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Packing Slip #{{ $deliveryOrder->delivery_no }}</title>
    <style>
        @page {
            margin: 25px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-bar {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            text-transform: uppercase;
            margin: 0 0 4px 0;
        }
        .company-sub {
            font-size: 10px;
            color: #64748b;
            margin: 0;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 800;
            color: #2563eb;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }
        .doc-no {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            text-align: right;
            margin: 0 0 6px 0;
        }
        .badge-status {
            display: inline-block;
            padding: 3px 10px;
            font-size: 10px;
            font-weight: 700;
            color: #ffffff;
            background-color: #059669;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-pending {
            background-color: #d97706;
        }

        /* 2-Column Info Grid */
        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-left: -10px;
            margin-right: -10px;
            margin-bottom: 15px;
        }
        .info-card {
            width: 50%;
            vertical-align: top;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
        }
        .card-heading {
            font-size: 10px;
            font-weight: 800;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .info-line {
            margin-bottom: 3px;
            font-size: 11px;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
        }
        .info-val {
            color: #0f172a;
            font-weight: 700;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-radius: 6px;
            overflow: hidden;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
            border: none;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }

        .totals-row th {
            background-color: #e2e8f0;
            color: #0f172a;
            font-size: 11px;
            padding: 8px 10px;
        }

        /* Notes Box */
        .notes-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 25px;
            font-size: 10.5px;
            color: #1e3a8a;
        }

        /* Signatures */
        .sig-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .sig-cell {
            width: 45%;
            vertical-align: top;
        }
        .sig-line {
            border-top: 1.5px dashed #94a3b8;
            margin-top: 35px;
            padding-top: 6px;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    {{-- Top Header Bar --}}
    <table class="header-bar">
        <tr>
            <td style="vertical-align: top; width: 55%;">
                <div class="company-name">{{ $settings['site_name'] }}</div>
                <div class="company-sub">{{ $settings['company_address'] }}</div>
                <div class="company-sub">Phone: {{ $settings['company_phone'] }} | Email: {{ $settings['company_email'] }}</div>
            </td>
            <td style="vertical-align: top; width: 45%; text-align: right;">
                <div class="doc-title">Outbound Delivery Challan</div>
                <div class="doc-no">#{{ $deliveryOrder->delivery_no }}</div>
                <div class="company-sub" style="margin-bottom: 4px;">Date: {{ $deliveryOrder->date ? $deliveryOrder->date->format('d M Y') : date('d M Y') }}</div>
                @if ($deliveryOrder->status === 'dispatched' || $deliveryOrder->status === 'delivered' || $deliveryOrder->status === 'shipped')
                    <span class="badge-status">Dispatched & Shipped</span>
                @else
                    <span class="badge-status badge-pending">Pending Dispatch</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- 2-Column Info Cards --}}
    <table class="info-table">
        <tr>
            {{-- Left Card: Recipient Info --}}
            <td class="info-card">
                <div class="card-heading">Delivery Recipient / Customer</div>
                @if ($deliveryOrder->order && $deliveryOrder->order->user)
                    <div class="info-line"><span class="info-label">Customer:</span> <span class="info-val">{{ $deliveryOrder->order->user->name }}</span></div>
                    @if($deliveryOrder->order->user->outlet_name)
                        <div class="info-line"><span class="info-label">Outlet Name:</span> <span class="info-val">{{ $deliveryOrder->order->user->outlet_name }}</span></div>
                    @endif
                    <div class="info-line"><span class="info-label">Address:</span> {{ $deliveryOrder->order->user->address ?: 'Standard Registered Outlet Address' }}</div>
                    <div class="info-line"><span class="info-label">Email:</span> {{ $deliveryOrder->order->user->email }}</div>
                    <div class="info-line"><span class="info-label">Phone:</span> {{ $deliveryOrder->order->user->phone ?: '-' }}</div>
                @else
                    <div class="info-line"><span class="info-val">Valued B2B Customer</span></div>
                @endif
            </td>

            {{-- Right Card: Logistics Info --}}
            <td class="info-card">
                <div class="card-heading">Shipment & Logistics Info</div>
                <div class="info-line"><span class="info-label">Linked Sales Order:</span> <span class="info-val">#{{ $deliveryOrder->order ? $deliveryOrder->order->order_no : 'N/A' }}</span></div>
                <div class="info-line"><span class="info-label">Carrier / Logistics:</span> <span class="info-val">{{ $deliveryOrder->carrier_name ?: 'Standard Delivery' }}</span></div>
                <div class="info-line"><span class="info-label">AWB / Tracking No:</span> <span class="info-val">{{ $deliveryOrder->awb_number ?: 'N/A' }}</span></div>
                <div class="info-line"><span class="info-label">Shipping Method:</span> <span class="info-val">{{ $deliveryOrder->shipping_method ?: 'Road Freight' }}</span></div>
            </td>
        </tr>
    </table>

    {{-- Items Table Grid --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">#</th>
                <th>Product Description / SKU / Variant</th>
                <th style="width: 75px;" class="text-center">Ordered</th>
                <th style="width: 85px;" class="text-center">Dispatched</th>
                <th style="width: 80px;" class="text-right">Unit Price</th>
                <th style="width: 90px;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalDeliveredQty = 0;
                $grandShipmentTotal = 0;
                $mainUnitName = 'Pcs';
            @endphp
            @foreach ($deliveryOrder->items as $index => $item)
                @php 
                    $qtyDelivered = (float)$item->qty_delivered;
                    $unitPrice = (float)$item->unit_price;
                    $lineTotal = $qtyDelivered * $unitPrice;
                    
                    $totalDeliveredQty += $qtyDelivered;
                    $grandShipmentTotal += $lineTotal;

                    $orderedQty = $item->orderItem ? (float)$item->orderItem->quantity : $qtyDelivered;
                    $itemUnit = ($item->product && $item->product->unit) ? $item->product->unit->name : 'Pcs';
                    $mainUnitName = $itemUnit;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $item->product ? $item->product->name : 'Product #' . $item->product_id }}</strong>
                        @if ($item->variant)
                            <br><small style="color: #64748b;">
                                @if($item->variant->color) Color: {{ $item->variant->color->name }} | @endif
                                @if($item->variant->size) Size: {{ $item->variant->size->name }} | @endif
                                {{ $item->variant->name }}
                            </small>
                        @endif
                    </td>
                    <td class="text-center" style="color: #64748b;">{{ number_format($orderedQty, 2) }}</td>
                    <td class="text-center font-bold" style="color: #0f172a; font-size: 11.5px;">{{ number_format($qtyDelivered, 2) }} {{ $itemUnit }}</td>
                    <td class="text-right">kr. {{ number_format($unitPrice, 2) }}</td>
                    <td class="text-right font-bold">kr. {{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <th colspan="3" class="text-right font-bold" style="letter-spacing: 0.5px;">SHIPMENT TOTALS:</th>
                <th class="text-center font-bold" style="color: #2563eb; font-size: 11px;">{{ number_format($totalDeliveredQty, 2) }} {{ $mainUnitName }}</th>
                <th class="text-right" style="color: #64748b;">-</th>
                <th class="text-right font-bold" style="color: #059669; font-size: 12px;">kr. {{ number_format($grandShipmentTotal, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    {{-- Driver / Special Instructions --}}
    @if($deliveryOrder->notes)
        <div class="notes-box">
            <strong><i class="fas fa-info-circle"></i> Special Delivery Instructions:</strong> {{ $deliveryOrder->notes }}
        </div>
    @endif

    {{-- Signatures --}}
    <table class="sig-table">
        <tr>
            <td class="sig-cell">
                <div class="sig-line">Authorized Warehouse Dispatcher</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="sig-cell">
                <div class="sig-line">Driver / Customer Receiver Signature</div>
            </td>
        </tr>
    </table>

</body>
</html>
