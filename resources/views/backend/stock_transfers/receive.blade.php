@extends('backend.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.stock-transfers.show', $stockTransfer->id) }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Receive Stock Transfer #{{ $stockTransfer->transfer_no }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.stock-transfers.index') }}">Stock Transfers</a></div>
            <div class="breadcrumb-item">Receive Verification</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('admin.stock-transfers.receive', $stockTransfer->id) }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-boxes mr-2 text-primary"></i> Verify & Confirm Incoming Quantities</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border mb-4">
                        <i class="fas fa-info-circle mr-1"></i> Please count the received physical goods and adjust the <strong>Received Qty</strong> below if there is any transit shortage before confirming.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-center" style="width: 150px;">Dispatched Qty</th>
                                    <th class="text-center" style="width: 200px;">Received Qty (Verified) <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockTransfer->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->product->name ?? 'Product #' . $item->product_id }}</strong>
                                            @if($item->variant)
                                                <br><small class="text-muted">{{ $item->variant->color->name ?? '' }} {{ $item->variant->size->name ?? '' }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center font-weight-bold text-primary">
                                            {{ number_format((float)$item->qty, 2) }} {{ $item->product->unit->name ?? 'pcs' }}
                                        </td>
                                        <td>
                                            <input type="number" step="any" min="0" max="{{ $item->qty }}" name="received_items[{{ $item->id }}]" class="form-control font-weight-bold text-center" value="{{ (float)$item->qty }}" required>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light border-top text-right py-3">
                    <a href="{{ route('admin.stock-transfers.show', $stockTransfer->id) }}" class="btn btn-secondary mr-2">Cancel</a>
                    <button type="button" class="btn btn-success px-4 font-weight-bold shadow-sm" id="btn_confirm_receipt">
                        <i class="fas fa-check-double mr-1"></i> Confirm & Add to Destination Stock
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#btn_confirm_receipt').on('click', function(e) {
            e.preventDefault();
            const $form = $(this).closest('form');
            Swal.fire({
                title: "Confirm Goods Receipt?",
                text: "This will add the verified received quantities into the destination warehouse stock.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#47c363",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, Confirm Receipt!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    $form.submit();
                }
            });
        });
    });
</script>
@endpush
