<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request Invoice - {{ $productRequest->request_no }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header { margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .company-info { text-align: right; width: 60%; float: right; }
        .invoice-title { width: 40%; float: left; }
        .invoice-title h1 { margin: 0; color: #333; font-size: 24px; text-transform: uppercase; }
        .details-box { margin-bottom: 30px; clear: both; }
        .box-left { float: left; width: 48%; }
        .box-right { float: right; width: 48%; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f9f9f9; text-align: left; padding: 10px; border-bottom: 2px solid #eee; text-transform: uppercase; font-size: 11px; color: #777; }
        td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-weight-bold { font-weight: bold; }
        .total-row td { font-weight: bold; background-color: #f8f9fa; border-top: 2px solid #ddd; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; padding: 20px 0; border-top: 1px solid #eee; }
        .clearfix:after { content: ""; display: table; clear: both; }
        .variant-tag { display: inline-block; padding: 2px 4px; background: #eee; border-radius: 3px; font-size: 10px; margin-right: 5px; }
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
    <div class="header clearfix">
        <div style="float: left; width: 40%;">
            <h1 style="margin: 0; color: #333; font-size: 24px; text-transform: uppercase;">OUTLET/SHOP REQUEST</h1>
            <div style="font-size: 14px; color: #777;">Ref: #{{ $productRequest->request_no }}</div>
        </div>
        <div style="float: right; width: 55%; text-align: right;">
            @if($logoData)
                <div>
                    <img src="{{ $logoData }}" alt="Logo" style="height: 40px; max-width: 160px; object-fit: contain;">
                </div>
            @endif
        </div>
    </div>

    <div style="margin-bottom: 30px; clear: both;" class="clearfix">
        <div style="float: left; width: 48%;">
            <div class="font-weight-bold">Requester Details:</div>
            <div>{{ $productRequest->user->name }}</div>
            <div>{{ $productRequest->user->outlet_name ?? 'N/A' }}</div>
            <div>{{ $productRequest->user->phone ?? 'N/A' }}</div>
        </div>
        <div style="float: right; width: 48%; text-align: right;">
            <div class="font-weight-bold">Request Details:</div>
            <div>Date: {{ $productRequest->created_at->format('d M, Y') }}</div>
            <div>Status: {{ strtoupper($productRequest->status) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="50%">Product Details</th>
                @if(Auth::user()->can('Manage Product Requests'))
                    <th width="10%" class="text-center">Shelve No</th>
                @endif
                <th class="text-right">Price</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productRequest->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div class="font-weight-bold">{{ $item->product->name }}</div>
                    @if($item->variant)
                        <div style="margin-top: 3px;">
                            <span class="variant-tag">{{ $item->variant->name }}</span>
                            </div>
                        @endif
                    </td>
                    @if(Auth::user()->can('Manage Product Requests'))
                        <td class="text-center">{{ $item->product ? ($item->product->self_number ?? '-') : '-' }}</td>
                    @endif
                    <td class="text-right">{!! formatConverted($item->unit_price) !!}</td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">{!! formatConverted($item->subtotal) !!}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="{{ Auth::user()->can('Manage Product Requests') ? '5' : '4' }}" class="text-right">GRAND TOTAL</td>
                <td class="text-right">{!! formatConverted($productRequest->total_amount) !!}</td>
            </tr>
            @if($productRequest->order)
            <tr>
                <td colspan="{{ Auth::user()->can('Manage Product Requests') ? '5' : '4' }}" style="text-align: right; border: none;">PAID TOTAL</td>
                <td class="text-right" style="color: #28a745; font-weight: bold;">{!! formatConverted($productRequest->order->paid_amount) !!}</td>
            </tr>
            <tr>
                <td colspan="{{ Auth::user()->can('Manage Product Requests') ? '5' : '4' }}" style="text-align: right; border: none; font-weight: bold;">DUE BALANCE</td>
                <td class="text-right" style="font-weight: bold; color: {{ $productRequest->order->due_amount > 0 ? '#dc3545' : '#28a745' }}; font-size: 14px;">{!! formatConverted($productRequest->order->due_amount) !!}</td>
            </tr>
            @endif
        </tbody>
    </table>

    @if($hasSavedPiInfo)
        @include('backend.pi._packing_table', [
            'piInfo' => $piInfo,
            'piTotals' => $piTotals,
        ])
    @endif


    @if($productRequest->note)
    <div style="margin-top: 30px; padding: 10px; background: #f8f9fa; border-left: 3px solid #ddd;">
        <div class="font-weight-bold" style="font-size: 11px;">Requester Note:</div>
        <div style="font-size: 11px; color: #555;">{{ $productRequest->note }}</div>
    </div>
    @endif

    @if($productRequest->admin_note)
    <div style="margin-top: 15px; padding: 10px; background: #eef2ff; border-left: 3px solid #6777ef;">
        <div class="font-weight-bold" style="font-size: 11px;">Admin Remarks:</div>
        <div style="font-size: 11px; color: #555;">{{ $productRequest->admin_note }}</div>
    </div>
    @endif

    <div class="footer">
        <div style="font-weight: bold;">{{ $settings->site_name ?? 'Inventory Management System' }}</div>
        <div>{{ $settings->contact_email ?? '' }}</div>
        <div>{!! nl2br(e($settings->address ?? '')) !!}</div>
    </div>
</body>
</html>
