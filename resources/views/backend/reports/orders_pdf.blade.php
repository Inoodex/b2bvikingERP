<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $settings->site_name ?? 'Inventory' }} — Order & Issue Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #222; line-height: 1.5; padding: 15px; }

        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #1a73e8; padding-bottom: 8px; }
        .header h1 { margin: 0; color: #1a73e8; font-size: 18px; }
        .header .sub { font-size: 13px; font-weight: bold; margin-top: 2px; }
        .header .info { font-size: 10px; color: #666; margin-top: 3px; }

        .profile-box { background: #f5f7fa; border: 1px solid #dde1e6; padding: 8px 12px; margin-bottom: 12px; border-radius: 4px; }
        .profile-box .name { font-size: 14px; font-weight: bold; color: #1a73e8; }
        .profile-box .details { font-size: 10px; color: #555; margin-top: 2px; }

        .summary-row { text-align: center; margin-bottom: 12px; }
        .summary-box { display: inline-block; width: 23%; padding: 8px 5px; margin: 0 4px; border: 1px solid #dde1e6; border-radius: 4px; background: #fafbfc; vertical-align: top; }
        .summary-box .lbl { font-size: 9px; color: #666; text-transform: uppercase; }
        .summary-box .val { font-size: 14px; font-weight: bold; color: #1a73e8; margin-top: 3px; }

        .section-title { font-size: 12px; font-weight: bold; margin: 10px 0 5px; padding: 4px 0; border-bottom: 1px solid #ccc; color: #333; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table th { background: #1a73e8; color: #fff; border: 1px solid #1a73e8; padding: 5px 6px; text-align: left; font-size: 11px; }
        table td { border: 1px solid #dde1e6; padding: 4px 6px; vertical-align: middle; font-size: 11px; }
        table tr:nth-child(even) td { background: #f8f9fa; }
        .tc { text-align: center; }
        .tr { text-align: right; }

        .badge { display: inline-block; padding: 2px 5px; font-size: 9px; font-weight: bold; border-radius: 3px; color: #fff; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; }
        .badge-primary { background-color: #007bff; }
        .badge-info { background-color: #17a2b8; }

        .footer { margin-top: 15px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 6px; }
    </style>
</head>

<body>

    <div class="header">
        <h1>{{ $settings->site_name ?? 'Inventory Management System' }}</h1>
        <div class="sub">Order & Issue Report</div>
        <div class="info">
            Generated: {{ date('d M Y, h:i A') }}
            @php $req = $request ?? []; @endphp
            @if (is_array($req) ? !empty($req['date_from']) : !empty($req->date_from))
                | From: {{ is_array($req) ? $req['date_from'] : $req->date_from }}
            @endif
            @if (is_array($req) ? !empty($req['date_to']) : !empty($req->date_to))
                | To: {{ is_array($req) ? $req['date_to'] : $req->date_to }}
            @endif
            @if (is_array($req) ? !empty($req['month']) : !empty($req->month))
                | Month: {{ date('F', mktime(0,0,0, is_array($req) ? $req['month'] : $req->month, 1)) }}
            @endif
            @if (is_array($req) ? !empty($req['year']) : !empty($req->year))
                | Year: {{ is_array($req) ? $req['year'] : $req->year }}
            @endif
        </div>
    </div>

    @isset($user)
        {{-- ═══════ 360° PER-USER PDF ═══════ --}}
        <div class="profile-box">
            <div class="name">{{ $user->name }} @if($user->outlet_name) ({{ $user->outlet_name }}) @endif</div>
            <div class="details">
                {{ $user->email ?? '—' }} | {{ $user->phone ?? '—' }} | {{ $user->address ?? '—' }}
                | Member since {{ $user->created_at->format('M Y') }}
                @if($user->discount_type) | Discount: {{ $user->discount_value }}{{ $user->discount_type === 'percent' ? '%' : ' Flat' }} @endif
            </div>
        </div>

        <div class="summary-row">
            <div class="summary-box"><div class="lbl">Completed Orders (Issued)</div><div class="val">{{ number_format($summary->total_orders) }}</div></div>
            <div class="summary-box"><div class="lbl">Actual Sales Value</div><div class="val" style="color:#28a745">{!! formatConverted($summary->total_value) !!}</div></div>
            <div class="summary-box"><div class="lbl">Total Paid</div><div class="val" style="color:#17a2b8">{!! formatConverted($paymentStats->total_paid) !!}</div></div>
            <div class="summary-box"><div class="lbl">{{ $totalDue > 0 ? 'Due' : 'Fully Paid' }}</div><div class="val mt-2" style="color:{{ $totalDue > 0 ? '#dc3545' : '#28a745' }}">{!! formatConverted($totalDue) !!}</div></div>
        </div>

        {{-- Orders --}}
        <div class="section-title">Orders ({{ $orders->count() }})</div>
        <table>
            <thead><tr>
                <th>#</th><th>Order No</th><th>Date</th><th class="tc">Items</th><th class="tc">Qty</th><th class="tr">Total</th><th class="tr">Paid</th><th class="tr">Due</th><th>Payment</th>
            </tr></thead>
            <tbody>
                @forelse($orders as $i => $o)
                    @php
                        $orderIssuedQty = \App\Models\IssueItem::whereHas('issue', fn($q) => $q->where('order_id', $o->id))->sum('quantity');
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $o->order_no }}</td>
                        <td>{{ $o->placed_at->format('d M Y') }}</td>
                        <td class="tc">{{ $o->items->count() }}</td>
                        <td class="tc">{{ $orderIssuedQty ? number_format($orderIssuedQty) : number_format($o->items->sum('quantity')) }}</td>
                        <td class="tr">{!! formatConverted($o->total_amount) !!}</td>
                        <td class="tr">{!! formatConverted($o->paid_amount) !!}</td>
                        <td class="tr">{!! formatConverted($o->due_amount) !!}</td>
                        <td>{{ ucfirst($o->payment_status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="tc" style="color:#999">No orders.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- Payments --}}
        <div class="section-title">Payments ({{ $payments->count() }})</div>
        <table>
            <thead><tr>
                <th>#</th><th>Date</th><th>Order</th><th>Method</th><th>Transaction ID</th><th class="tr">Amount</th><th>Note</th>
            </tr></thead>
            <tbody>
                @forelse($payments as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $p->created_at->format('d M Y') }}</td>
                        <td>{{ $p->order->order_no ?? '—' }}</td>
                        <td>{{ $p->payment_method ?? '—' }}</td>
                        <td>{{ $p->transaction_id ?? '—' }}</td>
                        <td class="tr">{!! formatConverted($p->amount) !!}</td>
                        <td>{{ $p->note ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="tc" style="color:#999">No payments.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- Monthly Trend --}}
        <div class="section-title">Monthly Trend</div>
        <table>
            <thead><tr>
                <th>Month</th><th class="tc">Orders</th><th class="tr">Order Value</th><th class="tc">Issue Qty</th><th class="tc">Products</th>
            </tr></thead>
            <tbody>
                @forelse($monthlyTrend as $t)
                    @php
                        $monthStart = $t->month;
                        $linkedQty = \App\Models\IssueItem::whereHas('issue', fn($q) => $q->whereIn('order_id', $orderIds ?? [])
                            ->whereYear('created_at', substr($monthStart, 0, 4))
                            ->whereMonth('created_at', substr($monthStart, 5, 2))
                        )->sum('quantity');
                        $standaloneQty = \App\Models\IssueItem::whereHas('issue', function($q) use ($monthStart) {
                            $q->whereNull('order_id')
                                ->whereYear('created_at', substr($monthStart, 0, 4))
                                ->whereMonth('created_at', substr($monthStart, 5, 2));
                        })->sum('quantity');
                        $monthIssueQty = (int) $linkedQty + (int) $standaloneQty;
                        $monthUniqueProducts = \App\Models\IssueItem::join('issues', 'issue_items.issue_id', '=', 'issues.id')
                            ->whereYear('issues.created_at', substr($monthStart, 0, 4))
                            ->whereMonth('issues.created_at', substr($monthStart, 5, 2))
                            ->distinct('issue_items.product_id')
                            ->count('issue_items.product_id');
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $t->month)->format('F Y') }}</td>
                        <td class="tc">{{ number_format($t->orders_count) }}</td>
                        <td class="tr">{!! formatConverted($t->total_amount) !!}</td>
                        <td class="tc">{{ number_format($monthIssueQty) }}</td>
                        <td class="tc">{{ number_format($monthUniqueProducts) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="tc" style="color:#999">No data.</td></tr>
                @endforelse
            </tbody>
        </table>

    @else
        {{-- ═══════ GLOBAL PDF ═══════ --}}
        <div class="summary-row">
            <div class="summary-box" style="width:48%"><div class="lbl">Completed Orders</div><div class="val">{{ number_format($summary->total_orders) }}</div></div>
            <div class="summary-box" style="width:48%"><div class="lbl">Total Amount</div><div class="val" style="color:#28a745">{!! formatConverted($totalRevenue) !!}</div></div>
        </div>

        <div class="section-title">Product Frequency</div>
        <table>
            <thead><tr>
                <th>#</th><th>Product Name</th><th class="tc">Times Ordered</th><th class="tc">Total Qty</th><th class="tr">Total Value</th>
            </tr></thead>
            <tbody>
                @forelse($productFrequency as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td class="tc">{{ number_format($item->times_ordered) }}</td>
                        <td class="tc">{{ number_format($item->total_qty) }}</td>
                        <td class="tr">{!! formatConverted($item->total_value) !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="tc" style="color:#999">No data.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Monthly Trend</div>
        <table>
            <thead><tr>
                <th>Month</th><th class="tc">Orders</th><th class="tr">Amount</th><th class="tc">Issue Qty</th><th class="tc">Products</th>
            </tr></thead>
            <tbody>
                @forelse($monthlyTrend as $t)
                    @php
                        $monthStart = $t->month;
                        $linkedQty = \App\Models\IssueItem::whereHas('issue', fn($q) => $q->whereIn('order_id', $orderIds ?? [])
                            ->whereYear('created_at', substr($monthStart, 0, 4))
                            ->whereMonth('created_at', substr($monthStart, 5, 2))
                        )->sum('quantity');
                        $standaloneQty = \App\Models\IssueItem::whereHas('issue', function($q) use ($monthStart) {
                            $q->whereNull('order_id')
                                ->whereYear('created_at', substr($monthStart, 0, 4))
                                ->whereMonth('created_at', substr($monthStart, 5, 2));
                        })->sum('quantity');
                        $monthIssueQty = (int) $linkedQty + (int) $standaloneQty;
                        $monthUniqueProducts = \App\Models\IssueItem::join('issues', 'issue_items.issue_id', '=', 'issues.id')
                            ->whereYear('issues.created_at', substr($monthStart, 0, 4))
                            ->whereMonth('issues.created_at', substr($monthStart, 5, 2))
                            ->distinct('issue_items.product_id')
                            ->count('issue_items.product_id');
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $t->month)->format('F Y') }}</td>
                        <td class="tc">{{ number_format($t->orders_count) }}</td>
                        <td class="tr">{!! formatConverted($t->total_amount) !!}</td>
                        <td class="tc">{{ number_format($monthIssueQty) }}</td>
                        <td class="tc">{{ number_format($monthUniqueProducts) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="tc" style="color:#999">No data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endisset

    <div class="footer">
        {{ $settings->site_name ?? 'Inventory Management System' }} | {{ date('Y') }}
    </div>

</body>
</html>

