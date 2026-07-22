<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Current Inventory Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #007bff;
            font-size: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        .table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: #fff;
        }

        .badge-danger {
            background-color: #dc3545;
            color: #fff;
        }
        
        .summary-box {
            float: right;
            width: 200px;
            margin-top: 15px;
            border: 1px solid #dee2e6;
            padding: 10px;
            background: #fdfdfd;
        }
        
        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ \App\Models\GeneralSetting::first()->site_name ?? 'Inventory Management System' }}</h1>
            <div style="font-size: 16px; font-weight: bold; margin-top: 5px;">CURRENT INVENTORY REPORT</div>
            <p>Generated on: {{ date('d M, Y h:i A') }}</p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th width="30">#</th>
                    <th width="60" class="text-center">Image</th>
                    <th>Product Name</th>
                    <th>Variant</th>
                    <th>Item Number</th>
                    <th>Category</th>
                    <th width="60" class="text-center">Stock</th>
                    <th width="80" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stocks as $index => $stock)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">
                            @if ($stock->optimized_image)
                                <img src="{{ $stock->optimized_image }}" 
                                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            @elseif ($stock->product && $stock->product->thumb_image)
                                <img src="{{ public_path('storage/' . $stock->product->thumb_image) }}"
                                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            @else
                                <span style="font-size: 8px; color: #ccc;">N/A</span>
                            @endif
                        </td>
                        <td>{{ $stock->product->name ?? 'N/A' }}</td>
                        <td>{{ $stock->variant ? $stock->variant->name : '-' }}</td>
                        <td>{{ $stock->product->product_number ?? '-' }}</td>
                        <td>{{ $stock->product->category->name ?? '-' }}</td>
                        <td class="text-center font-weight-bold">
                            {{ $stock->quantity }}
                        </td>
                        <td class="text-center">
                            @if ($stock->quantity > 0)
                                <span class="badge badge-success">In Stock</span>
                            @else
                                <span class="badge badge-danger">Out of Stock</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-box">
            <div style="font-size: 12px; border-bottom: 1px solid #eee; padding-bottom: 3px;">Total Items</div>
            <div style="font-size: 18px; font-weight: bold; color: #007bff; margin-top: 3px;">
                {{ $stocks->count() }}
            </div>
            <div style="font-size: 12px; border-bottom: 1px solid #eee; padding-bottom: 3px; margin-top: 10px;">Total Quantity</div>
            <div style="font-size: 18px; font-weight: bold; color: #007bff; margin-top: 3px;">
                {{ $totalQuantity }}
            </div>
        </div>
        <div class="clear"></div>

        <div class="footer">
            {{ \App\Models\GeneralSetting::first()->site_name ?? 'Inventory Management System' }} | {{ date('Y') }}
        </div>
    </div>
</body>

</html>
