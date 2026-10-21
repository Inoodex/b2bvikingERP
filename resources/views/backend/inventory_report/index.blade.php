@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Current Inventory Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Inventory Stock</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Stock Levels</h4>
                        </div>
                        <div class="card-body">
                            <form id="search-form" action="javascript:void(0);" onsubmit="event.preventDefault(); triggerAjaxFilter();">
                                {{-- Row 1: Category, Sub Category, Child Category (3 items) --}}
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-12 mb-2">
                                        <label class="font-weight-600 text-dark small mb-1">
                                            <i class="fas fa-folder text-primary mr-1"></i> Category
                                        </label>
                                        <select name="category_id" id="filter-category" class="form-control select2">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-sm-12 mb-2">
                                        <label class="font-weight-600 text-dark small mb-1">
                                            <i class="fas fa-folder-open text-info mr-1"></i> Sub Category
                                        </label>
                                        <select name="sub_category_id" id="filter-sub-category" class="form-control select2">
                                            <option value="">All Sub Categories</option>
                                            @foreach ($subCategories as $subCat)
                                                <option value="{{ $subCat->id }}" {{ (string) request('sub_category_id') === (string) $subCat->id ? 'selected' : '' }}>
                                                    {{ $subCat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-sm-12 mb-2">
                                        <label class="font-weight-600 text-dark small mb-1">
                                            <i class="fas fa-level-down-alt text-secondary mr-1"></i> Child Category
                                        </label>
                                        <select name="child_category_id" id="filter-child-category" class="form-control select2">
                                            <option value="">All Child Categories</option>
                                            @foreach ($childCategories as $childCat)
                                                <option value="{{ $childCat->id }}" {{ (string) request('child_category_id') === (string) $childCat->id ? 'selected' : '' }}>
                                                    {{ $childCat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Row 2: Search & Reset (Filter button removed) --}}
                                <div class="row align-items-end mb-3">
                                    <div class="col-md-9 col-lg-10 col-sm-12 mb-2">
                                        <label class="font-weight-600 text-dark small mb-1">
                                            <i class="fas fa-search text-muted mr-1"></i> Search
                                        </label>
                                        <input type="text" name="search" id="filter-search" class="form-control" placeholder="Search product name, number, or SKU..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3 col-lg-2 col-sm-12 mb-2">
                                        <button type="button" id="btn-reset-filters" class="btn btn-outline-danger btn-block d-flex align-items-center justify-content-center" style="height: 42px;" title="Reset Filters">
                                            <i class="fas fa-sync-alt mr-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div id="table-inventory-container">
                                @include('backend.inventory_report.partials.table')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        let currentAjaxRequest = null;
        let isResetting = false;

        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#table-inventory')) {
                $('#table-inventory').DataTable().destroy();
            }
            $('#table-inventory').DataTable({
                paging: false,
                searching: false, // Server-side search handled via AJAX
                info: false,
                order: [],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        className: 'btn btn-primary'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-primary'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-primary',
                        title: '{{ \App\Models\GeneralSetting::first()->site_name ?? 'Inventory System' }} - Current Inventory Report'
                    },
                    {
                        text: 'PDF',
                        className: 'btn btn-primary',
                        action: function (e, dt, node, config) {
                            const search = $('#filter-search').val() || '';
                            const category_id = $('#filter-category').val() || '';
                            const sub_category_id = $('#filter-sub-category').val() || '';
                            const child_category_id = $('#filter-child-category').val() || '';
                            const url = "{{ route('admin.inventory-reports.export-pdf') }}?" + 
                                        $.param({ 
                                            search: search, 
                                            category_id: category_id,
                                            sub_category_id: sub_category_id,
                                            child_category_id: child_category_id 
                                        });
                            window.location.href = url;
                        }
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-primary',
                        title: '{{ \App\Models\GeneralSetting::first()->site_name ?? 'Inventory System' }} - Current Inventory Report'
                    }
                ]
            });
        }

        function triggerAjaxFilter(targetUrl = null) {
            if (isResetting) return;

            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            const search = $('#filter-search').val();
            const category_id = $('#filter-category').val();
            const sub_category_id = $('#filter-sub-category').val();
            const child_category_id = $('#filter-child-category').val();

            const url = targetUrl || "{{ route('admin.inventory-reports.index') }}";
            const data = targetUrl ? {} : {
                search: search,
                category_id: category_id,
                sub_category_id: sub_category_id,
                child_category_id: child_category_id
            };

            const $container = $('#table-inventory-container');
            $container.css({
                'opacity': '0.5',
                'pointer-events': 'none',
                'transition': 'opacity 0.2s ease'
            });

            currentAjaxRequest = $.ajax({
                url: url,
                method: 'GET',
                data: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(res) {
                    if (res && res.html) {
                        $container.html(res.html);
                    }
                    $container.css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                    initDataTable();

                    // Sync browser URL cleanly without page reload
                    const params = {};
                    if (search) params.search = search;
                    if (category_id) params.category_id = category_id;
                    if (sub_category_id) params.sub_category_id = sub_category_id;
                    if (child_category_id) params.child_category_id = child_category_id;
                    const queryString = $.param(params);
                    const newUrl = "{{ route('admin.inventory-reports.index') }}" + (queryString ? '?' + queryString : '');
                    window.history.pushState(null, '', newUrl);
                },
                error: function(xhr) {
                    if (xhr.statusText !== 'abort') {
                        $container.css({
                            'opacity': '1',
                            'pointer-events': 'auto'
                        });
                    }
                }
            });
        }

        $(document).ready(function() {
            initDataTable();

            let searchTimer;
            const $searchInput = $('#filter-search');
            const $categorySelect = $('#filter-category');
            const $subCategorySelect = $('#filter-sub-category');
            const $childCategorySelect = $('#filter-child-category');

            // 1. Debounced text search
            $searchInput.on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    triggerAjaxFilter();
                }, 350);
            });

            // 2. Cascading Category -> Sub Category + AJAX Filter
            $categorySelect.on('change', function() {
                if (isResetting) return;

                const categoryId = $(this).val();

                $subCategorySelect.empty().append('<option value="">All Sub Categories</option>');
                $childCategorySelect.empty().append('<option value="">All Child Categories</option>');

                if (categoryId) {
                    $subCategorySelect.append('<option value="">Loading...</option>').trigger('change.select2');
                    $.ajax({
                        url: "{{ route('admin.get-subCategories') }}",
                        method: 'GET',
                        data: { id: categoryId },
                        success: function(data) {
                            $subCategorySelect.empty().append('<option value="">All Sub Categories</option>');
                            if (data && data.length > 0) {
                                $.each(data, function(i, item) {
                                    $subCategorySelect.append(new Option(item.name, item.id));
                                });
                            }
                            $subCategorySelect.trigger('change.select2');
                        },
                        error: function() {
                            $subCategorySelect.empty().append('<option value="">All Sub Categories</option>').trigger('change.select2');
                        }
                    });
                } else {
                    $subCategorySelect.trigger('change.select2');
                    $childCategorySelect.trigger('change.select2');
                }

                triggerAjaxFilter();
            });

            // 3. Cascading Sub Category -> Child Category + AJAX Filter
            $subCategorySelect.on('change', function() {
                if (isResetting) return;

                const subCategoryId = $(this).val();

                $childCategorySelect.empty().append('<option value="">All Child Categories</option>');

                if (subCategoryId) {
                    $childCategorySelect.append('<option value="">Loading...</option>').trigger('change.select2');
                    $.ajax({
                        url: "{{ route('admin.get-child-categories') }}",
                        method: 'GET',
                        data: { id: subCategoryId },
                        success: function(data) {
                            $childCategorySelect.empty().append('<option value="">All Child Categories</option>');
                            if (data && data.length > 0) {
                                $.each(data, function(i, item) {
                                    $childCategorySelect.append(new Option(item.name, item.id));
                                });
                            }
                            $childCategorySelect.trigger('change.select2');
                        },
                        error: function() {
                            $childCategorySelect.empty().append('<option value="">All Child Categories</option>').trigger('change.select2');
                        }
                    });
                } else {
                    $childCategorySelect.trigger('change.select2');
                }

                triggerAjaxFilter();
            });

            // 4. Child Category Change -> AJAX Filter
            $childCategorySelect.on('change', function() {
                if (isResetting) return;
                triggerAjaxFilter();
            });

            // 5. AJAX Pagination clicks
            $(document).on('click', '#table-inventory-container .pagination a', function(e) {
                e.preventDefault();
                const pageUrl = $(this).attr('href');
                if (pageUrl) {
                    triggerAjaxFilter(pageUrl);
                    $('html, body').animate({
                        scrollTop: $('#table-inventory-container').offset().top - 100
                    }, 300);
                }
            });

            // 6. Reset Filters (No page reload)
            $('#btn-reset-filters').on('click', function(e) {
                e.preventDefault();
                isResetting = true;

                $searchInput.val('');
                $categorySelect.val('').trigger('change.select2');
                $subCategorySelect.empty().append('<option value="">All Sub Categories</option>').trigger('change.select2');
                $childCategorySelect.empty().append('<option value="">All Child Categories</option>').trigger('change.select2');

                isResetting = false;
                triggerAjaxFilter("{{ route('admin.inventory-reports.index') }}");
            });
        });
    </script>
@endpush
