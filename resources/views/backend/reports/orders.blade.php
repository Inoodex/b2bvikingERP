@extends('backend.layouts.master')

@section('title', isset($user) ? '360° Report — ' . $user->name : 'Order & Sales Report')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>@isset($user) <i class="fas fa-user-circle text-primary mr-1"></i> {{ $user->name }} @else Order & Sales Report @endisset</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item active">@isset($user) 360° Report @else Order & Issue @endisset</div>
            </div>
        </div>

        <div class="section-body">
            {{-- ─── PER-USER MODE: Profile Banner + 8 Cards + Tabs ─── --}}
            @isset($user)
                <div class="bg-white rounded shadow-sm p-3 mb-4 border d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="mb-1">
                            {{ $user->name }}
                            @if ($user->outlet_name)
                                <span class="badge badge-info">{{ $user->outlet_name }}</span>
                            @endif
                            <span class="badge badge-{{ $user->status ? 'success' : 'danger' }} ml-1">
                                {{ $user->status ? 'Active' : 'Inactive' }}
                            </span>
                        </h5>
                        <small class="text-muted">
                            <i class="fas fa-envelope"></i> {{ $user->email ?? '—' }}
                            &nbsp; <i class="fas fa-phone"></i> {{ $user->phone ?? '—' }}
                            &nbsp; <i class="fas fa-map-marker-alt"></i> {{ $user->address ?? '—' }}
                            &nbsp; <i class="fas fa-calendar-alt"></i> {{ $user->created_at->format('M Y') }}
                            @if ($user->discount_type)
                                &nbsp; | &nbsp; <span class="text-success"><i class="fas fa-tag"></i> {{ $user->discount_value }}{{ $user->discount_type === 'percent' ? '%' : ' Flat' }}</span>
                            @endif
                        </small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="fas fa-shopping-cart"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Completed Orders (Issued)</h4></div>
                                <div class="card-body">{{ number_format($summary->total_orders) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-dollar-sign"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Actual Sales Value</h4></div>
                                <div class="card-body">{!! formatConverted($summary->total_value) !!}</div>
                            </div>
                        </div>
                    </div>
                    {{-- ISSUES COMMENTED OUT --}}{{-- 
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning"><i class="fas fa-truck-loading"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Issues Created</h4></div>
                                <div class="card-body">{{ number_format($issueStats->total_issues) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-secondary"><i class="fas fa-boxes"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Total Issued Qty</h4></div>
                                <div class="card-body">{{ number_format($issueStats->total_issued_qty) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-dark"><i class="fas fa-file-invoice-dollar"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Actual Issue Value</h4></div>
                                <div class="card-body">{!! formatConverted($issueValue) !!}</div>
                            </div>
                        </div>
                    </div>
                    --}}{{-- END ISSUES COMMENTED OUT --}}
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary"><i class="fas fa-hand-holding-usd"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Total Paid</h4></div>
                                <div class="card-body">{!! formatConverted($paymentStats->total_paid) !!}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-{{ $totalDue > 0 ? 'danger' : 'success' }}"><i class="fas fa-{{ $totalDue > 0 ? 'exclamation-circle' : 'check-circle' }}"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>{{ $totalDue > 0 ? 'Due' : 'Fully Paid' }}</h4></div>
                                <div class="card-body">{!! formatConverted($totalDue) !!}</div>
                            </div>
                        </div>
                    </div>
                    {{-- PENDING VALUE (commented out — unit_price backfill needed for old issues) --}}
                    {{-- <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-secondary"><i class="fas fa-hourglass-half"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Pending Value</h4></div>
                                <div class="card-body">{!! formatConverted($pendingValue) !!}</div>
                            </div>
                        </div>
                    </div> --}}
                </div>

                {{-- ─── PER-USER: Filter ───────────────────────── --}}
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-filter"></i> Filter</h4>
                        <div class="card-header-action">
                            <form method="GET" action="{{ route('admin.reports.orders.pdf.async') }}" style="display:inline">
                                @if(request('user_id'))
                                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                                @endif
                                @if(request('month'))
                                    <input type="hidden" name="month" value="{{ request('month') }}">
                                @endif
                                @if(request('year'))
                                    <input type="hidden" name="year" value="{{ request('year') }}">
                                @endif
                                @if(request('date_from'))
                                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                                @endif
                                @if(request('date_to'))
                                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                                @endif
                                <button type="submit" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Download PDF</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filter-form-user">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>User / Outlet</label>
                                    <select name="user_id" class="form-control select2">
                                        <option value="">All Users</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                                                {{ $u->name }} {{ $u->outlet_name ? '(' . $u->outlet_name . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Month</label>
                                    <select name="month" class="form-control auto-submit">
                                        <option value="">All</option>
                                        @foreach(range(1,12) as $m)
                                            <option value="{{ $m }}" {{ (string) request('month') === (string) $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Year</label>
                                    <select name="year" class="form-control auto-submit">
                                        <option value="">All</option>
                                        @foreach(range(date('Y'), date('Y')-5) as $y)
                                            <option value="{{ $y }}" {{ (string) request('year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Date From</label>
                                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control auto-submit">
                                </div>
                                <div class="col-md-2">
                                    <label>Date To</label>
                                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control auto-submit">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i></button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <a href="{{ route('admin.reports.orders') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo"></i> Reset</a>
                                    <small class="text-muted ml-2">Select a user and filter to see their 360° detailed report.</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="orders-tab" data-toggle="tab" href="#ordersTab">
                                    <i class="fas fa-shopping-cart"></i> Orders <span class="badge badge-primary">{{ $orders->count() }}</span>
                                </a>
                            </li>
                            {{-- <li class="nav-item">
                                <a class="nav-link" id="issues-tab" data-toggle="tab" href="#issuesTab">
                                    <i class="fas fa-truck-loading"></i> Issues <span class="badge badge-warning">{{ $issues->count() }}</span>
                                </a>
                            </li> --}}
                            <li class="nav-item">
                                <a class="nav-link" id="payments-tab" data-toggle="tab" href="#paymentsTab">
                                    <i class="fas fa-credit-card"></i> Payments <span class="badge badge-info">{{ $payments->count() }}</span>
                                </a>
                            </li>
                            {{-- <li class="nav-item">
                                <a class="nav-link" id="products-tab" data-toggle="tab" href="#productsTab">
                                    <i class="fas fa-box"></i> Product Comparison
                                </a>
                            </li> --}}
                            <li class="nav-item">
                                <a class="nav-link" id="monthly-tab" data-toggle="tab" href="#monthlyTab">
                                    <i class="fas fa-calendar-alt"></i> Monthly Trend
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="reportTabsContent">
                    <div class="tab-pane fade show active" id="ordersTab">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead><tr>
                                            <th>#</th><th>Order No</th><th>Date</th>
                                            <th class="text-center">Items</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-right">Paid</th>
                                            <th class="text-right">Due</th>
                                            <th>Payment</th>
                                        </tr></thead>
                                        <tbody>
                                            @forelse($orders as $i => $order)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td><a href="{{ route('admin.orders.show', $order->id) }}" target="_blank">{{ $order->order_no }}</a></td>
                                                    <td>{{ $order->placed_at->format('d M Y') }}</td>
                                                    <td class="text-center"><a href="{{ route('admin.orders.show', $order->id) }}" target="_blank" class="badge badge-info">{{ $order->items->count() }}</a></td>
                                                    <td class="text-center">{{ number_format($order->items->sum('quantity')) }}</td>
                                                    <td class="text-right">{!! formatConverted($order->total_amount) !!}</td>
                                                    <td class="text-right">{!! formatConverted($order->paid_amount) !!}</td>
                                                    <td class="text-right">{!! formatConverted($order->due_amount) !!}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($order->payment_status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="9" class="text-center text-muted py-4">No orders.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="tab-pane fade" id="issuesTab">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead><tr>
                                            <th>#</th><th>Issue No</th><th>Date</th>
                                            <th>Linked Order</th>
                                            <th class="text-center">Items</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right">Value</th>
                                        </tr></thead>
                                        <tbody>
                                            @forelse($issues as $i => $issue)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $issue->issue_no }}</td>
                                                    <td>{{ $issue->created_at->format('d M Y') }}</td>
                                                    <td>@if($issue->order_id)<a href="{{ route('admin.orders.show', $issue->order_id) }}" target="_blank">{{ optional($issue->order)->order_no ?? '—' }}</a>@else{{ optional($issue->order)->order_no ?? '—' }}@endif</td>
                                                    <td class="text-center">@if($issue->order_id)<a href="{{ route('admin.orders.show', $issue->order_id) }}" target="_blank">{{ $issue->items->count() }}</a>@else{{ $issue->items->count() }}@endif</td>
                                                    <td class="text-center">{{ number_format($issue->total_qty) }}</td>
                                                    <td class="text-right">{!! formatConverted($issue->computed_value) !!}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted py-4">No issues.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <div class="tab-pane fade" id="paymentsTab">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead><tr>
                                            <th>#</th><th>Date</th><th>Order</th>
                                            <th>Method</th><th>Transaction ID</th>
                                            <th class="text-right">Amount</th><th>Note</th>
                                        </tr></thead>
                                        <tbody>
                                            @forelse($payments as $i => $payment)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $payment->created_at->format('d M Y') }}</td>
                                                    <td>@if($payment->order_id)<a href="{{ route('admin.orders.show', $payment->order_id) }}" target="_blank">{{ $payment->order->order_no ?? '—' }}</a>@else{{ $payment->order->order_no ?? '—' }}@endif</td>
                                                    <td>{{ $payment->payment_method ?? '—' }}</td>
                                                    <td>{{ $payment->transaction_id ?? '—' }}</td>
                                                    <td class="text-right">{!! formatConverted($payment->amount) !!}</td>
                                                    <td>{{ $payment->note ?? '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted py-4">No payments.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="tab-pane fade" id="productsTab">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead><tr>
                                            <th>#</th><th>Product</th>
                                            <th class="text-center">Ordered Qty</th>
                                            <th class="text-right">Ordered Value</th>
                                            <th class="text-center">Issued Qty</th>
                                            <th class="text-center">Pending</th>
                                            <th>Fulfillment</th>
                                        </tr></thead>
                                        <tbody>
                                            @forelse($productComparison as $i => $pc)
                                                @php $pct = $pc->ordered_qty > 0 ? round(($pc->issued_qty / $pc->ordered_qty) * 100) : 0; @endphp
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $pc->product_name }}</td>
                                                    <td class="text-center">{{ number_format($pc->ordered_qty) }}</td>
                                                    <td class="text-right">{!! formatConverted($pc->ordered_value) !!}</td>
                                                    <td class="text-center">{{ number_format($pc->issued_qty) }}</td>
                                                    <td class="text-center">
                                                        @if($pc->pending_qty > 0)
                                                            <span class="text-danger font-weight-bold">{{ number_format($pc->pending_qty) }}</span>
                                                        @else
                                                            <span class="text-success">0</span>
                                                        @endif
                                                    </td>
                                                    <td style="min-width:120px">
                                                        <div class="progress" style="height:18px;background:#e9ecef">
                                                            <div class="progress-bar bg-{{ $pct >= 100 ? 'success' : 'warning' }}" style="width:{{ $pct }}%">
                                                                {{ $pct }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted py-4">No products.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <div class="tab-pane fade" id="monthlyTab">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead><tr>
                                            <th>Month</th>
                                            <th class="text-center">Orders</th>
                                            <th class="text-right">Order Value</th>
                                            <th class="text-center">Issue Qty</th>
                                            <th class="text-center">Products</th>
                                        </tr></thead>
                                        <tbody>
                                            @forelse($monthlyTrend as $trend)
                                                @php
                                                    $monthStart = $trend->month;
                                                    $monthIssueQty = \App\Models\OrderItem::whereHas('order', fn($q) => $q->whereIn('id', $orderIds)
                                                        ->where('status', 'completed')
                                                        ->whereYear('placed_at', substr($monthStart, 0, 4))
                                                        ->whereMonth('placed_at', substr($monthStart, 5, 2))
                                                    )->sum('quantity');
                                                    $monthUniqueProducts = \App\Models\OrderItem::whereHas('order', fn($q) => $q->whereIn('id', $orderIds)
                                                        ->where('status', 'completed')
                                                        ->whereYear('placed_at', substr($monthStart, 0, 4))
                                                        ->whereMonth('placed_at', substr($monthStart, 5, 2))
                                                    )->distinct('product_id')->count('product_id');
                                                @endphp
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $trend->month)->format('F Y') }}</td>
                                                    <td class="text-center"><span class="badge badge-primary">{{ number_format($trend->orders_count) }}</span></td>
                                                    <td class="text-right">{!! formatConverted($trend->total_amount) !!}</td>
                                                    <td class="text-center">{{ number_format($monthIssueQty) }}</td>
                                                    <td class="text-center">{{ number_format($monthUniqueProducts) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted py-4">No data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            {{-- ─── GLOBAL MODE: 2 Summary Cards + 3 Tabs ─── --}}
            @else
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="fas fa-shopping-cart"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Completed Orders</h4></div>
                                <div class="card-body">{{ number_format($summary->total_orders) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-dollar-sign"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Total Amount</h4></div>
                                <div class="card-body">{!! formatConverted($totalRevenue) !!}</div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-dollar-sign"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Total Issue Value</h4></div>
                                <div class="card-body">{!! formatConverted($issueValue) !!}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning"><i class="fas fa-truck-loading"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Issues Created</h4></div>
                                <div class="card-body">{{ number_format($issueStats->total_issues) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="fas fa-boxes"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Total Issued Qty</h4></div>
                                <div class="card-body">{{ number_format($issueStats->total_issued_qty) }}</div>
                            </div>
                        </div>
                    </div> --}}
                </div>

                {{-- ─── GLOBAL: Filter ────────────────────────── --}}
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-filter"></i> Filter</h4>
                        <div class="card-header-action">
                            <form method="GET" action="{{ route('admin.reports.orders.pdf.async') }}" style="display:inline">
                                @if(request('user_id'))
                                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                                @endif
                                @if(request('month'))
                                    <input type="hidden" name="month" value="{{ request('month') }}">
                                @endif
                                @if(request('year'))
                                    <input type="hidden" name="year" value="{{ request('year') }}">
                                @endif
                                @if(request('date_from'))
                                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                                @endif
                                @if(request('date_to'))
                                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                                @endif
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> Download PDF</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filter-form-global">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>User / Outlet</label>
                                    <select name="user_id" class="form-control select2">
                                        <option value="">All Users</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                                                {{ $u->name }} {{ $u->outlet_name ? '(' . $u->outlet_name . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Month</label>
                                    <select name="month" class="form-control auto-submit">
                                        <option value="">All</option>
                                        @foreach(range(1,12) as $m)
                                            <option value="{{ $m }}" {{ (string) request('month') === (string) $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Year</label>
                                    <select name="year" class="form-control auto-submit">
                                        <option value="">All</option>
                                        @foreach(range(date('Y'), date('Y')-5) as $y)
                                            <option value="{{ $y }}" {{ (string) request('year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Date From</label>
                                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control auto-submit">
                                </div>
                                <div class="col-md-2">
                                    <label>Date To</label>
                                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control auto-submit">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i></button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <a href="{{ route('admin.reports.orders') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo"></i> Reset</a>
                                    <small class="text-muted ml-2">Select a user and filter to see their 360° detailed report.</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="products-tab" data-toggle="tab" href="#products">
                                    <i class="fas fa-box"></i> Product Frequency
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="monthly-tab" data-toggle="tab" href="#monthly">
                                    <i class="fas fa-calendar-alt"></i> Monthly Trend
                                </a>
                            </li>
                            {{-- <li class="nav-item">
                                <a class="nav-link" id="users-tab" data-toggle="tab" href="#users">
                                    <i class="fas fa-users"></i> User Summary
                                </a>
                            </li> --}}
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="reportTabsContent">
                    <div class="tab-pane fade show active" id="products">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead><tr>
                                            <th>#</th><th>Product Name</th>
                                            <th class="text-center">Times Ordered</th>
                                            <th class="text-center">Total Qty</th>
                                            <th class="text-right">Total Value</th>
                                            {{-- <th class="text-center">Times Issued</th> --}}
                                        </tr></thead>
                                        <tbody>
                                            @forelse($productFrequency as $index => $item)
                                                <tr>
                                                    <td>{{ $productFrequency->firstItem() + $index }}</td>
                                                    <td>{{ $item->product_name }}</td>
                                                    <td class="text-center"><span class="badge badge-primary">{{ number_format($item->times_ordered) }}</span></td>
                                                    <td class="text-center">{{ number_format($item->total_qty) }}</td>
                                                    <td class="text-right">{!! formatConverted($item->total_value) !!}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted py-4">No data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3">{{ $productFrequency->links() }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="monthly">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead><tr>
                                            <th>Month</th>
                                            <th class="text-center">Orders</th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-center">Issue Qty</th>
                                            <th class="text-center">Products</th>
                                        </tr></thead>
                                        <tbody>
                                            @forelse($monthlyTrend as $trend)
                                                @php
                                                    $monthStart = $trend->month;
                                                    $monthIssueQty = \App\Models\OrderItem::whereHas('order', fn($q) => $q->whereIn('id', $orderIds)
                                                        ->where('status', 'completed')
                                                        ->whereYear('placed_at', substr($monthStart, 0, 4))
                                                        ->whereMonth('placed_at', substr($monthStart, 5, 2))
                                                    )->sum('quantity');
                                                    $monthUniqueProducts = \App\Models\OrderItem::whereHas('order', fn($q) => $q->whereIn('id', $orderIds)
                                                        ->where('status', 'completed')
                                                        ->whereYear('placed_at', substr($monthStart, 0, 4))
                                                        ->whereMonth('placed_at', substr($monthStart, 5, 2))
                                                    )->distinct('product_id')->count('product_id');
                                                @endphp
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $trend->month)->format('F Y') }}</td>
                                                    <td class="text-center"><span class="badge badge-primary">{{ number_format($trend->orders_count) }}</span></td>
                                                    <td class="text-right">{!! formatConverted($trend->total_amount) !!}</td>
                                                    <td class="text-center">{{ number_format($monthIssueQty) }}</td>
                                                    <td class="text-center">{{ number_format($monthUniqueProducts) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted py-4">No data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="tab-pane fade" id="users">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead><tr>
                                            <th>#</th><th>User / Outlet</th>
                                            <th class="text-center">Issues</th>
                                            <th class="text-right">Issue Value</th>
                                            <th class="text-center">Issue Qty</th>
                                        </tr></thead>
                                        <tbody>
                                            @forelse($userSummary as $userId => $usr)
                                                @php
                                                    $userName = optional(\App\Models\User::find($userId))->name ?? 'User #'.$userId;
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td><a href="{{ route('admin.reports.orders', ['user_id' => $userId]) }}">{{ $userName }}</a></td>
                                                    <td class="text-center"><span class="badge badge-primary">{{ number_format($usr->total_orders) }}</span></td>
                                                    <td class="text-right">{!! formatConverted($usr->total_value) !!}</td>
                                                    <td class="text-center">{{ number_format($usr->total_qty ?? 0) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted py-4">No data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>

            @endisset
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $('.select2').select2({ width: '100%', allowClear: true, placeholder: function() { return $(this).data('placeholder') || 'Select Option'; } });

        $('.auto-submit').on('change', function() {
            $(this).closest('form').submit();
        });
    </script>
@endpush
