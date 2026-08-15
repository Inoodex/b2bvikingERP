@extends('backend.layouts.master')

@section('title', 'Gift Card Details - ' . $giftCard->code)

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-credit-card text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Gift Card Ledger Details</h4>
                        <p class="text-muted mb-0 small">Transaction audit ledger for card {{ $giftCard->code }}</p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.gift-cards.index') }}" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" style="border-radius: 10px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                {{-- Card Overview Summary --}}
                <div class="col-lg-4">
                    <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-info-circle mr-2 text-primary"></i> Card Summary</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center p-3 mb-4 rounded text-white shadow-sm" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 12px; border: 1px solid rgba(205, 160, 90, 0.4);">
                                <small class="text-uppercase text-warning font-weight-bold">Gift Card Number</small>
                                <h4 class="my-2 text-white font-weight-bold" style="font-family: monospace; letter-spacing: 2px;">{{ $giftCard->code }}</h4>
                                <span class="badge {{ $giftCard->status ? 'badge-success' : 'badge-danger' }} px-3 py-1">
                                    {{ $giftCard->status ? 'ACTIVE' : 'INACTIVE' }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted font-weight-bold">Initial Value:</span>
                                <span class="font-weight-bold text-dark">{{ $giftCard->currency?->symbol ?? 'kr.' }} {{ number_format($giftCard->initial_value, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted font-weight-bold">Current Balance:</span>
                                <span class="font-weight-bold text-success" style="font-size: 1.1rem;">{{ $giftCard->currency?->symbol ?? 'kr.' }} {{ number_format($giftCard->balance, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted font-weight-bold">Expiration Date:</span>
                                <span class="font-weight-bold text-dark">{{ $giftCard->expires_at ? $giftCard->expires_at->format('d M, Y') : 'No Expiry' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Manual Balance Adjustment --}}
                    <div class="card card-warning border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-sliders-h mr-2 text-warning"></i> Adjust Balance</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.gift-cards.adjust-balance', $giftCard->id) }}" method="POST">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark">Adjustment Amount (+ or -)</label>
                                    <input type="number" step="0.01" name="adjustment_amount" class="form-control" placeholder="e.g. +50.00 or -25.00" required style="border-radius: 8px;">
                                    <small class="form-text text-muted">Use positive number to add funds, negative to deduct.</small>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark">Reason / Reference</label>
                                    <input type="text" name="reason" class="form-control" placeholder="e.g. Customer Compensation" style="border-radius: 8px;">
                                </div>
                                <button type="submit" class="btn btn-warning btn-block font-weight-bold shadow-sm" style="border-radius: 8px;">
                                    <i class="fas fa-save mr-1"></i> Apply Adjustment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Transaction Ledger History --}}
                <div class="col-lg-8">
                    <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-history mr-2 text-primary"></i> Transaction History Ledger</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Transaction Type</th>
                                            <th>Amount</th>
                                            <th>Balance After</th>
                                            <th>Linked Order</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($giftCard->transactions as $tx)
                                            <tr>
                                                <td class="align-middle"><small class="font-weight-bold text-dark">{{ $tx->created_at->format('d M, Y H:i') }}</small></td>
                                                <td class="align-middle">
                                                    @if($tx->type === 'redeem')
                                                        <span class="badge badge-danger"><i class="fas fa-arrow-down mr-1"></i> REDEEMED</span>
                                                    @elseif($tx->type === 'issue')
                                                        <span class="badge badge-primary"><i class="fas fa-plus-circle mr-1"></i> ISSUED</span>
                                                    @else
                                                        <span class="badge badge-warning"><i class="fas fa-sliders-h mr-1"></i> ADJUSTED</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle font-weight-bold {{ $tx->type === 'redeem' ? 'text-danger' : 'text-success' }}">
                                                    {{ $tx->type === 'redeem' ? '-' : '+' }} {{ $giftCard->currency?->symbol ?? 'kr.' }} {{ number_format($tx->amount, 2) }}
                                                </td>
                                                <td class="align-middle font-weight-bold text-dark">
                                                    {{ $giftCard->currency?->symbol ?? 'kr.' }} {{ number_format($tx->balance_after, 2) }}
                                                </td>
                                                <td class="align-middle">
                                                    @if($tx->order)
                                                        <a href="{{ route('admin.orders.show', $tx->order_id) }}" class="badge badge-info px-2 py-1">Order #{{ $tx->order_id }}</a>
                                                    @else
                                                        <span class="text-muted small">Manual Admin</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    <i class="fas fa-receipt mr-1"></i> No transactions logged for this Gift Card yet.
                                                </td>
                                            </tr>
                                        @endforelse
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
