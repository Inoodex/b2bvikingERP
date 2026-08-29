@extends('backend.layouts.master')
@section('title', 'Stock Ledger')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Stock Ledger</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.inventory-reports.index') }}">Inventory</a></div>
            <div class="breadcrumb-item">Stock Ledger</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                {{-- Filter Card --}}
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-filter text-primary mr-2"></i> Filter Ledger Movements</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-product" class="font-weight-bold">Product</label>
                                <select id="filter-product" class="form-control select2">
                                    <option value="">All Products</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="filter-variant" class="font-weight-bold">Variant</label>
                                <select id="filter-variant" class="form-control select2">
                                    <option value="">All Variants</option>
                                </select>
                            </div>

                            <div class="col-md-2 col-sm-6 form-group">
                                <label for="filter-reference-type" class="font-weight-bold">Reference Type</label>
                                <select id="filter-reference-type" class="form-control select2">
                                    <option value="">All References</option>
                                    @foreach ($referenceTypes as $referenceType)
                                        <option value="{{ $referenceType }}">{{ ucfirst($referenceType) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 col-sm-6 form-group">
                                <label for="filter-movement-type" class="font-weight-bold">Movement</label>
                                <select id="filter-movement-type" class="form-control select2">
                                    <option value="">All Types</option>
                                    <option value="in">IN (Stock Received)</option>
                                    <option value="out">OUT (Dispatched)</option>
                                </select>
                            </div>

                            <div class="col-md-2 col-sm-6 form-group">
                                <label for="filter-date-from" class="font-weight-bold">Date From</label>
                                <input type="date" id="filter-date-from" class="form-control">
                            </div>

                            <div class="col-md-2 col-sm-6 form-group">
                                <label for="filter-date-to" class="font-weight-bold">Date To</label>
                                <input type="date" id="filter-date-to" class="form-control">
                            </div>

                            <div class="col-md-2 col-sm-12 form-group d-flex align-items-end">
                                <button type="button" class="btn btn-warning px-4 w-100" id="reset-ledger-filters" style="height: 42px;">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table Card --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Inventory Movement History</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped table-bordered" id="table-ledger" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center" width="80">Image</th>
                                    <th>Product</th>
                                    <th>Variant</th>
                                    <th>Reference</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">In Qty</th>
                                    <th class="text-center">Out Qty</th>
                                    <th class="text-center font-weight-bold">Balance</th>
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
</section>
@endsection

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

    const ledgerTable = $("#table-ledger").DataTable({
        dom: '<"row mb-3"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'excel',
                className: 'btn btn-primary btn-sm mr-1',
                title: 'Stock Ledger Report'
            },
            {
                extend: 'csv',
                className: 'btn btn-primary btn-sm mr-1',
                title: 'Stock Ledger Report'
            },
            {
                extend: 'pdf',
                className: 'btn btn-primary btn-sm mr-1',
                title: 'Stock Ledger Report'
            },
            {
                extend: 'print',
                className: 'btn btn-primary btn-sm',
                title: 'Stock Ledger Report'
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
        pageLength: 10
    });

    $('#filter-product').on('change', function () {
        renderVariantOptions($(this).val());
        ledgerTable.ajax.reload();
    });

    $('#filter-reference-type, #filter-movement-type, #filter-date-from, #filter-date-to, #filter-variant').on('change', function () {
        ledgerTable.ajax.reload();
    });

    $('#reset-ledger-filters').on('click', function () {
        $('#filter-product').val('').trigger('change');
        $('#filter-reference-type').val('').trigger('change');
        $('#filter-movement-type').val('').trigger('change');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        renderVariantOptions('');
        ledgerTable.ajax.reload();
    });

    renderVariantOptions($('#filter-product').val());
</script>
@endpush