@extends('backend.layouts.master')

@section('title', 'Payment Transactions Audit Trail')

@section('content')
<section class="section">
  <div class="section-header d-flex justify-content-between align-items-center">
    <div>
      <h1 class="text-dark font-weight-bold"><i class="fas fa-receipt text-primary mr-2"></i> Payment Transactions Audit Trail</h1>
      <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('admin.payment-settings.index') }}">Payment Settings</a></div>
        <div class="breadcrumb-item">Transactions</div>
      </div>
    </div>
    <div class="section-header-button">
      <a href="{{ route('admin.payment-settings.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-sliders-h mr-1"></i> Gateway Settings
      </a>
    </div>
  </div>

  <div class="section-body">

    {{-- Top Stats --}}
    <div class="row">
      <div class="col-lg-4 col-md-4 col-12">
        <div class="card card-statistic-1 shadow-sm border-0">
          <div class="card-icon bg-success">
            <i class="fas fa-check-circle text-white"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>Total Settled Online & COD</h4>
            </div>
            <div class="card-body font-weight-bold text-success">
              kr. {{ number_format($totalCaptured, 2) }}
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-4 col-12">
        <div class="card card-statistic-1 shadow-sm border-0">
          <div class="card-icon bg-primary">
            <i class="fab fa-paypal text-white"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>PayPal Transactions</h4>
            </div>
            <div class="card-body font-weight-bold">
              {{ $paypalCount }} Transactions
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-4 col-12">
        <div class="card card-statistic-1 shadow-sm border-0">
          <div class="card-icon bg-warning">
            <i class="fas fa-truck text-white"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>Cash On Delivery</h4>
            </div>
            <div class="card-body font-weight-bold">
              {{ $codCount }} Collections
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Transactions Table Card --}}
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-history mr-2 text-primary"></i> Gateway Transactions Log</h5>
        <form method="GET" action="{{ route('admin.payments.transactions.index') }}" class="form-inline">
          <select name="gateway" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
            <option value="">-- All Gateways --</option>
            <option value="paypal" {{ request('gateway') === 'paypal' ? 'selected' : '' }}>PayPal Express</option>
            <option value="cod" {{ request('gateway') === 'cod' ? 'selected' : '' }}>Cash On Delivery (COD)</option>
          </select>
          <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
            <option value="">-- All Statuses --</option>
            <option value="captured" {{ request('status') === 'captured' ? 'selected' : '' }}>Captured / Settled</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
          </select>
          <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search Txn # / Ext ID / Inv..." value="{{ request('search') }}">
          <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
        </form>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>Txn #</th>
                <th>Gateway</th>
                <th>Order / Invoice</th>
                <th>Customer</th>
                <th>Amount (DKK)</th>
                <th>External Capture Ref</th>
                <th>Status</th>
                <th>Date & Time</th>
                <th class="text-center">Audit</th>
              </tr>
            </thead>
            <tbody>
              @forelse($transactions as $txn)
                <tr>
                  <td class="font-weight-bold">{{ $txn->transaction_no }}</td>
                  <td>
                    @if($txn->gateway === 'paypal')
                      <span class="badge badge-primary px-2 py-1"><i class="fab fa-paypal mr-1"></i> PayPal Express</span>
                    @elseif($txn->gateway === 'cod')
                      <span class="badge badge-success px-2 py-1"><i class="fas fa-money-bill-wave mr-1"></i> COD</span>
                    @else
                      <span class="badge badge-secondary px-2 py-1">{{ strtoupper($txn->gateway) }}</span>
                    @endif
                  </td>
                  <td>
                    @if($txn->invoice)
                      <a href="{{ route('admin.sales-invoices.show', $txn->sales_invoice_id) }}" class="font-weight-bold">
                        Inv #{{ $txn->invoice->invoice_number }}
                      </a>
                    @elseif($txn->order)
                      <a href="{{ route('admin.orders.show', $txn->order_id) }}">
                        SO #{{ $txn->order->order_no ?? $txn->order_id }}
                      </a>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>
                    {{ $txn->customer?->outlet_name ?: ($txn->customer?->name ?? 'Guest') }}
                  </td>
                  <td class="font-weight-bold text-dark">
                    kr. {{ number_format($txn->amount, 2) }}
                  </td>
                  <td>
                    <code class="text-primary font-weight-bold">{{ $txn->external_reference ?: '-' }}</code>
                  </td>
                  <td>
                    @if($txn->status === 'captured')
                      <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Captured</span>
                    @elseif($txn->status === 'pending')
                      <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> Pending</span>
                    @elseif($txn->status === 'cancelled')
                      <span class="badge badge-secondary px-2 py-1"><i class="fas fa-ban mr-1"></i> Cancelled</span>
                    @else
                      <span class="badge badge-danger px-2 py-1">{{ ucfirst($txn->status) }}</span>
                    @endif
                  </td>
                  <td class="small text-muted">
                    {{ $txn->created_at ? $txn->created_at->format('d M, Y H:i') : '-' }}
                  </td>
                  <td class="text-center">
                    @if(!empty($txn->gateway_response))
                      <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" data-toggle="modal" data-target="#payloadModal{{ $txn->id }}" title="View Gateway JSON Payload">
                        <i class="fas fa-code mr-1"></i> Payload
                      </button>

                      <div class="modal fade text-left" id="payloadModal{{ $txn->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                          <div class="modal-content">
                            <div class="modal-header bg-light">
                              <h5 class="modal-title font-weight-bold">
                                <i class="fas fa-microchip text-primary mr-1"></i> Gateway Payload: {{ $txn->transaction_no }}
                              </h5>
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body p-3">
                              <div class="mb-2 d-flex justify-content-between text-muted small">
                                <span><strong>Gateway:</strong> {{ strtoupper($txn->gateway) }}</span>
                                <span><strong>External Ref:</strong> {{ $txn->external_reference ?: 'N/A' }}</span>
                              </div>
                              <pre class="bg-dark text-light p-3 rounded small mb-0" style="max-height: 450px; overflow-y: auto;"><code>{{ json_encode($txn->gateway_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    @else
                      <span class="text-muted small">-</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 text-muted d-block"></i>
                    No payment gateway transactions found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($transactions->hasPages())
          <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
            {{ $transactions->links() }}
          </div>
        @endif
      </div>
    </div>

  </div>
</section>
@endsection
