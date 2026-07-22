@extends('backend.layouts.master')
@section('title', 'Top Customers')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-crown mr-2 text-warning"></i>Top Customers</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Top Customers</div>
            </div>
        </div>
        <div class="section-body">

            {{-- Summary Cards --}}
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-info"><i class="fas fa-users"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Customers</h4></div>
                            <div class="card-body" id="stat-customers">{{ number_format($grandTotals->total_customers ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning"><i class="fas fa-shopping-cart"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Orders</h4></div>
                            <div class="card-body" id="stat-orders">{{ number_format($grandTotals->grand_total_orders ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Grand Total Value</h4></div>
                            <div class="card-body" id="stat-value">{!! formatWithCurrency($grandTotals->grand_total_value ?? 0) !!}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Card --}}
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">

                        {{-- Filter Bar (AJAX - Clean single row) --}}
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap py-3" style="gap:15px;">
                            <h4 class="mb-0 text-dark font-weight-bold" style="font-size:16px;">
                                <i class="fas fa-crown text-warning mr-2" style="font-size:18px;"></i>All Customers by Order Value
                            </h4>
                            <div class="d-flex align-items-center flex-row flex-nowrap" style="gap:10px;" id="tc-filter-bar">
                                <div class="position-relative" style="width:230px;">
                                    <input type="text" id="tc-search" class="form-control form-control-sm pr-4" placeholder="Search customer, email..." value="{{ request('search') }}" style="height:38px; border-radius:6px; font-size:13px;">
                                    <i class="fas fa-search position-absolute text-muted" style="right:12px; top:12px; font-size:13px; pointer-events:none;"></i>
                                </div>
                                <select id="tc-year" class="form-control form-control-sm" style="width:120px; height:38px; border-radius:6px; font-size:13px;">
                                    <option value="">All Years</option>
                                    @foreach($availableYears as $yr)
                                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                                <select id="tc-month" class="form-control form-control-sm" style="width:130px; height:38px; border-radius:6px; font-size:13px;">
                                    <option value="">All Months</option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endforeach
                                </select>
                                <button id="tc-clear" class="btn btn-sm btn-outline-secondary" style="height:38px; border-radius:6px; font-size:12px; display:none;">
                                    <i class="fas fa-undo mr-1"></i> Clear
                                </button>
                                <div id="tc-loading" class="spinner-border spinner-border-sm text-primary ml-1" role="status" style="display:none;"></div>
                            </div>
                        </div>

                        {{-- Table Area --}}
                        <div class="card-body p-0" id="tc-table-wrap">
                            @include('backend.reports.partials.top_customers_table', ['customers' => $customers, 'settings' => $settings])
                        </div>

                        {{-- Pagination + showing info --}}
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;" id="tc-pagination-wrap">
                            <p class="text-muted mb-0" style="font-size:14px;" id="tc-showing">
                                @if($customers->count() > 0)
                                    Showing <strong>{{ $customers->firstItem() }}</strong>–<strong>{{ $customers->lastItem() }}</strong>
                                    of <strong>{{ $customers->total() }}</strong> customers
                                @endif
                            </p>
                            <div class="custom-pagination" id="tc-pagination">
                                {{ $customers->links() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Monthly Trend (commented out — activate when needed) --}}
            {{--
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h4><i class="fas fa-calendar-alt mr-2 text-primary"></i>Monthly Trend</h4>
                        </div>
                        <div class="card-body p-0">
                            ...monthly trend content...
                        </div>
                    </div>
                </div>
            </div>
            --}}

        </div>
    </section>

    <script>
    (function () {
        var fetchUrl   = "{{ route('admin.reports.top-customers') }}";
        var csrfToken  = "{{ csrf_token() }}";
        var debounceTimer;

        var searchEl = document.getElementById('tc-search');
        var yearEl   = document.getElementById('tc-year');
        var monthEl  = document.getElementById('tc-month');
        var clearBtn = document.getElementById('tc-clear');
        var loading  = document.getElementById('tc-loading');
        var tableWrap = document.getElementById('tc-table-wrap');
        var pageWrap  = document.getElementById('tc-pagination-wrap');
        var showingEl = document.getElementById('tc-showing');
        var paginEl   = document.getElementById('tc-pagination');

        function fetchData(page) {
            var params = new URLSearchParams();
            var s = searchEl.value.trim();
            var y = yearEl.value;
            var m = monthEl.value;
            if (s) params.set('search', s);
            if (y) params.set('year', y);
            if (m) params.set('month', m);
            if (page) params.set('page', page);

            // Show / hide clear button
            clearBtn.style.display = (s || y || m) ? '' : 'none';

            loading.style.display = '';
            tableWrap.style.opacity = '0.5';

            fetch(fetchUrl + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                tableWrap.innerHTML = data.table;
                tableWrap.style.opacity = '1';
                loading.style.display = 'none';

                // Update summary cards
                document.getElementById('stat-customers').textContent = data.total_customers;
                document.getElementById('stat-orders').textContent    = data.total_orders;
                document.getElementById('stat-value').innerHTML       = data.total_value;

                // Update showing info
                if (data.showing_total > 0) {
                    showingEl.innerHTML = 'Showing <strong>' + data.showing_from + '</strong>–<strong>' + data.showing_to + '</strong> of <strong>' + data.showing_total + '</strong> customers';
                } else {
                    showingEl.innerHTML = '';
                }

                // Update pagination links and re-bind click events
                paginEl.innerHTML = data.pagination;
                bindPaginationClicks();

                // Update browser URL without reload
                var newUrl = fetchUrl + (params.toString() ? '?' + params.toString() : '');
                history.replaceState(null, '', newUrl);
            })
            .catch(function() {
                tableWrap.style.opacity = '1';
                loading.style.display = 'none';
            });
        }

        function bindPaginationClicks() {
            paginEl.querySelectorAll('a[href]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var url = new URL(link.href);
                    fetchData(url.searchParams.get('page') || 1);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        }

        // Debounced search (500ms)
        searchEl.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() { fetchData(1); }, 500);
        });

        // Instant filter on dropdown change
        yearEl.addEventListener('change', function() { fetchData(1); });
        monthEl.addEventListener('change', function() { fetchData(1); });

        // Clear button
        clearBtn.addEventListener('click', function() {
            searchEl.value = '';
            yearEl.value   = '';
            monthEl.value  = '';
            fetchData(1);
        });

        // Initial pagination bind (for server-rendered links)
        bindPaginationClicks();
    })();
    </script>
@endsection
