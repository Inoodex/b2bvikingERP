<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PI Invoice #{{ $productRequest->request_no }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        @media screen {
            body {
                background-color: #f0f0f0;
                padding: 40px 0;
            }
            .container {
                max-width: 960px;
                background: #fff;
                box-shadow: 0 0 15px rgba(0,0,0,0.1);
                border-radius: 4px;
                padding: 40px;
            }
            .no-print {
                margin-bottom: 20px;
                display: flex;
                justify-content: flex-end;
            }
            .btn {
                display: inline-block;
                padding: 8px 16px;
                margin-left: 10px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: bold;
                font-size: 13px;
                cursor: pointer;
                border: none;
            }
            .btn-print { background: #ffc107; color: #000; }
            .btn-download { background: #3abaf4; color: #fff; }
            .btn-back { background: #6777ef; color: #fff; }
            .btn-close { background: #6c757d; color: #fff; }
        }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .container { width: 100%; padding: 0; box-shadow: none; }
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .company-info {
            text-align: right;
            float: right;
            width: 55%;
        }
        .invoice-title {
            float: left;
            width: 45%;
        }
        .invoice-title h1 {
            margin: 0;
            color: #333;
            font-size: 28px;
            text-transform: uppercase;
        }
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
        .details-box {
            margin-bottom: 30px;
        }
        .box-left {
            float: left;
            width: 48%;
        }
        .box-right {
            float: right;
            width: 48%;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f8f9fa;
            color: #333;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
        }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-info { background-color: #17a2b8; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .text-right { text-align: right; }
        .image-cell {
            text-align: center;
        }
        .image-cell img {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }
        .image-empty {
            font-size: 11px;
            color: #999;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    @php
        $status = strtolower((string) $productRequest->status);
        $statusClass = match($status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-info',
            'completed' => 'badge-success',
            'rejected', 'cancelled' => 'badge-danger',
            default => 'badge-info',
        };
        $isFrontend = (bool) ($isFrontend ?? false);
        $backUrl = $backUrl ?? null;
        $downloadUrl = $downloadUrl ?? null;
        $isPdf = (bool) ($isPdf ?? false);

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

    @if($isPdf)
        <style>
            body { background: #fff !important; padding: 0 !important; }
            .container { max-width: 100% !important; box-shadow: none !important; border-radius: 0 !important; padding: 24px !important; }
            .no-print { display: none !important; }
        </style>
    @endif

    <div class="container">
        @if(!$isPdf)
            <div class="no-print">
                <button onclick="window.print()" class="btn btn-print">Print Now</button>
                @if($downloadUrl)
                    <a href="{{ $downloadUrl }}" class="btn btn-download">Download PDF</a>
                @endif
                @if($isFrontend)
                    @if($backUrl)
                        <a href="{{ $backUrl }}" class="btn btn-back">Back</a>
                    @endif
                @else
                    <a href="{{ route('admin.product-requests.show', $productRequest->id) }}" class="btn btn-download">Edit PI Info</a>
                    <a href="{{ route('admin.product-requests.index') }}" class="btn btn-back">Back to List</a>
                @endif
                <button type="button" onclick="window.close(); if(!window.closed){ window.history.back(); }" class="btn btn-close">Close</button>
            </div>
        @endif

        <div class="header clearfix">
            <div class="invoice-title">
                <h1>PI Invoice</h1>
                <p><strong>Order No:</strong> #{{ $productRequest->request_no }}</p>
                <div style="margin-top: 10px;">
                    <span class="badge {{ $statusClass }}">{{ ucfirst($productRequest->status) }}</span>
                </div>
            </div>
            <div class="company-info">
                @if($logoData)
                    <div>
                        <img src="{{ $logoData }}" alt="Logo" style="height: 40px; max-width: 160px; object-fit: contain;">
                    </div>
                @endif
            </div>
        </div>

        <div class="details-box clearfix">
            <div class="box-left">
                <h4>Customer Details:</h4>
                <p>
                    <strong>{{ $productRequest->user->name }}</strong><br>
                    Outlet/Shop: {{ $productRequest->user->outlet_name ?? 'N/A' }}<br>
                    Phone: {{ $productRequest->user->phone ?? 'N/A' }}<br>
                    Email: {{ $productRequest->order?->pi_email ?: ($productRequest->user->email ?? 'N/A') }}
                </p>
            </div>
            <div class="box-right">
                <h4>Request Details:</h4>
                <p>
                    <strong>Date:</strong> {{ $productRequest->created_at?->format('d M, Y h:i A') }}<br>
                    <strong>Source:</strong> {{ $productRequest->order?->shipping_method ?: 'admin_request' }}<br>
                    <strong>Status:</strong> {{ strtoupper($productRequest->status) }}
                </p>
            </div>
        </div>

        @unless($hasSavedPiInfo)
            <div style="margin-bottom: 18px; background: #fff7ed; color: #9a3412; padding: 14px 16px; border-left: 4px solid #fb923c;">
                <strong>Draft PI:</strong> manual CTN information has not been saved yet. The packing section is currently prefilled from the request quantities.
            </div>
        @endunless

        @php
            // Group items by category and sort alphabetically
            $groupedItems = $productRequest->items->groupBy(function($item) {
                return $item->product?->category?->name ?? 'General';
            })->sortKeys();

            // Sort items within each category by product name (letter by letter)
            $sortedGroupedItems = $groupedItems->map(function($items) {
                return $items->sortBy(function($item) {
                    return strtolower($item->product?->name ?? ('Product #' . $item->product_id));
                })->values();
            });

            $globalIndex = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Product Information</th>
                    <th style="width: 10%;">Product No</th>
                    <th style="width: 14%;">Category</th>
                    <th style="width: 10%;">Unit</th>
                    <th style="width: 16%;">Variant</th>
                    <th style="width: 10%;" class="text-right">Total Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sortedGroupedItems as $categoryName => $categoryItems)
                    <tr style="background-color: #e9ecef;">
                        <td colspan="7" style="padding: 8px 12px; font-weight: bold; text-transform: uppercase; font-size: 12px; color: #495057;">
                            {{ $categoryName }}
                        </td>
                    </tr>
                    @foreach($categoryItems as $item)
                        @php
                            $variantText = trim((string) ($item->variant->name ?? ''));
                            if ($variantText === '') {
                                $variantText = trim(collect([
                                    $item->variant->color->name ?? null,
                                    $item->variant->size->name ?? null,
                                ])->filter()->implode(' / '));
                            }
                            $globalIndex++;
                        @endphp
                        <tr>
                            <td>{{ $globalIndex }}</td>
                        <td>
                            <strong>{{ $item->product->name ?? ('Product #' . $item->product_id) }}</strong><br>
                            {{-- @if($item->product?->brand)
                                <small><strong>Brand:</strong> {{ $item->product->brand->name }}</small><br>
                            @endif
                            @if($item->product?->vendor)
                                <small><strong>Vendor:</strong> {{ $item->product->vendor->shop_name ?? 'N/A' }}</small><br>
                            @endif --}}
                            @if($item->product?->productType)
                                <small><strong>Type:</strong> {{ $item->product->productType->name }}</small><br>
                            @endif
                            @if($item->product?->custom_label)
                                <small><strong>Label:</strong> {{ $item->product->custom_label }}</small>
                            @endif
                        </td>
                        <td>{{ $item->product?->product_number ?? 'N/A' }}</td>
                        <td>
                            <small><strong>Main:</strong> {{ $item->product?->category?->name ?? 'N/A' }}</small><br>
                            @if($item->product?->subCategory)
                                <small><strong>Sub:</strong> {{ $item->product->subCategory->name }}</small><br>
                            @endif
                            @if($item->product?->childCategory)
                                <small><strong>Child:</strong> {{ $item->product->childCategory->name }}</small>
                            @endif
                        </td>
                        <td>{{ $item->product?->unit?->name ?? 'N/A' }}</td>
                        <td>{{ $variantText !== '' ? $variantText : 'Standard' }}</td>
                        <td class="text-right"><strong>{{ $item->qty }}</strong></td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        @include('backend.pi._packing_table', [
            'items' => $productRequest->items,
            'piInfo' => $piInfo,
            'piTotals' => $piTotals,
        ])

        @if($productRequest->note)
            <div style="margin-top: 15px; background: #f8f9fa; padding: 15px; border-left: 4px solid #6777ef;">
                <strong>Requester Note:</strong><br>
                {{ $productRequest->note }}
            </div>
        @endif

        <div class="footer">
            <div style="font-weight: bold;">{{ $settings->site_name ?? 'Inventory Management System' }}</div>
            <div>{{ $settings->contact_email ?? '' }}</div>
            <div>{!! nl2br(e($settings->address ?? '')) !!}</div>
        </div>
    </div>
</body>
</html>
