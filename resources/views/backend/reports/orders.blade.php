@extends('backend.layouts.master')

@section('title', isset($user) ? '360° Report — ' . $user->name : 'Order & Sales Report')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>
                @isset($user)
                    <i class="fas fa-user-circle text-primary mr-1"></i> {{ $user->name }}
                @else
                    Order & Sales Report
                @endisset
            </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item active">
                    @isset($user)
                        360° Report
                    @else
                        Order & Issue
                    @endisset
                </div>
            </div>
        </div>

        <div class="section-body">
            {{-- ─── PER-USER MODE: Profile Banner + 8 Cards + Tabs ─── --}}
            @isset($user)
                <div
                    class="bg-white rounded shadow-sm p-3 mb-4 border d-flex justify-content-between align-items-center flex-wrap">
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
                                &nbsp; | &nbsp; <span class="text-success"><i class="fas fa-tag"></i>
                                    {{ $user->discount_value }}{{ $user->discount_type === 'percent' ? '%' : ' Flat' }}</span>
                            @endif
                        </small>
                    </div>
                </div>

                <div class="row mb-4">
                    {{-- ─── LEFT HALF: 4 Summary KPI Cards (2x2 Grid) ─── --}}
                    <div class="col-lg-6 col-12">
                        <div class="row">
                            <div class="col-sm-6 col-12">
                                <div class="card card-statistic-1 mb-3 shadow-sm border-0" style="border-radius: 12px;">
                                    <div class="card-icon bg-info"><i class="fas fa-shopping-cart"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Completed Orders</h4>
                                        </div>
                                        <div class="card-body">{{ number_format($summary->total_orders) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="card card-statistic-1 mb-3 shadow-sm border-0" style="border-radius: 12px;">
                                    <div class="card-icon bg-success"><i class="fas fa-dollar-sign"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Actual Sales Value</h4>
                                        </div>
                                        <div class="card-body">{!! formatConverted($summary->total_value) !!}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="card card-statistic-1 mb-3 shadow-sm border-0" style="border-radius: 12px;">
                                    <div class="card-icon bg-primary"><i class="fas fa-hand-holding-usd"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Total Paid</h4>
                                        </div>
                                        <div class="card-body">{!! formatConverted($paymentStats->total_paid) !!}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="card card-statistic-1 mb-3 shadow-sm border-0" style="border-radius: 12px;">
                                    <div class="card-icon bg-{{ $totalDue > 0 ? 'danger' : 'success' }}"><i
                                            class="fas fa-{{ $totalDue > 0 ? 'exclamation-circle' : 'check-circle' }}"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>{{ $totalDue > 0 ? 'Outstanding Due' : 'Fully Paid' }}</h4>
                                        </div>
                                        <div class="card-body">{!! formatConverted($totalDue) !!}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ─── RIGHT HALF: Category Sales Breakdown Chart (Excludes 0-order categories) ─── --}}
                    <div class="col-lg-6 col-12">
                        <div class="card shadow-sm border-0 mb-3 h-100" style="border-radius: 12px;">
                            <div
                                class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                                <h6 class="mb-0 font-weight-bold text-dark d-flex align-items-center"
                                    style="font-size: 13.5px;">
                                    <i class="fas fa-chart-pie text-primary mr-2"></i>
                                    {{ $user->outlet_name ?: $user->name }}'s Category Breakdown
                                </h6>
                                <span class="badge badge-light text-muted font-weight-normal border" style="font-size: 11px;">
                                    {{ $userCategories->count() }} Categories Ordered
                                </span>
                            </div>
                            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-center">
                                @if ($userCategories->isNotEmpty())
                                    <div class="w-100 row align-items-center">
                                        <div class="col-sm-6 col-12 text-center my-1"
                                            style="position: relative; height: 165px;">
                                            <canvas id="userCategoryDoughnutChart"></canvas>
                                        </div>
                                        <div class="col-sm-6 col-12 my-1" style="max-height: 175px; overflow-y: auto;">
                                            <ul class="list-unstyled mb-0" style="font-size: 11.5px;">
                                                @php
                                                    $chartTotal = $userCategories->sum('total_amount') ?: 1;
                                                    $palette = [
                                                        '#4f46e5',
                                                        '#06b6d4',
                                                        '#10b981',
                                                        '#f59e0b',
                                                        '#ec4899',
                                                        '#8b5cf6',
                                                        '#3b82f6',
                                                        '#14b8a6',
                                                        '#f97316',
                                                        '#64748b',
                                                        '#0284c7',
                                                        '#84cc16',
                                                    ];
                                                @endphp
                                                @foreach ($userCategories->take(6) as $idx => $ucat)
                                                    @php
                                                        $pct = round(($ucat->total_amount / $chartTotal) * 100, 1);
                                                        $color = $palette[$idx % count($palette)];
                                                    @endphp
                                                    <li class="d-flex justify-content-between align-items-center py-1 border-bottom"
                                                        style="border-bottom-style: dashed !important;">
                                                        <div class="text-truncate mr-2" style="max-width: 125px;"
                                                            title="{{ $ucat->category_name }}">
                                                            <span class="d-inline-block mr-1 rounded-circle"
                                                                style="width: 8px; height: 8px; background-color: {{ $color }};"></span>
                                                            <span
                                                                class="font-weight-600 text-dark">{{ $ucat->category_name }}</span>
                                                        </div>
                                                        <div class="text-right">
                                                            <span
                                                                class="font-weight-bold text-dark">{{ $pct }}%</span>
                                                            <small class="text-muted d-block"
                                                                style="font-size: 9.5px;">{{ number_format($ucat->total_qty) }}
                                                                pcs</small>
                                                        </div>
                                                    </li>
                                                @endforeach
                                                @if ($userCategories->count() > 6)
                                                    <li class="text-center pt-1 text-muted" style="font-size: 10.5px;">
                                                        +{{ $userCategories->count() - 6 }} more categories below
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-chart-pie fa-2x mb-2 text-muted" style="opacity: 0.4;"></i>
                                        <p class="mb-0 small">No category sales recorded for this customer in selected period.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── PER-USER: Filter ───────────────────────── --}}
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header">
                        <h4><i class="fas fa-filter text-primary mr-1"></i> Filter Options</h4>
                        <div class="card-header-action">
                            @if (isset($latestPdf) && $latestPdf)
                                <a href="{{ $latestPdf['url'] }}" id="btn-download-pdf-user"
                                    class="btn btn-success btn-sm font-weight-bold mr-2 btn-download-pdf"
                                    title="{{ $latestPdf['filename'] }}">
                                    <i class="fas fa-file-download mr-1"></i> Download PDF (Ready: {{ $latestPdf['time'] }})
                                </a>
                            @else
                                <a href="#" id="btn-download-pdf-user"
                                    class="btn btn-success btn-sm font-weight-bold mr-2 btn-download-pdf"
                                    style="display: none;">
                                    <i class="fas fa-file-download mr-1"></i> Download PDF
                                </a>
                            @endif
                            <button type="button" id="btn-generate-pdf-user" data-type="order_sales_report"
                                data-url="{{ route('admin.reports.orders.pdf') }}"
                                class="btn btn-danger btn-sm font-weight-bold btn-generate-pdf">
                                <i class="fas fa-file-pdf mr-1"></i> Generate PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filter-form-user">
                            {{-- ─── Row 1: Target Entity Filters (User & Category) ─── --}}
                            <div class="row">
                                <div class="col-md-4 col-12">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark small"><i
                                                class="fas fa-store text-primary mr-1"></i> User / Outlet</label>
                                        <select name="user_id" class="form-control select2">
                                            <option value="">All Users</option>
                                            @foreach ($users as $u)
                                                <option value="{{ $u->id }}"
                                                    {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }}
                                                    {{ $u->outlet_name ? '(' . $u->outlet_name . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark small"><i
                                                class="fas fa-tags text-success mr-1"></i> Category (Ordered by this
                                            User)</label>
                                        <select name="category_name" class="form-control select2 auto-submit">
                                            <option value="">All Ordered Categories ({{ $userCategories->count() }})
                                            </option>
                                            @foreach ($userCategories as $uc)
                                                <option value="{{ $uc->category_name }}"
                                                    {{ request('category_name') === $uc->category_name ? 'selected' : '' }}>
                                                    {{ $uc->category_name }} ({{ number_format($uc->total_qty) }} pcs)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-3 col-sm-6 col-12">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold text-dark small"><i
                                                class="far fa-calendar-alt text-info mr-1"></i> Month</label>
                                        <select name="month" class="form-control auto-submit">
                                            <option value="">All Months</option>
                                            @foreach (range(1, 12) as $m)
                                                <option value="{{ $m }}"
                                                    {{ (string) request('month') === (string) $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- ─── Row 2: Date & Period Filters + Action Buttons ─── --}}
                            <div class="row align-items-end">

                                <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold text-dark small"><i
                                                class="far fa-calendar-check text-info mr-1"></i> Year</label>
                                        <select name="year" class="form-control auto-submit">
                                            <option value="">All Years</option>
                                            @foreach (range(date('Y'), date('Y') - 5) as $y)
                                                <option value="{{ $y }}"
                                                    {{ (string) request('year') === (string) $y ? 'selected' : '' }}>
                                                    {{ $y }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold text-dark small">Date From</label>
                                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                                            class="form-control auto-submit">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold text-dark small">Date To</label>
                                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                                            class="form-control auto-submit">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-12 col-12">
                                    <div class="form-group mb-2 d-flex align-items-center" style="gap: 8px;">
                                        <button type="submit" class="btn btn-primary flex-grow-1 font-weight-bold"
                                            style="min-height: 42px;">
                                            <i class="fas fa-filter mr-1"></i> Apply Filter
                                        </button>
                                        <a href="{{ route('admin.reports.orders', ['user_id' => request('user_id')]) }}"
                                            class="btn btn-outline-secondary"
                                            style="min-height: 42px; display: inline-flex; align-items: center;"
                                            title="Reset Filters">
                                            <i class="fas fa-redo mr-1"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-1">
                                <div class="col-12 d-flex align-items-center flex-wrap" style="gap: 8px;">
                                    <small class="text-muted">
                                        Filtered by: <strong>{{ $user->name }}
                                            {{ $user->outlet_name ? '(' . $user->outlet_name . ')' : '' }}</strong>
                                        @if (request('category_name'))
                                            &nbsp;|&nbsp; Category: <span
                                                class="badge badge-primary px-2">{{ request('category_name') }}</span>
                                        @endif
                                        &nbsp;•&nbsp; <em>Categories without orders are automatically hidden.</em>
                                    </small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @php
                    $activeTab = 'orders';
                    if (request()->has('products_page') || request('tab') === 'products') {
                        $activeTab = 'products';
                    } elseif (request()->has('payments_page') || request('tab') === 'payments') {
                        $activeTab = 'payments';
                    } elseif (request()->has('monthly_page') || request('tab') === 'monthly') {
                        $activeTab = 'monthly';
                    }
                @endphp

                <div class="card">
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'orders' ? 'active' : '' }}" id="orders-tab"
                                    data-toggle="tab" href="#ordersTab">
                                    <i class="fas fa-shopping-cart"></i> Orders <span
                                        class="badge badge-primary">{{ $orders->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'products' ? 'active' : '' }}" id="products-tab"
                                    data-toggle="tab" href="#productsTab">
                                    <i class="fas fa-boxes text-success"></i> Outlet Demanded Products <span
                                        class="badge badge-success">{{ number_format($demandedSummary->total_items ?? $productComparison->total()) }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'payments' ? 'active' : '' }}" id="payments-tab"
                                    data-toggle="tab" href="#paymentsTab">
                                    <i class="fas fa-credit-card"></i> Payments <span
                                        class="badge badge-info">{{ $payments->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'monthly' ? 'active' : '' }}" id="monthly-tab"
                                    data-toggle="tab" href="#monthlyTab">
                                    <i class="fas fa-calendar-alt"></i> Monthly Trend <span
                                        class="badge badge-secondary">{{ $monthlyTrend->total() }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="reportTabsContent">
                    {{-- ─── TAB 1: ORDERS (Full Width) ───────────────────────── --}}
                    <div class="tab-pane fade {{ $activeTab === 'orders' ? 'show active' : '' }}" id="ordersTab">
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-md mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Order No</th>
                                                <th>Date</th>
                                                <th class="text-center">Items</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-right">Total</th>
                                                <th class="text-right">Paid</th>
                                                <th class="text-right">Due</th>
                                                <th>Payment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($orders as $i => $order)
                                                <tr>
                                                    <td>{{ $orders->firstItem() + $i }}</td>
                                                    <td><a href="{{ route('admin.orders.show', $order->id) }}"
                                                            target="_blank"
                                                            class="font-weight-600 text-primary">{{ $order->order_no }}</a>
                                                    </td>
                                                    <td>{{ $order->placed_at ? $order->placed_at->format('d M Y') : '—' }}</td>
                                                    <td class="text-center"><a
                                                            href="{{ route('admin.orders.show', $order->id) }}"
                                                            target="_blank"
                                                            class="badge badge-info">{{ $order->items->count() }}</a></td>
                                                    <td class="text-center">
                                                        {{ number_format($order->items->sum('quantity')) }}</td>
                                                    <td class="text-right font-weight-bold">{!! formatConverted($order->total_amount) !!}</td>
                                                    <td class="text-right text-success">{!! formatConverted($order->paid_amount) !!}</td>
                                                    <td
                                                        class="text-right {{ $order->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                                        {!! formatConverted($order->due_amount) !!}</td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $order->payment_status === 'paid' ? 'badge-success' : ($order->payment_status === 'partial' ? 'badge-success' : 'badge-danger') }}">
                                                            {{ ucfirst($order->payment_status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-4">No orders found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top flex-wrap"
                                style="gap: 12px;">
                                <div class="text-muted small">
                                    Showing {{ $orders->firstItem() ?? ($orders->total() > 0 ? 1 : 0) }} to
                                    {{ $orders->lastItem() ?? $orders->total() }} of {{ number_format($orders->total()) }}
                                    entries
                                </div>
                                <div>
                                    {{ $orders->appends(['tab' => 'orders'])->links('vendor.pagination.custom-report') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ─── TAB 2: OUTLET DEMANDED PRODUCTS (Paginated, Demanded First) ─── --}}
                    <div class="tab-pane fade {{ $activeTab === 'products' ? 'show active' : '' }}" id="productsTab">
                        {{-- Outlet Product Demand Summary Cards --}}
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-6 col-12">
                                <div class="card card-statistic-1 mb-0 shadow-sm border">
                                    <div class="card-icon bg-primary"><i class="fas fa-boxes"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Total Unique Demanded Items</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ number_format($demandedSummary->total_items ?? $productComparison->total()) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-12">
                                <div class="card card-statistic-1 mb-0 shadow-sm border">
                                    <div class="card-icon bg-info"><i class="fas fa-cubes"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Total Units Demanded</h4>
                                        </div>
                                        <div class="card-body">{{ number_format($demandedSummary->total_qty ?? 0) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-12">
                                <div class="card card-statistic-1 mb-0 shadow-sm border">
                                    <div class="card-icon bg-success"><i class="fas fa-money-bill-wave"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Total Value of Demands</h4>
                                        </div>
                                        <div class="card-body">{!! formatConverted($demandedSummary->total_value ?? 0) !!}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap"
                                style="gap: 12px;">
                                <div>
                                    <h5 class="mb-0 text-dark font-weight-bold">
                                        <i class="fas fa-box-open text-primary mr-1"></i> Outlet Demanded Products (Ranked by
                                        Quantity)
                                    </h5>
                                    <small class="text-muted">
                                        Showing {{ $productComparison->firstItem() ?? 0 }} to
                                        {{ $productComparison->lastItem() ?? 0 }} of
                                        {{ number_format($productComparison->total()) }} items ordered by {{ $user->name }}
                                        {{ $user->outlet_name ? '(' . $user->outlet_name . ')' : '' }}
                                        @if (request('category_name'))
                                            <span class="badge badge-primary ml-1">Filtered Category:
                                                {{ request('category_name') }}</span>
                                            <a href="{{ request()->fullUrlWithQuery(['category_name' => null, 'products_page' => 1, 'tab' => 'products']) }}"
                                                class="text-danger ml-1 font-weight-bold" title="Clear category filter">✕
                                                Clear</a>
                                        @endif
                                    </small>
                                </div>
                                @if (isset($userCategories) && $userCategories->isNotEmpty())
                                    <div class="d-flex align-items-center">
                                        <label class="mr-2 mb-0 small font-weight-bold text-muted d-none d-sm-inline">Quick
                                            Category:</label>
                                        <select class="form-control form-control-sm select2"
                                            onchange="window.location.href=this.value" style="min-width: 220px;">
                                            <option
                                                value="{{ request()->fullUrlWithQuery(['category_name' => null, 'products_page' => 1, 'tab' => 'products']) }}">
                                                All Ordered Categories ({{ $userCategories->count() }})</option>
                                            @foreach ($userCategories as $uc)
                                                <option
                                                    value="{{ request()->fullUrlWithQuery(['category_name' => $uc->category_name, 'products_page' => 1, 'tab' => 'products']) }}"
                                                    {{ request('category_name') === $uc->category_name ? 'selected' : '' }}>
                                                    {{ $uc->category_name }} ({{ number_format($uc->total_qty) }} pcs)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-md mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 70px;" class="text-center">Rank</th>
                                                <th>Product Details</th>
                                                <th>Category</th>
                                                <th class="text-center">Orders Count</th>
                                                <th class="text-center">Demanded Qty</th>
                                                <th class="text-right">Total Value</th>
                                                <th class="text-right">Avg Unit Price</th>
                                                <th class="text-center">Warehouse Stock</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($productComparison as $i => $pc)
                                                @php
                                                    $globalRank = ($productComparison->firstItem() ?? 1) + $i;
                                                @endphp
                                                <tr>
                                                    <td class="align-middle text-center">
                                                        @if ($globalRank === 1)
                                                            <span class="badge badge-warning font-weight-bold"
                                                                title="Rank 1 Demand" style="font-size: 13px;">
                                                                <i class="fas fa-crown"></i> #1
                                                            </span>
                                                        @elseif($globalRank === 2)
                                                            <span class="badge badge-secondary font-weight-bold"
                                                                style="font-size: 13px;">
                                                                #2
                                                            </span>
                                                        @elseif($globalRank === 3)
                                                            <span class="badge badge-dark font-weight-bold"
                                                                style="background:#cd7f32; color:#fff; font-size: 13px;">
                                                                #3
                                                            </span>
                                                        @else
                                                            <span class="badge badge-light border text-muted font-weight-bold">
                                                                #{{ $globalRank }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3"
                                                                style="width: 44px; height: 44px; flex-shrink: 0; background: #f8f9fa; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #e9ecef;">
                                                                @php
                                                                    $imgUrl = !empty($pc->display_image)
                                                                        ? (str_starts_with($pc->display_image, 'http')
                                                                            ? $pc->display_image
                                                                            : asset($pc->display_image))
                                                                        : null;
                                                                @endphp
                                                                @if ($imgUrl)
                                                                    <img src="{{ $imgUrl }}"
                                                                        alt="{{ $pc->product_name }}"
                                                                        style="max-width: 100%; max-height: 100%; object-fit: contain;"
                                                                        onerror="this.onerror=null;this.parentElement.innerHTML='<i class=\'fas fa-box text-secondary\'></i>';">
                                                                @else
                                                                    <i class="fas fa-box text-secondary"></i>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <div class="font-weight-bold text-dark">
                                                                    @if ($pc->product_id)
                                                                        <a href="{{ route('admin.products.edit', $pc->product_id) }}"
                                                                            target="_blank" class="text-dark font-weight-600">
                                                                            {{ $pc->product_name }}
                                                                        </a>
                                                                    @else
                                                                        {{ $pc->product_name }}
                                                                    @endif
                                                                </div>
                                                                @if ($pc->sku)
                                                                    <small class="text-muted"><i
                                                                            class="fas fa-barcode mr-1"></i>SKU:
                                                                        {{ $pc->sku }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span
                                                            class="badge badge-light border">{{ $pc->display_category }}</span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="badge badge-info badge-pill">
                                                            <i class="fas fa-receipt mr-1"></i>{{ $pc->times_ordered }}
                                                            {{ Str::plural('order', $pc->times_ordered) }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="badge badge-primary px-3 py-1 font-weight-bold"
                                                            style="font-size: 13px;">
                                                            {{ number_format($pc->ordered_qty) }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-right font-weight-bold text-dark">
                                                        {!! formatConverted($pc->ordered_value) !!}
                                                    </td>
                                                    <td class="align-middle text-right text-muted">
                                                        {!! formatConverted($pc->avg_price) !!}
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        @if (isset($pc->current_stock))
                                                            @if ($pc->current_stock > 10)
                                                                <span
                                                                    class="badge badge-success">{{ number_format($pc->current_stock) }}
                                                                    in stock</span>
                                                            @elseif($pc->current_stock > 0)
                                                                <span
                                                                    class="badge badge-warning">{{ number_format($pc->current_stock) }}
                                                                    low stock</span>
                                                            @else
                                                                <span class="badge badge-danger">Out of stock</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        @if ($pc->product_id)
                                                            <a href="{{ route('admin.products.edit', $pc->product_id) }}"
                                                                target="_blank" class="btn btn-outline-primary btn-sm"
                                                                title="Edit / View Product">
                                                                <i class="fas fa-external-link-alt"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-4">No product
                                                        purchases recorded for this customer.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top flex-wrap"
                                style="gap: 12px;">
                                <div class="text-muted small">
                                    Showing {{ $productComparison->firstItem() ?? ($productComparison->total() > 0 ? 1 : 0) }}
                                    to {{ $productComparison->lastItem() ?? $productComparison->total() }} of
                                    {{ number_format($productComparison->total()) }} entries
                                </div>
                                <div>
                                    {{ $productComparison->appends(['tab' => 'products'])->links('vendor.pagination.custom-report') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ─── TAB 3: PAYMENTS ───────────────────────── --}}
                    <div class="tab-pane fade {{ $activeTab === 'payments' ? 'show active' : '' }}" id="paymentsTab">
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-md mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Order</th>
                                                <th>Method</th>
                                                <th>Transaction ID</th>
                                                <th class="text-right">Amount</th>
                                                <th>Note</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($payments as $i => $payment)
                                                <tr>
                                                    <td>{{ $payments->firstItem() + $i }}</td>
                                                    <td>{{ $payment->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        @if ($payment->order_id)
                                                            <a href="{{ route('admin.orders.show', $payment->order_id) }}"
                                                                target="_blank">{{ $payment->order->order_no ?? '—' }}</a>@else{{ $payment->order->order_no ?? '—' }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $payment->payment_method ?? '—' }}</td>
                                                    <td>{{ $payment->transaction_id ?? '—' }}</td>
                                                    <td class="text-right font-weight-bold text-dark">{!! formatConverted($payment->amount) !!}
                                                    </td>
                                                    <td>{{ $payment->note ?? '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">No payments found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top flex-wrap"
                                style="gap: 12px;">
                                <div class="text-muted small">
                                    Showing {{ $payments->firstItem() ?? ($payments->total() > 0 ? 1 : 0) }} to
                                    {{ $payments->lastItem() ?? $payments->total() }} of
                                    {{ number_format($payments->total()) }} entries
                                </div>
                                <div>
                                    {{ $payments->appends(['tab' => 'payments'])->links('vendor.pagination.custom-report') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'monthly' ? 'show active' : '' }}" id="monthlyTab">
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th class="text-center">Orders</th>
                                                <th class="text-right">Order Value</th>
                                                <th class="text-center">Issue Qty</th>
                                                <th class="text-center">Products</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($monthlyTrend as $trend)
                                                @php
                                                    $monthStart = $trend->month;
                                                    $monthIssueQty = \App\Models\OrderItem::whereHas(
                                                        'order',
                                                        fn($q) => $q
                                                            ->whereIn('id', $orderIds)
                                                            ->where('status', 'completed')
                                                            ->whereYear('placed_at', substr($monthStart, 0, 4))
                                                            ->whereMonth('placed_at', substr($monthStart, 5, 2)),
                                                    )->sum('quantity');
                                                    $monthUniqueProducts = \App\Models\OrderItem::whereHas(
                                                        'order',
                                                        fn($q) => $q
                                                            ->whereIn('id', $orderIds)
                                                            ->where('status', 'completed')
                                                            ->whereYear('placed_at', substr($monthStart, 0, 4))
                                                            ->whereMonth('placed_at', substr($monthStart, 5, 2)),
                                                    )
                                                        ->distinct('product_id')
                                                        ->count('product_id');
                                                @endphp
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $trend->month)->format('F Y') }}
                                                    </td>
                                                    <td class="text-center"><span
                                                            class="badge badge-primary">{{ number_format($trend->orders_count) }}</span>
                                                    </td>
                                                    <td class="text-right">{!! formatConverted($trend->total_amount) !!}</td>
                                                    <td class="text-center">{{ number_format($monthIssueQty) }}</td>
                                                    <td class="text-center">{{ number_format($monthUniqueProducts) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No data.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top flex-wrap"
                                style="gap: 12px;">
                                <div class="text-muted small">
                                    Showing {{ $monthlyTrend->firstItem() ?? ($monthlyTrend->total() > 0 ? 1 : 0) }} to
                                    {{ $monthlyTrend->lastItem() ?? $monthlyTrend->total() }} of
                                    {{ number_format($monthlyTrend->total()) }} entries
                                </div>
                                <div>
                                    {{ $monthlyTrend->appends(['tab' => 'monthly'])->links('vendor.pagination.custom-report') }}
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
                                <div class="card-header">
                                    <h4>Completed Orders</h4>
                                </div>
                                <div class="card-body">{{ number_format($summary->total_orders) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-dollar-sign"></i></div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Amount</h4>
                                </div>
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
                            @if (isset($latestPdf) && $latestPdf)
                                <a href="{{ $latestPdf['url'] }}" id="btn-download-pdf-global"
                                    class="btn btn-success btn-sm font-weight-bold mr-2 btn-download-pdf"
                                    title="{{ $latestPdf['filename'] }}">
                                    <i class="fas fa-file-download mr-1"></i> Download PDF (Ready: {{ $latestPdf['time'] }})
                                </a>
                            @else
                                <a href="#" id="btn-download-pdf-global"
                                    class="btn btn-success btn-sm font-weight-bold mr-2 btn-download-pdf"
                                    style="display: none;">
                                    <i class="fas fa-file-download mr-1"></i> Download PDF
                                </a>
                            @endif
                            <button type="button" id="btn-generate-pdf-global" data-type="order_sales_report"
                                data-url="{{ route('admin.reports.orders.pdf') }}"
                                class="btn btn-danger btn-sm font-weight-bold btn-generate-pdf">
                                <i class="fas fa-file-pdf mr-1"></i> Generate PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filter-form-global">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>User / Outlet</label>
                                    <select name="user_id" class="form-control select2">
                                        <option value="">All Users</option>
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}"
                                                {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                                                {{ $u->name }} {{ $u->outlet_name ? '(' . $u->outlet_name . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Month</label>
                                    <select name="month" class="form-control auto-submit">
                                        <option value="">All</option>
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}"
                                                {{ (string) request('month') === (string) $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Year</label>
                                    <select name="year" class="form-control auto-submit">
                                        <option value="">All</option>
                                        @foreach (range(date('Y'), date('Y') - 5) as $y)
                                            <option value="{{ $y }}"
                                                {{ (string) request('year') === (string) $y ? 'selected' : '' }}>
                                                {{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Date From</label>
                                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                                        class="form-control auto-submit">
                                </div>
                                <div class="col-md-2">
                                    <label>Date To</label>
                                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                                        class="form-control auto-submit">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block"><i
                                            class="fas fa-filter"></i></button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <a href="{{ route('admin.reports.orders') }}"
                                        class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo"></i> Reset</a>
                                    <small class="text-muted ml-2">Select a user and filter to see their 360° detailed
                                        report.</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @php
                    $globalActiveTab =
                        request()->has('monthly_page') || request('tab') === 'monthly' ? 'monthly' : 'products';
                @endphp

                <div class="card">
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $globalActiveTab === 'products' ? 'active' : '' }}" id="products-tab"
                                    data-toggle="tab" href="#products">
                                    <i class="fas fa-box"></i> Product Frequency <span
                                        class="badge badge-primary">{{ $productFrequency->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $globalActiveTab === 'monthly' ? 'active' : '' }}" id="monthly-tab"
                                    data-toggle="tab" href="#monthly">
                                    <i class="fas fa-calendar-alt"></i> Monthly Trend <span
                                        class="badge badge-secondary">{{ $monthlyTrend->total() }}</span>
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
                    <div class="tab-pane fade {{ $globalActiveTab === 'products' ? 'show active' : '' }}" id="products">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product Name</th>
                                                <th class="text-center">Times Ordered</th>
                                                <th class="text-center">Total Qty</th>
                                                <th class="text-right">Total Value</th>
                                                {{-- <th class="text-center">Times Issued</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($productFrequency as $index => $item)
                                                <tr>
                                                    <td>{{ $productFrequency->firstItem() + $index }}</td>
                                                    <td>{{ $item->product_name }}</td>
                                                    <td class="text-center"><span
                                                            class="badge badge-primary">{{ number_format($item->times_ordered) }}</span>
                                                    </td>
                                                    <td class="text-center">{{ number_format($item->total_qty) }}</td>
                                                    <td class="text-right">{!! formatConverted($item->total_value) !!}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No data.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top flex-wrap"
                                style="gap: 12px;">
                                <div class="text-muted small">
                                    Showing {{ $productFrequency->firstItem() ?? ($productFrequency->total() > 0 ? 1 : 0) }}
                                    to {{ $productFrequency->lastItem() ?? $productFrequency->total() }} of
                                    {{ number_format($productFrequency->total()) }} entries
                                </div>
                                <div>
                                    {{ $productFrequency->links('vendor.pagination.custom-report') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $globalActiveTab === 'monthly' ? 'show active' : '' }}" id="monthly">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md mb-0">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th class="text-center">Orders</th>
                                                <th class="text-right">Amount</th>
                                                <th class="text-center">Issue Qty</th>
                                                <th class="text-center">Products</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($monthlyTrend as $trend)
                                                @php
                                                    $monthStart = $trend->month;
                                                    $monthIssueQty = \App\Models\OrderItem::whereHas(
                                                        'order',
                                                        fn($q) => $q
                                                            ->whereIn('id', $orderIds)
                                                            ->where('status', 'completed')
                                                            ->whereYear('placed_at', substr($monthStart, 0, 4))
                                                            ->whereMonth('placed_at', substr($monthStart, 5, 2)),
                                                    )->sum('quantity');
                                                    $monthUniqueProducts = \App\Models\OrderItem::whereHas(
                                                        'order',
                                                        fn($q) => $q
                                                            ->whereIn('id', $orderIds)
                                                            ->where('status', 'completed')
                                                            ->whereYear('placed_at', substr($monthStart, 0, 4))
                                                            ->whereMonth('placed_at', substr($monthStart, 5, 2)),
                                                    )
                                                        ->distinct('product_id')
                                                        ->count('product_id');
                                                @endphp
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $trend->month)->format('F Y') }}
                                                    </td>
                                                    <td class="text-center"><span
                                                            class="badge badge-primary">{{ number_format($trend->orders_count) }}</span>
                                                    </td>
                                                    <td class="text-right">{!! formatConverted($trend->total_amount) !!}</td>
                                                    <td class="text-center">{{ number_format($monthIssueQty) }}</td>
                                                    <td class="text-center">{{ number_format($monthUniqueProducts) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">No data.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top flex-wrap"
                                style="gap: 12px;">
                                <div class="text-muted small">
                                    Showing {{ $monthlyTrend->firstItem() ?? ($monthlyTrend->total() > 0 ? 1 : 0) }} to
                                    {{ $monthlyTrend->lastItem() ?? $monthlyTrend->total() }} of
                                    {{ number_format($monthlyTrend->total()) }} entries
                                </div>
                                <div>
                                    {{ $monthlyTrend->links('vendor.pagination.custom-report') }}
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
        $('.select2').select2({
            width: '100%',
            allowClear: true,
            placeholder: function() {
                return $(this).data('placeholder') || 'Select Option';
            }
        });

        $('.auto-submit').on('change', function() {
            $(this).closest('form').submit();
        });

        // Tab persistence on load and change
        var hash = window.location.hash;
        if (hash && $('#reportTabs a[href="' + hash + '"]').length) {
            $('#reportTabs a[href="' + hash + '"]').tab('show');
        }
        $('#reportTabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (history.replaceState) {
                history.replaceState(null, null, e.target.hash);
            }
        });
    </script>

    @if (isset($user) && isset($chartCategoryLabels) && count($chartCategoryLabels) > 0)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var ctx = document.getElementById('userCategoryDoughnutChart');
                if (ctx) {
                    var catLabels = @json($chartCategoryLabels);
                    var catData = @json($chartCategoryData);
                    var catQtys = @json($chartCategoryQuantities);
                    var palette = ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#3b82f6',
                        '#14b8a6', '#f97316', '#64748b', '#0284c7', '#84cc16'
                    ];

                    var displayLabels = [];
                    var displayData = [];
                    var displayQtys = [];
                    var bgColors = [];

                    if (catLabels.length > 7) {
                        displayLabels = catLabels.slice(0, 6);
                        displayData = catData.slice(0, 6);
                        displayQtys = catQtys.slice(0, 6);
                        bgColors = palette.slice(0, 6);

                        var otherSum = 0;
                        var otherQty = 0;
                        for (var i = 6; i < catData.length; i++) {
                            otherSum += Number(catData[i]) || 0;
                            otherQty += Number(catQtys[i]) || 0;
                        }
                        displayLabels.push('Other (' + (catLabels.length - 6) + ')');
                        displayData.push(Math.round(otherSum * 100) / 100);
                        displayQtys.push(otherQty);
                        bgColors.push('#94a3b8');
                    } else {
                        displayLabels = catLabels;
                        displayData = catData;
                        displayQtys = catQtys;
                        bgColors = palette.slice(0, catLabels.length);
                    }

                    var totalAmount = displayData.reduce(function(a, b) {
                        return a + Number(b);
                    }, 0) || 1;

                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: displayLabels,
                            datasets: [{
                                data: displayData,
                                backgroundColor: bgColors,
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutoutPercentage: 68,
                            legend: {
                                display: false
                            },
                            tooltips: {
                                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                titleFontSize: 12,
                                bodyFontSize: 12,
                                xPadding: 10,
                                yPadding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        var idx = tooltipItem.index;
                                        var label = data.labels[idx] || '';
                                        var val = data.datasets[0].data[idx] || 0;
                                        var qty = displayQtys[idx] || 0;
                                        var share = Math.round((val / totalAmount) * 1000) / 10;
                                        return ' ' + label + ': kr. ' + Number(val).toLocaleString(
                                            'da-DK', {
                                                minimumFractionDigits: 2
                                            }) + ' (' + share + '%, ' + qty.toLocaleString() + ' pcs)';
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endif

    @include('backend.reports.partials.async_analytics_report_pdf_js')
@endpush
