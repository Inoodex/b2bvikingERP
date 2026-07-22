<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Purchase #{{ $purchase->invoice_no }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #007bff;
            font-size: 24px;
        }

        .info-row {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-col {
            width: 50%;
            float: left;
        }

        .clear {
            clear: both;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #28a745;
            color: #fff;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }

        .table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .variant-item {
            display: inline-block;
            padding: 2px 6px;
            background: #eee;
            border-radius: 3px;
            font-size: 11px;
            margin-right: 5px;
            margin-top: 3px;
        }

        .summary-box {
            float: right;
            width: 250px;
            margin-top: 20px;
            border: 1px solid #dee2e6;
            padding: 15px;
            background: #fdfdfd;
        }
    </style>
</head>

<body>
    @php
        // Logo optimization
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
    <div class="container">
        <div class="header">
            @if($logoData)
                <div style="margin-bottom: 10px;">
                    <img src="{{ $logoData }}" alt="Logo" style="height: 46px; max-width: 180px; object-fit: contain;">
                </div>
            @endif
            <div style="font-size: 18px; font-weight: bold;">ORDER RECEIVE DETAILS</div>
        </div>

        <div class="info-row">
            <div class="info-col">
                <strong>Vendor Information:</strong><br>
                {{ $purchase->vendor->shop_name }}<br>
                {{ $purchase->vendor->address }}<br>
                {{ $purchase->vendor->phone }}
            </div>
            <div class="info-col text-right">
                <strong>Invoice No:</strong> #{{ $purchase->invoice_no }}<br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($purchase->date)->format('d M, Y') }}<br>
                <strong>Shipping Method:</strong> {{ $purchase->shipping_method ?? 'N/A' }}<br>
                {{-- <strong>Status:</strong>
                <span class="badge badge-success">Received</span> --}}
            </div>
            <div class="clear"></div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th width="30">#</th>
                    <th>Product Details</th>
                    <th width="80" class="text-center">Quantity</th>
                    <th width="100" class="text-right">Unit Cost</th>
                    <th width="100" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; @endphp
                @foreach ($purchase->details as $index => $detail)
                    @php $totalQty += $detail->qty; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                @if($detail->product && $detail->product->optimized_image)
                                    <img src="{{ $detail->product->optimized_image }}" style="width: 50px; height: 50px; margin-right: 10px; border: 1px solid #eee; object-fit: cover;">
                                @endif
                                <div>
                                    <div style="font-weight: bold;">{{ $detail->product->name }}</div>
                                    <div style="font-size: 11px; color: #666;">Product No: {{ $detail->product->product_number ?? 'N/A' }}</div>
                                    @if ($detail->variant_info)
                                        @foreach ($detail->variant_info as $name => $qty)
                                            <span class="variant-item">{{ $name }}: {{ $qty }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center">{{ (float) $detail->qty }}</td>
                        <td class="text-right">{{ $settings->currency_icon }}{{ number_format($detail->unit_cost, 2) }}</td>
                        <td class="text-right">{{ $settings->currency_icon }}{{ number_format($detail->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($purchase->note)
            <div style="margin-top: 30px;">
                <strong>Note / Reference:</strong><br>
                <div style="padding: 10px; background: #f9f9f9; border: 1px solid #eee; margin-top: 5px;">
                    {{ $purchase->note }}
                </div>
            </div>
        @endif

        @php
            $vendorSubtotal = $purchase->details->sum(function($d) { 
                return $d->unit_cost_vendor * $d->qty; 
            });
        @endphp

        <div class="summary-box">
            <div style="font-size: 14px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Grand Total (Local)</div>
            <div style="font-size: 24px; font-weight: bold; color: #007bff; margin-top: 5px;">
                {{ $settings->currency_icon }}{{ number_format($purchase->total_amount, 2) }}
            </div>
            <div style="font-size: 12px; color: #666; margin-top: 5px;">
                Total Qty: {{ (float) $totalQty }}
            </div>
            
            @if($purchase->vendor && $vendorSubtotal > 0)
                <div style="border-top: 1px solid #eee; margin-top: 10px; padding-top: 10px;">
                    <div style="font-size: 12px; color: #666;">Vendor Total</div>
                    <div style="font-size: 18px; font-weight: bold; color: #28a745; margin-top: 3px;">
                        {{ $purchase->vendor->currency_icon }}{{ number_format($vendorSubtotal, 2) }}
                    </div>
                </div>
            @endif
            <div style="border-top: 1px solid #eee; margin-top: 10px; padding-top: 10px;">
                <div style="font-size: 12px; color: #666;">Paid</div>
                <div style="font-size: 16px; font-weight: bold; color: #28a745; margin-top: 3px;">
                    {{ $settings->currency_icon }}{{ number_format($purchase->paid_amount, 2) }}
                </div>
            </div>
            <div style="border-top: 1px solid #eee; margin-top: 10px; padding-top: 10px;">
                <div style="font-size: 12px; color: #666;">Due</div>
                <div style="font-size: 16px; font-weight: bold; color: {{ $purchase->due_amount > 0 ? '#dc3545' : '#28a745' }}; margin-top: 3px;">
                    {{ $settings->currency_icon }}{{ number_format($purchase->due_amount, 2) }}
                </div>
            </div>
        </div>
        <div class="clear"></div>

        <div class="footer">
            <div style="font-weight: bold;">{{ $settings->site_name ?? 'Inventory Management System' }}</div>
            <div>{{ $settings->contact_email ?? '' }}</div>
            <div>{!! nl2br(e($settings->address ?? '')) !!}</div>
        </div>
    </div>
</body>

</html>
