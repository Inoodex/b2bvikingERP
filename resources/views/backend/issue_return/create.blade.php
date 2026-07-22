@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.issue-returns.index') }}" class="btn btn-icon">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <h1>Create Stock Return Request</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.issue-returns.index') }}">Stock Returns</a></div>
                <div class="breadcrumb-item">Create</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <form action="{{ route('admin.issue-returns.store') }}" method="POST" id="return_form">
                        @csrf
                        <div class="row">
                            <div class="col-md-9">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom pb-3">
                                        <div class="row w-100 align-items-center">
                                            <div class="col-md-6">
                                                <h4 class="text-primary mb-0"><i class="fas fa-undo mr-2"></i>Select Issue to Return</h4>
                                            </div>
                                            <div class="col-md-6">
                                                <select class="form-control select2" name="issue_id" id="issue_select" required>
                                                    <option value="" disabled selected>Select Issue No...</option>
                                                    @foreach($issues as $issue)
                                                        <option value="{{ $issue->id }}">
                                                            {{ $issue->issue_no }} - {{ $issue->outlet ? $issue->outlet->name . ($issue->outlet->outlet_name ? ' (' . $issue->outlet->outlet_name . ')' : '') : 'Main Warehouse' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table" id="return_table" style="display: none;">
                                                <thead>
                                                    <tr class="text-uppercase small text-muted font-weight-bold">
                                                        <th width="35%">Product Details</th>
                                                        <th width="15%" class="text-center">Issued Qty</th>
                                                        <th width="15%" class="text-center">Returned Qty</th>
                                                        <th width="15%" class="text-center">Remaining Qty</th>
                                                        <th width="12%">Return Qty</th>
                                                        <th width="13%">Condition</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="return_items_body">
                                                    <!-- Dynamic Rows -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="empty_state" class="text-center py-5 text-muted">
                                            <i class="fas fa-dolly fa-3x mb-3 opacity-2"></i>
                                            <p>Please select an Issue from the dropdown above to load items.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <label class="font-weight-bold text-muted text-uppercase small">Return Reason / Note</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="Enter reason for return or product condition check notes..." required></textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                                    <div class="card-header bg-primary text-white">
                                        <h4 class="mb-0 text-white font-weight-600" style="font-size: 1.1rem;">Return Summary</h4>
                                    </div>
                                    <div class="card-body bg-light">
                                        <div class="d-flex justify-content-between mb-3 px-1">
                                            <span class="text-muted">Return Date:</span>
                                            <span class="font-weight-bold text-dark">{{ date('d M, Y') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3 border-top pt-3 px-1">
                                            <span class="text-muted text-uppercase small" style="font-size: 11px;">Total Items:</span>
                                            <span id="summary_total_items" class="font-weight-bold">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-4 px-1">
                                            <span class="text-muted text-uppercase small" style="font-size: 11px;">Return Qty:</span>
                                            <span id="summary_total_qty" class="h5 mb-0 font-weight-bold text-primary">0</span>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm py-2 font-weight-bold" id="confirm_btn" disabled>
                                                <i class="fas fa-check-circle mr-2"></i> Submit Request
                                            </button>
                                        </div>
                                        <p class="text-center text-muted small mt-3 mb-0">Submitting will create a pending return request. Stock will be updated upon admin approval.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            $('#issue_select').on('change', function() {
                const issueId = $(this).val();
                if (!issueId) return;

                Swal.fire({
                    title: 'Loading Items...',
                    text: 'Fetching items from the selected stock issue.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "{{ route('admin.issue-returns.get-issue-items') }}",
                    method: "GET",
                    data: { issue_id: issueId },
                    success: function(data) {
                        Swal.close();
                        $('#return_items_body').empty();
                        
                        if (data.items && data.items.length > 0) {
                            $('#return_table').show();
                            $('#empty_state').hide();

                            data.items.forEach(function(item, index) {
                                const variantName = item.variant_name ? ` (${item.variant_name})` : '';
                                const remaining = item.remaining_qty;

                                const row = `
                                    <tr class="return-row">
                                        <td>
                                            <strong>${item.product_name}</strong>${variantName}
                                            <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                                            <input type="hidden" name="items[${index}][variant_id]" value="${item.variant_id || ''}">
                                        </td>
                                        <td class="text-center">${item.issued_qty}</td>
                                        <td class="text-center">${item.already_returned}</td>
                                        <td class="text-center font-weight-bold">${remaining}</td>
                                        <td>
                                            <input type="number" name="items[${index}][quantity]" 
                                                   class="form-control qty-input text-center" 
                                                   value="0" min="0" max="${remaining}" 
                                                   ${remaining === 0 ? 'disabled' : ''}>
                                        </td>
                                        <td>
                                            <select name="items[${index}][condition]" class="form-control" ${remaining === 0 ? 'disabled' : ''}>
                                                <option value="good">Good</option>
                                                <option value="damaged">Damaged</option>
                                            </select>
                                        </td>
                                    </tr>
                                `;
                                $('#return_items_body').append(row);
                            });
                        } else {
                            $('#return_table').hide();
                            $('#empty_state').show();
                        }
                        updateSummary();
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to fetch issue items.', 'error');
                    }
                });
            });

            $(document).on('input change', '.qty-input', function() {
                const max = parseInt($(this).attr('max')) || 0;
                let val = parseInt($(this).val()) || 0;

                if (val < 0) {
                    val = 0;
                    $(this).val(0);
                }
                if (val > max) {
                    val = max;
                    $(this).val(max);
                    Toastr.warning('Cannot return more than remaining quantity.');
                }
                updateSummary();
            });

            function updateSummary() {
                let totalQty = 0;
                let totalItems = 0;

                $('.qty-input').each(function() {
                    const qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        totalQty += qty;
                        totalItems++;
                    }
                });

                $('#summary_total_items').text(totalItems);
                $('#summary_total_qty').text(totalQty);

                $('#confirm_btn').prop('disabled', totalQty <= 0);
            }
        });
    </script>
@endpush
