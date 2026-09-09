@extends('backend.layouts.master')

@section('title', 'COD Collections & Handover Management')

@section('content')
<section class="section">
  <div class="section-header d-flex justify-content-between align-items-center">
    <div>
      <h1 class="text-dark font-weight-bold"><i class="fas fa-money-bill-wave text-success mr-2"></i> Cash On Delivery (COD) Collections</h1>
      <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('admin.payment-settings.index', ['tab' => 'cod']) }}">Payment Settings</a></div>
        <div class="breadcrumb-item">COD Collections</div>
      </div>
    </div>
    <div class="section-header-button">
      <a href="{{ route('admin.payment-settings.index', ['tab' => 'cod']) }}" class="btn btn-outline-secondary">
        <i class="fas fa-cog mr-1"></i> COD Settings
      </a>
    </div>
  </div>

  <div class="section-body">

    {{-- Top 4 KPI Metrics --}}
    <div class="row">
      <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1 shadow-sm border-0">
          <div class="card-icon bg-info">
            <i class="fas fa-clock text-white"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>Pending Dispatch</h4>
            </div>
            <div class="card-body font-weight-bold">
              {{ $pendingDispatchCount }} Orders
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1 shadow-sm border-0">
          <div class="card-icon bg-warning">
            <i class="fas fa-truck-loading text-white"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>Cash In Transit</h4>
            </div>
            <div class="card-body font-weight-bold text-dark">
              kr. {{ number_format($totalInTransit, 2) }}
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1 shadow-sm border-0">
          <div class="card-icon bg-success">
            <i class="fas fa-hand-holding-usd text-white"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>Cash Handed Over</h4>
            </div>
            <div class="card-body font-weight-bold text-success">
              kr. {{ number_format($totalHandedOver, 2) }}
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1 shadow-sm border-0">
          <div class="card-icon bg-dark">
            <i class="fas fa-coins text-white"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>Total COD Expected</h4>
            </div>
            <div class="card-body font-weight-bold text-dark">
              kr. {{ number_format($totalExpected, 2) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Collections Table Card --}}
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-list mr-2 text-primary"></i> COD Delivery Runs & Cash Handover Ledger</h5>
        <form method="GET" action="{{ route('admin.payments.cod.index') }}" class="form-inline">
          <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
            <option value="">-- All Statuses --</option>
            <option value="pending_dispatch" {{ request('status') === 'pending_dispatch' ? 'selected' : '' }}>Pending Dispatch</option>
            <option value="out_for_delivery" {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>Out For Delivery</option>
            <option value="collected" {{ request('status') === 'collected' ? 'selected' : '' }}>Cash Collected (In Transit)</option>
            <option value="handed_over" {{ request('status') === 'handed_over' ? 'selected' : '' }}>Handed Over to Cashier</option>
          </select>
          <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search COD / Order / Inv..." value="{{ request('search') }}">
          <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
        </form>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>COD Ref #</th>
                <th>Order / Invoice</th>
                <th>Driver / Courier</th>
                <th>Expected Amount</th>
                <th>Collected Amount</th>
                <th>Status</th>
                <th>Cashier Handover</th>
                <th class="text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($collections as $cod)
                <tr>
                  <td class="font-weight-bold text-primary">{{ $cod->collection_no }}</td>
                  <td>
                    @if($cod->order)
                      <a href="{{ route('admin.orders.show', $cod->order_id) }}" class="font-weight-bold">
                        #{{ $cod->order->order_no ?? $cod->order_id }}
                      </a>
                    @endif
                    @if($cod->invoice)
                      <div class="small text-muted">
                        Inv: <a href="{{ route('admin.sales-invoices.show', $cod->sales_invoice_id) }}">#{{ $cod->invoice->invoice_number }}</a>
                      </div>
                    @endif
                  </td>
                  <td>
                    @if($cod->driver)
                      <span class="badge badge-light border">
                        <i class="fas fa-user-circle mr-1"></i> {{ $cod->driver->name }}
                      </span>
                    @elseif($cod->courier_name)
                      <span class="badge badge-light border">
                        <i class="fas fa-shipping-fast mr-1"></i> {{ $cod->courier_name }}
                      </span>
                    @else
                      <span class="text-muted font-italic">Unassigned</span>
                    @endif
                  </td>
                  <td class="font-weight-bold">kr. {{ number_format($cod->expected_amount, 2) }}</td>
                  <td class="font-weight-bold {{ $cod->collected_amount > 0 ? 'text-success' : 'text-muted' }}">
                    kr. {{ number_format($cod->collected_amount, 2) }}
                  </td>
                  <td>
                    @if($cod->status === 'pending_dispatch')
                      <span class="badge badge-secondary px-2 py-1"><i class="fas fa-box mr-1"></i> Pending Dispatch</span>
                    @elseif($cod->status === 'out_for_delivery')
                      <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-truck mr-1"></i> Out for Delivery</span>
                    @elseif($cod->status === 'collected')
                      <span class="badge badge-info px-2 py-1"><i class="fas fa-money-bill mr-1"></i> Cash with Driver</span>
                    @elseif($cod->status === 'handed_over')
                      <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Handed Over (Paid)</span>
                    @else
                      <span class="badge badge-danger px-2 py-1">{{ ucfirst($cod->status) }}</span>
                    @endif
                  </td>
                  <td>
                    @if($cod->cashier)
                      <div><strong class="text-dark">{{ $cod->cashier->name }}</strong></div>
                      <small class="text-muted">{{ $cod->handed_over_at ? $cod->handed_over_at->format('d M, Y H:i') : '' }}</small>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="text-right">
                    <div class="btn-group">
                      @if($cod->status === 'pending_dispatch')
                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#dispatchModal{{ $cod->id }}">
                          <i class="fas fa-truck mr-1"></i> Dispatch
                        </button>
                      @elseif($cod->status === 'out_for_delivery')
                        <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#collectModal{{ $cod->id }}">
                          <i class="fas fa-hand-holding-usd mr-1"></i> Mark Collected
                        </button>
                      @elseif($cod->status === 'collected')
                        <button type="button" class="btn btn-sm btn-success font-weight-bold" data-toggle="modal" data-target="#settleModal{{ $cod->id }}">
                          <i class="fas fa-check-double mr-1"></i> Cashier Settle
                        </button>
                      @elseif($cod->status === 'handed_over')
                        <span class="badge badge-light border text-success">
                          <i class="fas fa-lock mr-1"></i> Settled
                        </span>
                      @endif
                    </div>

                    {{-- Modal: Assign & Dispatch --}}
                    <div class="modal fade text-left" id="dispatchModal{{ $cod->id }}" tabindex="-1" role="dialog">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <form action="{{ route('admin.payments.cod.dispatch', $cod->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                              <h5 class="modal-title">Assign Driver / Courier for COD #{{ $cod->collection_no }}</h5>
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                              <div class="form-group">
                                <label>Internal Delivery Driver</label>
                                <select name="driver_id" class="form-control select2">
                                  <option value="">-- Select Driver --</option>
                                  @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" {{ $cod->driver_id == $driver->id ? 'selected' : '' }}>
                                      {{ $driver->name }} ({{ $driver->email }})
                                    </option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="form-group">
                                <label>Or Third-Party Courier Name</label>
                                <input type="text" name="courier_name" class="form-control" placeholder="e.g. PostNord, GLS, DAO" value="{{ $cod->courier_name }}">
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-primary font-weight-bold">Confirm & Dispatch</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    {{-- Modal: Mark Collected --}}
                    <div class="modal fade text-left" id="collectModal{{ $cod->id }}" tabindex="-1" role="dialog">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <form action="{{ route('admin.payments.cod.collect', $cod->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                              <h5 class="modal-title">Driver Cash Collection Receipt #{{ $cod->collection_no }}</h5>
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                              <div class="form-group">
                                <label>Expected Amount</label>
                                <input type="text" class="form-control bg-light font-weight-bold" readonly value="kr. {{ number_format($cod->expected_amount, 2) }}">
                              </div>
                              <div class="form-group">
                                <label>Actual Cash Collected by Driver (DKK) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="collected_amount" class="form-control font-weight-bold" value="{{ $cod->expected_amount }}" required>
                              </div>
                              <div class="form-group">
                                <label>Driver Delivery Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Delivered to receptionist, exact cash received.">{{ $cod->notes }}</textarea>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-info font-weight-bold">Mark Cash Collected</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    {{-- Modal: Cashier Settle Handover --}}
                    <div class="modal fade text-left" id="settleModal{{ $cod->id }}" tabindex="-1" role="dialog">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-success">
                          <form action="{{ route('admin.payments.cod.settle', $cod->id) }}" method="POST">
                            @csrf
                            <div class="modal-header bg-success text-white">
                              <h5 class="modal-title"><i class="fas fa-coins mr-1"></i> Cashier Handover: COD #{{ $cod->collection_no }}</h5>
                              <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                              <div class="alert alert-light border">
                                <div class="d-flex justify-content-between mb-1">
                                  <span>Driver Collected:</span>
                                  <strong>kr. {{ number_format($cod->collected_amount, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                  <span>Invoice Due:</span>
                                  <strong>kr. {{ number_format($cod->expected_amount, 2) }}</strong>
                                </div>
                              </div>

                              <div class="form-group">
                                <label class="font-weight-bold">Cash Received into Office Till (DKK) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="cash_received" class="form-control form-control-lg font-weight-bold text-success" value="{{ $cod->collected_amount > 0 ? $cod->collected_amount : $cod->expected_amount }}" required>
                              </div>

                              <div class="form-group">
                                <label class="font-weight-bold">Deposit to Cash Head Account</label>
                                <select name="deposit_account_id" class="form-control">
                                  @foreach($cashAccounts as $acc)
                                    <option value="{{ $acc->id }}" {{ $acc->account_code === '1010' ? 'selected' : '' }}>
                                      [{{ $acc->account_code }}] {{ $acc->account_name }}
                                    </option>
                                  @endforeach
                                </select>
                                <small class="text-muted">Auto-posts balanced double-entry: <strong>DR 1010 Petty Cash / CR 1030 AR</strong>.</small>
                              </div>

                              <div class="form-group mb-0">
                                <label>Verification Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="e.g. Physical cash envelope counted and verified.">
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-success font-weight-bold">
                                <i class="fas fa-check-circle mr-1"></i> Confirm & Post to Ledger
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 text-muted d-block"></i>
                    No Cash On Delivery (COD) collections found matching criteria.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($collections->hasPages())
          <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
            {{ $collections->links() }}
          </div>
        @endif
      </div>
    </div>

  </div>
</section>
@endsection
