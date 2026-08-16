@extends('backend.layouts.master')
@section('title', 'Sales Return Details')

@section('content')
    <section class="section">
        {{-- Standard Stisla Section Header --}}
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.sales-returns.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Sales Return #{{ $salesReturn->return_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales-returns.index') }}">Customer Returns</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-8 col-12">
                    {{-- Return Details & Items Table Card --}}
                    <div class="card card-primary">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-undo-alt mr-2"></i>Return Merchandise Authorization Details</h4>
                            @if ($salesReturn->status === 'pending')
                                <form action="{{ route('admin.sales-returns.approve', $salesReturn->id) }}" method="POST" class="d-inline" id="approve-return-form">
                                    @csrf
                                    <button type="button" class="btn btn-success font-weight-bold shadow-sm" id="btn-approve-return">
                                        <i class="fas fa-check-circle mr-1"></i> Approve & Issue Credit Note
                                    </button>
                                </form>
                            @else
                                <span class="badge badge-success px-3 py-2 font-weight-bold">
                                    <i class="fas fa-check-circle mr-1"></i> Approved
                                </span>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-striped">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Product / Variant</th>
                                            <th class="text-center">Returned Qty</th>
                                            <th class="text-center">Stock Action</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($salesReturn->items as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong class="text-dark">{{ $item->product ? $item->product->name : 'Product #' . $item->product_id }}</strong>
                                                    @if ($item->variant)
                                                        <br><small class="text-muted">
                                                            @if($item->variant->color) Color: {{ $item->variant->color->name }} @endif
                                                            @if($item->variant->size) Size: {{ $item->variant->size->name }} @endif
                                                            {{ $item->variant->name }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td class="text-center font-weight-bold">{{ $item->qty }}</td>
                                                <td class="text-center">
                                                    @if ($item->disposition === 'scrap')
                                                        <span class="badge badge-danger"><i class="fas fa-trash-alt mr-1"></i> Scrap / Damaged</span>
                                                    @else
                                                        <span class="badge badge-info"><i class="fas fa-boxes mr-1"></i> Restock Stock</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge badge-light text-dark">{{ $item->reason ?: 'Damaged / Customer Return' }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row bg-light p-3 rounded mx-0">
                                <div class="col-md-6 col-12">
                                    <span class="text-muted d-block small">Physical Warehouse Restock:</span>
                                    @php
                                        $hasRestock = $salesReturn->items->contains(fn($i) => $i->disposition !== 'scrap');
                                    @endphp
                                    @if ($hasRestock && $salesReturn->return_to_stock)
                                        <span class="badge badge-info mt-1"><i class="fas fa-boxes mr-1"></i> Salable Items Will Restock</span>
                                    @else
                                        <span class="badge badge-warning text-dark mt-1"><i class="fas fa-exclamation-triangle mr-1"></i> Damaged / No Inventory Restock</span>
                                    @endif
                                </div>
                                <div class="col-md-6 col-12 text-md-right mt-2 mt-md-0">
                                    <span class="text-muted d-block small">Total Refund Amount:</span>
                                    <h4 class="font-weight-bold text-success mb-0">kr. {{ number_format((float)$salesReturn->refund_amount, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar Order & Credit Note Summary Card --}}
                <div class="col-md-4 col-12">
                    {{-- Linked Order Info --}}
                    <div class="card card-info">
                        <div class="card-header">
                            <h4><i class="fas fa-shopping-bag mr-2"></i>Original Order Ref</h4>
                        </div>
                        <div class="card-body">
                            @if ($salesReturn->order)
                                <p class="mb-1"><strong>Order No:</strong> <a href="{{ route('admin.orders.show', $salesReturn->order->id) }}">#{{ $salesReturn->order->order_no }}</a></p>
                                <p class="mb-1"><strong>Customer:</strong> {{ $salesReturn->order->user ? ($salesReturn->order->user->outlet_name ?: $salesReturn->order->user->name) : 'Guest / Cash' }}</p>
                                <p class="mb-0"><strong>Order Date:</strong> {{ $salesReturn->order->created_at ? $salesReturn->order->created_at->format('d M Y') : '-' }}</p>
                            @else
                                <p class="text-muted mb-0">No linked order</p>
                            @endif
                        </div>
                    </div>

                    {{-- Linked Credit Note Info --}}
                    <div class="card card-warning">
                        <div class="card-header">
                            <h4><i class="fas fa-file-invoice-dollar mr-2"></i>Accounts Credit Note</h4>
                        </div>
                        <div class="card-body">
                            @if ($salesReturn->creditNote)
                                <div class="text-center py-2">
                                    <h5 class="font-weight-bold text-dark mb-1">{{ $salesReturn->creditNote->credit_note_no }}</h5>
                                    <span class="badge badge-success mb-3"><i class="fas fa-check-double mr-1"></i> Issued</span>
                                    <a href="{{ route('admin.credit-notes.show', $salesReturn->creditNote->id) }}" class="btn btn-warning btn-block font-weight-bold">
                                        <i class="fas fa-eye mr-1"></i> View Credit Note & Settle
                                    </a>
                                </div>
                            @else
                                <div class="text-center text-muted py-2">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <p class="mb-0">Credit Note will be issued automatically upon return approval.</p>
                                </div>
                            @endif
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
            $('#btn-approve-return').on('click', function(e) {
                e.preventDefault();
                @php
                    $restockCount = $salesReturn->items->where('disposition', '!=', 'scrap')->count();
                    $scrapCount = $salesReturn->items->where('disposition', 'scrap')->count();
                @endphp
                var promptText = "Are you sure you want to approve Return #{{ $salesReturn->return_no }}? An official Accounts Credit Note will be issued.";
                @if($scrapCount > 0 && $restockCount == 0)
                    promptText = "Return #{{ $salesReturn->return_no }} contains DAMAGED items (Scrap). Official Accounts Credit Note will be issued WITHOUT inventory restock.";
                @elseif($scrapCount > 0 && $restockCount > 0)
                    promptText = "Return #{{ $salesReturn->return_no }} contains both Salable & Damaged items. Salable items will restock and Credit Note will be issued.";
                @endif

                Swal.fire({
                    title: 'Approve Sales Return & Issue Credit Note?',
                    text: promptText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#27ae60',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Approve & Issue Credit Note!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#approve-return-form').submit();
                    }
                });
            });
        });
    </script>
@endpush
