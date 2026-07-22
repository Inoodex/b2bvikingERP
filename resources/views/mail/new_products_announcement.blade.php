<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Products Announcement</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f1f5f9;
            font-family: Arial, "Helvetica Neue", sans-serif;
            color: #0f172a;
        }
        .container {
            max-width: 880px;
            margin: 0 auto;
            padding: 24px 12px;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
        }
        .head {
            background: linear-gradient(135deg, #1d4ed8, #4338ca);
            color: #ffffff;
            padding: 18px 20px;
        }
        .head h1 {
            margin: 0;
            font-size: 20px;
            line-height: 1.3;
        }
        .head p {
            margin: 8px 0 0;
            font-size: 13px;
            opacity: 0.92;
        }
        .content {
            padding: 18px 20px 22px;
        }
        .meta {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 999px;
            padding: 5px 10px;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f8fafc;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #334155;
        }
        .btn-wrap {
            margin-top: 16px;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            padding: 10px 14px;
            border-radius: 9px;
        }
        .muted {
            color: #64748b;
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="head">
                <h1>New Product Update</h1>
                <p>
                    {{ $announcementMessage ?? 'A new product has been added.' }}
                </p>
            </div>
            <div class="content">
                <div class="meta">
                    Total New Products: {{ (int) $totalProducts }}
                </div>

                <p style="margin: 0 0 8px; font-size: 14px;">
                    Hello {{ $recipientName }},
                </p>
                <p style="margin: 0; font-size: 13px; color: #334155;">
                    {!! nl2br(e($announcementMessage ?? 'Here is the latest product update. You can review product information below.')) !!}
                </p>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Wholesale</th>
                            <th>Discount</th>
                            <th>VAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $index => $product)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $product['name'] ?? 'N/A' }}</strong>
                                    @if (!empty($product['url']))
                                        <div style="margin-top: 4px;">
                                            <a href="{{ $product['url'] }}" style="font-size: 11px; color: #2563eb; text-decoration: none;">View Product</a>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $product['product_number'] ?: 'N/A' }}</td>
                                <td>{{ $product['category'] ?? 'N/A' }}</td>
                                <td>{{ $product['brand'] ?? 'N/A' }}</td>
                                <td>{{ number_format((float) ($product['outlet_price'] ?? 0), 2) }}</td>
                                <td>{{ $product['discount_label'] ?? 'N/A' }}</td>
                                <td>{{ $product['vat_label'] ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ((int) $hiddenCount > 0)
                    <p class="muted">
                        +{{ (int) $hiddenCount }} more products are available in the catalog.
                    </p>
                @endif

                <div class="btn-wrap">
                    <a href="{{ $shopUrl }}" class="btn">Browse Full Catalog</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
