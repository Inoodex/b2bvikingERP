<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Credit Note #{{ $creditNote->credit_note_no }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 13px; line-height: 1.5; margin: 0; padding: 20px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { vertical-align: top; }
        .title { font-size: 24px; font-weight: bold; color: #1e293b; text-transform: uppercase; margin: 0; }
        .badge { display: inline-block; padding: 4px 8px; font-size: 11px; font-weight: bold; border-radius: 4px; color: #fff; background: #2563eb; }
        .company-info { text-align: right; }
        .bill-to-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; }
        .bill-to-table td { padding: 8px 12px; vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .items-table th { background: #1e293b; color: #fff; text-align: left; padding: 10px; font-size: 12px; }
        .items-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .totals-table { width: 40%; float: right; border-collapse: collapse; margin-bottom: 30px; }
        .totals-table td { padding: 6px 10px; text-align: right; }
        .totals-table tr.grand-total td { font-size: 15px; font-weight: bold; color: #0f172a; border-top: 2px solid #1e293b; border-bottom: 2px solid #1e293b; }
        .footer { margin-top: 50px; text-align: center; color: #64748b; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 15px; clear: both; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <h1 class="title">CREDIT NOTE</h1>
                <p style="margin: 5px 0 0 0; color: #64748b;">No: <strong>{{ $creditNote->credit_note_no }}</strong></p>
                <p style="margin: 2px 0 0 0; color: #64748b;">Date: {{ $creditNote->created_at ? $creditNote->created_at->format('d M Y') : date('d M Y') }}</p>
            </td>
            <td class="company-info">
                <h3 style="margin: 0; color: #0f172a;">B2B VIKING ERP</h3>
                <p style="margin: 3px 0 0 0; color: #64748b;">Commercial Accounts & Distribution</p>
                <p style="margin: 2px 0 0 0; color: #64748b;">Support: accounts@b2bvikingerp.com</p>
            </td>
        </tr>
    </table>

    <table class="bill-to-table">
        <tr>
            <td width="50%">
                <strong style="color: #475569; font-size: 11px; text-transform: uppercase;">CREDIT ISSUED TO:</strong><br>
                <strong style="font-size: 14px; color: #0f172a;">
                    {{ $creditNote->customer ? ($creditNote->customer->outlet_name ?: $creditNote->customer->name) : 'General Customer' }}
                </strong><br>
                @if($creditNote->customer && $creditNote->customer->email)
                    Email: {{ $creditNote->customer->email }}<br>
                @endif
                @if($creditNote->customer && $creditNote->customer->phone)
                    Phone: {{ $creditNote->customer->phone }}
                @endif
            </td>
            <td width="50%">
                <strong style="color: #475569; font-size: 11px; text-transform: uppercase;">REFERENCE DETAILS:</strong><br>
                Sales Return Ref: <strong>#{{ $creditNote->salesReturn ? $creditNote->salesReturn->return_no : 'N/A' }}</strong><br>
                Original Order: <strong>#{{ $creditNote->salesReturn && $creditNote->salesReturn->order ? $creditNote->salesReturn->order->order_no : 'N/A' }}</strong><br>
                Status: <span class="badge">{{ strtoupper($creditNote->settlement_status) }}</span>
            </td>
        </tr>
    </table>

    @if($creditNote->salesReturn && $creditNote->salesReturn->items)
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="50%">Description / Product</th>
                    <th width="15%" style="text-align: center;">Returned Qty</th>
                    <th width="30%" style="text-align: right;">Return Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creditNote->salesReturn->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->product ? $item->product->name : 'Product #' . $item->product_id }}</strong>
                            @if($item->variant)
                                <br><small style="color: #64748b;">{{ $item->variant->name }}</small>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $item->qty }}</td>
                        <td style="text-align: right;">{{ $item->reason ?: 'Customer Return' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="totals-table">
        <tr class="grand-total">
            <td>Total Credit Value:</td>
            <td>kr. {{ number_format((float)$creditNote->amount, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #16a34a; font-weight: bold;">Settled Amount:</td>
            <td style="color: #16a34a; font-weight: bold;">kr. {{ number_format((float)$creditNote->settled_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #dc2626; font-weight: bold;">Remaining Unsettled:</td>
            <td style="color: #dc2626; font-weight: bold;">kr. {{ number_format((float)$creditNote->remaining_amount, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>This is an official computer-generated Accounts Credit Note for B2B Viking ERP. No signature required.</p>
    </div>
</body>
</html>
