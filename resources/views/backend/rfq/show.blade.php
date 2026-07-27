@extends('backend.layouts.master')
@section('title', 'RFQ Details')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>RFQ Details: {{ $rfq->rfq_no }}</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Info</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Status:</strong> 
                                @if($rfq->status === 'open')
                                    <span class="badge badge-warning">Open</span>
                                @else
                                    <span class="badge badge-success">Closed</span>
                                @endif
                            </p>
                            <p><strong>Source:</strong> 
                                @if(empty($rfq->source_type))
                                    <span class="badge badge-secondary">Ad-hoc (Manual)</span>
                                @elseif($rfq->source_type === 'App\Models\Order')
                                    <span class="badge badge-info">Order #{{ $rfq->source_id }}</span>
                                @elseif($rfq->source_type === 'App\Models\CustomProductRequest')
                                    <span class="badge badge-primary">Custom Req #{{ $rfq->source_id }}</span>
                                @else
                                    <span class="badge badge-secondary">Unknown Source</span>
                                @endif
                            </p>
                            <p><strong>Due Date:</strong> {{ $rfq->due_date ? $rfq->due_date->format('d M, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Requested Items</h4>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                </tr>
                                @foreach($rfq->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->qty }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Invited Vendors & Quotations</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.rfqs.cs.create', $rfq->id) }}" class="btn btn-primary">Generate Comparison Statement (CS)</a>
                                <a href="{{ route('admin.rfqs.index') }}" class="btn btn-secondary">Back to List</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Vendor Name</th>
                                        <th>Invited At</th>
                                        <th>Quotation Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rfq->vendors as $rv)
                                        @php
                                            $quote = $rfq->quotations->where('vendor_id', $rv->vendor_id)->first();
                                        @endphp
                                        <tr>
                                            <td>{{ $rv->vendor->shop_name }}</td>
                                            <td>{{ $rv->invited_at ? $rv->invited_at->format('d M, Y H:i') : 'N/A' }}</td>
                                            <td>
                                                @if($quote)
                                                    <span class="badge badge-success">Received ({{ $quote->currency->symbol ?? '' }}{{ $quote->items->sum(fn($i) => $i->qty * $i->unit_price) }})</span>
                                                @else
                                                    <span class="badge badge-danger">Not Submitted</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$quote && $rfq->status === 'open')
                                                    <a href="{{ route('admin.rfqs.quotations.create', ['rfq' => $rfq->id, 'vendor' => $rv->vendor_id]) }}" class="btn btn-sm btn-primary">Submit Quotation</a>
                                                @elseif($quote)
                                                    <a href="#" class="btn btn-sm btn-info">View Quote</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
