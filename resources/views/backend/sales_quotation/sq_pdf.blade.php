<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SQ - {{ $salesQuotation->quotation_no }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 18px;
        }
        .header-table, .info-table, .items-table, .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
            text-align: right;
        }
        .info-section {
            margin-top: 15px;
            margin-bottom: 15px;
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .items-table {
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .items-table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 10px;
            text-transform: uppercase;
            padding: 7px 8px;
            text-align: left;
        }
        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .product-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
        }
        .no-thumb-placeholder {
            width: 48px;
            height: 48px;
            line-height: 48px;
            text-align: center;
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            font-size: 8px;
            color: #94a3b8;
        }
        .summary-box {
            float: right;
            width: 42%;
        }
        .summary-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 4px 0;
            font-size: 11px;
        }
        .total-row td {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            border-top: 2px solid #0f172a;
            padding-top: 6px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            font-size: 9px;
            color: #64748b;
        }
        .badge-code {
            display: inline-block;
            padding: 1px 4px;
            font-size: 9px;
            color: #475569;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td>
                <h1 class="brand-title">{{ $settings->site_name ?? 'B2B Viking ERP' }}</h1>
                <p style="margin: 2px 0; color: #64748b; font-size: 10px;">Official Product Catalog & Price Quotation | Copenhagen Tourist Point</p>
            </td>
            <td style="text-align: right;">
                <div class="doc-title">{{ (!empty($isLookbook) || $salesQuotation->is_prospect) ? 'PRODUCT CATALOG & PRICE LIST' : 'SALES QUOTATION (SQ)' }}</div>
                <div style="font-weight: bold; font-size: 12px; color: #0f172a;"># {{ $salesQuotation->quotation_no }}</div>
                <div style="color: #64748b; font-size: 10px;">Date: {{ $salesQuotation->created_at ? $salesQuotation->created_at->format('d M, Y') : date('d M, Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- Info Section --}}
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <strong style="color: #64748b; text-transform: uppercase; font-size: 9px; display: block;">
                        {{ $salesQuotation->is_prospect ? 'Prospective Buyer / Business:' : 'Customer / Buyer Info:' }}
                    </strong>
                    <div style="font-size: 12px; font-weight: bold; color: #0f172a; margin-top: 2px;">
                        {{ $salesQuotation->buyer_display_name }}
                    </div>
                    @if($salesQuotation->is_prospect)
                        @if($salesQuotation->buyer_phone)
                            <div>Phone: <strong>{{ $salesQuotation->buyer_phone }}</strong></div>
                        @endif
                        <div style="color: #64748b; font-size: 10px;">Commercial Status: Direct Inquiry</div>
                    @else
                        <div>Email: {{ $salesQuotation->customer?->email ?? 'N/A' }}</div>
                        <div>Phone: {{ $salesQuotation->customer?->phone ?? 'N/A' }}</div>
                    @endif
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong style="color: #64748b; text-transform: uppercase; font-size: 9px; display: block;">Quotation Terms:</strong>
                    <div>Valid Until: <strong>{{ $salesQuotation->valid_until ? $salesQuotation->valid_until->format('d M, Y') : 'N/A' }}</strong></div>
                    <div>Incoterms: <strong>{{ $salesQuotation->incoterm ?? 'EXW' }}</strong></div>
                    <div>Currency: <strong>{{ $salesQuotation->currency?->code ?? 'DKK' }} ({{ $salesQuotation->currency?->symbol ?? 'kr.' }})</strong></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Line Items --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 10%; text-align: center;">Image</th>
                <th style="width: 40%;">Product & Identification</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Wholesale Price</th>
                <th style="width: 10%; text-align: right;">MSRP</th>
                <th style="width: 10%; text-align: right;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesQuotation->items as $index => $item)
                @php
                    $prod = $item->product;
                    $optimizedImg = $prod->pdf_optimized_image ?? null;
                    if (!$optimizedImg && $prod && !empty($prod->thumb_image)) {
                        $optimizedImg = \App\Support\PdfImageHelper::optimize($prod->thumb_image, 80, 80, 70);
                    }
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">
                        @if($optimizedImg)
                            <img src="{{ $optimizedImg }}" class="product-thumb" alt="Product Image">
                        @else
                            <div class="no-thumb-placeholder">NO IMG</div>
                        @endif
                    </td>
                    <td>
                        <strong style="font-size: 11px; color: #0f172a;">{{ $prod?->name ?? 'Product' }}</strong>
                        <div style="margin-top: 2px;">
                            @if($prod?->product_number)
                                <span class="badge-code">SKU: {{ $prod->product_number }}</span>
                            @endif
                            @if($prod?->sku)
                                <span class="badge-code">Barcode: {{ $prod->sku }}</span>
                            @endif
                        </div>
                        @if($item->variant)
                            <div style="font-size: 9px; color: #64748b; margin-top: 1px;">
                                Variant: {{ $item->variant->name }}
                                @if($item->variant->color) | Color: {{ $item->variant->color->name }} @endif
                                @if($item->variant->size) | Size: {{ $item->variant->size->name }} @endif
                            </div>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format((float)$item->qty) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #1e293b;">
                        {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format((float)$item->unit_price, 2) }}
                    </td>
                    <td style="text-align: right; color: #64748b;">
                        @if($prod && $prod->price > 0)
                            {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format((float)$prod->price, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #0f172a;">
                        {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format((float)($item->qty * $item->unit_price), 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary --}}
    <div class="summary-box">
        <table>
            <tr>
                <td style="color: #64748b;">Subtotal Amount:</td>
                <td style="text-align: right; font-weight: bold;">{{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format((float)$salesQuotation->subtotal_amount, 2) }}</td>
            </tr>
            @if((float)($salesQuotation->tax_amount ?? 0) > 0)
                <tr>
                    <td style="color: #64748b;">Tax / VAT Amount:</td>
                    <td style="text-align: right; font-weight: bold;">{{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format((float)$salesQuotation->tax_amount, 2) }}</td>
                </tr>
            @endif
            @if((float)($salesQuotation->discount_amount ?? 0) > 0)
                <tr>
                    <td style="color: #64748b;">Special Discount:</td>
                    <td style="text-align: right; font-weight: bold; color: #dc2626;">- {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format((float)$salesQuotation->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>Total Quotation Value:</td>
                <td style="text-align: right; font-weight: bold;">
                    {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format((float)$salesQuotation->total_amount, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    @if($salesQuotation->clean_notes)
        <div style="margin-top: 20px; padding: 10px; background: #f8fafc; border-left: 3px solid #2563eb; font-size: 10px;">
            <strong style="color: #0f172a; display: block; margin-bottom: 2px;">Terms & Conditions / Buyer Notes:</strong>
            {{ $salesQuotation->clean_notes }}
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Thank you for doing business with {{ $settings->site_name ?? 'B2B Viking ERP' }}! | Copenhagen Tourist Point & Enterprise Wholesale Distribution
    </div>
</body>
</html>
