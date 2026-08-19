<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Stock Transfer Challan #{{ $stockTransfer->transfer_no }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            padding: 10px;
        }
        .header-table, .content-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 6px;
            font-size: 11px;
        }
        .content-table {
            width: 100%;
            border: 1px solid #cbd5e1;
            margin-bottom: 30px;
        }
        .content-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        .content-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 11px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .signatures {
            margin-top: 50px;
            width: 100%;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding-top: 40px;
        }
        .sig-line {
            border-top: 1px solid #64748b;
            width: 80%;
            margin: 0 auto;
            padding-top: 5px;
            font-size: 10px;
            font-weight: bold;
            color: #475569;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="title">Stock Transfer Challan / Gate Pass</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                    <strong>{{ $settings->site_name ?? 'B2B Viking ERP' }}</strong><br>
                    Official Inter-Warehouse Inventory Movement Document
                </div>
            </td>
            <td style="width: 40%;" class="text-right">
                <h3 style="margin: 0; color: #0284c7;">#{{ $stockTransfer->transfer_no }}</h3>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                    Date: <strong>{{ optional($stockTransfer->transfer_date)->format('d M, Y') ?: date('d M, Y') }}</strong><br>
                    Status: <strong style="text-transform: uppercase;">{{ $stockTransfer->status }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 50%;">
                    <strong style="color: #64748b; font-size: 10px; text-transform: uppercase;">From (Source Warehouse):</strong><br>
                    <span style="font-size: 13px; font-weight: bold; color: #0f172a;">
                        {{ $stockTransfer->fromOutlet ? ($stockTransfer->fromOutlet->outlet_name ?? $stockTransfer->fromOutlet->name) : 'Central Warehouse' }}
                    </span>
                </td>
                <td style="width: 50%;">
                    <strong style="color: #64748b; font-size: 10px; text-transform: uppercase;">To (Destination Outlet/Branch):</strong><br>
                    <span style="font-size: 13px; font-weight: bold; color: #0f172a;">
                        {{ $stockTransfer->toOutlet ? ($stockTransfer->toOutlet->outlet_name ?? $stockTransfer->toOutlet->name) : 'Outlet #' . $stockTransfer->to_outlet_id }}
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 8px;">
                    Vehicle No: <strong>{{ $stockTransfer->vehicle_no ?: 'N/A' }}</strong><br>
                    Challan Ref: <strong>{{ $stockTransfer->challan_no ?: 'N/A' }}</strong>
                </td>
                <td style="padding-top: 8px;">
                    Driver: <strong>{{ $stockTransfer->driver_name ?: 'N/A' }}</strong> ({{ $stockTransfer->driver_phone ?: 'N/A' }})<br>
                    Dispatched At: <strong>{{ optional($stockTransfer->dispatched_at)->format('d M, Y h:i A') ?: 'Pending' }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">#</th>
                <th>Item Description</th>
                <th style="width: 100px;" class="text-center">Dispatched Qty</th>
                <th style="width: 100px;" class="text-center">Received Qty</th>
                <th style="width: 140px;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockTransfer->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name ?? 'Product #' . $item->product_id }}</strong>
                        @if($item->variant)
                            <br><span style="color: #64748b; font-size: 10px;">{{ $item->variant->color->name ?? '' }} {{ $item->variant->size->name ?? '' }}</span>
                        @endif
                    </td>
                    <td class="text-center font-bold">
                        {{ number_format((float)$item->qty, 2) }} {{ $item->product->unit->name ?? 'pcs' }}
                    </td>
                    <td class="text-center font-bold">
                        {{ $item->received_qty !== null ? number_format((float)$item->received_qty, 2) : '-' }}
                    </td>
                    <td style="color: #64748b; font-size: 10px;">
                        {{ $item->item_note ?: '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($stockTransfer->note)
        <div style="font-size: 11px; margin-bottom: 20px;">
            <strong>Special Instructions:</strong> {{ $stockTransfer->note }}
        </div>
    @endif

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">Prepared / Dispatched By</div>
            </td>
            <td>
                <div class="sig-line">Carrier / Driver Signature</div>
            </td>
            <td>
                <div class="sig-line">Received & Verified By</div>
            </td>
        </tr>
    </table>

</body>
</html>
