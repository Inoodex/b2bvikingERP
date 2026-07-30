<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $po->po_no ?? ('PO-' . $po->id) }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; font-size: 12px; margin: 0; padding: 20px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-title { font-size: 24px; font-weight: bold; color: #6777ef; text-transform: uppercase; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 6px; vertical-align: top; }
        .box { border: 1px solid #e2e8f0; background: #f8fafc; padding: 10px; border-radius: 4px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #6777ef; color: white; padding: 8px; font-size: 11px; text-transform: uppercase; text-align: left; }
        .items-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .totals-table { width: 40%; float: right; border-collapse: collapse; }
        .totals-table td { padding: 6px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .clear { clear: both; }
        .footer { margin-top: 50px; font-size: 10px; color: #718096; border-top: 1px solid #cbd5e1; padding-top: 10px; text-align: center; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                @if(!empty($settings->optimized_logo))
                    <img src="{{ $settings->optimized_logo }}" alt="{{ $settings->site_name ?? 'Company Logo' }}" style="max-height: 70px; max-width: 220px; object-fit: contain;">
                @else
                    <span class="header-title">{{ $settings->site_name ?? 'Company' }}</span>
                @endif
                <br><small style="color: #718096;">Official Purchase Order Document</small>
            </td>
            <td class="text-right">
                <h2 style="margin: 0; color: #2d3748;">OFFICIAL PO</h2>
                <strong>PO #:</strong> {{ $po->po_no ?? ('PO-' . $po->id) }}<br>
                <strong>Date:</strong> {{ $po->date ? \Carbon\Carbon::parse($po->date)->format('d M, Y') : date('d M, Y') }}
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <div class="box">
                    <strong style="color: #6777ef;">SUPPLIER / VENDOR DETAILS:</strong><br>
                    <strong>{{ $po->vendor ? $po->vendor->shop_name : 'N/A' }}</strong><br>
                    Email: {{ $po->vendor->email ?? 'N/A' }}<br>
                    Phone: {{ $po->vendor->phone ?? 'N/A' }}<br>
                    Address: {{ $po->vendor->address ?? 'N/A' }}
                </div>
            </td>
            <td style="width: 50%;">
                <div class="box">
                    <strong style="color: #6777ef;">ORDER METADATA:</strong><br>
                    <strong>Purchase Type:</strong> {{ strtoupper($po->purchase_type ?? 'LOCAL') }}<br>
                    <strong>Currency:</strong> {{ $po->currency ? $po->currency->code : 'DKK' }} ({{ $po->currency ? $po->currency->symbol : 'kr.' }})<br>
                    <strong>Exchange Rate:</strong> 1 {{ $po->currency ? $po->currency->code : 'DKK' }} = {{ $po->exchange_rate_used ?? 1.0 }} Base<br>
                    <strong>Status:</strong> Approved
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Item Description</th>
                <th style="width: 20%;">Variant / Spec</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 10%;">Unit Price</th>
                <th class="text-right" style="width: 10%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $item->product ? $item->product->name : 'Item #' . $item->product_id }}</strong></td>
                    <td>{{ $item->variant ? $item->variant->name : 'Default' }}</td>
                    <td class="text-center">{{ number_format($item->qty ?? $item->quantity ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($item->unit_cost ?? $item->unit_price ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total ?? $item->total_price ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td class="text-right">{{ $po->currency ? $po->currency->symbol : 'kr.' }} {{ number_format($po->foreign_amount ?? $po->total_amount, 2) }}</td>
        </tr>
        <tr style="font-size: 14px; background: #edf2f7; font-weight: bold;">
            <td style="padding: 8px;">Grand Total:</td>
            <td class="text-right" style="padding: 8px; color: #2b6cb0;">
                {{ $po->currency ? $po->currency->symbol : 'kr.' }} {{ number_format($po->foreign_amount ?? $po->total_amount, 2) }}
            </td>
        </tr>
    </table>

    <div class="clear"></div>

    <div class="footer">
        <p>This is a computer-generated Purchase Order issued by Copenhagen Tourist Point ERP.</p>
    </div>
</body>
</html>
