@extends('backend.layouts.master')
@section('title', 'Create RFQ')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Create RFQ (Request For Quotation)</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>New RFQ Details</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.rfqs.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>RFQ No *</label>
                                            <input type="text" name="rfq_no" class="form-control" value="{{ $rfqNo }}" readonly required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Source Type (Optional)</label>
                                            <select name="source_type" id="source_type" class="form-control select2">
                                                <option value="">Select Source</option>
                                                <option value="App\Models\Order" {{ $selectedSourceType == 'App\Models\Order' ? 'selected' : '' }}>Order</option>
                                                <option value="App\Models\CustomProductRequest" {{ $selectedSourceType == 'App\Models\CustomProductRequest' ? 'selected' : '' }}>Custom Product Request</option>
                                                <!-- <option value="App\Models\ProductRequest" {{ $selectedSourceType == 'App\Models\ProductRequest' ? 'selected' : '' }}>Old Product Request</option> -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Reference ID</label>
                                            
                                            <!-- Default Empty Dropdown -->
                                            <div id="wrapper_default" class="source-wrapper">
                                                <select class="form-control select2" disabled>
                                                    <option>Select Source Type first</option>
                                                </select>
                                            </div>

                                            <!-- Orders Dropdown -->
                                            <div id="wrapper_order" class="source-wrapper" style="display: none;">
                                                <select name="source_id" id="source_id_order" class="form-control select2 source-id-dropdown">
                                                    <option value="">Select Order</option>
                                                    @foreach($orders as $order)
                                                        <option value="{{ $order->id }}" {{ ($selectedSourceType == 'App\Models\Order' && $selectedSourceId == $order->id) ? 'selected' : '' }}>
                                                            {{ $order->order_no }} ({{ optional($order->user)->outlet_name ?? optional($order->user)->name ?? 'N/A' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Custom Requests Dropdown -->
                                            <div id="wrapper_custom" class="source-wrapper" style="display: none;">
                                                <select name="source_id" id="source_id_custom" class="form-control select2 source-id-dropdown">
                                                    <option value="">Select Custom Request</option>
                                                    @foreach($customRequests as $cr)
                                                        <option value="{{ $cr->id }}" {{ ($selectedSourceType == 'App\Models\CustomProductRequest' && $selectedSourceId == $cr->id) ? 'selected' : '' }}>
                                                            REQ-{{ $cr->request_no }} ({{ $cr->product_name }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Product Requests Dropdown (Commented Out) -->
                                            <!--
                                            <div id="wrapper_pr" class="source-wrapper" style="display: none;">
                                                <select name="source_id" id="source_id_pr" class="form-control select2 source-id-dropdown">
                                                    <option value="">Select Old Request</option>
                                                    @foreach($productRequests as $pr)
                                                        <option value="{{ $pr->id }}" {{ ($selectedSourceType == 'App\Models\ProductRequest' && $selectedSourceId == $pr->id) ? 'selected' : '' }}>
                                                            PR-{{ str_pad($pr->id, 5, '0', STR_PAD_LEFT) }} ({{ optional($pr->user)->outlet_name ?? optional($pr->user)->name ?? 'N/A' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            -->
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Due Date</label>
                                            <input type="date" name="due_date" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Select Vendors to Invite *</label>
                                    <select name="vendors[]" class="form-control select2" multiple required>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->shop_name }} ({{ $vendor->email }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">These vendors will receive the RFQ.</small>
                                </div>

                                <hr>
                                <h5>RFQ Items</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="items_table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="items_body">
                                            <tr>
                                                <td>
                                                    <select name="items[0][product_id]" class="form-control select2" required>
                                                        <option value="">Select Product</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[0][qty]" class="form-control" step="0.01" required>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove_row"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-info btn-sm mt-2" id="add_row"><i class="fas fa-plus"></i> Add Item</button>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">Create RFQ</button>
                                    <a href="{{ route('admin.rfqs.index') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    let rowIndex = 1;
    $('#add_row').click(function() {
        let row = `<tr>
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-control select2" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][qty]" class="form-control" step="0.01" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove_row"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
        $('#items_body').append(row);
        $('.select2').select2();
        rowIndex++;
    });

    $(document).on('click', '.remove_row', function() {
        $(this).closest('tr').remove();
    });

    // Handle Source Type Dropdown Change
    function handleSourceTypeChange() {
        let type = $('#source_type').val();
        
        // Hide all wrappers, disable their select name attributes so they don't submit if hidden
        $('.source-wrapper').hide();
        $('.source-id-dropdown').attr('name', '').prop('required', false);
        
        // Show the relevant wrapper and enable its name attribute
        if(type === 'App\\Models\\Order') {
            $('#wrapper_order').show();
            $('#source_id_order').attr('name', 'source_id').prop('required', true);
        } else if(type === 'App\\Models\\CustomProductRequest') {
            $('#wrapper_custom').show();
            $('#source_id_custom').attr('name', 'source_id').prop('required', true);
        } else {
            // Show default if nothing selected
            $('#wrapper_default').show();
        }
        /* 
        else if(type === 'App\\Models\\ProductRequest') {
            $('#wrapper_pr').show();
            $('#source_id_pr').attr('name', 'source_id').prop('required', true);
        } 
        */
    }

    $('#source_type').on('change', function() {
        handleSourceTypeChange();
    });

    // AJAX Auto-fetch Items for Orders
    $('#source_id_order').on('change', function() {
        let orderId = $(this).val();
        if (!orderId) return;

        let sourceType = 'App\\Models\\Order';
        
        // Show loading state
        $('#items_body').html('<tr><td colspan="3" class="text-center">Loading items...</td></tr>');

        $.ajax({
            url: "{{ route('admin.rfqs.fetch-source-items') }}",
            type: "GET",
            data: {
                source_type: sourceType,
                source_id: orderId
            },
            success: function(response) {
                if (response.status === 'success' && response.items.length > 0) {
                    $('#items_body').empty();
                    rowIndex = 0; // Reset row index
                    
                    response.items.forEach(function(item) {
                        let options = '<option value="">Select Product</option>';
                        @foreach($products as $product)
                            options += `<option value="{{ $product->id }}" ${item.product_id == {{ $product->id }} ? 'selected' : ''}>{{ $product->name }}</option>`;
                        @endforeach

                        let row = `<tr>
                            <td>
                                <select name="items[${rowIndex}][product_id]" class="form-control select2" required>
                                    ${options}
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[${rowIndex}][qty]" class="form-control" step="0.01" value="${item.qty}" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove_row"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                        $('#items_body').append(row);
                        rowIndex++;
                    });
                    
                    $('.select2').select2(); // Re-initialize select2
                    toastr.success('Items auto-fetched successfully!');
                } else {
                    $('#items_body').empty();
                    toastr.info('No products found or it is a custom request.');
                }
            },
            error: function() {
                $('#items_body').empty();
                toastr.error('Failed to fetch items.');
            }
        });
    });

    // Trigger on load for pre-selected values
    handleSourceTypeChange();
</script>
@endpush
