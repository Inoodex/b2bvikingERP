@extends('backend.layouts.master')
@section('title', 'Inventory Reorder Risk & PO Pipeline')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-exclamation-triangle mr-2 text-danger"></i>Inventory Reorder Risk & PO Pipeline</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Reorder Risk</div>
            </div>
        </div>

        <div class="section-body">

            {{-- Summary KPI Cards --}}
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-icon bg-danger" style="border-radius: 10px;">
                            <i class="fas fa-ban text-white"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Stockout & NOT Ordered</h4></div>
                            <div class="card-body font-weight-bold text-danger" style="font-size: 1.4rem;">
                                {{ number_format($totalStockoutsUnordered) }} items
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-icon bg-primary" style="border-radius: 10px;">
                            <i class="fas fa-truck-loading text-white"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Active POs in Pipeline</h4></div>
                            <div class="card-body font-weight-bold text-primary" style="font-size: 1.4rem;">
                                {{ number_format($totalActivePOsCount) }} orders
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-icon bg-success" style="border-radius: 10px;">
                            <i class="fas fa-boxes text-white"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Incoming Units on Order</h4></div>
                            <div class="card-body font-weight-bold text-success" style="font-size: 1.4rem;">
                                {{ number_format($totalUnitsInPipeline) }} pcs
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter & Tab Scope --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                    <ul class="nav nav-pills" id="riskTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold {{ $statusFilter === 'all' ? 'active' : '' }}" href="{{ route('admin.reports.reorder-risk', ['status_filter' => 'all']) }}" style="border-radius: 8px;">
                                <i class="fas fa-layer-group mr-1.5"></i> All Inventory Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold {{ $statusFilter === 'out_of_stock_not_ordered' ? 'active bg-danger text-white' : 'text-danger' }}" href="{{ route('admin.reports.reorder-risk', ['status_filter' => 'out_of_stock_not_ordered']) }}" style="border-radius: 8px;">
                                <i class="fas fa-exclamation-circle mr-1.5"></i> ⚠️ Out of Stock & NOT Ordered
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold {{ $statusFilter === 'on_order' ? 'active' : '' }}" href="{{ route('admin.reports.reorder-risk', ['status_filter' => 'on_order']) }}" style="border-radius: 8px;">
                                <i class="fas fa-shipping-fast mr-1.5"></i> 🚚 On Order / In Pipeline
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.reorder-risk') }}">
                        <input type="hidden" name="status_filter" value="{{ $statusFilter }}">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="font-weight-bold small text-dark">Category</label>
                                <select name="category_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="font-weight-bold small text-dark">Supplier / Vendor</label>
                                <select name="vendor_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>
                                            {{ $v->shop_name ?? $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="font-weight-bold small text-dark">Search Product</label>
                                <input type="text" name="search" class="form-control" placeholder="Search name, code, sku..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('admin.reports.reorder-risk') }}" class="btn btn-outline-secondary btn-block font-weight-bold py-2" style="border-radius: 8px;">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-boxes mr-2 text-primary"></i>Inventory Status & Pipeline Tracking</h6>
                    <span class="badge badge-light border text-muted px-2.5 py-1.5" style="font-size: 11px;">
                        Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} items
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100">
                            <thead class="bg-light" style="font-size: 0.75rem; text-transform: uppercase; color: #475569;">
                                <tr>
                                    <th class="pl-4 py-3">Product Name & Code</th>
                                    <th class="py-3">Category</th>
                                    <th class="py-3">Preferred Supplier</th>
                                    <th class="text-center py-3">Warehouse Stock</th>
                                    <th class="py-3">Pipeline Status / Milestone</th>
                                    <th class="text-center pr-4 py-3" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $noImg = asset('uploads/no-image.svg');
                                @endphp
                                @forelse($products as $p)
                                    @php
                                        $stock = (float) ($p->inventory_stocks_sum_quantity ?? 0);
                                        $activePOs = $p->activePurchaseDetails;
                                        $hasActivePO = $activePOs && $activePOs->isNotEmpty();
                                        $isStockoutNoOrder = ($stock <= 0 && !$hasActivePO);
                                        $imgSrc = (!empty($p->thumb_image) && $p->thumb_image !== 'null') ? asset(ltrim($p->thumb_image, '/')) : $noImg;
                                    @endphp
                                    <tr style="{{ $isStockoutNoOrder ? 'background-color: #fef2f2;' : '' }}">
                                        <td class="pl-4">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $imgSrc }}" onerror="this.onerror=null;this.src='{{ $noImg }}';" alt="" style="width: 38px; height: 38px; object-fit: cover; border-radius: 6px;" class="mr-2.5 border">
                                                <div>
                                                    <strong class="text-dark d-block">{{ $p->name }}</strong>
                                                    <small class="text-muted">{{ $p->product_number ?? $p->sku ?? ('PROD-'.$p->id) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border">{{ $p->category?->name ?? 'General' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-dark font-weight-semibold">{{ $p->vendor?->shop_name ?? ($p->vendor?->name ?? 'Default Vendor') }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($stock <= 0)
                                                <span class="badge badge-danger px-2.5 py-1 font-weight-bold" style="font-size: 11px;">0 pcs (Out of Stock)</span>
                                            @elseif($stock <= ($p->min_inventory_qty ?? 10))
                                                <span class="badge badge-warning text-dark px-2.5 py-1 font-weight-bold" style="font-size: 11px;">{{ number_format($stock) }} pcs (Low)</span>
                                            @else
                                                <span class="badge badge-success px-2.5 py-1 font-weight-bold" style="font-size: 11px;">{{ number_format($stock) }} pcs</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hasActivePO)
                                                @foreach($activePOs as $apd)
                                                    @php
                                                        $purchase = $apd->purchase;
                                                        $milestone = $purchase?->milestone_status ?? 'ordered';
                                                        $firstShipment = $purchase?->shipments?->first();
                                                        $eta = $firstShipment?->estimated_delivery_date ? \Carbon\Carbon::parse($firstShipment->estimated_delivery_date)->format('M d') : null;
                                                    @endphp
                                                    <div class="d-inline-block mr-1 mb-1">
                                                        @if($milestone === 'shipped')
                                                            <span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                                                🚢 Shipped {{ $eta ? '(ETA: '.$eta.')' : '' }}
                                                            </span>
                                                        @elseif($milestone === 'goods_partial')
                                                            <span class="badge badge-info px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                                                📦 Goods Partial
                                                            </span>
                                                        @elseif($milestone === 'in_production')
                                                            <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                                                ⚙️ In Production
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                                                📄 On Order (#{{ $purchase?->purchase_no ?? $purchase?->id }})
                                                            </span>
                                                        @endif
                                                        <small class="text-muted d-block" style="font-size: 10px;">{{ number_format($apd->qty) }} pcs on PO</small>
                                                    </div>
                                                @endforeach
                                            @elseif($stock <= 0)
                                                <span class="badge badge-danger px-2.5 py-1.5 font-weight-bold shadow-sm" style="font-size: 11px; background: #ef4444; border-radius: 6px;">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i> Out of Stock • NOT Ordered
                                                </span>
                                            @else
                                                <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                                                    ✅ Normal Warehouse Stock
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center pr-4">
                                            <button type="button" 
                                                    class="btn btn-outline-warning btn-sm add-to-basket font-weight-bold shadow-sm" 
                                                    data-id="{{ $p->id }}" 
                                                    data-product-name="{{ $p->name }}"
                                                    data-product-img="{{ $imgSrc }}"
                                                    data-vendor-name="{{ $p->vendor?->shop_name ?? ($p->vendor?->name ?? 'Primary Supplier') }}"
                                                    data-sku="{{ $p->sku ?? ($p->product_number ?? '') }}"
                                                    title="Add to Procurement Basket" 
                                                    style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;">
                                                <i class="fas fa-shopping-basket"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No items matching the selected inventory risk filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 border-top">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                        <div class="text-muted" style="font-size: 13.5px; color: #64748b;">
                            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ number_format($products->total()) }} entries
                        </div>
                        <div>
                            {{ $products->appends(request()->query())->links('vendor.pagination.custom-report') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Reapply button state based on cartStore
        function applyButtonStates() {
            var bookingIds = (window.cartStore && window.cartStore.booking && window.cartStore.booking.ids) 
                ? window.cartStore.booking.ids.map(Number) 
                : [];

            $('.add-to-basket').each(function() {
                var pid = Number($(this).data('id'));
                if (bookingIds.includes(pid)) {
                    $(this).addClass('added btn-success text-white')
                           .removeClass('btn-outline-warning')
                           .html('<i class="fas fa-check"></i>')
                           .attr('title', 'Added to Procurement Basket');
                } else {
                    $(this).removeClass('added btn-success text-white')
                           .addClass('btn-outline-warning')
                           .html('<i class="fas fa-shopping-basket"></i>')
                           .attr('title', 'Add to Procurement Basket');
                }
            });
        }

        // Expose globally so cart_drawer.blade.php can trigger it on item remove/clear
        window.reapplyCartButtonStates = applyButtonStates;

        // Initial sync of basket states and full items from server
        if (window.fetch) {
            fetch("{{ route('admin.cart.all-state') }}")
                .then(function(res) { return res.json(); })
                .then(function(res) {
                    if (res && res.booking) {
                        if (window.cartStore) {
                            window.cartStore.booking = res.booking;
                        }
                        if (window.updateGlobalCartBadges) {
                            window.updateGlobalCartBadges(res.booking.count, undefined);
                        }
                        applyButtonStates();
                    }
                })
                .catch(function() {
                    $.get("{{ route('admin.cart.product-ids') }}", { cart_type: 'booking' }, function(res) {
                        if (res && res.ids && window.cartStore && window.cartStore.booking) {
                            window.cartStore.booking.ids = res.ids.map(Number);
                            applyButtonStates();
                        }
                    });
                });
        } else {
            $.get("{{ route('admin.cart.product-ids') }}", { cart_type: 'booking' }, function(res) {
                if (res && res.ids && window.cartStore && window.cartStore.booking) {
                    window.cartStore.booking.ids = res.ids.map(Number);
                    applyButtonStates();
                }
            });
        }

        // Toggle add to procurement basket
        $(document).on('click', '.add-to-basket', function(e) {
            e.preventDefault();
            var $btn = $(this);
            if ($btn.data('is-busy')) return;
            $btn.data('is-busy', true);

            var productId = Number($btn.data('id'));
            var productName = $btn.data('product-name') || 'Product';
            var productImg = $btn.data('product-img') || "{{ asset('uploads/no-image.svg') }}";
            var vendorName = $btn.data('vendor-name') || 'Primary Supplier';
            var sku = $btn.data('sku') || '';

            var isAlreadyAdded = $btn.hasClass('added');
            var desiredAction = isAlreadyAdded ? 'remove' : 'add';

            // Optimistic Store & UI Update (0ms instant response)
            if (isAlreadyAdded) {
                $btn.removeClass('added btn-success text-white')
                    .addClass('btn-outline-warning')
                    .html('<i class="fas fa-shopping-basket"></i>')
                    .attr('title', 'Add to Procurement Basket');

                if (window.cartStore && window.cartStore.booking) {
                    window.cartStore.booking.ids = window.cartStore.booking.ids.filter(function(id) { return Number(id) !== productId; });
                    window.cartStore.booking.items = window.cartStore.booking.items.filter(function(i) { return Number(i.product_id) !== productId; });
                    window.cartStore.booking.count = window.cartStore.booking.items.length;
                    if (window.updateGlobalCartBadges) {
                        window.updateGlobalCartBadges(window.cartStore.booking.count, undefined);
                    }
                }
            } else {
                $btn.addClass('added btn-success text-white')
                    .removeClass('btn-outline-warning')
                    .html('<i class="fas fa-check"></i>')
                    .attr('title', 'Added to Procurement Basket');

                if (window.cartStore && window.cartStore.booking) {
                    if (!window.cartStore.booking.ids.map(Number).includes(productId)) {
                        window.cartStore.booking.ids.push(productId);
                    }
                    var exists = window.cartStore.booking.items.some(function(i) { return Number(i.product_id) === productId && !i.variant_id; });
                    if (!exists) {
                        window.cartStore.booking.items.push({
                            id: 'temp_' + productId,
                            cart_id: 'temp_' + productId,
                            product_id: productId,
                            variant_id: null,
                            variant_name: '',
                            product_name: productName,
                            thumb_image: productImg,
                            vendor_name: vendorName,
                            sku: sku,
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
                    action: desiredAction,
                    quantity: 1
                },
                success: function(response) {
                    $btn.data('is-busy', false);
                    if (response.success) {
                        if (response.action === 'added' && response.item && window.cartStore && window.cartStore.booking) {
                            var idx = window.cartStore.booking.items.findIndex(function(i) { return Number(i.product_id) === productId && !i.variant_id; });
                            if (idx !== -1) {
                                window.cartStore.booking.items[idx] = response.item;
                            } else {
                                window.cartStore.booking.items.push(response.item);
                            }
                        }

                        if (window.toastr) {
                            if (response.action === 'added') {
                                toastr.success(response.message || 'Added to Procurement basket');
                            } else {
                                toastr.info(response.message || 'Removed from Procurement basket');
                            }
                        }

                        if (window.updateGlobalCartBadges && response.count !== undefined) {
                            window.updateGlobalCartBadges(response.count, undefined);
                        }
                    }
                    applyButtonStates();
                },
                error: function(xhr) {
                    $btn.data('is-busy', false);
                    if (window.toastr) toastr.error('Failed to update procurement basket');
                    if (window.fetch) {
                        fetch("{{ route('admin.cart.all-state') }}")
                            .then(function(res) { return res.json(); })
                            .then(function(res) {
                                if (res && res.booking && window.cartStore) {
                                    window.cartStore.booking = res.booking;
                                    applyButtonStates();
                                }
                            });
                    }
                }
            });
        });
    });
</script>
@endpush
