<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comparison Statement {{ $cs->cs_no }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; color: #2c3e50; }
        .header p { margin: 2px 0; color: #7f8c8d; }
        .info-table, .cs-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .cs-table th, .cs-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .cs-table th { background-color: #f2f2f2; font-weight: bold; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; color: #fff; display: inline-block; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-info { background-color: #17a2b8; }
        .badge-danger { background-color: #dc3545; }
        .highlight-l1 { background-color: #d4edda; font-weight: bold; }
        .signature-section { margin-top: 50px; width: 100%; }
        .signature-box { width: 30%; float: left; text-align: center; border-top: 1px dashed #333; padding-top: 5px; margin-right: 3%; }
        .clear { clear: both; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Comparison Statement (CS)</h2>
        <p>CS Reference: <strong>{{ $cs->cs_no }}</strong> | RFQ No: <strong>{{ $cs->rfq->rfq_no }}</strong></p>
        <p>Date: {{ $cs->created_at ? $cs->created_at->format('d M, Y') : date('d M, Y') }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <strong>RFQ Status:</strong> {{ ucfirst($cs->rfq->status) }}<br>
                <strong>Approval Status:</strong> 
                <span class="badge {{ $cs->approval_status === 'approved' ? 'badge-success' : ($cs->approval_status === 'rejected' ? 'badge-danger' : 'badge-warning') }}">
                    {{ strtoupper($cs->approval_status ?? 'PENDING') }}
                </span>
            </td>
            <td style="text-align: right;">
                <strong>Recommended Winner:</strong> {{ $cs->recommendedVendor ? $cs->recommendedVendor->shop_name : 'Split Award (Line Item Basis)' }}<br>
                <strong>Total Estimated Value:</strong> {{ number_format($cs->total_amount, 2) }}
            </td>
        </tr>
    </table>

    <h3>Quotation Comparison Matrix</h3>
    <table class="cs-table">
        <thead>
            <tr>
                <th>Product Requested</th>
                <th>Unit</th>
                <th>Qty</th>
                @foreach($quotations as $q)
                    <th>
                        {{ $q->vendor->shop_name }}<br>
                        <small>({{ $q->currency->name ?? 'Base' }})</small>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($cs->rfq->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->product->unit->name ?? 'Pcs' }}</td>
                    <td>{{ $item->qty }}</td>
                    @foreach($quotations as $q)
                        @php
                            $qi = $q->items->where('product_id', $item->product_id)->first();
                            $csItem = $cs->items->where('product_id', $item->product_id)->first();
                            $isSelected = $csItem && $qi && $csItem->selected_vendor_quotation_item_id == $qi->id;
                        @endphp
                        <td class="{{ $isSelected ? 'highlight-l1' : '' }}">
                            @if($qi)
                                {{ number_format($qi->unit_price, 2) }}
                                @if($isSelected)
                                    <br><small style="color: green;">✔ Awarded</small>
                                @endif
                            @else
                                <span style="color: #999;">N/A</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($cs->approvals && $cs->approvals->count() > 0)
        <h3>Approval Audit Log</h3>
        <table class="cs-table">
            <thead>
                <tr>
                    <th>Step / Role</th>
                    <th>Approver</th>
                    <th>Status</th>
                    <th>Date / Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cs->approvals as $approval)
                    <tr>
                        <td>{{ $approval->step->approverRole->name ?? 'Approver' }}</td>
                        <td>{{ $approval->user->name ?? 'Pending' }}</td>
                        <td>{{ strtoupper($approval->status) }}</td>
                        <td>
                            {{ $approval->updated_at ? $approval->updated_at->format('d M, Y H:i') : '-' }}
                            @if($approval->comments)
                                <br><small><em>"{{ $approval->comments }}"</em></small>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            Prepared By<br>
            <small>Procurement Officer</small>
        </div>
        <div class="signature-box">
            Verified By<br>
            <small>Finance Manager</small>
        </div>
        <div class="signature-box" style="margin-right: 0;">
            Approved By<br>
            <small>Management / Director</small>
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>
