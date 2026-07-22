@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product Purchase History</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Product Purchase Tracking</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Track Product Purchases by Vendor & User</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.reports.product-purchase-history') }}" method="GET" class="mb-4">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label>Filter by Product</label>
                                        <select name="product_id" class="form-control select2">
                                            <option value="">All Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }} ({{ $product->sku }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>&nbsp;</label>
                                        <div class="d-flex">
                                            <button type="submit" class="btn btn-primary flex-grow-1 mr-2">
                                                <i class="fas fa-filter"></i> Filter
                                            </button>
                                            <a href="{{ route('admin.reports.product-purchase-history') }}" class="btn btn-secondary">
                                                <i class="fas fa-redo"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice No</th>
                                            <th>Product</th>
                                            <th>Vendor</th>
                                             <th>Created By</th>
                                             <th>Qty</th>
                                             <th>Base Unit Cost</th>
                                             <th>Vendor Unit Cost</th>
                                             <th>Base Total</th>
                                             <th>Vendor Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($details as $detail)
                                            <tr>
                                                <td>{{ $detail->purchase->date }}</td>
                                                <td>{{ $detail->purchase->invoice_no }}</td>
                                                <td>{{ $detail->product->name }}</td>
                                                <td>{{ $detail->purchase->vendor->shop_name ?? 'N/A' }}</td>
                                                <td>{{ $detail->purchase->user->name ?? 'System' }}</td>
                                                <td>{{ $detail->qty }}</td>
                                                <td>{!! formatConverted($detail->unit_cost) !!}</td>
                                                <td>
                                                    @if($detail->purchase->vendor)
                                                        {!! formatWithVendor($detail->unit_cost, $detail->purchase->vendor->currency_icon, $detail->purchase->vendor->currency_rate) !!}
                                                    @else
                                                        {!! formatConverted($detail->unit_cost) !!}
                                                    @endif
                                                </td>
                                                <td>{!! formatConverted($detail->total) !!}</td>
                                                <td>
                                                    @if($detail->purchase->vendor)
                                                        {!! formatWithVendor($detail->total, $detail->purchase->vendor->currency_icon, $detail->purchase->vendor->currency_rate) !!}
                                                    @else
                                                        {!! formatConverted($detail->total) !!}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted mb-3 text-center" style="font-size: 14px; font-weight: 500;">
                                Showing 
                                <span class="text-dark font-weight-bold">
                                    {{ $details->firstItem() ?? 0 }} - {{ $details->lastItem() ?? 0 }}
                                </span> 
                                of 
                                 <span class="text-dark font-weight-bold">
                                    {{ $details->total() }}
                                </span> 
                                Purchase History
                            </p>
                            <div class="d-flex justify-content-center flex-wrap custom-pagination">
                                {{ $details->links() }}
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
        $("#table-1").dataTable({
            "order": [[0, "desc"]],
            paging: false,
            info: false,
            searching: false
        });
    </script>
@endpush
