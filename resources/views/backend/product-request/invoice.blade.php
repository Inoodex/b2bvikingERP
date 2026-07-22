<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request Invoice #{{ $productRequest->request_no }}</title>
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
        
        /* Web View Styling */
        @media screen {
            body {
                background-color: #f0f0f0;
                padding: 40px 0;
            }
            .container {
                max-width: 800px;
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
            width: 60%;
        }
        .invoice-title {
            float: left;
            width: 40%;
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
        .table-responsive {
            width: 100%;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
        }
        .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
            border-top: 2px solid #ddd;
        }
        .variant-tag {
            display: inline-block;
            padding: 2px 6px;
            background: #eee;
            border-radius: 3px;
            font-size: 11px;
            margin-right: 5px;
            margin-top: 3px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
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
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-primary { background-color: #6777ef; }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-print">Print Now</button>
            <a href="{{ route('admin.product-requests.download-invoice', $productRequest->id) }}" class="btn btn-download">Download PDF</a>
            <a href="{{ route('admin.product-requests.index') }}" class="btn btn-back">Back to List</a>
            <button type="button" onclick="window.close(); if(!window.closed){ window.history.back(); }" class="btn btn-close">Close</button>
        </div>

        <div class="header clearfix">
            <div class="invoice-title">
                <h1>OUTLET/SHOP REQUEST</h1>
                <p><strong>Order No:</strong> #{{ $productRequest->request_no }}</p>
                <div style="margin-top: 10px;">
                    <span class="badge {{ $productRequest->status == 'completed' || $productRequest->status == 'approved' ? 'badge-success' : ($productRequest->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                        {{ ucfirst($productRequest->status) }}
                    </span>
                </div>
            </div>
            <div class="company-info">
                <div>
                    <img src="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}" alt="Logo" style="height: 42px; max-width: 180px; object-fit: contain;">
                </div>
            </div>
        </div>

        <div class="details-box clearfix">
            <div class="box-left">
                <h4>Requester Details:</h4>
                <p>
                    <strong>{{ $productRequest->user->name }}</strong><br>
                    Outlet: {{ $productRequest->user->outlet_name ?? 'N/A' }}<br>
                    Phone: {{ $productRequest->user->phone ?? 'N/A' }}<br>
                    Email: {{ $productRequest->user->email ?? 'N/A' }}
                </p>
            </div>
            <div class="box-right">
                <h4>Request Details:</h4>
                <p>
                    <strong>Date:</strong> {{ $productRequest->created_at->format('d M, Y h:i A') }}<br>
                    <strong>Status:</strong> {{ strtoupper($productRequest->status) }}
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 55%;">Product Details</th>
                        @can('Manage Product Requests')
                            <th style="width: 10%; text-align: center;">Shelve No</th>
                        @endcan
                        <th style="width: 15%; text-align: center;">Price</th>
                        <th style="width: 10%; text-align: center;">Qty</th>
                        <th style="width: 15%; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productRequest->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->name }}</strong><br>
                                <span style="font-size: 11px; color: #666;">product no: {{ $item->product->product_number ?? 'N/A' }}</span>
                                @if($item->variant)
                                    <div style="margin-top: 5px;">
                                        <span class="variant-tag">{{ $item->variant->name }} </span>
                                    </div>
                                @endif
                            </td>
                            @can('Manage Product Requests')
                                <td style="text-align: center;">{{ $item->product ? ($item->product->self_number ?? '-') : '-' }}</td>
                            @endcan
                            <td style="text-align: center;">{!! formatConverted($item->unit_price) !!}</td>
                            <td style="text-align: center;">{{ $item->qty }}</td>
                            <td style="text-align: right;">{!! formatConverted($item->subtotal) !!}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="{{ Auth::user()->can('Manage Product Requests') ? '6' : '5' }}" style="text-align: right;">GRAND TOTAL</td>
                        <td style="text-align: right;">{!! formatConverted($productRequest->total_amount) !!}</td>
                    </tr>
                    @if($productRequest->order)
                    <tr>
                        <td colspan="{{ Auth::user()->can('Manage Product Requests') ? '6' : '5' }}" style="text-align: right; border: none; padding: 5px 12px;">PAID TOTAL</td>
                        <td style="text-align: right; border-bottom: 1px solid #ddd; color: #28a745; font-weight: bold; padding: 5px 12px;">{!! formatConverted($productRequest->order->paid_amount) !!}</td>
                    </tr>
                    <tr>
                        <td colspan="{{ Auth::user()->can('Manage Product Requests') ? '6' : '5' }}" style="text-align: right; border: none; font-weight: bold; padding: 8px 12px;">DUE BALANCE</td>
                        <td style="text-align: right; font-weight: bold; color: {{ $productRequest->order->due_amount > 0 ? '#dc3545' : '#28a745' }}; font-size: 16px; padding: 8px 12px;">{!! formatConverted($productRequest->order->due_amount) !!}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($hasSavedPiInfo)
            @include('backend.pi._packing_table', [
                'piInfo' => $piInfo,
                'piTotals' => $piTotals,
            ])
        @endif


        @if($productRequest->note)
        <div style="margin-bottom: 20px; background: #f8f9fa; padding: 15px; border-left: 4px solid #ddd;">
            <strong>Requester Note:</strong><br>
            {{ $productRequest->note }}
        </div>
        @endif

        @if($productRequest->admin_note)
        <div style="margin-bottom: 20px; background: #eef2ff; padding: 15px; border-left: 4px solid #6777ef;">
            <strong>Admin Remarks:</strong><br>
            {{ $productRequest->admin_note }}
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
