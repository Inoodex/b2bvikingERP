@extends('backend.layouts.master')

@section('title')
    Product Reviews & Ratings
@endsection

@push('styles')
<style>
    /* KPI Summary Cards */
    .kpi-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 1.2rem 1.4rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    /* Modern Filter Suite Toolbar */
    .filter-suite-card {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 20px;
    }
    .filter-toolbar-row {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }
    .filter-field-label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        letter-spacing: 0.3px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        white-space: nowrap;
    }
    .filter-icon {
        margin-right: 6px !important;
        font-size: 13px;
    }
    .filter-item-category {
        flex: 2.2;
        min-width: 190px;
    }
    .filter-item-product {
        flex: 2.7;
        min-width: 220px;
    }
    .filter-item-rating {
        flex: 2.3;
        min-width: 190px;
    }
    .filter-item-status {
        flex: 1.9;
        min-width: 170px;
    }
    .filter-item-reset {
        flex: 0 0 40px;
        width: 40px;
    }
    .modern-filter-select {
        height: 40px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        color: #1e293b !important;
        background-color: #ffffff !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    /* Reset Filter Icon Only Button (Red Theme & Bulletproof Hover) */
    .btn-modern-reset-icon,
    button#btn-reset-filters.btn-modern-reset-icon {
        height: 40px !important;
        width: 40px !important;
        border-radius: 8px !important;
        border: 1px solid #fecaca !important;
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
        font-size: 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        cursor: pointer !important;
        padding: 0 !important;
        outline: none !important;
    }
    .btn-modern-reset-icon i,
    button#btn-reset-filters.btn-modern-reset-icon i {
        color: #dc2626 !important;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s ease !important;
        display: inline-block !important;
    }
    .btn-modern-reset-icon:hover,
    button#btn-reset-filters.btn-modern-reset-icon:hover,
    .btn-modern-reset-icon:focus,
    button#btn-reset-filters.btn-modern-reset-icon:focus {
        background-color: #dc2626 !important;
        border-color: #b91c1c !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35) !important;
    }
    .btn-modern-reset-icon:hover i,
    button#btn-reset-filters.btn-modern-reset-icon:hover i,
    .btn-modern-reset-icon:focus i,
    button#btn-reset-filters.btn-modern-reset-icon:focus i {
        color: #ffffff !important;
        transform: rotate(-180deg) !important;
    }
    .btn-modern-reset-icon:active,
    button#btn-reset-filters.btn-modern-reset-icon:active {
        background-color: #991b1b !important;
        border-color: #7f1d1d !important;
        transform: scale(0.94) !important;
    }

    /* DataTable Table Polish & Strict Column Width Control */
    #reviews-table {
        width: 100% !important;
        margin-bottom: 0 !important;
    }
    #reviews-table th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #475569;
        background: #f8fafc;
        border-top: none;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 14px;
        white-space: nowrap;
    }
    #reviews-table td {
        vertical-align: middle !important;
        font-size: 13px;
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
    }
    #reviews-table tbody tr:hover {
        background-color: #f8fafc !important;
    }

    /* Uniform Comment Line Clamp */
    .review-comment-wrap {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        line-height: 1.45;
        font-size: 12.5px;
        color: #334155;
        max-width: 230px;
        min-width: 160px;
        transition: color 0.15s ease;
    }
    .review-comment-wrap:hover {
        color: #2563eb !important;
    }

    /* Select2 overrides for filter toolbar */
    .select2-container--default .select2-selection--single {
        height: 40px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 12px !important;
        padding-right: 28px !important;
        color: #1e293b !important;
        font-size: 13px !important;
        font-weight: 500 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        right: 8px !important;
    }
    .select2-dropdown {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        z-index: 1050;
    }
    .dataTables_length select {
        height: 36px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 13px;
        margin: 0 4px;
    }
    .dataTables_filter input {
        height: 36px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 4px 12px;
        font-size: 13px;
        margin-left: 6px;
    }

    /* Modal Inspection Card Styles */
    .modal-card-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        height: 100%;
    }
    .modal-sub-heading {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .modal-quote-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 3px solid #3b82f6;
        border-radius: 0 8px 8px 0;
        padding: 12px 14px;
        font-size: 13.5px;
        line-height: 1.55;
        color: #1e293b;
        max-height: 150px;
        overflow-y: auto;
        white-space: pre-wrap;
    }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <div>
            <h1 style="font-size: 20px; font-weight: 700; color: #1e293b;">
                <i class="fas fa-star-half-alt text-warning mr-2"></i> Product Reviews & Customer Feedback
            </h1>
            <div class="section-header-breadcrumb text-muted small mt-1">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></div>
                <div class="breadcrumb-item active">Reviews</div>
            </div>
        </div>
    </div>

    <div class="section-body">
        {{-- KPI Cards Row --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="kpi-card">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Total Reviews</div>
                        <div class="h3 font-weight-bold text-dark mb-0 mt-1">{{ number_format($totalReviews) }}</div>
                        <div class="text-success small font-weight-bold mt-1">
                            <i class="fas fa-check-circle mr-1"></i> Customer Submissions
                        </div>
                    </div>
                    <div class="kpi-icon bg-primary text-white">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="kpi-card">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Average Rating</div>
                        <div class="h3 font-weight-bold text-dark mb-0 mt-1">
                            {{ $averageRating }} <span style="font-size: 16px; color: #f59e0b;">★</span>
                        </div>
                        <div class="text-muted small mt-1">Across published reviews</div>
                    </div>
                    <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.15); color: #d97706;">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="kpi-card">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">5-Star Feedback</div>
                        <div class="h3 font-weight-bold text-dark mb-0 mt-1">{{ number_format($fiveStarCount) }}</div>
                        <div class="text-muted small mt-1">
                            @if ($totalReviews > 0)
                                {{ round(($fiveStarCount / $totalReviews) * 100, 1) }}% Top Rated
                            @else
                                0% Top Rated
                            @endif
                        </div>
                    </div>
                    <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.15); color: #059669;">
                        <i class="fas fa-award"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="kpi-card">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Hidden / Inactive</div>
                        <div class="h3 font-weight-bold text-dark mb-0 mt-1">{{ number_format($hiddenReviews) }}</div>
                        <div class="text-danger small font-weight-bold mt-1">
                            <i class="fas fa-eye-slash mr-1"></i> Moderated reviews
                        </div>
                    </div>
                    <div class="kpi-icon" style="background: rgba(239, 68, 68, 0.15); color: #dc2626;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Review Management Card --}}
        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center">
                <div class="py-1">
                    <h5 class="font-weight-bold text-dark mb-0" style="font-size: 16px;">
                        <i class="fas fa-comments text-primary mr-2"></i> Manage Customer Reviews
                    </h5>
                    <small class="text-muted">Search, filter, inspect and moderate reviews submitted by outlet customers</small>
                </div>
                <div class="mt-2 mt-md-0">
                    <span class="badge badge-light border text-muted px-3 py-2 font-weight-bold" style="font-size: 12px; background: #f8fafc;">
                        <i class="fas fa-layer-group text-primary mr-1.5"></i> Total: {{ number_format($totalReviews) }} Reviews
                    </span>
                </div>
            </div>

            {{-- Filter Suite Toolbar with Select2 Dropdowns & Icon-Only Reset --}}
            <div class="filter-suite-card">
                <div class="filter-toolbar-row">
                    {{-- Filter by Category --}}
                    <div class="filter-item-category">
                        <label class="filter-field-label">
                            <i class="fas fa-th-large text-primary filter-icon"></i> Category:
                        </label>
                        <select id="filter_category_id" class="form-control select2" style="width: 100%;">
                            <option value="">-- All Categories --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter by Product --}}
                    <div class="filter-item-product">
                        <label class="filter-field-label">
                            <i class="fas fa-box text-primary filter-icon"></i> Product:
                        </label>
                        <select id="filter_product_id" class="form-control select2" style="width: 100%;">
                            <option value="">-- All Products ({{ $productsWithReviews->count() }}) --</option>
                            @foreach ($productsWithReviews as $prod)
                                <option value="{{ $prod->id }}" data-category="{{ $prod->category_id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                                    {{ $prod->name }} (SKU: {{ $prod->sku ?? $prod->product_number ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter by Star Rating --}}
                    <div class="filter-item-rating">
                        <label class="filter-field-label">
                            <i class="fas fa-star text-warning filter-icon"></i> Star Rating:
                        </label>
                        <select id="filter_rating" class="form-control select2" style="width: 100%;">
                            <option value="">-- All Ratings ({{ $totalReviews }}) --</option>
                            <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars (★★★★★)</option>
                            <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars (★★★★☆)</option>
                            <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars (★★★☆☆)</option>
                            <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars (★★☆☆☆)</option>
                            <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star (★☆☆☆☆)</option>
                        </select>
                    </div>

                    {{-- Filter by Status --}}
                    <div class="filter-item-status">
                        <label class="filter-field-label">
                            <i class="fas fa-toggle-on text-success filter-icon"></i> Status:
                        </label>
                        <select id="filter_status" class="form-control select2" style="width: 100%;">
                            <option value="">All Statuses</option>
                            <option value="1">Published (Visible)</option>
                            <option value="0">Hidden (Unapproved)</option>
                        </select>
                    </div>

                    {{-- Reset Button (Icon Only) --}}
                    <div class="filter-item-reset">
                        <label class="filter-field-label d-none d-xl-block">&nbsp;</label>
                        <button type="button" id="btn-reset-filters" class="btn btn-modern-reset-icon" title="Reset Filters" data-toggle="tooltip">
                            <i class="fas fa-redo-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="table-responsive">
                    {!! $dataTable->table(['class' => 'table table-hover table-striped w-100', 'id' => 'reviews-table']) !!}
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Review Detail Modal --}}
<div class="modal fade" id="reviewDetailModal" tabindex="-1" role="dialog" aria-labelledby="reviewDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document" style="max-width: 680px; width: 95%;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-white py-2.5 px-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center flex-wrap">
                    <h5 class="modal-title font-weight-bold text-dark mb-0" id="reviewDetailModalLabel" style="font-size: 16px;">
                        <i class="fas fa-file-alt text-primary mr-2"></i> Review Details
                    </h5>
                    <span id="modalReviewHeaderBadge" class="ml-2"></span>
                </div>
                <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close" style="opacity: 0.7; font-size: 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 p-md-4" id="modalReviewBody" style="background: #f8fafc;">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white py-2.5 px-4 border-top d-flex justify-content-between align-items-center" id="modalReviewFooter">
                <div id="modalReviewActions" class="d-flex align-items-center"></div>
                <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="font-weight: 500; border-radius: 6px;">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{!! $dataTable->scripts() !!}

<script>
$(document).ready(function() {
    // Clone original product options for dynamic category-to-product sync
    let $allProductOptions = $('#filter_product_id option').clone();

    // Initialize Tooltips & Select2 for all filter controls
    $('[data-toggle="tooltip"]').tooltip();

    if ($.fn.select2) {
        $('#filter_category_id').select2({
            placeholder: '-- All Categories --',
            allowClear: true,
            width: '100%'
        });

        $('#filter_product_id').select2({
            placeholder: '-- All Products --',
            allowClear: true,
            width: '100%'
        });

        $('#filter_rating').select2({
            placeholder: '-- All Ratings --',
            allowClear: true,
            width: '100%'
        });

        $('#filter_status').select2({
            placeholder: 'All Statuses',
            allowClear: true,
            width: '100%'
        });
    }

    // Intercept DataTables AJAX request to append custom filter parameters
    $('#reviews-table').on('preXhr.dt', function(e, settings, data) {
        data.category_id = $('#filter_category_id').val();
        data.product_id = $('#filter_product_id').val();
        data.rating = $('#filter_rating').val();
        data.status = $('#filter_status').val();
    });

    // When Category changes: dynamically update Product options & redraw DataTable
    $('#filter_category_id').on('change', function() {
        let selectedCat = $(this).val();

        $('#filter_product_id').empty();
        $allProductOptions.each(function() {
            let itemCat = $(this).data('category');
            let itemVal = $(this).val();
            if (!itemVal || !selectedCat || itemCat == selectedCat) {
                $('#filter_product_id').append($(this).clone());
            }
        });

        if ($.fn.select2) {
            $('#filter_product_id').val('').trigger('change.select2');
        } else {
            $('#filter_product_id').val('');
        }

        window.LaravelDataTables['reviews-table'].draw();
    });

    // Product, Rating, and Status filter change
    $('#filter_product_id, #filter_rating, #filter_status').on('change', function() {
        window.LaravelDataTables['reviews-table'].draw();
    });

    // Reset Filters button (Icon only)
    $('#btn-reset-filters').on('click', function() {
        if ($.fn.select2) {
            $('#filter_category_id').val('').trigger('change.select2');
            $('#filter_product_id').val('').trigger('change.select2');
            $('#filter_rating').val('').trigger('change.select2');
            $('#filter_status').val('').trigger('change.select2');
        } else {
            $('#filter_category_id').val('');
            $('#filter_product_id').val('');
            $('#filter_rating').val('');
            $('#filter_status').val('');
        }

        window.LaravelDataTables['reviews-table'].draw();
    });

    // Status Toggle Switch AJAX (from table)
    $('body').on('change', '.toggle-review-status', function() {
        let isChecked = $(this).is(':checked');
        let id = $(this).data('id');

        updateReviewStatus(id, isChecked ? 1 : 0);
    });

    function updateReviewStatus(id, status, callback) {
        $.ajax({
            url: "{{ route('admin.reviews.change-status') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                status: status
            },
            success: function(data) {
                if (data.status === 'success') {
                    toastr.success(data.message || 'Review status updated');
                    if (callback) callback(true);
                } else {
                    toastr.error('Failed to update status');
                    if (callback) callback(false);
                }
            },
            error: function() {
                toastr.error('Server error updating review status');
                if (callback) callback(false);
            }
        });
    }

    // View Full Review Modal Click
    $('body').on('click', '.btn-view-review', function() {
        let id = $(this).data('id');
        let url = "{{ route('admin.reviews.show', ':id') }}".replace(':id', id);

        $('#modalReviewHeaderBadge').html('');
        $('#modalReviewActions').html('');
        $('#modalReviewBody').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `);
        $('#reviewDetailModal').modal('show');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(res) {
                if (res.status === 'success') {
                    let r = res.review;
                    let stars = '';
                    for (let i = 1; i <= 5; i++) {
                        let color = i <= r.rating ? '#f59e0b' : '#cbd5e1';
                        stars += `<i class="fas fa-star" style="color: ${color}; font-size: 15px; margin-right: 2px;"></i>`;
                    }

                    let statusBadge = r.status 
                        ? '<span class="badge px-2.5 py-1 font-weight-bold" style="font-size: 11px; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; border-radius: 20px;"><i class="fas fa-check-circle mr-1"></i>Published (Visible)</span>'
                        : '<span class="badge px-2.5 py-1 font-weight-bold" style="font-size: 11px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 20px;"><i class="fas fa-eye-slash mr-1"></i>Hidden (Unapproved)</span>';

                    $('#modalReviewHeaderBadge').html(statusBadge);

                    let fallbackSvg = "{{ asset('uploads/no-image.svg') }}";

                    let ratingSentiment = '';
                    if (r.rating >= 5) {
                        ratingSentiment = '<span class="badge font-weight-bold ml-2" style="font-size: 10px; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 2px 7px; border-radius: 4px;">Excellent</span>';
                    } else if (r.rating >= 4) {
                        ratingSentiment = '<span class="badge font-weight-bold ml-2" style="font-size: 10px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 2px 7px; border-radius: 4px;">Good</span>';
                    } else if (r.rating >= 3) {
                        ratingSentiment = '<span class="badge font-weight-bold ml-2" style="font-size: 10px; background: #fffbeb; color: #d97706; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 4px;">Average</span>';
                    } else {
                        ratingSentiment = '<span class="badge font-weight-bold ml-2" style="font-size: 10px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 2px 7px; border-radius: 4px;">Poor</span>';
                    }

                    let html = `
                        {{-- Top Row: Product & Reviewer Side-by-Side --}}
                        <div class="row">
                            {{-- Product Card --}}
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="modal-card-box">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="modal-sub-heading"><i class="fas fa-box text-primary mr-1"></i> Reviewed Product</span>
                                        <a href="${r.product.url}" target="_blank" class="btn btn-xs btn-outline-primary" style="font-size: 10.5px; padding: 1px 7px; border-radius: 4px;">
                                            <i class="fas fa-external-link-alt mr-1"></i> View on Store
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <img src="${r.product.thumb_image}" onerror="this.onerror=null;this.src='${fallbackSvg}';" class="rounded mr-2.5 border" style="width: 48px; height: 48px; object-fit: contain; background: #ffffff; padding: 2px; flex-shrink: 0;">
                                        <div style="min-width: 0;">
                                            <h6 class="font-weight-bold mb-1 text-dark text-truncate" title="${r.product.name}" style="font-size: 13px;">${r.product.name}</h6>
                                            <div class="d-flex flex-wrap align-items-center" style="gap: 4px;">
                                                <span class="badge badge-light border text-muted px-1.5 py-0.5" style="font-family: monospace; font-size: 10px;">SKU: ${r.product.sku}</span>
                                                <span class="badge text-primary px-1.5 py-0.5" style="background: #eff6ff; font-size: 10px;">${r.product.category}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Reviewer Card --}}
                            <div class="col-md-6">
                                <div class="modal-card-box">
                                    <div class="modal-sub-heading mb-2"><i class="fas fa-user-circle text-primary mr-1"></i> Reviewer Details</div>
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="font-weight-bold text-dark text-truncate" style="font-size: 13px;">${r.user.name}</div>
                                        <div class="text-primary font-weight-bold text-truncate ml-1" style="font-size: 11px;"><i class="fas fa-store mr-1 text-muted"></i>${r.user.outlet_name}</div>
                                    </div>
                                    <div class="small text-muted text-truncate mb-0.5">
                                        <i class="fas fa-envelope text-muted mr-1" style="font-size: 11px;"></i>
                                        <a href="mailto:${r.user.email}" class="text-dark">${r.user.email}</a>
                                    </div>
                                    <div class="small text-muted text-truncate">
                                        <i class="fas fa-phone text-muted mr-1" style="font-size: 11px;"></i>
                                        <a href="tel:${r.user.phone}" class="text-dark">${r.user.phone || 'N/A'}</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bottom Row: Rating & Customer Feedback --}}
                        <div class="modal-card-box mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                <div class="d-flex align-items-center">
                                    ${stars}
                                    <span class="font-weight-bold ml-2 text-dark" style="font-size: 14px;">${r.rating}.0 <span class="text-muted font-weight-normal" style="font-size: 11.5px;">/ 5.0</span></span>
                                    ${ratingSentiment}
                                </div>
                                <div class="small text-muted mt-1 mt-sm-0">
                                    <i class="far fa-clock mr-1 text-secondary"></i>${r.created_at} <span class="font-italic">(${r.created_ago})</span>
                                </div>
                            </div>
                            <div class="modal-sub-heading mt-2.5 mb-1.5"><i class="fas fa-quote-left text-primary mr-1"></i> Customer Comment</div>
                            <div class="modal-quote-box">${r.comment}</div>
                        </div>
                    `;
                    $('#modalReviewBody').html(html);

                    // Add Modal Quick Action Buttons
                    let toggleStatusBtn = r.status
                        ? `<button type="button" class="btn btn-sm btn-outline-warning btn-modal-toggle mr-2" data-id="${r.id}" data-status="0" style="font-weight: 600; border-radius: 6px;"><i class="fas fa-eye-slash mr-1"></i> Hide Review</button>`
                        : `<button type="button" class="btn btn-sm btn-outline-success btn-modal-toggle mr-2" data-id="${r.id}" data-status="1" style="font-weight: 600; border-radius: 6px;"><i class="fas fa-check-circle mr-1"></i> Publish Review</button>`;

                    let deleteUrl = "{{ route('admin.reviews.destroy', ':id') }}".replace(':id', r.id);
                    let modalDeleteBtn = `<a href="${deleteUrl}" class="btn btn-sm btn-outline-danger delete-item mr-2" style="font-weight: 600; border-radius: 6px;"><i class="fas fa-trash-alt mr-1"></i> Delete Review</a>`;

                    $('#modalReviewActions').html(toggleStatusBtn + modalDeleteBtn);
                } else {
                    $('#modalReviewBody').html('<div class="alert alert-danger mb-0">Failed to load review details.</div>');
                }
            },
            error: function() {
                $('#modalReviewBody').html('<div class="alert alert-danger mb-0">Error communicating with server.</div>');
            }
        });
    });

    // Quick toggle from inside the modal
    $('body').on('click', '.btn-modal-toggle', function() {
        let id = $(this).data('id');
        let newStatus = $(this).data('status');

        updateReviewStatus(id, newStatus, function(success) {
            if (success) {
                $('#reviewDetailModal').modal('hide');
                window.LaravelDataTables['reviews-table'].draw();
            }
        });
    });
});
</script>
@endpush
