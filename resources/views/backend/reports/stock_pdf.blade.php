<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $settings->site_name ?? 'B2B Viking ERP' }} — Stock Valuation Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            padding: 18px 22px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 8px;
        }

        .header h1 {
            margin: 0;
            color: #1e1b4b;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: -0.02em;
        }

        .header .sub {
            font-size: 13px;
            font-weight: bold;
            color: #4f46e5;
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
            margin-bottom: 14px;
            table-layout: fixed;
        }

        .summary-box {
            width: 24%;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            text-align: center;
            vertical-align: middle;
        }

        .summary-box .lbl {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.04em;
        }

        .summary-box .val {
            font-size: 13.5px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }

        .val-indigo { color: #4338ca !important; }
        .val-emerald { color: #047857 !important; }
        .val-sky { color: #0369a1 !important; }
        .val-amber { color: #b45309 !important; }

        /* Data Table */
        table.stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        table.stock-table thead {
            display: table-header-group;
        }

        table.stock-table th {
            background: #1e1b4b;
            color: #ffffff;
            border: 1px solid #1e1b4b;
            padding: 6px 7px;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        table.stock-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            vertical-align: middle;
            font-size: 9.5px;
        }

        table.stock-table tr:nth-child(even) td {
            background: #f8fafc;
        }

        table.stock-table tr {
            page-break-inside: avoid;
        }

        .tc { text-align: center; }
        .tr { text-align: right; }
        .tl { text-align: left; }

        .p-img {
            width: 24px;
            height: 24px;
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

        .brand-text {
            color: #64748b;
            font-size: 8.5px;
        }

        .badge-stock {
            display: inline-block;
            padding: 1px 5px;
            font-size: 8.5px;
            font-weight: bold;
            border-radius: 3px;
        }

        .badge-ok { background: #dcfce7; color: #15803d; }
        .badge-low { background: #fee2e2; color: #b91c1c; }

        /* Grand Total Row */
        table.stock-table tfoot tr td {
            background: #f1f5f9;
            font-weight: bold;
            border-top: 2px solid #6366f1;
            padding: 6px 7px;
            font-size: 10px;
        }

        .footer {
            margin-top: 14px;
            text-align: center;
            font-size: 8.5px;
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
        <div class="sub">Stock Valuation & Margin Report</div>
        <div class="info">
            <strong>Generated on:</strong> {{ $generatedAt }}
            @if(!empty($categoryName))
                &nbsp;|&nbsp; <strong>Category:</strong> {{ $categoryName }}
            @endif
            @if(!empty($brandName))
                &nbsp;|&nbsp; <strong>Brand:</strong> {{ $brandName }}
            @endif
            @if(empty($categoryName) && empty($brandName))
                &nbsp;|&nbsp; <strong>Scope:</strong> Full Inventory ({{ $products->count() }} Products)
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
                <div class="lbl">Total Asset Value (Cost)</div>
                <div class="val val-emerald">{{ $settings->currency_icon }}{{ number_format($totalValue, 2) }}</div>
            </td>
            <td class="summary-box">
                <div class="lbl">Potential Revenue (Sales)</div>
                <div class="val val-sky">{{ $settings->currency_icon }}{{ number_format($potentialRevenue, 2) }}</div>
            </td>
            <td class="summary-box">
                <div class="lbl">Projected Gross Margin</div>
                <div class="val val-amber">{{ $settings->currency_icon }}{{ number_format($potentialProfit, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Product Valuation Table --}}
    <table class="stock-table">
        <thead>
            <tr>
                <th style="width: 24px;" class="tc">#</th>
                <th style="width: 32px;" class="tc">Image</th>
                <th class="tl">Product Info</th>
                <th style="width: 105px;" class="tl">Category / Brand</th>
                <th style="width: 60px;" class="tc">Stock Qty</th>
                <th style="width: 75px;" class="tr">Unit Cost</th>
                <th style="width: 75px;" class="tr">Unit Price</th>
                <th style="width: 95px;" class="tr">Asset Value</th>
                <th style="width: 95px;" class="tr">Profit Potential</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $product)
                <tr>
                    <td class="tc">{{ $index + 1 }}</td>
                    <td class="tc">
                        @if(!empty($product->pdf_image_path) && file_exists($product->pdf_image_path))
                            <img src="{{ $product->pdf_image_path }}" class="p-img" alt="Product" />
                        @else
                            <span style="font-size: 8px; color: #94a3b8;">N/A</span>
                        @endif
                    </td>
                    <td>
                        <div class="p-name">{{ $product->name }}</div>
                    </td>
                    <td>
                        <div class="cat-text">{{ $product->category->name ?? '—' }}</div>
                        <div class="brand-text">{{ $product->brand->name ?? '—' }}</div>
                    </td>
                    <td class="tc">
                        @if($product->calc_qty <= ($product->min_inventory_qty ?? 0))
                            <span class="badge-stock badge-low">{{ number_format($product->calc_qty) }}</span>
                        @else
                            <span class="badge-stock badge-ok">{{ number_format($product->calc_qty) }}</span>
                        @endif
                    </td>
                    <td class="tr">{{ $settings->currency_icon }}{{ number_format((float) $product->purchase_price, 2) }}</td>
                    <td class="tr">{{ $settings->currency_icon }}{{ number_format((float) $product->price, 2) }}</td>
                    <td class="tr" style="font-weight: bold; color: #4338ca;">{{ $settings->currency_icon }}{{ number_format($product->calc_asset_value, 2) }}</td>
                    <td class="tr" style="font-weight: bold; color: #047857;">{{ $settings->currency_icon }}{{ number_format($product->calc_potential_profit, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="tc" style="padding: 16px; color: #94a3b8;">
                        No products match the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="tr">Grand Totals:</td>
                <td class="tc">{{ number_format($totalQty) }}</td>
                <td class="tr">—</td>
                <td class="tr">—</td>
                <td class="tr" style="color: #4338ca;">{{ $settings->currency_icon }}{{ number_format($totalValue, 2) }}</td>
                <td class="tr" style="color: #047857;">{{ $settings->currency_icon }}{{ number_format($potentialProfit, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ $settings->site_name ?? 'B2B Viking ERP' }} &bull; Confidential Inventory Valuation &bull; {{ date('Y') }}
    </div>

</body>

</html>
