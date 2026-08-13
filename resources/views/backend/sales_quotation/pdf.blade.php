<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Quotation - {{ $salesQuotation->quotation_no }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header-table, .info-table, .items-table, .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .brand-title {
            font-size: 22px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
            text-align: right;
        }
        .info-section {
            margin-top: 20px;
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .items-table {
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .summary-box {
            float: right;
            width: 40%;
        }
        .summary-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 5px 0;
        }
        .total-row td {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            border-top: 2px solid #0f172a;
            padding-top: 8px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td>
                <h1 class="brand-title">{{ $settings->site_name ?? 'B2B Viking ERP' }}</h1>
                <p style="margin: 3px 0; color: #64748b;">Enterprise Sales & Commercial Distribution System</p>
            </td>
            <td style="text-align: right;">
                <div class="doc-title">SALES QUOTATION</div>
                <div style="font-weight: bold; font-size: 13px; color: #0f172a;"># {{ $salesQuotation->quotation_no }}</div>
                <div style="color: #64748b; font-size: 11px;">Date: {{ $salesQuotation->created_at->format('d M, Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- Info Section --}}
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <strong style="color: #64748b; text-transform: uppercase; font-size: 10px; display: block;">Customer Info:</strong>
                    <div style="font-size: 13px; font-weight: bold; color: #0f172a; margin-top: 2px;">{{ $salesQuotation->customer?->name ?? 'N/A' }}</div>
                    <div>Email: {{ $salesQuotation->customer?->email ?? 'N/A' }}</div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong style="color: #64748b; text-transform: uppercase; font-size: 10px; display: block;">Quote Details:</strong>
                    <div>Valid Until: <strong>{{ $salesQuotation->valid_until ? $salesQuotation->valid_until->format('d M, Y') : 'N/A' }}</strong></div>
                    <div>Incoterm: <strong>{{ $salesQuotation->incoterm ?? 'EXW' }}</strong></div>
                    <div>Currency: <strong>{{ $salesQuotation->currency?->code ?? 'DKK' }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Line Items --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%;">#</th>
                <th style="width: 47%;">Product Description</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Unit Price</th>
                <th style="width: 15%; text-align: right;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesQuotation->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name ?? 'Product' }}</strong>
                        @if($item->variant)
                            <div style="font-size: 10px; color: #64748b;">Variant: {{ $item->variant->name }}</div>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $item->qty }}</td>
                    <td style="text-align: right;">{{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($item->qty * $item->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary --}}
    <div class="summary-box">
        <table>
            <tr>
                <td style="color: #64748b;">Subtotal:</td>
                <td style="text-align: right; font-weight: bold;">{{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->subtotal_amount, 2) }}</td>
            </tr>
            <tr>
                <td style="color: #64748b;">Tax / VAT Amount:</td>
                <td style="text-align: right; font-weight: bold;">{{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td style="color: #64748b;">Discount Amount:</td>
                <td style="text-align: right; font-weight: bold; color: #dc2626;">- {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->discount_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Grand Total:</td>
                <td style="text-align: right; font-weight: bold;">
                    {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->total_amount, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    @if($salesQuotation->notes)
        <div style="margin-top: 30px; padding: 12px; background: #f8fafc; border-left: 3px solid #2563eb; font-size: 11px;">
            <strong style="color: #0f172a; display: block; margin-bottom: 4px;">Terms & Conditions / Notes:</strong>
            {{ $salesQuotation->notes }}
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Thank you for doing business with {{ $settings->site_name ?? 'B2B Viking ERP' }}! | Computer Generated Commercial Quotation
    </div>
</body>
</html>
