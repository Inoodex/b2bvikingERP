@extends('backend.layouts.master')
@section('title', 'Credit Note Details')

@section('content')
    <section class="section">
        {{-- Standard Stisla Section Header --}}
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.credit-notes.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Credit Note #{{ $creditNote->credit_note_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.credit-notes.index') }}">Credit Notes</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="section-body">
            {{-- KPI Financial Cards --}}
            <div class="row">
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Credit Value</h4>
                            </div>
                            <div class="card-body">
                                kr. {{ number_format((float)$creditNote->amount, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Settled Amount</h4>
                            </div>
                            <div class="card-body">
                                kr. {{ number_format((float)$creditNote->settled_amount, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Remaining Unsettled</h4>
                            </div>
                            <div class="card-body">
                                kr. {{ number_format((float)$creditNote->remaining_amount, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 col-12">
                    <div class="card card-primary">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-info-circle mr-2"></i>Credit Note Statement & Status</h4>
                            <div>
                                <a href="{{ route('admin.credit-notes.pdf', $creditNote->id) }}" target="_blank" class="btn btn-danger font-weight-bold mr-2">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF Export
                                </a>
                                @if ($creditNote->remaining_amount > 0)
                                    <button type="button" class="btn btn-success font-weight-bold" data-toggle="modal" data-target="#settleModal">
                                        <i class="fas fa-handshake mr-1"></i> Settle Credit Note
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <span class="text-muted d-block small">Customer / Outlet:</span>
                                    <h5 class="font-weight-bold text-dark mb-1">
                                        {{ $creditNote->customer ? ($creditNote->customer->outlet_name ?: $creditNote->customer->name) : 'General Customer' }}
                                    </h5>
                                    @if($creditNote->customer && $creditNote->customer->email)
                                        <small class="text-muted"><i class="fas fa-envelope mr-1"></i> {{ $creditNote->customer->email }}</small>
                                    @endif
                                </div>
                                <div class="col-md-6 text-md-right mt-3 mt-md-0">
                                    <span class="text-muted d-block small">Settlement Status:</span>
                                    @if ($creditNote->settlement_status === 'settled')
                                        <span class="badge badge-success px-3 py-2 font-weight-bold"><i class="fas fa-check-double mr-1"></i> Fully Settled</span>
                                    @elseif ($creditNote->settlement_status === 'partial')
                                        <span class="badge badge-info px-3 py-2 font-weight-bold"><i class="fas fa-adjust mr-1"></i> Partially Settled</span>
                                    @else
                                        <span class="badge badge-warning px-3 py-2 font-weight-bold"><i class="fas fa-clock mr-1"></i> Unsettled (Pending)</span>
                                    @endif
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Settlement Field</th>
                                            <th>Details / Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Settlement Mode</th>
                                            <td>
                                                @if ($creditNote->settlement_mode === 'invoice_offset')
                                                    <span class="badge badge-primary"><i class="fas fa-receipt mr-1"></i> Invoice Offset</span>
                                                @elseif ($creditNote->settlement_mode === 'replacement')
                                                    <span class="badge badge-info"><i class="fas fa-sync-alt mr-1"></i> Product Replacement</span>
                                                @elseif ($creditNote->settlement_mode === 'direct_refund')
                                                    <span class="badge badge-success"><i class="fas fa-money-bill-wave mr-1"></i> Direct Cash / Bank Refund</span>
                                                @else
                                                    <span class="text-muted">Not Settled Yet</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Notes / Remarks</th>
                                            <td>{{ $creditNote->notes ?: 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar Audit & References --}}
                <div class="col-md-4 col-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h4><i class="fas fa-link mr-2"></i>Related References</h4>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong>Sales Return:</strong> 
                                @if($creditNote->salesReturn)
                                    <a href="{{ route('admin.sales-returns.show', $creditNote->salesReturn->id) }}">#{{ $creditNote->salesReturn->return_no }}</a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>Original Order:</strong> 
                                @if($creditNote->salesReturn && $creditNote->salesReturn->order)
                                    <a href="{{ route('admin.orders.show', $creditNote->salesReturn->order->id) }}">#{{ $creditNote->salesReturn->order->order_no }}</a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </p>
                            <p class="mb-0">
                                <strong>Issued On:</strong> {{ $creditNote->created_at ? $creditNote->created_at->format('d M Y, h:i A') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 3-Mode Settlement Modal --}}
    @if ($creditNote->remaining_amount > 0)
        <div class="modal fade" id="settleModal" tabindex="-1" role="dialog" aria-labelledby="settleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.credit-notes.settle', $creditNote->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title font-weight-bold" id="settleModalLabel">
                                <i class="fas fa-handshake mr-2"></i>Settle Credit Note #{{ $creditNote->credit_note_no }}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Select Settlement Mode <span class="text-danger">*</span></label>
                                <select name="settlement_mode" id="modal_settlement_mode" class="form-control" required>
                                    <option value="invoice_offset">Mode A: Invoice Offset (Deduct from Unpaid Order Balance)</option>
                                    <option value="replacement">Mode B: Product Replacement (Issue Replacement Stock)</option>
                                    <option value="direct_refund">Mode C: Direct Cash/Bank Refund (Voucher)</option>
                                </select>
                            </div>

                            <div class="form-group" id="target_order_group">
                                <label class="font-weight-bold">Target Unpaid Order to Deduct Balance From <span class="text-danger">*</span></label>
                                @if (count($customerOrders) > 0)
                                    <select name="target_order_id" id="target_order_id" class="form-control select2" style="width: 100%;">
                                        @foreach ($customerOrders as $cOrder)
                                            <option value="{{ $cOrder->id }}" data-due="{{ (float)$cOrder->due_amount }}" {{ $creditNote->salesReturn && $creditNote->salesReturn->order_id == $cOrder->id ? 'selected' : '' }}>
                                                Order #{{ $cOrder->order_no }} (Unpaid Due: kr. {{ number_format((float)$cOrder->due_amount, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="alert alert-warning mb-0 py-2">
                                        <small><i class="fas fa-info-circle mr-1"></i> No pending unpaid orders found for this customer. Credit will apply as general balance or select Direct Refund mode.</small>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Amount to Settle (<span id="max_settle_label">Max kr. {{ number_format((float)$creditNote->remaining_amount, 2) }}</span>) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" max="{{ $creditNote->remaining_amount }}" id="settle_amount_input" name="settle_amount" class="form-control" value="{{ $creditNote->remaining_amount }}" required>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Notes / Audit Remarks</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Applied credit to Order #SO-104"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success font-weight-bold">
                                <i class="fas fa-check mr-1"></i> Apply Settlement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function updateSettleAmountLimit() {
                var mode = $('#modal_settlement_mode').val();
                var cnRemaining = parseFloat("{{ $creditNote->remaining_amount }}") || 0;
                var maxLimit = cnRemaining;

                if (mode === 'invoice_offset') {
                    $('#target_order_group').show();
                    var selectedOption = $('#target_order_id option:selected');
                    if (selectedOption.length > 0) {
                        var selectedDue = parseFloat(selectedOption.data('due')) || 0;
                        if (selectedDue > 0) {
                            maxLimit = Math.min(cnRemaining, selectedDue);
                        }
                    }
                } else {
                    $('#target_order_group').hide();
                }

                $('#settle_amount_input').attr('max', maxLimit.toFixed(2)).val(maxLimit.toFixed(2));
                $('#max_settle_label').text('Max kr. ' + maxLimit.toFixed(2));
            }

            $('#modal_settlement_mode, #target_order_id').on('change', function() {
                updateSettleAmountLimit();
            });

            updateSettleAmountLimit();
        });
    </script>
@endpush
