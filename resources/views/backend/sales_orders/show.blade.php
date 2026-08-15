@extends('backend.layouts.master')

@section('title', 'Sales Order Details - ' . $order->order_no)

@section('content')
    <section class="section">
        {{-- Top Bar Navigation & Actions --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-file-invoice text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center">
                            <h4 class="mb-0 font-weight-bold text-dark mr-3" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                Sales Order #{{ $order->order_no }}
                            </h4>
                            @if($order->status === 'credit_hold')
                                <span class="badge badge-danger px-3 py-1 font-weight-bold"><i class="fas fa-lock mr-1"></i> CREDIT HOLD</span>
                            @else
                                <span class="badge badge-success px-3 py-1 font-weight-bold text-uppercase">{{ str_replace('_', ' ', $order->status) }}</span>
                            @endif
                        </div>
                        <p class="text-muted mb-0 small mt-1">Placed on: {{ $order->placed_at?->format('d M, Y H:i') ?? ($order->created_at?->format('d M, Y H:i') ?? 'N/A') }}</p>
                    </div>
                </div>

                <div class="ml-auto d-flex align-items-center flex-wrap">
                    @if($order->status === 'credit_hold')
                        <button type="button" class="btn btn-danger font-weight-bold text-white mr-2 shadow-sm" data-toggle="modal" data-target="#releaseCreditModal" style="border-radius: 8px;">
                            <i class="fas fa-unlock-alt mr-1"></i> Release Credit Hold
                        </button>
                    @endif

                    <a href="{{ route('admin.sales-orders.index') }}" class="btn btn-outline-secondary font-weight-bold" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>

        {{-- Order Status Banner --}}
        @if($order->status === 'credit_hold')
            <div class="alert alert-danger shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                    <div>
                        <h6 class="mb-1 font-weight-bold">Order Flagged Under Credit Hold</h6>
                        <p class="mb-0 small">This order exceeds the customer's approved credit limit exposure. Fulfillment is blocked until a Credit Manager releases the hold.</p>
                    </div>
                </div>
            </div>
        @endif

        @if($order->pi_email)
            <div class="alert alert-info shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <i class="fas fa-info-circle mr-2"></i> {{ $order->pi_email }}
            </div>
        @endif

        <div class="section-body">
            <div class="row">
                {{-- Left Details Column --}}
                <div class="col-lg-8">
                    <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-boxes mr-2 text-primary"></i> Order Line Items</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                                        <tr>
                                            <th class="pl-4">Product</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right pr-4">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td class="pl-4 align-middle">
                                                    <strong class="text-dark">{{ $item->product_name ?? ($item->product?->name ?? 'Product Item') }}</strong>
                                                    @if($item->variant_label || $item->variant)
                                                        <br><small class="text-muted">Variant: {{ $item->variant_label ?? $item->variant?->name }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center align-middle font-weight-bold">{{ $item->quantity ?? $item->qty }}</td>
                                                <td class="text-right align-middle font-weight-bold text-muted">kr. {{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-right align-middle pr-4 font-weight-bold text-dark">kr. {{ number_format($item->line_total ?? $item->subtotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column (Customer & Financial Summary) --}}
                <div class="col-lg-4">
                    {{-- Credit Exposure Summary Box --}}
                    <div class="card card-warning border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-shield-alt mr-2 text-warning"></i> Customer Credit Exposure</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted font-weight-bold">Approved Credit Limit:</span>
                                <span class="font-weight-bold text-dark">kr. {{ number_format($creditEvaluation['credit_limit'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted font-weight-bold">Unpaid Dues:</span>
                                <span class="font-weight-bold text-danger">kr. {{ number_format($creditEvaluation['current_dues'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted font-weight-bold">This Order:</span>
                                <span class="font-weight-bold text-primary">kr. {{ number_format($order->total_amount, 2) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold text-dark">Total Exposure:</span>
                                <span class="font-weight-bold {{ $creditEvaluation['is_exceeded'] ? 'text-danger' : 'text-success' }}" style="font-size: 1.1rem;">
                                    kr. {{ number_format($creditEvaluation['total_exposure'], 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Financial Summary Box --}}
                    <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-calculator mr-2 text-primary"></i> Financial Summary</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted font-weight-bold">Subtotal:</span>
                                <span class="font-weight-bold text-dark">kr. {{ number_format($order->subtotal_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted font-weight-bold">Tax ({{ $order->tax_label }}):</span>
                                <span class="font-weight-bold text-dark">kr. {{ number_format($order->tax_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted font-weight-bold">Discount:</span>
                                <span class="font-weight-bold text-danger">- kr. {{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-dark font-weight-bold">Grand Total:</h6>
                                <h4 class="mb-0 text-primary font-weight-bold">kr. {{ number_format($order->total_amount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Credit Hold Release Modal --}}
    @if($order->status === 'credit_hold')
        <div class="modal fade" id="releaseCreditModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-unlock-alt text-danger mr-2"></i> Authorize Credit Hold Release</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.sales-orders.release-credit', $order->id) }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="alert alert-warning mb-3 small">
                                <strong>Warning:</strong> Releasing credit hold will bypass the customer's credit limit rule for Sales Order #{{ $order->order_no }}.
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark">Override Reason / Authorization Notes <span class="text-danger">*</span></label>
                                <textarea name="override_reason" class="form-control" rows="3" placeholder="e.g. Approved by Finance Manager (Payment Promise on 25th)" required style="border-radius: 8px;"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-3 border-top">
                            <button type="button" class="btn btn-secondary px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">Authorize Release</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
