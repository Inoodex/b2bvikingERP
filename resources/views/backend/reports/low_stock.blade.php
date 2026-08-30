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
                                <button type="button" class="btn btn-warning font-weight-bold shadow-sm" id="add_to_booking_btn" style="display: none; color: #1a1408;">
                                    <i class="fas fa-shopping-basket mr-1"></i> Add to Procurement Cart (<span id="selected_count">0</span>)
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
                                                        <button type="button" class="btn btn-outline-warning btn-sm add-to-basket font-weight-bold" data-id="{{ $product->id }}" title="Add to Procurement Basket" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;">
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
    <style>
        .cursor-pointer { cursor: pointer; }
        .lazy-load { opacity: 0; transition: opacity 0.3s ease-in; }
        .lazy-load.loaded { opacity: 1; }
        
        .add-to-basket.added {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: #fff !important;
        }
    </style>
    <script>
        // Remove DataTable initialization that conflicts with Laravel Pagination
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

            // --- Unified Cart Synchronization Logic ---
            let activeBookingIds = [];

            function syncCartState() {
                if (window.cartStore && window.cartStore.booking && window.cartStore.booking.ids.length > 0) {
                    activeBookingIds = (window.cartStore.booking.ids || []).map(Number);
                    applyButtonStates();
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.cart.all-state') }}",
                    method: 'GET',
                    success: function(data) {
                        if (data && data.booking) {
                            if (window.cartStore) {
                                if (!window.cartStore.booking.items.length) window.cartStore.booking = data.booking;
                            }
                            activeBookingIds = (data.booking.ids || []).map(Number);
                            if (window.updateGlobalCartBadges) {
                                window.updateGlobalCartBadges(data.booking.count, data.request ? data.request.count : undefined);
                            }
                            applyButtonStates();
                        }
                    }
                });
            }

            function applyButtonStates() {
                const bIds = (window.cartStore && window.cartStore.booking && window.cartStore.booking.ids) ? window.cartStore.booking.ids.map(Number) : activeBookingIds;

                $('.add-to-basket').each(function() {
                    const id = Number($(this).data('id'));
                    if (bIds.includes(id)) {
                        $(this).addClass('added').html('<i class="fas fa-check"></i>').attr('title', 'Added to Procurement Basket');
                    } else {
                        $(this).removeClass('added').html('<i class="fas fa-shopping-basket"></i>').attr('title', 'Add to Procurement Basket');
                    }
                });
            }

            window.reapplyCartButtonStates = applyButtonStates;

            // Initial UI sync
            syncCartState();

            // Add/Toggle Procurement Basket Click (Single Item)
            $(document).on('click', '.add-to-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $btn = $(this);
                if ($btn.data('is-busy')) return;
                $btn.data('is-busy', true);

                const productId = Number($btn.data('id'));
                if (!productId) {
                    $btn.data('is-busy', false);
                    return;
                }

                const isAlreadyAdded = $btn.hasClass('added');
                const desiredAction = isAlreadyAdded ? 'remove' : 'add';

                // Optimistic UI update
                if (isAlreadyAdded) {
                    $btn.removeClass('added').html('<i class="fas fa-shopping-basket"></i>');
                    if (window.toastr) toastr.info('Removed from Procurement basket');

                    if (window.cartStore && window.cartStore.booking) {
                        window.cartStore.booking.ids = window.cartStore.booking.ids.filter(id => Number(id) !== productId);
                        window.cartStore.booking.items = window.cartStore.booking.items.filter(i => Number(i.product_id) !== productId);
                        window.cartStore.booking.count = window.cartStore.booking.items.length;
                        if (window.updateGlobalCartBadges) {
                            window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                        }
                    }
                } else {
                    $btn.addClass('added').html('<i class="fas fa-check"></i>');
                    if (window.toastr) toastr.success('Added to Procurement basket');

                    if (window.cartStore && window.cartStore.booking) {
                        if (!window.cartStore.booking.ids.map(Number).includes(productId)) {
                            window.cartStore.booking.ids.push(productId);
                        }
                        const exists = window.cartStore.booking.items.some(i => Number(i.product_id) === productId);
                        if (!exists) {
                            window.cartStore.booking.items.push({
                                id: `temp_${productId}`,
                                cart_id: `temp_${productId}`,
                                product_id: productId,
                                variant_id: null,
                                variant_name: '',
                                product_name: $btn.closest('tr').find('td:nth-child(3)').text().trim() || 'Product',
                                thumb_image: $btn.closest('tr').find('img').attr('src') || "{{ asset('uploads/no-image.svg') }}",
                                vendor_name: 'Primary Supplier',
                                sku: '',
                                price: 0,
                                quantity: 1
                            });
                        }
                        window.cartStore.booking.count = window.cartStore.booking.items.length;
                        if (window.updateGlobalCartBadges) {
                            window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                        }
                    }
                }

                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        product_id: productId,
                        cart_type: 'booking',
                        action: desiredAction
                    },
                    success: function(response) {
                        if (response.success && response.item && window.cartStore && window.cartStore.booking) {
                            const idx = window.cartStore.booking.items.findIndex(i => Number(i.product_id) === productId && !i.variant_id);
                            if (idx !== -1) {
                                window.cartStore.booking.items[idx] = response.item;
                            }
                        }
                        applyButtonStates();
                    },
                    error: function(xhr) {
                        if (isAlreadyAdded) {
                            $btn.addClass('added').html('<i class="fas fa-check"></i>');
                        } else {
                            $btn.removeClass('added').html('<i class="fas fa-shopping-basket"></i>');
                        }
                        if (window.toastr) toastr.error('Error updating basket');
                    },
                    complete: function() {
                        $btn.data('is-busy', false);
                    }
                });
            });

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
                    if (ids.indexOf(Number($(this).val())) !== -1 || ids.indexOf($(this).val().toString()) !== -1) {
                        $(this).prop('checked', true);
                    }
                });
                $('#select_all').prop('checked', $('.product-checkbox:checked').length === $('.product-checkbox').length && $('.product-checkbox').length > 0);
                updatePersistedUI();
            })();

            // Select All checkbox
            $('#select_all').on('change', function() {
                const checked = $(this).is(':checked');
                let ids = getPersistedIds();
                const visibleIds = [];
                $('.product-checkbox').each(function() {
                    const vid = Number($(this).val());
                    visibleIds.push(vid);
                    $(this).prop('checked', checked);
                });
                if (checked) {
                    visibleIds.forEach(function(vid) {
                        if (ids.indexOf(vid) === -1) ids.push(vid);
                    });
                } else {
                    ids = ids.filter(function(id) { return visibleIds.indexOf(Number(id)) === -1; });
                }
                savePersistedIds(ids);
                updatePersistedUI();
            });

            // Individual checkbox change
            $(document).on('change', '.product-checkbox', function() {
                let ids = getPersistedIds();
                const id = Number($(this).val());
                if ($(this).is(':checked')) {
                    if (ids.indexOf(id) === -1) ids.push(id);
                } else {
                    ids = ids.filter(function(i) { return Number(i) !== id; });
                }
                savePersistedIds(ids);
                updatePersistedUI();
                $('#select_all').prop('checked', $('.product-checkbox:checked').length === $('.product-checkbox').length && $('.product-checkbox').length > 0);
            });

            // Bulk Add to Procurement Basket
            $('#add_to_booking_btn').on('click', function() {
                const ids = getPersistedIds().map(Number);
                if (ids.length === 0) {
                    if (window.toastr) toastr.warning('Please select at least one product');
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Adding...');

                $.ajax({
                    url: "{{ route('admin.cart.bulk-add-products') }}",
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        product_ids: ids,
                        cart_type: 'booking'
                    },
                    success: function(res) {
                        if (res.success) {
                            if (window.toastr) toastr.success(res.message);
                            if (window.cartStore && window.cartStore.booking) {
                                (res.product_ids || []).forEach(function(pid) {
                                    if (!window.cartStore.booking.ids.includes(pid)) {
                                        window.cartStore.booking.ids.push(pid);
                                    }
                                });
                                window.cartStore.booking.count = res.count;
                                if (window.updateGlobalCartBadges) {
                                    window.updateGlobalCartBadges(res.count, undefined);
                                }
                            }
                            applyButtonStates();

                            // Clear selections
                            localStorage.removeItem(STORAGE_KEY);
                            $('.product-checkbox').prop('checked', false);
                            $('#select_all').prop('checked', false);
                            updatePersistedUI();
                        }
                    },
                    error: function() {
                        if (window.toastr) toastr.error('Failed to add products to cart');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-shopping-basket mr-1"></i> Add to Procurement Cart (<span id="selected_count">0</span>)');
                    }
                });
            });
        });
    </script>
@endpush
