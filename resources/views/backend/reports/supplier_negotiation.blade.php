@extends('backend.layouts.master')
@section('title', 'Supplier Negotiation & Landed Cost Intelligence')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-handshake mr-2 text-primary"></i>Supplier Negotiation & Landed Cost Intelligence</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Supplier Negotiation</div>
            </div>
        </div>

        <div class="section-body">

            {{-- 4 Executive KPI Metric Cards --}}
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-icon bg-success" style="border-radius: 10px;">
                            <i class="fas fa-arrow-down text-white"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Lowest Historical Cost</h4></div>
                            <div class="card-body font-weight-bold text-success" id="kpi_lowest_cost" style="font-size: 1.3rem;">
                                kr. {{ number_format($negotiationMetrics['lowest_cost'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-icon bg-danger" style="border-radius: 10px;">
                            <i class="fas fa-arrow-up text-white"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Highest Historical Cost</h4></div>
                            <div class="card-body font-weight-bold text-danger" id="kpi_highest_cost" style="font-size: 1.3rem;">
                                kr. {{ number_format($negotiationMetrics['highest_cost'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-icon bg-info" style="border-radius: 10px;">
                            <i class="fas fa-balance-scale text-white"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Weighted Average (WAC)</h4></div>
                            <div class="card-body font-weight-bold text-info" id="kpi_weighted_avg_cost" style="font-size: 1.3rem;">
                                kr. {{ number_format($negotiationMetrics['weighted_avg_cost'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-icon bg-primary" style="border-radius: 10px;">
                            <i class="fas fa-history text-white"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Latest Replacement Cost</h4></div>
                            <div class="card-body font-weight-bold text-primary" style="font-size: 1.3rem;">
                                <span id="kpi_latest_cost">kr. {{ number_format($negotiationMetrics['latest_cost'], 2) }}</span>
                                <span id="kpi_trend_wrap">
                                    @if($negotiationMetrics['trend_percent'] !== null)
                                        <small class="d-block mt-0 font-weight-semibold {{ $negotiationMetrics['trend_percent'] > 0 ? 'text-danger' : 'text-success' }}" style="font-size: 11px;">
                                            {{ $negotiationMetrics['trend_percent'] > 0 ? '▲ +' : '▼ ' }}{{ $negotiationMetrics['trend_percent'] }}% vs prev
                                        </small>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Form --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-filter mr-2 text-primary"></i>Negotiation Filter Scope</h6>
                    <span id="kpi_shipment_count_badge" class="badge badge-light border text-muted px-2.5 py-1.5" style="font-size: 11px;">
                        {{ number_format($negotiationMetrics['shipment_count']) }} Historical Shipments Analyzed
                    </span>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.supplier-negotiation') }}" id="negotiationFilterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="font-weight-bold small text-dark">Category</label>
                                <select name="category_id" id="filter_category_id" class="form-control select2">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}" {{ $selectedCategoryId == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold small text-dark">Product</label>
                                <select name="product_id" id="filter_product_id" class="form-control select2">
                                    <option value="">All Products</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" {{ $selectedProductId == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }} ({{ $p->product_number ?? $p->sku ?? 'PROD-'.$p->id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="font-weight-bold small text-dark">Supplier / Vendor</label>
                                <select name="vendor_id" id="filter_vendor_id" class="form-control select2">
                                    <option value="">All Suppliers</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}" {{ $selectedVendorId == $v->id ? 'selected' : '' }}>
                                            {{ $v->shop_name ?? $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btn-reset-negotiation-filter" class="btn btn-outline-secondary btn-block font-weight-bold py-2" style="border-radius: 8px;">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Procurement Insight Alert --}}
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 12px; background: #eff6ff; color: #1e40af; border-left: 5px solid #2563eb !important;">
                <i class="fas fa-lightbulb fa-2x mr-3 text-primary"></i>
                <div>
                    <h6 class="mb-1 font-weight-bold text-primary">Procurement Counter-Offer Guidance</h6>
                    <span id="guidance_text_content" class="small">
                        Based on historical shipments, your optimal negotiation target is between 
                        <strong>kr. {{ number_format($negotiationMetrics['lowest_cost'], 2) }}</strong> (lowest achieved) and 
                        <strong>kr. {{ number_format($negotiationMetrics['weighted_avg_cost'], 2) }}</strong> (volume weighted average). 
                        Total historical procurement spend for this selection is <strong>kr. {{ number_format($negotiationMetrics['total_spend'], 2) }}</strong> across <strong>{{ number_format($negotiationMetrics['total_units']) }} units</strong>.
                    </span>
                </div>
            </div>

            {{-- Historical Shipments Timeline Table Wrapper (AJAX Target) --}}
            <div id="shipments-table-wrapper">
                @include('backend.reports.partials.supplier_negotiation_table', ['shipments' => $shipments, 'negotiationMetrics' => $negotiationMetrics])
            </div>

        </div>
    </section>

    {{-- Shipment & Landed Cost Intelligence Modal --}}
    <div class="modal fade" id="shipmentDetailModal" tabindex="-1" role="dialog" aria-labelledby="shipmentDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="shipmentDetailModalLabel">
                        <i class="fas fa-file-contract mr-2 text-primary"></i>Procurement PO & Shipment Intelligence
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    {{-- Quick Metadata Bar --}}
                    <div class="d-flex justify-content-between align-items-center flex-wrap p-3 mb-4 rounded border bg-light" style="gap: 10px;">
                        <div>
                            <small class="text-muted text-uppercase d-block font-weight-bold" style="font-size: 11px;">PO / Invoice No</small>
                            <span class="font-weight-bold text-dark h6 mb-0" id="modal_po_no">—</span>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase d-block font-weight-bold" style="font-size: 11px;">Receipt Date</small>
                            <span class="font-weight-bold text-dark" id="modal_date">—</span>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase d-block font-weight-bold" style="font-size: 11px;">Status</small>
                            <span class="badge badge-success px-2.5 py-1 font-weight-bold" id="modal_status">Completed</span>
                        </div>
                    </div>

                    {{-- Product & Supplier Cards --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="p-3 rounded border h-100 bg-white">
                                <small class="text-muted text-uppercase font-weight-bold d-block mb-1" style="font-size: 11px;">
                                    <i class="fas fa-boxes mr-1 text-primary"></i> Product Received
                                </small>
                                <strong class="text-dark d-block" id="modal_product" style="font-size: 14px;">—</strong>
                                <span class="badge badge-light border mt-1" id="modal_variant_wrap" style="display: none;">
                                    Variant: <span id="modal_variant"></span>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded border h-100 bg-white">
                                <small class="text-muted text-uppercase font-weight-bold d-block mb-1" style="font-size: 11px;">
                                    <i class="fas fa-store mr-1 text-primary"></i> Preferred Supplier
                                </small>
                                <strong class="text-dark d-block" id="modal_vendor" style="font-size: 14px;">—</strong>
                                <small class="text-muted">Direct Vendor Shipment Record</small>
                            </div>
                        </div>
                    </div>

                    {{-- Cost & Landed Intelligence Breakdown --}}
                    <h6 class="font-weight-bold text-dark mb-3">
                        <i class="fas fa-calculator mr-2 text-primary"></i>Landed Cost Breakdown per Unit
                    </h6>
                    <div class="table-responsive rounded border bg-light mb-4">
                        <table class="table table-bordered mb-0 bg-white">
                            <thead class="thead-light" style="font-size: 11px; text-transform: uppercase;">
                                <tr>
                                    <th>Quantity</th>
                                    <th>Base Unit Cost</th>
                                    <th>Freight / Transport</th>
                                    <th>Tax / Tariffs</th>
                                    <th class="text-primary font-weight-bold">Effective Landed Cost</th>
                                    <th class="text-dark font-weight-bold">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="font-weight-bold">
                                    <td id="modal_qty">—</td>
                                    <td class="text-muted" id="modal_unit_cost">—</td>
                                    <td class="text-muted" id="modal_transport_cost">—</td>
                                    <td class="text-muted" id="modal_tax_cost">—</td>
                                    <td class="text-primary h6 mb-0" id="modal_landed_cost">—</td>
                                    <td class="text-dark h6 mb-0" id="modal_total">—</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Strategic Negotiation Tip --}}
                    <div class="p-3 rounded bg-light border-left border-primary" style="border-left-width: 4px !important;">
                        <small class="text-dark font-weight-semibold">
                            <i class="fas fa-info-circle mr-1 text-primary"></i>
                            <strong>Negotiation Note:</strong> Compare this unit landed cost against your <strong>WAC (Weighted Average Cost)</strong> shown on the executive cards above to formulate counter-offers for volume reorders.
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 8px;">
                        Close
                    </button>
                    <a href="#" id="modal_full_purchase_link" target="_blank" class="btn btn-primary px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">
                        <i class="fas fa-file-contract mr-1.5"></i> Open PO in Procurement
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var ajaxReq = null;

        if ($.fn.tooltip) {
            $('body').tooltip({
                selector: '[data-toggle="tooltip"]',
                container: 'body'
            });
        }

        function fetchNegotiationData(overrideParams) {
            var form = $('#negotiationFilterForm');
            var url = form.attr('action');
            var formData = form.serializeArray();
            var paramsObj = {};

            formData.forEach(function(item) {
                if (item.value !== '') {
                    paramsObj[item.name] = item.value;
                }
            });

            if (overrideParams) {
                for (var key in overrideParams) {
                    if (overrideParams[key] !== undefined && overrideParams[key] !== null && overrideParams[key] !== '') {
                        paramsObj[key] = overrideParams[key];
                    } else if (overrideParams[key] === '') {
                        delete paramsObj[key];
                    }
                }
            }

            var queryString = $.param(paramsObj);

            // Show table loading overlay
            $('.table-loading-overlay').css('display', 'flex');

            if (ajaxReq && ajaxReq.readyState !== 4) {
                ajaxReq.abort();
            }

            ajaxReq = $.ajax({
                url: url,
                type: 'GET',
                data: queryString,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(res) {
                    // 1. Update shipments table & pagination
                    $('#shipments-table-wrapper').html(res.table_html);

                    // 2. Update KPI metric cards
                    if (res.metrics) {
                        $('#kpi_lowest_cost').text('kr. ' + res.metrics.lowest_cost);
                        $('#kpi_highest_cost').text('kr. ' + res.metrics.highest_cost);
                        $('#kpi_weighted_avg_cost').text('kr. ' + res.metrics.weighted_avg_cost);
                        $('#kpi_latest_cost').text('kr. ' + res.metrics.latest_cost);
                        $('#kpi_shipment_count_badge').text(res.metrics.shipment_count + ' Historical Shipments Analyzed');

                        // Trend indicator
                        if (res.metrics.trend_percent !== null && res.metrics.trend_percent !== undefined) {
                            var isUp = res.metrics.trend_percent > 0;
                            var trendText = (isUp ? '▲ +' : '▼ ') + res.metrics.trend_percent + '% vs prev';
                            var trendClass = isUp ? 'text-danger' : 'text-success';
                            $('#kpi_trend_wrap').html('<small class="d-block mt-0 font-weight-semibold ' + trendClass + '" style="font-size: 11px;">' + trendText + '</small>').show();
                        } else {
                            $('#kpi_trend_wrap').empty().hide();
                        }

                        // Guidance alert text
                        var guidanceHtml = 'Based on historical shipments, your optimal negotiation target is between ' +
                            '<strong>kr. ' + res.metrics.lowest_cost + '</strong> (lowest achieved) and ' +
                            '<strong>kr. ' + res.metrics.weighted_avg_cost + '</strong> (volume weighted average). ' +
                            'Total historical procurement spend for this selection is <strong>kr. ' + res.metrics.total_spend + '</strong> across <strong>' + res.metrics.total_units + ' units</strong>.';
                        $('#guidance_text_content').html(guidanceHtml);
                    }

                    // 3. Update Product dropdown if products list returned and category was altered
                    if (res.products && overrideParams && ('category_id' in overrideParams || 'reset' in overrideParams)) {
                        updateProductDropdown(res.products, res.selected_product_id);
                    }

                    // 4. Update browser URL without reloading page
                    var newUrl = url + (queryString ? '?' + queryString : '');
                    if (window.history && window.history.pushState) {
                        window.history.pushState({ path: newUrl }, '', newUrl);
                    }
                },
                error: function(xhr) {
                    if (xhr.statusText !== 'abort') {
                        console.error('AJAX Error filtering supplier negotiation:', xhr);
                    }
                },
                complete: function() {
                    $('.table-loading-overlay').hide();
                }
            });
        }

        function updateProductDropdown(products, selectedProductId) {
            var $productSelect = $('#filter_product_id');
            var currentVal = selectedProductId !== undefined ? selectedProductId : $productSelect.val();

            $productSelect.empty();
            $productSelect.append(new Option('All Products', ''));

            if (Array.isArray(products)) {
                products.forEach(function(p) {
                    var text = p.name + ' (' + p.code + ')';
                    var isSelected = (currentVal && String(currentVal) === String(p.id));
                    var opt = new Option(text, p.id, isSelected, isSelected);
                    $productSelect.append(opt);
                });
            }

            if ($.fn.select2) {
                $productSelect.trigger('change.select2');
            }
        }

        // Category change: fetches new products and updates table without reload
        $('#filter_category_id').on('change', function() {
            var catId = $(this).val();
            fetchNegotiationData({ category_id: catId, product_id: '', page: 1 });
        });

        // Product and Vendor changes: updates table & KPIs without reload
        $('#filter_product_id, #filter_vendor_id').on('change', function() {
            fetchNegotiationData({ page: 1 });
        });

        // AJAX pagination: page clicks inside the table without reload
        $(document).on('click', '#shipments-table-wrapper .pagination a', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if (!href) return;
            var urlObj = new URL(href, window.location.origin);
            var page = urlObj.searchParams.get('page') || 1;
            fetchNegotiationData({ page: page });
        });

        // Reset button: resets filters without reload
        $('#btn-reset-negotiation-filter').on('click', function(e) {
            e.preventDefault();
            $('#filter_category_id').val('');
            $('#filter_product_id').val('');
            $('#filter_vendor_id').val('');

            if ($.fn.select2) {
                $('#filter_category_id, #filter_product_id, #filter_vendor_id').trigger('change.select2');
            }

            fetchNegotiationData({ category_id: '', product_id: '', vendor_id: '', page: 1, reset: true });
        });

        // Prevent standard form submission
        $('#negotiationFilterForm').on('submit', function(e) {
            e.preventDefault();
            fetchNegotiationData({ page: 1 });
        });

        // Modal Trigger for In-page Shipment Intelligence (no page redirects)
        $(document).on('click', '.view-shipment-detail-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $btn = $(this);

            var poNo = $btn.data('po-no') || '—';
            var date = $btn.data('date') || '—';
            var vendor = $btn.data('vendor') || '—';
            var product = $btn.data('product') || '—';
            var variant = $btn.data('variant') || '';
            var qty = $btn.data('qty') || '—';
            var unitCost = $btn.data('unit-cost') || '—';
            var transportCost = $btn.data('transport-cost') || 'kr. 0.00';
            var taxCost = $btn.data('tax-cost') || 'kr. 0.00';
            var landedCost = $btn.data('landed-cost') || '—';
            var total = $btn.data('total') || '—';
            var status = $btn.data('status') || 'Completed';
            var url = $btn.data('url') || '#';

            $('#modal_po_no').text(poNo);
            $('#modal_date').text(date);
            $('#modal_status').text(status);
            $('#modal_vendor').text(vendor);
            $('#modal_product').text(product);

            if (variant && variant.trim() !== '') {
                $('#modal_variant').text(variant);
                $('#modal_variant_wrap').show();
            } else {
                $('#modal_variant_wrap').hide();
            }

            $('#modal_qty').text(qty);
            $('#modal_unit_cost').text(unitCost);
            $('#modal_transport_cost').text(transportCost);
            $('#modal_tax_cost').text(taxCost);
            $('#modal_landed_cost').text(landedCost);
            $('#modal_total').text(total);

            if (url && url !== '#' && url.length > 5) {
                $('#modal_full_purchase_link').attr('href', url).show();
            } else {
                $('#modal_full_purchase_link').hide();
            }

            $('#shipmentDetailModal').modal('show');
        });
    });
</script>
@endpush
