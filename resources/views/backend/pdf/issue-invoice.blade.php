<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Issue Invoice - {{ $issue->issue_no }}</title>
    <style>
        @page {
            margin: 0.5cm 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .container {
            padding: 0.5cm;
        }
        
        /* Web View Mockup */
        @media screen {
            body {
                background-color: #f4f7f6;
                padding: 40px 0;
            }
            .container {
                max-width: 850px;
                margin: 0 auto;
                background: #fff;
                box-shadow: 0 10px 25px rgba(0,0,0,0.05);
                border-radius: 8px;
                padding: 40px;
                min-height: 29.7cm;
            }
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .invoice-title {
            color: #2c3e50;
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 1px;
        }
        .ref-no {
            font-size: 13px;
            color: #7f8c8d;
            margin-top: 3px;
        }
        .company-name {
            font-size: 20px;
            color: #2c3e50;
            font-weight: bold;
            margin: 0;
        }
        .company-details {
            font-size: 11px;
            color: #7f8c8d;
            line-height: 1.4;
        }

        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-box-title {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            border-bottom: 1px solid #ecf0f1;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .info-content {
            font-size: 12px;
            color: #2c3e50;
        }
        .info-label {
            color: #7f8c8d;
            width: 90px;
            display: inline-block;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
            background-color: #27ae60;
            border-radius: 15px;
            margin-top: 8px;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.items-table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #dee2e6;
        }
        table.items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: middle;
        }
        .image-cell {
            text-align: center;
        }
        .image-cell img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ecf0f1;
        }
        .product-name {
            font-weight: bold;
            color: #2c3e50;
            font-size: 12px;
        }
        .variant-info {
            font-size: 10px;
            color: #7f8c8d;
            margin-top: 2px;
        }

        .total-section {
            background-color: #f8f9fa;
        }
        .total-label {
            text-align: right;
            font-weight: bold;
            color: #2c3e50;
            font-size: 12px;
            padding: 12px !important;
        }
        .total-value {
            text-align: center;
            font-weight: bold;
            color: #2c3e50;
            font-size: 14px;
            border-top: 2px solid #2c3e50;
            padding: 12px !important;
        }

        .notes-section {
            margin-top: 15px;
            padding: 12px;
            background-color: #fdfaf0;
            border-left: 3px solid #f1c40f;
            border-radius: 3px;
        }
        .notes-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 4px;
            font-size: 11px;
            text-transform: uppercase;
        }

        .signature-table {
            width: 100%;
            margin-top: 60px;
        }
        .signature-line {
            border-top: 1px solid #bdc3c7;
            width: 180px;
            margin: 0 auto 5px;
        }
        .signature-text {
            font-size: 11px;
            color: #7f8c8d;
            text-align: center;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #bdc3c7;
            font-size: 10px;
            border-top: 1px solid #ecf0f1;
            padding-top: 10px;
        }
        
        .action-bar {
            display: none;
        }
        @media screen {
            .action-bar {
                max-width: 850px;
                margin: 0 auto 20px;
                text-align: right;
                display: block;
                padding: 0 40px;
            }
            .btn {
                padding: 8px 16px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: bold;
                font-size: 13px;
                margin-left: 10px;
                display: inline-block;
            }
            .btn-secondary { background: #95a5a6; color: #fff; }
            .btn-info { background: #3498db; color: #fff; }
            .btn-primary { background: #2c3e50; color: #fff; }
            .btn-close { background: #6c757d; color: #fff; }
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
    @if(!($is_pdf ?? false))
    <div class="action-bar">
        <a href="{{ route('admin.issues.index') }}" class="btn btn-secondary">Back to List</a>
        <a href="#" onclick="window.print(); return false;" class="btn btn-info">Print Now</a>
        <a href="{{ route('admin.issues.download-invoice', $issue->id) }}" class="btn btn-primary">Download PDF</a>
        <button type="button" onclick="window.close(); if(!window.closed){ window.history.back(); }" class="btn btn-close">Close</button>
    </div>
    @endif

    <div class="container">
        <table style="width: 100%; border-bottom: 2px solid #2c3e50; padding-bottom: 15px; margin-bottom: 25px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <h1 class="invoice-title">Issue Invoice</h1>
                    <div class="ref-no">Ref: {{ $issue->issue_no }}</div>
                    <div class="badge">Completed</div>
                </td>
                <td style="width: 50%; text-align: right; vertical-align: top;">
                    @if($logoData)
                        <div>
                            <img src="{{ $logoData }}" alt="Logo" style="height: 38px; max-width: 160px; object-fit: contain;">
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                    <div class="info-box-title">Issued To</div>
                    <div class="info-content">
                        <strong>{{ $issue->outlet->outlet_name ?? $issue->outlet->name ?? 'N/A' }}</strong>
                        <div style="margin-top: 5px;">
                            <div style="margin-bottom: 2px;"><span style="color: #7f8c8d; width: 60px; display: inline-block;">User:</span> <strong>{{ $issue->outlet->name ?? '' }}</strong></div>
                            <div style="margin-bottom: 2px;"><span style="color: #7f8c8d; width: 60px; display: inline-block;">Phone:</span> <strong>{{ $issue->outlet->phone ?? 'N/A' }}</strong></div>
                            <div style="margin-bottom: 2px;"><span style="color: #7f8c8d; width: 60px; display: inline-block;">Email:</span> <strong>{{ $issue->outlet->email ?? 'N/A' }}</strong></div>
                            <div style="margin-bottom: 2px;"><span style="color: #7f8c8d; width: 60px; display: inline-block; vertical-align: top;">Address:</span> <strong style="display: inline-block; width: 220px;">{{ $issue->outlet->address ?? 'N/A' }}</strong></div>
                        </div>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div class="info-box-title" style="text-align: right;">Issue Details</div>
                    <div class="info-content text-right" style="text-align: right;">
                        <div style="margin-bottom: 2px;"><span style="color: #7f8c8d;">Date:</span> <strong>{{ $issue->created_at->format('d M, Y h:i A') }}</strong></div>
                        <div style="margin-bottom: 2px;"><span style="color: #7f8c8d;">Request Ref:</span> <strong>{{ $issue->productRequest->request_no ?? 'N/A' }}</strong></div>
                        <div style="margin-bottom: 2px;"><span style="color: #7f8c8d;">Issued By:</span> <strong>{{ Auth::user()->name }}</strong></div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">SL</th>
                    <th style="width: 12%; text-align: center;">Image</th>
                    <th style="width: 50%;">Product Description</th>
                    <th style="width: 30%; text-align: center;">Qty</th>
                </tr>
            </thead>
            @php
                $groupedItems = $issue->items->groupBy(function($item) {
                    return $item->product && $item->product->category ? $item->product->category->name : 'General';
                })->sortKeys();

                $sortedGroupedItems = $groupedItems->map(function($items) {
                    return $items->sortBy(function($item) {
                        return strtolower($item->product ? $item->product->name : '');
                    })->values();
                });

                $globalIndex = 0;
            @endphp
            <tbody>
                @foreach($sortedGroupedItems as $categoryName => $categoryItems)
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="4" style="padding: 8px 12px; font-weight: bold; text-transform: uppercase; font-size: 11px; color: #2c3e50; border-bottom: 1px solid #ecf0f1;">
                            {{ $categoryName }}
                        </td>
                    </tr>
                    @foreach($categoryItems as $item)
                        @php $globalIndex++; @endphp
                        <tr>
                            <td style="text-align: center; color: #7f8c8d;">{{ str_pad($globalIndex, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="image-cell">
                                @if($item->product && !empty($item->product->optimized_image))
                                    <img src="{{ $item->product->optimized_image }}" alt="{{ $item->product->name ?? 'Product Image' }}">
                                @elseif($item->product && !empty($item->product->thumb_image))
                                    <img src="{{ str_starts_with((string) $item->product->thumb_image, 'http') ? $item->product->thumb_image : asset('storage/' . ltrim((string) $item->product->thumb_image, '/')) }}" alt="{{ $item->product->name ?? 'Product Image' }}">
                                @else
                                    <span style="font-size: 11px; color: #7f8c8d;">No Image</span>
                                @endif
                            </td>
                            <td>
                                <div class="product-name">{{ $item->product->name ?? 'Deleted Product' }}</div>
                                <div class="variant-info">
                                    <span style="color: #666;">Product No: {{ $item->product->product_number ?? 'N/A' }}</span><br>
                                    @if($item->variant)
                                        Variant: {{ $item->variant->name }}
                                    @else
                                        Standard
                                    @endif
                                    @if($item->product && $item->product->sku)
                                        | SKU: {{ $item->product->sku }}
                                    @endif
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: bold; color: #2c3e50;">{{ $item->quantity }}</td>
                        </tr>
                    @endforeach
                @endforeach
                <tr class="total-section">
                    <td colspan="3" class="total-label">Total Quantity Combined</td>
                    <td class="total-value">{{ $issue->total_qty }}</td>
                </tr>
            </tbody>
        </table>

        @if($issue->note)
        <div class="notes-section">
            <div class="notes-title">Admin Note / Instructions</div>
            <div style="font-size: 12px; color: #2c3e50;">{{ $issue->note }}</div>
        </div>
        @endif

        <table class="signature-table">
            <tr>
                <td style="width: 33%; vertical-align: bottom;">
                    <div class="signature-line"></div>
                    <div class="signature-text">Issued By</div>
                </td>
                <td style="width: 33%; vertical-align: bottom;">
                    <div class="signature-line"></div>
                    <div class="signature-text">Authorized Signature</div>
                </td>
                <td style="width: 33%; vertical-align: bottom;">
                    <div class="signature-line"></div>
                    <div class="signature-text">Receiver Signature</div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <div style="font-weight: bold;">{{ $settings->site_name ?? config('app.name') }}</div>
            <div>{{ $settings->contact_email ?? '' }}</div>
            <div>{!! nl2br(e($settings->address ?? '')) !!}</div>
        </div>
    </div>
</body>
</html>
