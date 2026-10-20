<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $settings->site_name ?? 'B2B Viking ERP' }} — Expected Retail Price Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.4;
            padding: 16px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 8px;
        }

        .header h1 {
            margin: 0;
            color: #0c4a6e;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: -0.02em;
        }

        .header .sub {
            font-size: 13px;
            font-weight: bold;
            color: #0284c7;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .header .info {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 4px;
        }

        /* 4 KPI Summary Chips */
        .summary-row {
            width: 100%;
            margin-bottom: 12px;
            table-layout: fixed;
        }

        .summary-box {
            width: 24%;
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            text-align: center;
            vertical-align: middle;
        }

        .summary-box .lbl {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.04em;
        }

        .summary-box .val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        .val-indigo { color: #4338ca !important; }
        .val-emerald { color: #047857 !important; }
        .val-sky { color: #0284c7 !important; }
        .val-amber { color: #b45309 !important; }

        /* Table */
        table.stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        table.stock-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 6px 7px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            border-left: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }

        table.stock-table td {
            padding: 5px 7px;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: 9px;
        }

        table.stock-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.stock-table tr {
            page-break-inside: avoid;
        }

        .tc { text-align: center; }
        .tr { text-align: right; }
        .tl { text-align: left; }

        .p-img {
            width: 22px;
            height: 22px;
            object-fit: contain;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            display: block;
            margin: 0 auto;
        }

        .p-name {
            font-weight: bold;
            color: #0f172a;
            line-height: 1.25;
        }

        .cat-text {
            color: #334155;
            font-weight: 500;
        }

        .badge-retail {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
        }

        .badge-total {
            color: #15803d;
            font-weight: bold;
        }

        .footer {
            margin-top: 14px;
            text-align: right;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>

<body>
    {{-- Corporate Header --}}
    <div class="header">
        <h1>{{ $settings->site_name ?? 'B2B Viking ERP' }}</h1>
        <div class="sub">Expected Retail Price & Stock Valuation Report</div>
        <div class="info">
            <strong>Generated on:</strong> {{ $generatedAt }}
            @if(!empty($outletName))
                &nbsp;|&nbsp; <strong>Outlet:</strong> {{ $outletName }}
            @endif
            @if(!empty($userName))
                &nbsp;|&nbsp; <strong>Customer:</strong> {{ $userName }}
            @endif
            @if(!empty($categoryName))
                &nbsp;|&nbsp; <strong>Category:</strong> {{ $categoryName }}
            @endif
            @if(!empty($brandName))
                &nbsp;|&nbsp; <strong>Brand:</strong> {{ $brandName }}
            @endif
            @if(empty($outletName) && empty($userName) && empty($categoryName) && empty($brandName))
                &nbsp;|&nbsp; <strong>Scope:</strong> All Stores & Inventory ({{ $products->count() }} Products)
            @endif
        </div>
    </div>

    {{-- 4 Executive KPI Summary Chips --}}
    <table class="summary-row" cellspacing="6" cellpadding="0">
        <tr>
            <td class="summary-box">
                <div class="lbl">Total Stock Units</div>
                <div class="val val-indigo">{{ number_format($totalQty) }}</div>
            </td>
            <td class="summary-box">
                <div class="lbl">Total Cost Basis</div>
                <div class="val val-emerald">{{ $settings->currency_icon }}{{ number_format($totalCost, 2) }}</div>
            </td>
            <td class="summary-box">
                <div class="lbl">Expected Retail Value</div>
                <div class="val val-sky">{{ $settings->currency_icon }}{{ number_format($totalRetail, 2) }}</div>
            </td>
            <td class="summary-box">
                <div class="lbl">Expected Profit Margin</div>
                <div class="val val-amber">{{ $settings->currency_icon }}{{ number_format($totalMargin, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Product Valuation Table --}}
    <table class="stock-table">
        <thead>
            <tr>
                <th style="width: 24px;" class="tc">#</th>
                <th style="width: 28px;" class="tc">Image</th>
                <th class="tl">Product Info</th>
                <th style="width: 95px;" class="tl">Category</th>
                <th style="width: 55px;" class="tc">Stock Qty</th>
                <th style="width: 70px;" class="tr">Unit Cost</th>
                <th style="width: 70px;" class="tr">Wholesale</th>
                <th style="width: 80px;" class="tr" style="background:#e0f2fe; color:#0369a1;">Expected Retail</th>
                <th style="width: 90px;" class="tr" style="background:#ecfdf5; color:#047857;">Expected Total</th>
                <th style="width: 80px;" class="tr">Expected Margin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $product)
                <tr>
                    <td class="tc">{{ $index + 1 }}</td>
                    <td class="tc">
                        @if(!empty($product->pdf_image_path) && file_exists($product->pdf_image_path))
                            <img src="{{ $product->pdf_image_path }}" class="p-img" alt="">
                        @else
                            <div style="width:22px; height:22px; background:#e2e8f0; border-radius:3px; margin:0 auto; line-height:22px; text-align:center; font-size:7px; color:#94a3b8;">N/A</div>
                        @endif
                    </td>
                    <td class="tl">
                        <div class="p-name">{{ $product->name }}</div>
                        @if(!empty($product->variants) && $product->variants->isNotEmpty())
                            <div style="margin-top: 3px; padding-top: 2px; border-top: 1px dashed #cbd5e1;">
                                @foreach($product->variants as $variant)
                                    @php
                                        $vQty = (float) ($variant->inventory_stocks_sum_quantity ?? 0);
                                        $vRetail = (float) ($variant->price ?? $product->price ?? 0);
                                        $vSpecs = array_filter([$variant->name, $variant->color->name ?? null, $variant->size->name ?? null]);
                                        $vTitle = !empty($vSpecs) ? implode(' / ', array_unique($vSpecs)) : ('Variant #' . $variant->id);
                                    @endphp
                                    <div style="font-size: 7.5px; color: #475569; margin-top: 1px;">
                                        <span style="color: #0284c7; font-weight: bold;">&bull; {{ $vTitle }}:</span>
                                        Stock: <strong>{{ number_format($vQty) }}</strong> | Retail: <strong>{{ $settings->currency_icon }}{{ number_format($vRetail, 2) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="tl cat-text">{{ $product->category->name ?? '-' }}</td>
                    <td class="tc font-weight-bold">{{ number_format($product->calc_qty) }}</td>
                    <td class="tr">{{ $settings->currency_icon }}{{ number_format($product->purchase_price ?? 0, 2) }}</td>
                    <td class="tr">{{ $settings->currency_icon }}{{ number_format($product->outlet_price ?? 0, 2) }}</td>
                    <td class="tr"><span class="badge-retail">{{ $settings->currency_icon }}{{ number_format($product->price ?? 0, 2) }}</span></td>
                    <td class="tr"><span class="badge-total">{{ $settings->currency_icon }}{{ number_format($product->calc_retail_val ?? 0, 2) }}</span></td>
                    <td class="tr" style="color: #0284c7; font-weight:bold;">{{ $settings->currency_icon }}{{ number_format($product->calc_margin_val ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="tc" style="padding: 20px; color: #64748b;">
                        No inventory products found matching the criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($products->count() > 0)
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #cbd5e1;">
                    <td colspan="4" class="tr" style="padding: 7px 10px; font-size: 9.5px;">GRAND TOTAL:</td>
                    <td class="tc" style="font-size: 9.5px;">{{ number_format($totalQty) }}</td>
                    <td class="tr">-</td>
                    <td class="tr">-</td>
                    <td class="tr">-</td>
                    <td class="tr badge-total" style="font-size: 9.5px;">{{ $settings->currency_icon }}{{ number_format($totalRetail, 2) }}</td>
                    <td class="tr" style="color:#0284c7; font-size: 9.5px;">{{ $settings->currency_icon }}{{ number_format($totalMargin, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Generated by B2B Viking ERP System &bull; {{ $generatedAt }}
    </div>
</body>

</html>
