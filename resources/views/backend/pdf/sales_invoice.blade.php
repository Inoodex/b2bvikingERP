<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Commercial Sales Invoice #{{ $invoice->invoice_no }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #1a252f;
            margin: 0;
            text-transform: uppercase;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            text-align: right;
            margin: 0;
            text-transform: uppercase;
        }
        .invoice-number {
            font-size: 14px;
            font-weight: bold;
            color: #7f8c8d;
            text-align: right;
            margin-top: 4px;
        }
        .info-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-card {
            width: 48%;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            background-color: #f8fafc;
        }
        .card-title {
            font-weight: bold;
            font-size: 11px;
            color: #475569;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            padding: 8px;
            border: 1px solid #1e293b;
        }
        .items-table td {
            padding: 8px;
            border: 1px solid #cbd5e1;
            font-size: 11px;
        }
        .summary-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table td {
            padding: 5px 8px;
            font-size: 11px;
        }
        .summary-table .total-row td {
            font-weight: bold;
            font-size: 13px;
            color: #1e293b;
            border-top: 2px solid #1e293b;
            border-bottom: 2px solid #1e293b;
        }
        .payment-box {
            border: 1px solid #94a3b8;
            background-color: #f1f5f9;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td>
                @if (!empty($generalSetting->site_logo) && file_exists(public_path($generalSetting->site_logo)))
                    <img src="{{ public_path($generalSetting->site_logo) }}" style="max-height: 55px; margin-bottom: 6px;">
                @else
                    <h1 class="company-name">{{ $generalSetting->site_name ?? 'B2B VIKING ERP' }}</h1>
                @endif
                <div style="font-size: 10px; color: #64748b;">
                    {{ $generalSetting->address ?? 'Corporate Headquarters' }}<br>
                    Phone: {{ $generalSetting->phone ?? '+45 00 00 00 00' }} | Email: {{ $generalSetting->email ?? 'billing@b2bviking.com' }}<br>
                    VAT Reg No: {{ $generalSetting->vat_number ?? 'DK-99238419' }}
                </div>
            </td>
            <td>
                <h2 class="invoice-title">COMMERCIAL INVOICE</h2>
                <div class="invoice-number">Invoice No: #{{ $invoice->invoice_no }}</div>
                <div style="font-size: 11px; text-align: right; margin-top: 4px;">
                    <strong>Date:</strong> {{ $invoice->date ? $invoice->date->format('d M Y') : date('d M Y') }}<br>
                    <strong>Payment Due:</strong> {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : date('d M Y', strtotime('+30 days')) }}<br>
                    <strong>Order Ref:</strong> #{{ $invoice->order ? $invoice->order->order_no : '-' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- 2-Column Addresses -->
    <table class="info-container">
        <tr>
            <td class="info-card">
                <div class="card-title">Billed To (Customer):</div>
                @if ($invoice->order && $invoice->order->user)
                    <strong>{{ $invoice->order->user->outlet_name ?: $invoice->order->user->name }}</strong><br>
                    {{ $invoice->order->user->address ?: 'Billing Address' }}<br>
                    Phone: {{ $invoice->order->user->phone ?: '-' }}<br>
                    Email: {{ $invoice->order->user->email }}
                @else
                    <strong>Guest / Cash Customer</strong>
                @endif
            </td>
            <td style="width: 4%;"></td>
            <td class="info-card">
                <div class="card-title">Payment Terms & Terms of Sale:</div>
                <strong>Payment Terms:</strong> {{ $invoice->payment_terms ?: 'Net 30 Days' }}<br>
                <strong>Currency:</strong> DKK (kr.)<br>
                <strong>Billing Status:</strong> {{ strtoupper($invoice->status) }}<br>
                <strong>Sales Order Ref:</strong> #{{ $invoice->order ? $invoice->order->order_no : '-' }}
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 45%;">Item Description</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Unit Price</th>
                <th style="width: 20%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $idx => $item)
                @php
                    $lineSubtotal = (float)$item->qty * (float)$item->price;
                    $unitName = ($item->product && $item->product->unit) ? $item->product->unit->name : 'Pcs';
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->product ? $item->product->name : 'Product' }}</strong>
                        @if ($item->variant)
                            <br><span style="font-size: 10px; color: #64748b;">Variant: {{ $item->variant->name }}</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format((float)$item->qty, 2) }} {{ $unitName }}</td>
                    <td style="text-align: right;">kr. {{ number_format((float)$item->price, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">kr. {{ number_format($lineSubtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Financial Totals Summary Table -->
    <table class="summary-table">
        <tr>
            <td>Subtotal:</td>
            <td style="text-align: right;">kr. {{ number_format((float)$invoice->subtotal_amount, 2) }}</td>
        </tr>
        @if ($invoice->discount_amount > 0)
            <tr>
                <td style="color: #dc2626;">Discount:</td>
                <td style="text-align: right; color: #dc2626;">- kr. {{ number_format((float)$invoice->discount_amount, 2) }}</td>
            </tr>
        @endif
        @if ($invoice->tax_amount > 0)
            <tr>
                <td>VAT Tax:</td>
                <td style="text-align: right;">kr. {{ number_format((float)$invoice->tax_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="total-row">
            <td>INVOICE TOTAL:</td>
            <td style="text-align: right;">kr. {{ number_format((float)$invoice->total_amount, 2) }}</td>
        </tr>
    </table>

    <!-- Bank Wire Transfer Details -->
    <div class="payment-box">
        <strong style="font-size: 11px; color: #1e293b;">Bank Wire Transfer Payment Instructions:</strong>
        <div style="font-size: 10px; color: #475569; margin-top: 4px;">
            Bank Name: Nordea Bank Denmark | IBAN: DK89 2000 0123 4567 89 | SWIFT/BIC: NDEA22DK<br>
            Please reference Commercial Invoice Number <strong>#{{ $invoice->invoice_no }}</strong> on all bank wire transfers.
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        {{ $generalSetting->site_name ?? 'B2B Viking ERP' }} — Official Commercial Sales Invoice #{{ $invoice->invoice_no }} — Generated on {{ date('d M Y, h:i A') }}
    </div>

</body>
</html>
