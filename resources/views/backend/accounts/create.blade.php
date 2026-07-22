@extends('backend.layouts.master')

@section('title', 'Record Manual Payment')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Accounts - Record Payment</h1>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Search Order & Pay</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Order Number</label>
                            <div class="input-group">
                                <input type="text" id="order_no_input" class="form-control" placeholder="Enter Order Number (e.g. ORD-123456)">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" id="search_btn" type="button">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="order_details_section" style="display: none;">
                            <hr>
                            <div class="row mb-4">
                                <div class="col-sm-6">
                                    <h6 class="text-muted">Customer Name:</h6>
                                    <p id="customer_name" class="font-weight-bold h6"></p>
                                </div>
                                <div class="col-sm-6">
                                    <h6 class="text-muted">Order Status:</h6>
                                    <span id="order_status" class="badge badge-info"></span>
                                </div>
                            </div>

                            <div class="row text-center mb-4">
                                <div class="col-4 border-right">
                                    <div class="text-muted small text-uppercase">Total</div>
                                    <div class="h5 font-weight-bold text-primary" id="total_amount"></div>
                                </div>
                                <div class="col-4 border-right">
                                    <div class="text-muted small text-uppercase">Paid</div>
                                    <div class="h5 font-weight-bold text-success" id="paid_amount"></div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small text-uppercase">Due</div>
                                    <div class="h5 font-weight-bold text-danger" id="due_amount"></div>
                                </div>
                            </div>

                            <div id="payment_form_wrapper">
                                <form id="payment_form" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="source" value="central_entry">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Amount to Pay</label>
                                                <input type="number" name="amount" id="pay_amount_input" class="form-control" step="0.01" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Payment Method</label>
                                                <select name="payment_method" class="form-control selectric" required>
                                                    <option value="cash">Cash</option>
                                                    <option value="bank">Bank Transfer</option>
                                                    <option value="mobile_banking">Mobile Pay</option>
                                                    <option value="cheque">Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Transaction ID / Reference</label>
                                        <input type="text" name="transaction_id" class="form-control" placeholder="Optional">
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Note</label>
                                        <textarea name="note" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Bank Receipt(s)</label>
                                        <input type="file" name="receipts[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp,.pdf">
                                        <small class="text-muted">You can upload multiple files (JPG/PNG/WEBP/PDF).</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm">
                                        <i class="fas fa-check-circle mr-1"></i> Confirm Payment
                                    </button>
                                </form>
                            </div>

                            <div id="full_paid_msg" style="display: none;" class="alert alert-success text-center">
                                <i class="fas fa-check-circle mr-1"></i> This order is already fully paid.
                            </div>
                        </div>
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
        // Auto-search if order_no is passed in URL
        const urlParams = new URLSearchParams(window.location.search);
        const orderNoParam = urlParams.get('order_no');
        if (orderNoParam) {
            $('#order_no_input').val(orderNoParam);
            setTimeout(() => {
                $('#search_btn').click();
            }, 500);
        }

        $('#search_btn').on('click', function() {
            let order_no = $('#order_no_input').val();
            if (!order_no) {
                toastr.warning('Please enter an order number');
                return;
            }

            $.ajax({
                url: "{{ route('admin.accounts.search-order') }}",
                method: "GET",
                data: { order_no: order_no },
                beforeSend: function() {
                    $('#search_btn').addClass('btn-progress disabled');
                },
                success: function(response) {
                    $('#search_btn').removeClass('btn-progress disabled');
                    if (response.success) {
                        let order = response.order;
                        $('#order_details_section').show();
                        $('#customer_name').text(order.customer_name);
                        $('#order_status').text(order.status);
                        $('#total_amount').text(order.total_amount);
                        $('#paid_amount').text(order.paid_amount);
                        $('#due_amount').text(order.due_amount);
                        
                        // Setup form
                        $('#payment_form').attr('action', `/admin/accounts/orders/${order.id}/payment`);
                        $('#pay_amount_input').val(order.due_raw).attr('max', order.due_raw);
                        
                        if (parseFloat(order.due_raw) <= 0) {
                            $('#payment_form_wrapper').hide();
                            $('#full_paid_msg').show();
                        } else {
                            $('#payment_form_wrapper').show();
                            $('#full_paid_msg').hide();
                        }
                    }
                },
                error: function(xhr) {
                    $('#search_btn').removeClass('btn-progress disabled');
                    $('#order_details_section').hide();
                    toastr.error(xhr.responseJSON.message || 'Error occurred');
                }
            });
        });

        // Trigger search on enter key
        $('#order_no_input').on('keypress', function(e) {
            if (e.which == 13) {
                $('#search_btn').click();
            }
        });
    });
</script>
@endpush
