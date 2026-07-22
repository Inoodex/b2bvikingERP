<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Invoice - {{ $order->order_no }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header { margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .left { width: 45%; float: left; }
        .right { width: 50%; float: right; text-align: right; }
        .clearfix:after { content: ""; display: table; clear: both; }
        h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f9f9f9; text-align: left; padding: 10px; border-bottom: 2px solid #eee; text-transform: uppercase; font-size: 11px; color: #777; }
        td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: top; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background-color: #f8f9fa; border-top: 2px solid #ddd; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; padding: 16px 0; border-top: 1px solid #eee; }
        .box { margin-top: 10px; }
        .image-cell { text-align: center; }
        .image-cell img { width: 36px; height: 36px; object-fit: cover; border-radius: 3px; border: 1px solid #ddd; }
        .image-empty { font-size: 10px; color: #999; }
    </style>
</head>
<body>
    @php
        $currency = $settings->currency_icon ?? '$';
        // Logo is already optimized if passed from controller, but fallback to direct base64 if needed
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

    <div class="header clearfix">
        <div class="left">
            <h1>OUTLET/SHOP ORDER</h1>
            <div>Ref: #{{ $order->order_no }}</div>
            <div>Status: {{ strtoupper($order->status) }}</div>
        </div>
        <div class="right">
            @if($logoData)
                <div>
                    <img src="{{ $logoData }}" alt="Logo" style="height: 40px; max-width: 160px; object-fit: contain;">
                </div>
            @endif
        </div>
    </div>

    <div class="clearfix box">
        <div class="left">
            <div style="font-weight: bold;">Customer Details:</div>
            <div>{{ $order->billing_name }}</div>
            <div>{{ $order->billing_phone }}</div>
            <div>{{ $order->billing_email }}</div>
            <div>{{ $order->billing_outlet_name ?: ($order->user->outlet_name ?? 'N/A') }}</div>
        </div>
        <div class="right">
            <div style="font-weight: bold;">Order Details:</div>
            <div>Date: {{ $order->created_at?->format('d M, Y h:i A') }}</div>
            <div>Source: {{ $order->shipping_method ?: 'frontend_checkout' }}</div>
            <div>Ship Different: {{ $order->ship_different ? 'YES' : 'NO' }}</div>
        </div>
    </div>

    @php
        // Group items by category and sort alphabetically
        $groupedItems = $order->items->groupBy(function($item) {
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

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="43%">Product</th>
                <th width="18%">Variant</th>
                <th width="10%" class="text-right">Qty</th>
                <th width="12%" class="text-right">Unit</th>
                <th width="15%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sortedGroupedItems as $categoryName => $categoryItems)
                <tr style="background-color: #f0f0f0;">
                    <td colspan="6" style="padding: 6px 10px; font-weight: bold; text-transform: uppercase; font-size: 10px; color: #555;">
                        {{ $categoryName }}
                    </td>
                </tr>
                @foreach($categoryItems as $item)
                @php $globalIndex++; @endphp
                <tr>
                    <td>{{ $globalIndex }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $item->product_name }}</div>
                        <div style="font-size: 11px; color: #666;">product no: {{ $item->product->product_number ?? 'N/A' }}</div>
                        <div style="font-size: 11px; color: #777;">{{ $item->category_name ?: 'General' }}</div>
                    </td>
                    <td>{{ $item->variant_label ?: 'Standard' }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $currency }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $currency }}{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
            @endforeach
            @php
                $displaySubtotal = isset($issuedItems) ? $issuedItems->sum('line_total') : ($order->subtotal_amount ?: $order->total_amount);
                $displayGrandTotal = isset($issuedItems) ? ($displaySubtotal - $order->discount_amount + $order->tax_amount) : $order->total_amount;
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
                <td colspan="5" style="text-align: right; border: none; padding: 4px 10px; font-size: 12px;">PAID TOTAL</td>
                <td style="text-align: right; border-bottom: 1px solid #ddd; color: #28a745; font-weight: bold; padding: 4px 10px; font-size: 12px;">{{ $currency }}{{ number_format($displayPaid, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: right; border: none; font-weight: bold; padding: 6px 10px; font-size: 14px;">DUE BALANCE</td>
                <td style="text-align: right; font-weight: bold; color: {{ $displayDue > 0 ? '#dc3545' : '#28a745' }}; font-size: 14px; padding: 6px 10px;">{{ $currency }}{{ number_format($displayDue, 2) }}</td>
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
        <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-left: 3px solid #6777ef;">
            <div style="font-weight: bold; font-size: 11px;">Shipping Info:</div>
            <div style="font-size: 11px;">
                {{ $order->shipping_name ?: 'N/A' }},
                {{ $order->shipping_phone ?: 'N/A' }},
                {{ $order->shipping_email ?: 'N/A' }}<br>
                {{ $order->shipping_address ?: 'N/A' }},
                {{ $order->shipping_city ?: '' }} {{ $order->shipping_state ?: '' }},
                {{ $order->shipping_zip_code ?: '' }},
                {{ $order->shipping_country ?: '' }}
            </div>
        </div>
    @endif

    <div class="footer">
        <div style="font-weight: bold;">{{ $settings->site_name ?? 'Inventory Management System' }}</div>
        <div>{{ $settings->contact_email ?? '' }}</div>
        <div>{!! nl2br(e($settings->address ?? '')) !!}</div>
    </div>
</body>
</html>
