@extends('backend.layouts.master')

@section('title', 'Stock Ledger')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center">
                    <i class="fas fa-book mr-2 text-primary"></i>
                    Stock Ledger
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item active">Stock Ledger</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    {{-- Filter Card --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                    <i class="fas fa-filter"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">Filter Ledger</h5>
                                    <small class="text-muted">Search and filter inventory movements</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-box text-primary mr-1"></i>
                                            Product
                                        </label>
                                        <select id="filter-product" class="form-control form-control-sm select2" data-placeholder="Select Product">
                                            <option value="">All Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-tags text-info mr-1"></i>
                                            Variant
                                        </label>
                                        <select id="filter-variant" class="form-control form-control-sm select2" data-placeholder="Select Variant">
                                            <option value="">All Variants</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-file-alt text-warning mr-1"></i>
                                            Reference Type
                                        </label>
                                        <select id="filter-reference-type" class="form-control form-control-sm">
                                            <option value="">All References</option>
                                            @foreach ($referenceTypes as $referenceType)
                                                <option value="{{ $referenceType }}">{{ ucfirst($referenceType) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-arrows-alt-h text-success mr-1"></i>
                                            Movement
                                        </label>
                                        <select id="filter-movement-type" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            <option value="in">IN</option>
                                            <option value="out">OUT</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                            Date From
                                        </label>
                                        <input type="date" id="filter-date-from" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                            Date To
                                        </label>
                                        <input type="date" id="filter-date-to" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-danger btn-sm px-4 shadow-sm" id="reset-ledger-filters">
                                                <i class="fas fa-undo mr-1"></i> Reset 
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table Card --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center flex-wrap w-100">
                                <div class="d-flex align-items-center mb-2 mb-sm-0">
                                    <div class="mr-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                        <i class="fas fa-list"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 font-weight-bold text-dark">Inventory Movement History</h5>
                                        <small class="text-muted">Complete stock movement records</small>
                                    </div>
                                </div>
                                <div class="ml-auto">
                                    <span class="badge badge-primary p-2">
                                        <i class="fas fa-database mr-1"></i> Live Data
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="table-ledger" style="width: 100%;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="font-weight-bold text-dark">Date</th>
                                            <th class="font-weight-bold text-dark text-center" width="80">Image</th>
                                            <th class="font-weight-bold text-dark">Product</th>
                                            <th class="font-weight-bold text-dark">Variant</th>
                                            <th class="font-weight-bold text-dark">Reference</th>
                                            <th class="font-weight-bold text-dark text-center">Type</th>
                                            <th class="font-weight-bold text-dark text-center">In Qty</th>
                                            <th class="font-weight-bold text-dark text-center">Out Qty</th>
                                            <th class="font-weight-bold text-dark text-center">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    /* =============================================
       STOCK LEDGER - CUSTOM STYLES
       ============================================= */

    .form-group label {
        font-size: 0.8rem;
        margin-bottom: 0.4rem;
        letter-spacing: 0.3px;
    }

    .form-control {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0;
        transition: all 0.3s ease;
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
        background: #fafbfc;
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        background: #ffffff;
    }

    .form-control-sm {
        height: 38px !important;
        font-size: 0.85rem !important;
    }

    .btn {
        border-radius: 8px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        min-height: 38px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.85rem !important;
        letter-spacing: 0.3px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .btn:active {
        transform: scale(0.97);
    }

    .btn-primary {
        background: linear-gradient(135deg, #4e73df, #224abe) !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        box-shadow: 0 6px 20px rgba(78, 115, 223, 0.3) !important;
    }

    .btn-danger {
        background: linear-gradient(135deg, #e74a3b, #c0392b) !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-danger:hover {
        box-shadow: 0 6px 20px rgba(231, 74, 59, 0.3) !important;
        transform: translateY(-2px);
    }

    .btn-outline-secondary {
        color: #858796 !important;
        border-color: #858796 !important;
        background: transparent !important;
    }

    .btn-outline-secondary:hover {
        background: #858796 !important;
        color: #fff !important;
        border-color: #858796 !important;
    }

    .card {
        border-radius: 12px !important;
        overflow: hidden !important;
    }

    .card-header:first-child {
        border-radius: 12px 12px 0 0 !important;
    }

    .card-footer:last-child {
        border-radius: 0 0 12px 12px !important;
    }

    /* Table Styles */
    .table th {
        border-top: none !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 1rem !important;
    }

    .table td {
        vertical-align: middle !important;
        font-size: 0.85rem;
        padding: 0.75rem 1rem !important;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.03);
    }

    /* Badge Styles */
    .badge {
        font-weight: 600 !important;
        padding: 0.35rem 0.75rem !important;
        border-radius: 50rem !important;
        font-size: 0.7rem !important;
    }

    .badge-in {
        background: #1cc88a !important;
        color: #ffffff !important;
    }

    .badge-out {
        background: #e74a3b !important;
        color: #ffffff !important;
    }

    /* Image Container */
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        background: #f8f9fc;
        transition: all 0.3s ease;
    }

    .product-image:hover {
        transform: scale(1.1);
        border-color: #4e73df;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Mobile Responsive */
    @media (max-width: 575.98px) {
        .section-header {
            padding: 12px 15px !important;
        }
        .section-header h1 {
            font-size: 1rem !important;
        }
        .card-body {
            padding: 15px !important;
        }
        .card-header {
            padding: 10px 15px !important;
        }
        .form-group {
            margin-bottom: 1rem !important;
        }
        .form-control-sm {
            height: 36px !important;
            font-size: 0.8rem !important;
        }
        .btn {
            font-size: 0.8rem !important;
            min-height: 36px !important;
            padding: 0 0.8rem !important;
        }
        .table td {
            font-size: 0.75rem !important;
            padding: 0.5rem 0.6rem !important;
        }
        .table th {
            font-size: 0.65rem !important;
            padding: 0.5rem 0.6rem !important;
        }
        .badge {
            font-size: 0.6rem !important;
            padding: 0.2rem 0.5rem !important;
        }
        .product-image {
            width: 35px !important;
            height: 35px !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
        }
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }
        .table-responsive {
            margin: 0 -15px !important;
            padding: 0 15px !important;
            width: calc(100% + 30px) !important;
        }
    }

    @media (min-width: 576px) and (max-width: 767.98px) {
        .card-body {
            padding: 20px !important;
        }
        .card-header {
            padding: 12px 20px !important;
        }
        .table td {
            font-size: 0.8rem !important;
            padding: 0.6rem 0.8rem !important;
        }
        .table th {
            font-size: 0.7rem !important;
            padding: 0.6rem 0.8rem !important;
        }
        .product-image {
            width: 40px !important;
            height: 40px !important;
        }
    }

    @media (max-width: 767.98px) {
        .section-header .breadcrumb {
            font-size: 0.75rem !important;
        }
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }
        .card-header .ml-auto .btn {
            width: 100% !important;
        }
        .card-header .ml-auto .badge {
            display: inline-block !important;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeInUp 0.4s ease-out;
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #4e73df;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #224abe;
    }

    /* Select2 Custom */
    .select2-container--default .select2-selection--single {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0 !important;
        height: 38px !important;
        padding: 0 12px;
        background: #fafbfc !important;
        transition: all 0.3s ease;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 34px !important;
        font-size: 0.85rem;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #4e73df !important;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    }

    .select2-dropdown {
        border-radius: 8px !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    /* DataTable Custom */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3rem 0.8rem !important;
        border-radius: 6px !important;
        border: none !important;
        margin: 0 2px !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #4e73df, #224abe) !important;
        color: #fff !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #4e73df !important;
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.2);
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 0.4rem 0.75rem !important;
        font-size: 0.85rem !important;
        background: #fafbfc !important;
        transition: all 0.3s ease !important;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #4e73df !important;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        background: #ffffff !important;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 0.3rem 0.5rem !important;
        font-size: 0.85rem !important;
        background: #fafbfc !important;
    }

    .dataTables_wrapper .dataTables_info {
        font-size: 0.85rem !important;
        color: #858796 !important;
        padding-top: 0.75rem !important;
    }

    /* DataTable Buttons */
    .dt-buttons .btn {
        margin-right: 5px !important;
        padding: 0.4rem 1rem !important;
        font-size: 0.8rem !important;
        min-height: 32px !important;
        border-radius: 6px !important;
    }

    .dt-buttons .btn-primary {
        background: linear-gradient(135deg, #4e73df, #224abe) !important;
        border: none !important;
        color: #fff !important;
    }

    .dt-buttons .btn-primary:hover {
        box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3) !important;
    }

    @media (max-width: 575.98px) {
        .dataTables_wrapper .dataTables_filter input {
            width: 150px !important;
            font-size: 0.75rem !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.2rem 0.5rem !important;
            font-size: 0.7rem !important;
        }
        .dataTables_wrapper .dataTables_length select {
            font-size: 0.75rem !important;
            padding: 0.2rem 0.3rem !important;
        }
        .dataTables_wrapper .dataTables_info {
            font-size: 0.75rem !important;
        }
        .dt-buttons .btn {
            font-size: 0.7rem !important;
            padding: 0.3rem 0.6rem !important;
            min-height: 28px !important;
        }
    }

    /* Type Badge */
    .badge-in {
        background: #1cc88a !important;
        color: #ffffff !important;
        padding: 0.3rem 0.8rem !important;
        font-weight: 700 !important;
    }

    .badge-out {
        background: #e74a3b !important;
        color: #ffffff !important;
        padding: 0.3rem 0.8rem !important;
        font-weight: 700 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    const ledgerProducts = @json($ledgerProducts);

    function renderVariantOptions(productId) {
        const $variant = $('#filter-variant');
        const variants = productId && Object.prototype.hasOwnProperty.call(ledgerProducts, String(productId))
            ? ledgerProducts[String(productId)]
            : [];

        $variant.empty().append('<option value="">All Variants</option>');

        variants.forEach(function (variant) {
            $variant.append(new Option(variant.label, variant.id));
        });

        $variant.trigger('change.select2');
    }

    $('.select2').select2({
        width: '100%',
        allowClear: true,
        placeholder: function () {
            return $(this).data('placeholder') || 'Select Option';
        }
    });

    const ledgerTable = $("#table-ledger").DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-primary btn-sm'
            },
            {
                extend: 'csv',
                className: 'btn btn-primary btn-sm'
            },
            {
                extend: 'excel',
                className: 'btn btn-primary btn-sm',
                title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Stock Ledger Report'
            },
            {
                extend: 'pdf',
                className: 'btn btn-primary btn-sm',
                title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Stock Ledger Report'
            },
            {
                extend: 'print',
                className: 'btn btn-primary btn-sm',
                title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Stock Ledger Report'
            }
        ],
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.stock-ledger.index') }}",
            data: function (d) {
                d.product_id = $('#filter-product').val();
                d.variant_id = $('#filter-variant').val();
                d.reference_type = $('#filter-reference-type').val();
                d.movement_type = $('#filter-movement-type').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            {data: 'date', name: 'created_at'},
            {data: 'image', name: 'image', orderable: false, searchable: false, className: 'text-center'},
            {data: 'product_name', name: 'product_name'},
            {data: 'variant_name', name: 'variant_name'},
            {data: 'reference', name: 'reference'},
            {data: 'type', name: 'type', orderable: false, searchable: false, className: 'text-center'},
            {data: 'in_qty', name: 'in_qty', className: 'text-center'},
            {data: 'out_qty', name: 'out_qty', className: 'text-center'},
            {data: 'balance_qty', name: 'balance_qty', className: 'text-center font-weight-bold'}
        ],
        order: [[0, "desc"]],
        pageLength: 10,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No stock movements found"
        }
    });

    $('#filter-product').on('change', function () {
        renderVariantOptions($(this).val());
        ledgerTable.ajax.reload();
    });

    $('#filter-reference-type, #filter-movement-type, #filter-date-from, #filter-date-to, #filter-variant').on('change', function () {
        ledgerTable.ajax.reload();
    });

    $('#reset-ledger-filters').on('click', function () {
        $('#filter-product').val('').trigger('change.select2');
        $('#filter-reference-type').val('');
        $('#filter-movement-type').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        renderVariantOptions('');
        ledgerTable.ajax.reload();
    });

    renderVariantOptions($('#filter-product').val());
</script>
@endpush