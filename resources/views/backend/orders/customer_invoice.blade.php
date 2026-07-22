<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_no }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .container { padding: 30px; }
        .site-info { text-align: center; margin-bottom: 25px; }
        .site-info h2 { margin: 5px 0; font-size: 18px; text-transform: uppercase; color: #000; }
        .site-info p { margin: 2px 0; color: #666; font-size: 11px; }
        .logo { margin-bottom: 10px; }

        .invoice-header { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .invoice-header table { width: 100%; border: none; }
        .invoice-header td { vertical-align: top; border: none; padding: 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f4f4f4; text-align: left; padding: 8px; border: 1px solid #ddd; text-transform: uppercase; font-size: 10px; font-weight: bold; }
        td { padding: 8px; border: 1px solid #ddd; vertical-align: middle; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals { margin-top: 15px; width: 40%; float: right; }
        .totals table { margin-top: 0; border: none; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .grand-total { font-weight: bold; font-size: 14px; border-top: 1px solid #333; }

        .footer { clear: both; margin-top: 40px; text-align: center; font-size: 10px; color: #999; padding-top: 10px; border-top: 1px solid #eee; }
        .clearfix:after { content: ""; display: table; clear: both; }

        @page { margin: 20px; }
    </style>
</head>
<body>
    @php
        $currency = $settings->currency_icon ?? '$';
        $logoData = $settings->optimized_logo ?? null;
    @endphp

    <div class="container">
        <!-- Site Info (Top Center) -->
        <div class="site-info">
            @if($logoData)
                <div class="logo">
                    <img src="{{ $logoData }}" height="35" style="max-width: 180px;">
                </div>
            @endif
            <h2>{{ $settings->site_name ?? 'INVOICE' }}</h2>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-header clearfix">
            <table>
                <tr>
                    <td width="50%">
                        <div style="font-weight: bold; color: #777; margin-bottom: 5px;">BILL TO:</div>
                        <div style="font-size: 13px; font-weight: bold;">{{ $order->billing_name }}</div>
                        <div>{{ $order->billing_address }}</div>
                        <div>Phone: {{ $order->billing_phone }}</div>
                    </td>
                    <td width="50%" class="text-right">
                        <div style="font-size: 20px; font-weight: bold; color: #333; margin-bottom: 5px;">INVOICE</div>
                        <div>Invoice No: <strong>#{{ $order->order_no }}</strong></div>
                        <div>Date: {{ $order->created_at?->format('d M, Y') }}</div>
                        <div>Status: {{ strtoupper($order->status) }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Product Details -->
        <table>
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="10%" class="text-center">Image</th>
                    <th>Product Description</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="15%" class="text-right">Price</th>
                    <th width="15%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // First group by category, then by product within each category
                    $groupedByCategory = [];
                    foreach($order->items as $item) {
                        $categoryName = $item->category_name ?: 'General';
                        $productId = $item->product_id;

                        if (!isset($groupedByCategory[$categoryName])) {
                            $groupedByCategory[$categoryName] = [];
                        }

                        if (!isset($groupedByCategory[$categoryName][$productId])) {
                            $groupedByCategory[$categoryName][$productId] = [
                                'product_name' => $item->product_name,
                                'optimized_image' => $item->optimized_image,
                                'unit_price' => $item->unit_price,
                                'total_qty' => 0,
                                'total_price' => 0,
                                'variants' => []
                            ];
                        }
                        $groupedByCategory[$categoryName][$productId]['total_qty'] += $item->quantity;
                        $groupedByCategory[$categoryName][$productId]['total_price'] += $item->line_total;

                        if ($item->variant_label) {
                            $vName = $item->variant_label;
                            if (!isset($groupedByCategory[$categoryName][$productId]['variants'][$vName])) {
                                $groupedByCategory[$categoryName][$productId]['variants'][$vName] = 0;
                            }
                            $groupedByCategory[$categoryName][$productId]['variants'][$vName] += $item->quantity;
                        } elseif ($item->variant && $item->variant->name) {
                            $vName = $item->variant->name;
                            if (!isset($groupedByCategory[$categoryName][$productId]['variants'][$vName])) {
                                $groupedByCategory[$categoryName][$productId]['variants'][$vName] = 0;
                            }
                            $groupedByCategory[$categoryName][$productId]['variants'][$vName] += $item->quantity;
                        }
                    }

                    // Sort categories alphabetically
                    ksort($groupedByCategory);

                    // Sort products within each category alphabetically
                    foreach ($groupedByCategory as $categoryName => &$products) {
                        uksort($products, function($a, $b) use ($products) {
                            return strcasecmp($products[$a]['product_name'], $products[$b]['product_name']);
                        });
                    }

                    $rowNum = 1;
                @endphp

                @foreach($groupedByCategory as $categoryName => $categoryProducts)
                    <tr style="background-color: #f4f4f4;">
                        <td colspan="6" style="padding: 6px 8px; font-weight: bold; text-transform: uppercase; font-size: 10px; color: #555; border: 1px solid #ddd;">
                            {{ $categoryName }}
                        </td>
                    </tr>
                    @foreach($categoryProducts as $productId => $group)
                    <tr style="page-break-inside: avoid;">
                        <td class="text-center">{{ $rowNum++ }}</td>
                        <td class="image-cell">
                            @if($group['optimized_image'])
                                <img src="{{ $group['optimized_image'] }}" alt="{{ $group['product_name'] }}">
                            @else
                                <span style="font-size: 10px; color: #999;">No Image</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $group['product_name'] }}</div>
                            {{-- <div style="font-size: 11px; color: #666;">product no: {{ $group['first_item']->product->product_number ?? 'N/A' }}</div> --}}
                            @if(count($group['variants']) > 0)
                                <div style="font-size: 10px; color: #666; margin-top: 4px;">
                                    @foreach($group['variants'] as $vName => $vQty)
                                        <div>• {{ $vName }}: <strong>{{ $vQty }}</strong></div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="text-center">{{ $group['total_qty'] }}</td>
                    <td class="text-right">{{ $currency }}{{ number_format($group['unit_price'], 2) }}</td>
                    <td class="text-right">{{ $currency }}{{ number_format($group['total_price'], 2) }}</td>
                </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals clearfix">
            <table>
                @php
                    $displaySubtotal = isset($issuedItems) ? $issuedItems->sum('line_total') : ($order->subtotal_amount ?: $order->total_amount);
                    $displayGrandTotal = isset($issuedItems) ? ($displaySubtotal - $order->discount_amount + $order->tax_amount) : $order->total_amount;
                    $displayPaid = (float) $order->paid_amount;
                    $displayDue = max(0, round($displayGrandTotal - $displayPaid, 2));
                @endphp

                <tr>
                            <td class="text-right">Subtotal:</td>
                            <td class="text-right" width="40%">{{ $currency }}{{ number_format($displaySubtotal, 2) }}</td>
                        </tr>
                        @if($order->discount_amount > 0)
                        <tr>
                            <td class="text-right">Discount:</td>
                            <td class="text-right">-{{ $currency }}{{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($order->tax_amount > 0)
                        <tr>
                            <td class="text-right">VAT:</td>
                            <td class="text-right">{{ $currency }}{{ number_format($order->tax_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="grand-total">
                            <td class="text-right">Grand Total:</td>
                            <td class="text-right">{{ $currency }}{{ number_format($displayGrandTotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-right">Paid Amount:</td>
                            <td class="text-right">{{ $currency }}{{ number_format($displayPaid, 2) }}</td>
                        </tr>
                        <tr style="font-weight: bold; color: {{ $displayDue > 0 ? '#d9534f' : '#5cb85c' }};">
                            <td class="text-right">Due Balance:</td>
                            <td class="text-right">{{ $currency }}{{ number_format($displayDue, 2) }}</td>
                        </tr>
            </table>
        </div>

        <div class="footer">
            <div style="margin-bottom: 5px;">
                <strong>{{ $settings->site_name ?? 'Inventory Management System' }}</strong>
            </div>
            <div>{{ $settings->contact_email ?? '' }} | {{ $settings->phone ?? '' }}</div>
            <div>{!! str_replace(["\r\n", "\r", "\n"], ' | ', e($settings->address ?? '')) !!}</div>
            <p style="margin-top: 15px; color: #aaa;">Thank you for your business! This is a computer generated invoice.</p>
        </div>
    </div>
</body>
</html>
