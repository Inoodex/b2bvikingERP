<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Goods Received Note - {{ $grn->grn_no }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header-table, .content-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { vertical-align: top; }
        .title { font-size: 22px; font-weight: bold; color: #2b4c7e; text-transform: uppercase; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; background: #e0f2fe; color: #0369a1; }
        .content-table th { background: #2b4c7e; color: #ffffff; padding: 8px; text-align: left; font-size: 11px; }
        .content-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; width: 100%; }
        .signature-box { width: 30%; float: left; text-align: center; border-top: 1px dashed #666; padding-top: 5px; font-size: 11px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="title">GOODS RECEIVED NOTE (GRN)</div>
                <div style="margin-top: 5px;">GRN Number: <strong>{{ $grn->grn_no }}</strong></div>
                <div>Date: {{ $grn->created_at ? $grn->created_at->format('d M Y, h:i A') : date('d M Y') }}</div>
                <div>QC Status: <span class="status-badge">{{ strtoupper($grn->qc_status) }}</span></div>
            </td>
            <td class="text-right">
                <div style="font-size: 16px; font-weight: bold;">Copenhagen Tourist Point</div>
                <div>Destination Outlet: <strong>{{ $grn->outlet?->name }}</strong></div>
                <div>Received By: {{ $grn->receivedBy?->name ?? 'Store Staff' }}</div>
            </td>
        </tr>
    </table>

    <table class="header-table" style="background: #f8fafc; padding: 10px; border-radius: 4px;">
        <tr>
            <td width="50%">
                <strong>Purchase Order Details:</strong><br>
                PO Reference: {{ $grn->purchase?->po_no ?? 'PO #'.$grn->purchase_id }}<br>
                PO Type: {{ strtoupper($grn->purchase?->purchase_type ?? 'LOCAL') }}<br>
                Vendor: {{ $grn->purchase?->vendor?->name }}
            </td>
            <td width="50%">
                <strong>Gate Pass & Receiving Notes:</strong><br>
                {{ $grn->remarks ?? 'None recorded.' }}
            </td>
        </tr>
    </table>

    <table class="content-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="45%">Product Item & Description</th>
                <th width="15%">Variant</th>
                <th width="15%" class="text-right">Accepted Qty</th>
                <th width="15%" class="text-right">Rejected Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grn->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $item->product?->name }}</strong></td>
                    <td>{{ $item->variant?->name ?? '-' }}</td>
                    <td class="text-right" style="color: #15803d; font-weight: bold;">{{ number_format($item->accepted_qty, 2) }}</td>
                    <td class="text-right" style="color: #b91c1c; font-weight: bold;">{{ number_format($item->rejected_qty, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box" style="margin-right: 5%;">
            Received By (Store Storekeeper)
        </div>
        <div class="signature-box" style="margin-right: 5%;">
            QC Inspector Signature
        </div>
        <div class="signature-box" style="float: right;">
            Store Manager Approval
        </div>
    </div>
</body>
</html>
