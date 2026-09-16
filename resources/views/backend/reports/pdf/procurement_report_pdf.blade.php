<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $settings->site_name ?? 'B2B Viking' }} — Procurement Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #222; line-height: 1.4; padding: 15px; }

        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #28549e; padding-bottom: 8px; }
        .header h1 { margin: 0; color: #28549e; font-size: 17px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header .sub { font-size: 12px; font-weight: bold; margin-top: 2px; color: #333; }
        .header .info { font-size: 9px; color: #666; margin-top: 3px; }

        .vendor-card { background: #f4f6f9; border: 1px solid #d2d6de; padding: 8px 12px; margin-bottom: 12px; border-radius: 4px; }
        .vendor-card .name { font-size: 13px; font-weight: bold; color: #28549e; }
        .vendor-card .details { font-size: 9px; color: #555; margin-top: 2px; }

        .summary-grid { width: 100%; margin-bottom: 12px; border-collapse: separate; border-spacing: 5px; }
        .summary-cell { width: 25%; padding: 8px 6px; border: 1px solid #d2d6de; border-radius: 4px; background: #fafbfc; text-align: center; vertical-align: top; }
        .summary-cell .lbl { font-size: 8px; color: #666; text-transform: uppercase; font-weight: bold; }
        .summary-cell .val { font-size: 13px; font-weight: bold; margin-top: 2px; }
        .val-primary { color: #28549e; }
        .val-success { color: #28a745; }
        .val-danger { color: #dc3545; }
        .val-info { color: #17a2b8; }

        .section-title { font-size: 11px; font-weight: bold; margin: 10px 0 5px; padding: 3px 0; border-bottom: 1px solid #ccc; color: #333; text-transform: uppercase; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data-table th { background: #28549e; color: #fff; border: 1px solid #28549e; padding: 5px 6px; text-align: left; font-size: 9px; text-transform: uppercase; }
        table.data-table td { border: 1px solid #e1e4e8; padding: 4px 6px; vertical-align: middle; font-size: 9px; }
        table.data-table tr:nth-child(even) td { background: #f9fafb; }
        
        .tc { text-align: center; }
        .tr { text-align: right; }
        .font-bold { font-weight: bold; }

        .badge { display: inline-block; padding: 2px 4px; font-size: 8px; font-weight: bold; border-radius: 2px; text-transform: uppercase; }
        .badge-local { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
        .badge-foreign { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .badge-received { background-color: #d4edda; color: #155724; }
        .badge-pending { background-color: #fff3cd; color: #856404; }

        .footer { margin-top: 15px; text-align: center; font-size: 8px; color: #888; border-top: 1px solid #eee; padding-top: 6px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $settings->site_name ?? 'B2B Viking ERP' }}</h1>
        <div class="sub">Executive Procurement & Purchase Orders Audit Report</div>
        <div class="info">
            Generated: {{ now()->format('d M Y, h:i A') }}
            @if(!empty($filters['start_date'])) | From: {{ $filters['start_date'] }} @endif
            @if(!empty($filters['end_date'])) | To: {{ $filters['end_date'] }} @endif
            @if(!empty($filters['purchase_type'])) | Type: {{ ucfirst($filters['purchase_type']) }} @endif
            @if(!empty($filters['milestone_status'])) | Milestone: {{ ucfirst(str_replace('_', ' ', $filters['milestone_status'])) }} @endif
            @if(isset($selectedVendor)) | Vendor: {{ $selectedVendor->shop_name ?? $selectedVendor->name }} @endif
        </div>
    </div>

    @isset($selectedVendor)
        <div class="vendor-card">
            <div class="name">{{ $selectedVendor->shop_name ?? $selectedVendor->name }}</div>
            <div class="details">
                Email: {{ $selectedVendor->email ?? '—' }} | Phone: {{ $selectedVendor->phone ?? '—' }} | Country: {{ $selectedVendor->country ?? 'Denmark' }}
                @if($selectedVendor->currency) | Billing Currency: {{ $selectedVendor->currency->currency_code }} ({{ $selectedVendor->currency->currency_icon }}) @endif
            </div>
        </div>
    @endisset

    <!-- Executive KPI Grid -->
    <table class="summary-grid">
        <tr>
            <td class="summary-cell">
                <div class="lbl">Total POs Issued</div>
                <div class="val val-primary">{{ number_format($summary['total_pos']) }}</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Total Procurement Spend</div>
                <div class="val val-primary">{{ $currencyIcon }}{{ number_format($summary['total_spend'], 2) }}</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Total Disbursed (Paid)</div>
                <div class="val val-success">{{ $currencyIcon }}{{ number_format($summary['total_paid'], 2) }}</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Total Outstanding (Due)</div>
                <div class="val val-danger">{{ $currencyIcon }}{{ number_format($summary['total_due'], 2) }}</div>
            </td>
        </tr>
    </table>

    <table class="summary-grid">
        <tr>
            <td class="summary-cell">
                <div class="lbl">Local Procurement Spend</div>
                <div class="val">{{ $currencyIcon }}{{ number_format($summary['local_spend'], 2) }}</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Import / Foreign Spend</div>
                <div class="val val-info">{{ $currencyIcon }}{{ number_format($summary['foreign_spend'], 2) }}</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Goods Received (Completed)</div>
                <div class="val val-success">{{ number_format($summary['goods_received_count']) }}</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">In-Transit / Pending Milestone</div>
                <div class="val val-danger">{{ number_format($summary['pending_milestone_count']) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Itemized Purchase Orders Registry ({{ $purchases->count() }} Records)</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 13%;">PO Number</th>
                <th style="width: 11%;">Invoice No</th>
                <th style="width: 20%;">Supplier / Vendor</th>
                <th style="width: 8%;" class="tc">Type</th>
                <th style="width: 11%;" class="tc">Milestone</th>
                <th style="width: 5%;" class="tc">Items</th>
                <th style="width: 11%;" class="tr">Total Spend</th>
                <th style="width: 11%;" class="tr">Due Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $p)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($p->date)->format('d-M-Y') }}</td>
                    <td class="font-bold">{{ $p->po_no ?: ('PO-' . $p->id) }}</td>
                    <td>{{ $p->invoice_no ?: '—' }}</td>
                    <td>{{ $p->vendor?->shop_name ?? $p->vendor?->name ?? 'N/A' }}</td>
                    <td class="tc">
                        <span class="badge badge-{{ $p->purchase_type == 'foreign' ? 'foreign' : 'local' }}">
                            {{ ucfirst($p->purchase_type ?? 'local') }}
                        </span>
                    </td>
                    <td class="tc">
                        <span class="badge badge-{{ $p->milestone_status == 'goods_received' ? 'received' : 'pending' }}">
                            {{ ucfirst(str_replace('_', ' ', $p->milestone_status ?? 'draft')) }}
                        </span>
                    </td>
                    <td class="tc">{{ $p->details->count() }}</td>
                    <td class="tr font-bold">{{ $currencyIcon }}{{ number_format($p->total_amount, 2) }}</td>
                    <td class="tr {{ $p->due_amount > 0 ? 'val-danger font-bold' : '' }}">
                        {{ $currencyIcon }}{{ number_format($p->due_amount, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="tc" style="padding: 20px;">No purchase order records match the specified filter criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #eef2f7; font-weight: bold;">
                <td colspan="7" class="tr">GRAND TOTALS:</td>
                <td class="tr val-primary">{{ $currencyIcon }}{{ number_format($summary['total_spend'], 2) }}</td>
                <td class="tr val-danger">{{ $currencyIcon }}{{ number_format($summary['total_due'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Confidential ERP System Report &bull; {{ $settings->site_name ?? 'B2B Viking' }} &bull; Page 1 of 1 &bull; Generated by {{ auth()->user()?->name ?? 'System Administrator' }}
    </div>

</body>
</html>
