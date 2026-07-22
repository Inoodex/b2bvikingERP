<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>PI Invoice #{{ $order->order_no }}</title>
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
                max-width: 900px;
                background: #fff;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
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

            .btn-print {
                background: #ffc107;
                color: #000;
            }

            .btn-download {
                background: #3abaf4;
                color: #fff;
            }

            .btn-back {
                background: #6777ef;
                color: #fff;
            }

            .btn-close {
                background: #6c757d;
                color: #fff;
            }
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: #fff;
            }

            .container {
                width: 100%;
                padding: 0;
                box-shadow: none;
            }
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

        .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
            border-top: 2px solid #ddd;
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

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .badge-primary {
            background-color: #6777ef;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .text-right {
            text-align: right;
        }

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
        $status = strtolower((string) $order->status);
        $statusClass = match ($status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-info',
            'processing' => 'badge-primary',
            'shipped' => 'badge-primary',
            'completed' => 'badge-success',
            'rejected', 'cancelled' => 'badge-danger',
            default => 'badge-info',
        };
        $currency = $settings->currency_icon ?? '$';
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

    @if ($isPdf)
        <style>
            body {
                background: #fff !important;
                padding: 0 !important;
            }

            .container {
                max-width: 100% !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 24px !important;
            }

            .no-print {
                display: none !important;
            }
        </style>
    @endif

    <div class="container">
        @if (!$isPdf)
            <div class="no-print">
                <button onclick="window.print()" class="btn btn-print">Print Now</button>
                @if ($downloadUrl)
                    <a href="{{ $downloadUrl }}" class="btn btn-download">Download PDF</a>
                @endif
                @if ($isFrontend)
                    @if ($backUrl)
                        <a href="{{ $backUrl }}" class="btn btn-back">Back</a>
                    @endif
                @else
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-download">Edit PI Info</a>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-back">Back to List</a>
                @endif
                <button type="button" onclick="window.close(); if(!window.closed){ window.history.back(); }"
                    class="btn btn-close">Close</button>
            </div>
        @endif

        <div class="header clearfix">
            <div class="invoice-title">
                <h1>PI Invoice</h1>
                <p><strong>Order No:</strong> #{{ $order->order_no }}</p>
                <div style="margin-top: 10px;">
                    <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                </div>
            </div>
            <div class="company-info">
                @if ($logoData)
                    <div style="margin-bottom: 6px;">
                        <img src="{{ $logoData }}" alt="Logo"
                            style="height: 40px; max-width: 160px; object-fit: contain;">
                    </div>
                @endif
                {{-- <h3>{{ $settings->site_name ?? 'Inventory Management System' }}</h3>
                <p>
                    {{ $settings->contact_email ?? '' }}<br>
                    {!! nl2br(e($settings->address ?? '')) !!}
                </p> --}}
            </div>
        </div>

        <div class="details-box clearfix">
            <div class="box-left">
                <h4>Customer Details:</h4>
                <p>
                    <strong>{{ $order->billing_name }}</strong><br>
                    Outlet/Shop: {{ $order->billing_outlet_name ?: $order->user->outlet_name ?? 'N/A' }}<br>
                    Phone: {{ $order->billing_phone }}<br>
                    Email: {{ $order->pi_email ?: $order->billing_email }}
                </p>
            </div>
            <div class="box-right">
                <h4>Order Details:</h4>
                <p>
                    <strong>Date:</strong> {{ $order->created_at?->format('d M, Y h:i A') }}<br>
                    <strong>Source:</strong> {{ $order->shipping_method ?: 'frontend_checkout' }}<br>
                    <strong>Status:</strong> {{ strtoupper($order->status) }}
                </p>
            </div>
        </div>

        @unless ($hasSavedPiInfo)
            <div
                style="margin-bottom: 18px; background: #fff7ed; color: #9a3412; padding: 14px 16px; border-left: 4px solid #fb923c;">
                <strong>Draft PI:</strong> manual CTN information has not been saved yet. The packing section below is
                currently prefilled from the order quantities.
            </div>
        @endunless

        @php
            $itemsForColumns = isset($issuedItems) ? $issuedItems : ($order->items ?? collect());
            $showImageCol = collect($itemsForColumns)->contains(fn ($row) => !empty($row->product_image));
            $showProductNoCol = $itemsForColumns->contains(fn ($row) => !empty(optional($row->product)->product_number));
            $showCategoryCol = $itemsForColumns->contains(fn ($row) => !empty(optional(optional($row->product)->category)->name) || !empty($row->category_name));
            $showUnitCol = $itemsForColumns->contains(fn ($row) => !empty(optional(optional($row->product)->unit)->name));
            $showVariantsCol = $itemsForColumns->contains(fn ($row) => !empty($row->variant_label));
        @endphp

        @php
            // Group items by category first, then by product
            $groupedByCategory = [];
            $displayItems = isset($issuedItems) ? $issuedItems : $order->items;
            foreach ($displayItems as $item) {
                $categoryName = $item->category_name ?: 'General';
                $productId = $item->product_id;

                if (!isset($groupedByCategory[$categoryName])) {
                    $groupedByCategory[$categoryName] = [];
                }

                if (!isset($groupedByCategory[$categoryName][$productId])) {
                    $groupedByCategory[$categoryName][$productId] = [
                        'first_item' => $item,
                        'total_qty' => 0,
                        'variants' => [],
                    ];
                }
                $groupedByCategory[$categoryName][$productId]['total_qty'] += $item->quantity;
                $variantName = $item->variant_label ?: ($item->variant->name ?? 'Standard');

                if (!isset($groupedByCategory[$categoryName][$productId]['variants'][$variantName])) {
                    $groupedByCategory[$categoryName][$productId]['variants'][$variantName] = 0;
                }
                $groupedByCategory[$categoryName][$productId]['variants'][$variantName] += $item->quantity;
            }

            // Sort categories alphabetically
            ksort($groupedByCategory);

            // Sort products within each category alphabetically by product name
            foreach ($groupedByCategory as $categoryName => &$products) {
                uksort($products, function($a, $b) use ($products) {
                    return strcasecmp($products[$a]['first_item']->product_name, $products[$b]['first_item']->product_name);
                });
            }

            $globalIndex = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    @if ($showImageCol)
                        <th style="width: 15%;">Image</th>
                    @endif
                    <th style="width: 20%;">Product Information</th>
                    @if ($showProductNoCol)
                        <th style="width: 10%;">Product No</th>
                    @endif
                    @if ($showCategoryCol)
                        <th style="width: 15%;">Category</th>
                    @endif
                    @if ($showUnitCol)
                        <th style="width: 10%;">Unit</th>
                    @endif
                    @if ($showVariantsCol)
                        <th style="width: 15%;">Variants Ordered</th>
                    @endif
                    <th style="width: 10%;" class="text-right">Total Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedByCategory as $categoryName => $categoryProducts)
                    <tr style="background-color: #e9ecef;">
                        <td colspan="{{ 2 + ($showImageCol ? 1 : 0) + ($showProductNoCol ? 1 : 0) + ($showCategoryCol ? 1 : 0) + ($showUnitCol ? 1 : 0) + ($showVariantsCol ? 1 : 0) }}" style="padding: 8px 12px; font-weight: bold; text-transform: uppercase; font-size: 12px; color: #495057;">
                            {{ $categoryName }}
                        </td>
                    </tr>
                    @foreach ($categoryProducts as $productId => $group)
                        @php
                            $item = $group['first_item'];
                            $globalIndex++;
                            $imageSrc = $item->optimized_image ?? null;

                            // Fallback for non-PDF view if needed
                            if (!$isPdf && !$imageSrc) {
                                $imagePath = (string) ($item->product_image ?? '');
                                if ($imagePath !== '') {
                                    if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
                                        $imageSrc = $imagePath;
                                    } else {
                                        $imageSrc = asset('storage/' . ltrim($imagePath, '/'));
                                    }
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $globalIndex }}</td>
                        @if ($showImageCol)
                            <td class="image-cell">
                                @if ($imageSrc)
                                    <img src="{{ $imageSrc }}" alt="{{ $item->product_name }}">
                                @else
                                    <span class="image-empty">No Image</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            <strong>{{ $item->product_name }}</strong><br>

                            @if ($item->product)
                                {{-- @if ($item->product->slug)
                                    <small><strong>Slug:</strong> {{ $item->product->slug }}</small><br>
                                @endif --}}
                                {{-- @if ($item->product->brand)
                                    <small><strong>Brand:</strong> {{ $item->product->brand->name }}</small><br>
                                @endif
                                @if ($item->product->vendor)
                                    <small><strong>Vendor:</strong>
                                        {{ $item->product->vendor->shop_name ?? 'N/A' }}</small><br>
                                @endif --}}
                                @if ($item->product->barcode)
                                    <small><strong>Barcode:</strong> {{ $item->product->barcode }}</small><br>
                                @endif
                                @if ($item->product->self_number)
                                    <small><strong>Shelf No:</strong> {{ $item->product->self_number }}</small><br>
                                @endif
                                @if ($item->product->productType)
                                    <small><strong>Type:</strong> {{ $item->product->productType->name }}</small><br>
                                @endif
                                @if ($item->product->custom_label)
                                    <small><strong>Label:</strong> {{ $item->product->custom_label }}</small><br>
                                @endif
                                @if (!$isPdf && $item->product->long_description)
                                    <div style="font-size: 11px; margin-top: 5px; color: #555;">
                                        <strong>Description:</strong><br>
                                        {!! strip_tags($item->product->long_description) !!}
                                    </div>
                                @endif
                            @else
                                <small>No extra product details.</small>
                            @endif
                        </td>

                        <!-- Product No Column -->
                        @if ($showProductNoCol)
                            <td>
                                @if ($item->product && $item->product->product_number)
                                    <small>{{ $item->product->product_number }}</small>
                                @else
                                    <small>N/A</small>
                                @endif
                            </td>
                        @endif

                        <!-- Category Column -->
                        @if ($showCategoryCol)
                            <td>
                                @if ($item->product)
                                    <small><strong>Main:</strong> {{ $item->product->category->name ?? 'N/A' }}</small><br>
                                    @if ($item->product->subCategory)
                                        <small><strong>Sub:</strong> {{ $item->product->subCategory->name }}</small><br>
                                    @endif
                                    @if ($item->product->childCategory)
                                        <small><strong>Child:</strong>
                                            {{ $item->product->childCategory->name }}</small><br>
                                    @endif
                                @else
                                    <small>{{ $item->category_name ?: 'General' }}</small>
                                @endif
                            </td>
                        @endif

                        <!-- Unit Column -->
                        @if ($showUnitCol)
                            <td>
                                @if ($item->product && $item->product->unit)
                                    <small>{{ $item->product->unit->name }}</small>
                                @else
                                    <small>N/A</small>
                                @endif
                            </td>
                        @endif

                        <!-- Variants Column -->
                        @if ($showVariantsCol)
                            <td>
                                @if (count($group['variants']) > 0)
                                    @foreach ($group['variants'] as $vName => $vQty)
                                        <div style="margin-bottom: 3px;">
                                            <span class="badge badge-info"
                                                style="color: #0f0f0f; font-size: 11px;">{{ $vName }} &times;
                                                {{ $vQty }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <small>Standard</small>
                                @endif
                            </td>
                        @endif

                        <td class="text-right"><strong>{{ $group['total_qty'] }}</strong></td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        @include('backend.pi._packing_table', [
            'items' => $order->items,
            'piInfo' => $piInfo,
            'piTotals' => $piTotals,
        ])

        @if ($order->ship_different)
            <div style="margin-top: 15px; background: #f8f9fa; padding: 15px; border-left: 4px solid #6777ef;">
                <strong>Shipping Address:</strong><br>
                {{ $order->shipping_name ?: 'N/A' }} |
                {{ $order->shipping_phone ?: 'N/A' }} |
                {{ $order->shipping_email ?: 'N/A' }}<br>
                {{ $order->shipping_address ?: 'N/A' }},
                {{ $order->shipping_city ?: '' }} {{ $order->shipping_state ?: '' }},
                {{ $order->shipping_zip_code ?: '' }},
                {{ $order->shipping_country ?: '' }}
            </div>
        @endif

        <div class="footer">
            {{-- <p>Generated by {{ $settings->site_name ?? 'Inventory Management System' }} on {{ now()->format('d M, Y h:i A') }}</p> --}}
            <h3>{{ $settings->site_name ?? 'Inventory Management System' }}</h3>
            <p>
                {{ $settings->contact_email ?? '' }}<br>
                {!! nl2br(e($settings->address ?? '')) !!}
            </p>
        </div>
    </div>
</body>

</html>
