@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Low Stock Alert</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Low Stock Alert</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Products Below Minimum Inventory Level</h4>
                            <div class="card-header-action">
                                <button type="button" class="btn btn-success" id="add_to_booking_btn" style="display: none;">
                                    <i class="fas fa-shopping-cart"></i> Add to Booking (<span id="selected_count">0</span>)
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="font-weight-bold text-dark" style="font-size: 14px;"><i class="fas fa-store mr-1"></i>Filter by Vendor</label>
                                    <select name="vendor_id" id="vendor_filter" class="form-control select2">
                                        <option value="">All Vendors</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->shop_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.reports.low-stock') }}" class="btn btn-danger btn-block" style="margin-top: 30px;"><i class="fas fa-undo mr-1"></i> Reset</a>
                                </div>
                            </div>
                            
                            <div id="products-container">
                            @if($products->count() > 0)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong>{{ $products->total() }}</strong> product(s) found
                                    @if(request('search'))
                                        matching "{{ request('search') }}"
                                    @else
                                        with stock levels at or below 100!
                                    @endif
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th width="5%">
                                                    <input type="checkbox" id="select_all" title="Select All">
                                                </th>
                                                <th width="10%">Image</th>
                                                <th>Product</th>
                                                <th>Category</th>
                                                <th>Current Stock</th>
                                                <th>Status</th>
                                                <th width="15%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($products as $product)
                                                @php
                                                    $currentStock = $product->inventory_stocks_sum_quantity ?? 0;
                                                    $isCritical = $currentStock == 0;
                                                @endphp
                                                <tr class="{{ $isCritical ? 'table-danger' : '' }}">
                                                    <td>
                                                        <input type="checkbox" class="product-checkbox" value="{{ $product->id }}" data-product-name="{{ $product->name }}">
                                                    </td>
                                                    <td class="text-center">
                                                        @if($product->thumb_image)
                                                            <img src="{{ asset('storage/' . $product->thumb_image) }}"
                                                                 alt="{{ $product->name }}"
                                                                 class="img-fluid rounded"
                                                                 style="width:40px;height:40px;object-fit:cover;"
                                                                 loading="lazy"
                                                                 onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
                                                            <div class="rounded align-items-center justify-content-center text-muted"
                                                                 style="display:none;width:40px;height:40px;background:#f8f9fa;border:1px solid #e9ecef;">
                                                                <i class="fas fa-image" style="font-size:12px;"></i>
                                                            </div>
                                                        @else
                                                            <div class="rounded d-inline-flex align-items-center justify-content-center text-muted"
                                                                 style="width:40px;height:40px;background:#f8f9fa;border:1px solid #e9ecef;">
                                                                <i class="fas fa-image" style="font-size:12px;"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $isCritical ? 'danger' : 'warning' }}">
                                                            {{ $currentStock }} {{ $product->unit->name ?? '' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($isCritical)
                                                            <span class="badge badge-danger">OUT OF STOCK</span>
                                                        @else
                                                            <span class="badge badge-warning">LOW STOCK</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @can('Manage Order Place')
                                                        <button type="button" class="btn btn-outline-info btn-sm add-to-basket" data-id="{{ $product->id }}" title="Add to Booking Basket">
                                                            <i class="fas fa-shopping-basket"></i>
                                                        </button>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <p class="text-muted mb-1 mt-3 text-center" style="font-size: 14px; font-weight: 500;">
                                    Showing 
                                    <span class="text-dark font-weight-bold">
                                        {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}
                                    </span> 
                                    of 
                                     <span class="text-dark font-weight-bold">
                                        {{ $products->total() }}
                                    </span> 
                                    products low stock
                                </p>
                                <div class="mt-4 d-flex justify-content-center flex-wrap custom-pagination" id="pagination-container">
                                    {{ $products->links() }}
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> 
                                    @if(request('search'))
                                        No products found matching "{{ request('search') }}"
                                    @else
                                        All products are adequately stocked!
                                    @endif
                                </div>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <!-- Floating Basket Container (Booking Only) -->
    @can('Manage Order Place')
    <div id="floating-baskets-container" class="position-fixed d-flex align-items-center" style="bottom: 30px; right: 30px; z-index: 99999; gap: 20px;">
        <style>#floating-baskets-container, #floating-baskets-container > * { pointer-events: auto; }</style>
        <!-- Floating Basket Widget (Booking) -->
        <div id="floating-basket" style="display: none;">
            <div class="d-flex flex-column align-items-center">
                <div class="cursor-pointer bg-primary text-white shadow-lg rounded-circle d-flex align-items-center justify-content-center position-relative mb-2 basket-fab" 
                     id="go-to-booking" title="Place Order" style="width: 55px; height: 55px; transition: all 0.3s ease;">
                    <i class="fas fa-shopping-basket fa-lg"></i>
                    <span id="basket-count" class="badge badge-danger position-absolute" style="top: -5px; right: -5px; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 11px; border: 2px solid #fff;">0</span>
                </div>
                <button class="btn btn-sm btn-light shadow-sm rounded-circle d-flex align-items-center justify-content-center" 
                        id="clear-booking-basket" title="Clear Booking Basket" style="width: 25px; height: 25px; padding: 0; opacity: 0.8;">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 10px;"></i>
                </button>
            </div>
        </div>
    </div>
    @endcan

    <style>
        .cursor-pointer { cursor: pointer; }
        .lazy-load { opacity: 0; transition: opacity 0.3s ease-in; }
        .lazy-load.loaded { opacity: 1; }
        
        @keyframes shake-basket {
            0% { transform: scale(1) rotate(0); }
            20% { transform: scale(1.2) rotate(-10deg); }
            40% { transform: scale(1.2) rotate(10deg); }
            60% { transform: scale(1.2) rotate(-10deg); }
            80% { transform: scale(1.2) rotate(10deg); }
            100% { transform: scale(1) rotate(0); }
        }
        .animate-shake {
            animation: shake-basket 0.5s ease-in-out;
        }

        .basket-fab:hover {
            transform: scale(1.1);
            filter: brightness(1.1);
        }
        .basket-fab {
            box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
        }
        .add-to-basket.added, .add-to-request-basket.added {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }
    </style>
    <script>
        // Remove DataTable initialization that conflicts with Laravel Pagination
        // We will use a simple table or a different DataTable config
        if ($.fn.DataTable.isDataTable('#table-1')) {
            $('#table-1').DataTable().destroy();
        }
        
        // Initialize as a simple searchable table without internal paging
        $("#table-1").dataTable({
            "ordering": false,
            "paging": false,
            "info": false,
            "searching": true,
            "language": {
                "search": "Filter:",
                "searchPlaceholder": "Search in table..."
            }
        });

        $(document).ready(function() {
            // Init searchable Select2 on vendor filter
            $('#vendor_filter').select2({ width: '100%' });

            // Auto-submit on change
            $('#vendor_filter').on('change', function() {
                let vendorId = $(this).val();
                let url = "{{ route('admin.reports.low-stock') }}";
                if (vendorId) {
                    url += '?vendor_id=' + encodeURIComponent(vendorId);
                }
                window.location.href = url;
            });

            // --- Basket Logic Start (Database Cart System) ---
            
            /**
             * Update basket UI with counts and button states
             */
            function updateBasketUI() {
                $.ajax({
                    url: "{{ route('admin.cart.count') }}?cart_type=booking",
                    method: 'GET',
                    success: function(data) {
                        $('#basket-count').text(data.count);
                        if (data.count > 0) {
                            $('#floating-basket').fadeIn();
                        } else {
                            $('#floating-basket').fadeOut();
                        }
                    }
                });

                // Update button states
                $.ajax({
                    url: "{{ route('admin.cart.items') }}?cart_type=booking",
                    method: 'GET',
                    success: function(data) {
                        const bookingIds = data.product_ids;
                        $('.add-to-basket').each(function() {
                            const id = $(this).data('id').toString();
                            if (bookingIds.includes(parseInt(id))) {
                                $(this).addClass('added').html('<i class="fas fa-check"></i>');
                            } else {
                                $(this).removeClass('added').html('<i class="fas fa-shopping-basket"></i>');
                            }
                        });
                    }
                });
            }

            // Initial UI Update on page load
            updateBasketUI();

            // Clear Booking Basket
            $(document).on('click', '#clear-booking-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();

                Swal.fire({
                    title: 'Clear Booking Basket?',
                    text: "You are about to remove all items from the booking basket.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6777ef',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, clear it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.cart.clear') }}",
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: { cart_type: 'booking' },
                            success: function(response) {
                                toastr.info(response.message);
                                updateBasketUI();
                            },
                            error: function() {
                                toastr.error('Error clearing booking basket');
                            }
                        });
                    }
                });
            });

            // Add to Booking Basket Click
            $(document).on('click', '.add-to-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const productId = $(this).data('id');
                if (!productId) return;

                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        product_id: productId,
                        cart_type: 'booking'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#go-to-booking').addClass('animate-shake');
                            setTimeout(function() { 
                                $('#go-to-booking').removeClass('animate-shake'); 
                            }, 500);
                            toastr.success(response.message);
                            updateBasketUI();
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error adding to basket');
                        console.error(xhr);
                    }
                });
            });

            // Navigation
            $(document).on('click', '#go-to-booking', function() {
                $.ajax({
                    url: "{{ route('admin.cart.product-ids') }}?cart_type=booking",
                    method: 'GET',
                    success: function(data) {
                        const ids = data.ids.join(',');
                        window.location.href = "{{ route('admin.bookings.create') }}?ids=" + ids;
                    }
                });
            });

            // NOTE: Removed global ajaxComplete handler that was causing infinite loop
            // The updateBasketUI() is already called explicitly after cart operations
            // $(document).ajaxComplete(function() {
            //     updateBasketUI();
            // });
            // --- Basket Logic End ---

            // --- Cross-page Persisted Selections (localStorage) ---
            const STORAGE_KEY = 'low_stock_selected_ids';

            function getPersistedIds() {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            }

            function savePersistedIds(ids) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
            }

            function updatePersistedUI() {
                const ids = getPersistedIds();
                $('#selected_count').text(ids.length);
                if (ids.length > 0) {
                    $('#add_to_booking_btn').show();
                } else {
                    $('#add_to_booking_btn').hide();
                }
            }

            // Restore checkboxes from persisted selections on page load
            (function restoreSelections() {
                const ids = getPersistedIds();
                if (ids.length === 0) return;
                $('.product-checkbox').each(function() {
                    if (ids.indexOf($(this).val()) !== -1) {
                        $(this).prop('checked', true);
                    }
                });
                $('#select_all').prop('checked', $('.product-checkbox:checked').length === $('.product-checkbox').length);
                updatePersistedUI();
            })();

            // Select All checkbox
            $('#select_all').on('change', function() {
                const checked = $(this).is(':checked');
                let ids = getPersistedIds();
                const visibleIds = [];
                $('.product-checkbox').each(function() {
                    const vid = $(this).val();
                    visibleIds.push(vid);
                    $(this).prop('checked', checked);
                });
                if (checked) {
                    visibleIds.forEach(function(vid) {
                        if (ids.indexOf(vid) === -1) ids.push(vid);
                    });
                } else {
                    ids = ids.filter(function(id) { return visibleIds.indexOf(id) === -1; });
                }
                savePersistedIds(ids);
                updatePersistedUI();
            });

            // Individual checkbox change
            $(document).on('change', '.product-checkbox', function() {
                let ids = getPersistedIds();
                const id = $(this).val();
                if ($(this).is(':checked')) {
                    if (ids.indexOf(id) === -1) ids.push(id);
                } else {
                    ids = ids.filter(function(i) { return i !== id; });
                }
                savePersistedIds(ids);
                updatePersistedUI();
                $('#select_all').prop('checked', $('.product-checkbox:checked').length === $('.product-checkbox').length);
            });

            // Add to Booking (bulk) — uses persisted IDs across all pages
            $('#add_to_booking_btn').on('click', function() {
                const ids = getPersistedIds();
                if (ids.length === 0) {
                    toastr.warning('Please select at least one product');
                    return;
                }
                localStorage.removeItem(STORAGE_KEY);
                window.location.href = "{{ route('admin.bookings.create') }}?ids=" + ids.join(',');
            });
        });
    </script>
@endpush
