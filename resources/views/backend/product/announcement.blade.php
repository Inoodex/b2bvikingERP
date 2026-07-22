@extends('backend.layouts.master')
@section('title', 'Manual Product Announcement')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Manual Product Announcement</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active">Product Announcement</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Select Products And Send Email</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-12">
                                    <div class="form-group">
                                        <label for="announcement-subject">Subject (Optional)</label>
                                        <input type="text" id="announcement-subject" class="form-control"
                                            maxlength="255"
                                            placeholder="Leave empty to use default subject">
                                        <small class="text-muted">If empty, the default subject will be used.</small>
                                    </div>
                                </div>
                                <div class="col-12 col-md-12">
                                    <div class="form-group">
                                        <label for="announcement-message">Message (Optional)</label>
                                        <textarea id="announcement-message" class="form-control" rows="4" maxlength="5000"
                                            placeholder="Leave empty to use default message"></textarea>
                                        <small class="text-muted">If empty, the default message will be used.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label for="announcement-category">Category</label>
                                        <select id="announcement-category" class="form-control select2">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label for="announcement-sort">Filter By</label>
                                        <select id="announcement-sort" class="form-control select2">
                                            <option value="latest" selected>Latest Products</option>
                                            <option value="a-z">A-Z way (Alphabetical)</option>
                                            <option value="z-a">Z-A way (Reverse)</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label for="announcement-product-type">Occasion/Type</label>
                                        <select id="announcement-product-type" class="form-control select2">
                                            <option value="">All Types</option>
                                            <option value="new_arrival">New Arrival (Legacy)</option>
                                            <option value="upcoming">Upcoming (Legacy)</option>
                                            @foreach ($productTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label for="announcement-vendor">Vendor</label>
                                        <select id="announcement-vendor" class="form-control select2">
                                            <option value="">All Vendors</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label for="announcement-stock-filter">Stock</label>
                                        <select id="announcement-stock-filter" class="form-control select2">
                                            <option value="">All Stock</option>
                                            <option value="in_stock">In Stock</option>
                                            <option value="new_stock">New Stock (7 days)</option>
                                            <option value="out_of_stock">Out Of Stock</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="d-block">&nbsp;</label>
                                        <button type="button" id="announcement-filter-reset" class="btn btn-danger btn-sm shadow-sm rounded-pill w-100">
                                            <i class="fas fa-redo mr-1"></i>Reset Filters
                                        </button>
                                    </div>
                                </div>
                            </div>

                            

                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="badge badge-primary p-2">Selected Products: <span id="selected-products-count">0</span></span>
                                </div>
                                <button type="button" id="send-announcement-btn" class="btn btn-success">
                                    <i class="fas fa-paper-plane mr-1"></i> Send To All Users
                                </button>
                            </div>

                            <div class="table-responsive">
                                {{ $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'id' => 'product-announcement-table']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function() {
            const selectedProductIds = new Set();
            const tableSelector = '#product-announcement-table';
            let isResettingFilters = false;

            $(tableSelector).on('preXhr.dt', function(e, settings, data) {
                data.category = $('#announcement-category').val();
                data.sort = $('#announcement-sort').val();
                data.product_type = $('#announcement-product-type').val();
                data.vendor = $('#announcement-vendor').val();
                data.stock_filter = $('#announcement-stock-filter').val();
            });

            function refreshSelectionCounter() {
                $('#selected-products-count').text(selectedProductIds.size);
            }

            function syncCurrentPageSelections() {
                $(tableSelector + ' .announcement-product-checkbox').each(function() {
                    const id = parseInt($(this).data('id'), 10);
                    $(this).prop('checked', selectedProductIds.has(id));
                });

                const checkboxes = $(tableSelector + ' .announcement-product-checkbox');
                const checkedCount = checkboxes.filter(':checked').length;
                $('#select-all-products').prop('checked', checkboxes.length > 0 && checkedCount === checkboxes.length);
            }

            $('body').on('change', '.announcement-product-checkbox', function() {
                const id = parseInt($(this).data('id'), 10);
                if (Number.isNaN(id)) {
                    return;
                }

                if ($(this).is(':checked')) {
                    selectedProductIds.add(id);
                } else {
                    selectedProductIds.delete(id);
                }

                refreshSelectionCounter();
                syncCurrentPageSelections();
            });

            $('body').on('change', '#select-all-products', function() {
                const shouldSelect = $(this).is(':checked');
                $(tableSelector + ' .announcement-product-checkbox').each(function() {
                    const id = parseInt($(this).data('id'), 10);
                    if (Number.isNaN(id)) {
                        return;
                    }

                    if (shouldSelect) {
                        selectedProductIds.add(id);
                        $(this).prop('checked', true);
                    } else {
                        selectedProductIds.delete(id);
                        $(this).prop('checked', false);
                    }
                });
                refreshSelectionCounter();
            });

            $(tableSelector).on('draw.dt', function() {
                syncCurrentPageSelections();
                refreshSelectionCounter();
            });

            $('#announcement-category, #announcement-sort, #announcement-product-type, #announcement-vendor, #announcement-stock-filter').on('change', function() {
                if (isResettingFilters) {
                    return;
                }

                $(tableSelector).DataTable().ajax.reload(null, true);
            });

            $('#announcement-filter-reset').on('click', function() {
                isResettingFilters = true;
                $('#announcement-category').val('').trigger('change');
                $('#announcement-sort').val('latest').trigger('change');
                $('#announcement-product-type').val('').trigger('change');
                $('#announcement-vendor').val('').trigger('change');
                $('#announcement-stock-filter').val('').trigger('change');
                isResettingFilters = false;
                $(tableSelector).DataTable().ajax.reload(null, true);
            });

            $('#send-announcement-btn').on('click', function() {
                const productIds = Array.from(selectedProductIds);

                if (productIds.length === 0) {
                    toastr.error('Please select at least one product.');
                    return;
                }

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Queueing...');

                $.ajax({
                    url: "{{ route('admin.products.announcement.send') }}",
                    method: 'POST',
                    data: {
                        product_ids: productIds,
                        subject: $('#announcement-subject').val(),
                        message: $('#announcement-message').val(),
                    },
                    success: function(response) {
                        toastr.success(response.message || 'Announcement queued successfully.');
                        selectedProductIds.clear();
                        refreshSelectionCounter();
                        $('#announcement-subject').val('');
                        $('#announcement-message').val('');
                        $(tableSelector).DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to queue announcement.';
                        toastr.error(message);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
@endpush
