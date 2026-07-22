@extends('backend.layouts.master')
@section('title', 'Best Seller Products')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-fire mr-2 text-danger"></i>Best Seller Products</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Best Sellers</div>
            </div>
        </div>

        <div class="section-body">

            {{-- Summary Cards --}}
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Unique Products</h4></div>
                            <div class="card-body" id="summary-total-products">{{ number_format($products->total()) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Qty Ordered</h4></div>
                            <div class="card-body" id="summary-grand-total-qty">{{ number_format($grandTotals->grand_total_qty ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Order Value</h4></div>
                            <div class="card-body" id="summary-grand-total-value">{!! formatWithCurrency($grandTotals->grand_total_value ?? 0) !!}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h4><i class="fas fa-filter mr-2"></i>Filter Options</h4>
                </div>
                <div class="card-body">
                    <form id="best-sellers-filter-form" method="GET" action="{{ route('admin.reports.best-sellers') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Year</label>
                                    <select name="year" id="year" class="form-control select2">
                                        <option value="">All Years</option>
                                        @foreach($availableYears as $yr)
                                            <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Month</label>
                                    <select name="month" id="month" class="form-control select2">
                                        <option value="">All Months</option>
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category_id" id="category_id" class="form-control select2">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sub Category</label>
                                    <select name="sub_category_id" id="sub_category_id" class="form-control select2">
                                        <option value="">All Sub Categories</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Child Category</label>
                                    <select name="child_category_id" id="child_category_id" class="form-control select2">
                                        <option value="">All Child Categories</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Search Product</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search product..." value="{{ request('search') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-right">
                                <a href="{{ route('admin.reports.best-sellers') }}" class="btn btn-danger btn-sm" id="btn-reset"><i class="fas fa-undo"></i> Reset Filters</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="row">
                <div class="col-12">
                    <div class="card border shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
                            <h4 class="mb-0"><i class="fas fa-fire mr-2 text-danger"></i>All Products by Order Frequency</h4>
                        </div>

                        <div class="card-body p-0" id="best-sellers-table-container">
                            @include('backend.reports.partials.best_sellers_table')
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script>
        var filtering = false;

        function refreshSelect2(selector) {
            $(selector).trigger('change');
        }

        function loadSubCategories(categoryId, selectedSubCategoryId, callback) {
            if (!categoryId) {
                filtering = true;
                $('#sub_category_id').empty().append('<option value="">All Sub Categories</option>');
                $('#child_category_id').empty().append('<option value="">All Child Categories</option>');
                refreshSelect2('#sub_category_id');
                refreshSelect2('#child_category_id');
                filtering = false;
                if (callback) callback();
                return;
            }
            $.get('{{ route("admin.get-subCategories") }}', { id: categoryId }, function (data) {
                filtering = true;
                $('#sub_category_id').empty().append('<option value="">All Sub Categories</option>');
                $.each(data, function (i, item) {
                    var selected = selectedSubCategoryId && parseInt(selectedSubCategoryId) === item.id ? 'selected' : '';
                    $('#sub_category_id').append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
                });
                $('#child_category_id').empty().append('<option value="">All Child Categories</option>');
                refreshSelect2('#sub_category_id');
                refreshSelect2('#child_category_id');
                filtering = false;
                if (callback) callback();
            });
        }

        function loadChildCategories(subCategoryId, selectedChildCategoryId, callback) {
            if (!subCategoryId) {
                filtering = true;
                $('#child_category_id').empty().append('<option value="">All Child Categories</option>');
                refreshSelect2('#child_category_id');
                filtering = false;
                if (callback) callback();
                return;
            }
            $.get('{{ route("admin.get-child-categories") }}', { id: subCategoryId }, function (data) {
                filtering = true;
                $('#child_category_id').empty().append('<option value="">All Child Categories</option>');
                $.each(data, function (i, item) {
                    var selected = selectedChildCategoryId && parseInt(selectedChildCategoryId) === item.id ? 'selected' : '';
                    $('#child_category_id').append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
                });
                refreshSelect2('#child_category_id');
                filtering = false;
                if (callback) callback();
            });
        }

        function fetchBestSellers(page) {
            var form = $('#best-sellers-filter-form');
            var url = form.attr('action');
            var data = form.serialize();
            if (page) data += '&page=' + page;

            $.ajax({
                url: url,
                type: 'GET',
                data: data,
                dataType: 'json',
                beforeSend: function () {
                    $('#best-sellers-table-container').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>');
                },
                success: function (res) {
                    $('#best-sellers-table-container').html(res.html);
                    $('#summary-total-products').text(res.total_products);
                    $('#summary-grand-total-qty').text(res.grand_total_qty);
                    $('#summary-grand-total-value').html(res.grand_total_value);
                }
            });
        }

        $(document).ready(function () {
            var selectedCategory = '{{ request("category_id") }}';
            var selectedSubCategory = '{{ request("sub_category_id") }}';
            var selectedChildCategory = '{{ request("child_category_id") }}';

            if (selectedCategory) {
                loadSubCategories(selectedCategory, selectedSubCategory || null);
            }
            if (selectedSubCategory) {
                loadChildCategories(selectedSubCategory, selectedChildCategory || null);
            }

            $('#year, #month').on('change', function () {
                fetchBestSellers();
            });

            $('#category_id').on('change', function () {
                var val = $(this).val();
                loadSubCategories(val, null, function () {
                    fetchBestSellers();
                });
            });

            $('#sub_category_id').on('change', function () {
                if (filtering) return;
                var val = $(this).val();
                loadChildCategories(val, null, function () {
                    fetchBestSellers();
                });
            });

            $('#child_category_id').on('change', function () {
                if (filtering) return;
                fetchBestSellers();
            });

            var searchTimeout;
            $('#best-sellers-filter-form input[name="search"]').on('input keyup', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    fetchBestSellers();
                }, 400);
            });

            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                var url = new URL($(this).attr('href'));
                var page = url.searchParams.get('page') || 1;
                window.history.pushState(null, '', window.location.pathname + '?' + $('#best-sellers-filter-form').serialize() + '&page=' + page);
                fetchBestSellers(page);
            });
        });
    </script>
@endpush
