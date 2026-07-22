@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Order Receive</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Order Receive</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Order Receive</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Order Receive</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice No</th>
                                            <th>Vendor</th>
                                            <th>Shipping</th>
                                            <th>Created By</th>
                                            <th>local currency Total</th>
                                            <th>Paid</th>
                                            <th>Due</th>
                                            <th>Vendor Total price</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Invoice Attachments</th>
                                            <th style="min-width: 180px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->date }}</td>
                                                <td>{{ $purchase->invoice_no }}</td>
                                                <td>{{ $purchase->vendor->shop_name ?? 'N/A' }}</td>
                                                <td>{{ $purchase->shipping_method ?? 'N/A' }}</td>
                                                <td>{{ $purchase->user->name ?? 'System' }}</td>
                                                <td>{{ formatConverted($purchase->total_amount) }}</td>
                                                <td class="text-success font-weight-bold">{{ formatConverted($purchase->paid_amount) }}</td>
                                                <td class="text-danger font-weight-bold">{{ formatConverted($purchase->due_amount) }}</td>
                                                <td>
                                                    @if($purchase->vendor)
                                                        @php
                                                            $vendorSubtotal = $purchase->details->sum(function($d) { return $d->unit_cost_vendor * $d->qty; });
                                                        @endphp
                                                        {{ $purchase->vendor->currency_icon }}{{ number_format($vendorSubtotal, 2) }}
                                                    @else
                                                        {{ formatConverted($purchase->total_amount) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($purchase->status == 1)
                                                        <div class="badge badge-success">Completed</div>
                                                    @else
                                                        <div class="badge badge-warning">Draft</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $paymentClass = match($purchase->payment_status) {
                                                            'paid' => 'badge-success',
                                                            'partial' => 'badge-warning',
                                                            default => 'badge-secondary',
                                                        };
                                                    @endphp
                                                    <div class="badge {{ $paymentClass }}">{{ ucfirst($purchase->payment_status ?? 'pending') }}</div>
                                                </td>
                                                <td>
                                                    @php
                                                        $attachmentList = $purchase->attachments;
                                                        if ($attachmentList->isEmpty() && $purchase->invoice_attachment) {
                                                            $attachmentList = collect([
                                                                (object) [
                                                                    'id' => null,
                                                                    'file_path' => $purchase->invoice_attachment,
                                                                    'original_name' => basename($purchase->invoice_attachment),
                                                                ]
                                                            ]);
                                                        }
                                                    @endphp

                                                    @if($attachmentList->count() > 0)
                                                        <div class="dropdown d-inline-block">
                                                            <button class="btn btn-info btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fas fa-paperclip"></i> {{ $attachmentList->count() }}
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right p-1" style="min-width: 260px;">
                                                                @foreach($attachmentList as $index => $attachment)
                                                                    @php
                                                                        $downloadUrl = !empty($attachment->id)
                                                                            ? route('admin.purchases.download-attachment', [$purchase->id, $attachment->id])
                                                                            : route('admin.purchases.download-legacy-attachment', $purchase->id);
                                                                    @endphp
                                                                    <div class="dropdown-item d-flex justify-content-between align-items-center px-2 py-1">
                                                                        <a href="{{ $downloadUrl }}"
                                                                           class="text-dark text-truncate pr-2"
                                                                           style="max-width: 190px;"
                                                                           title="{{ $attachment->original_name ?? ('Attachment ' . ($index + 1)) }}"
                                                                           download>
                                                                            {{ $attachment->original_name ?? ('Attachment ' . ($index + 1)) }}
                                                                        </a>

                                                                        @if(!empty($attachment->id))
                                                                            <form action="{{ route('admin.purchases.delete-attachment', [$purchase->id, $attachment->id]) }}" method="POST" class="attachment-delete-form">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="btn btn-link text-danger p-0" title="Delete Attachment">
                                                                                    <i class="fas fa-trash-alt"></i>
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="badge badge-light">No Attachment</span>
                                                    @endif
                                                </td>
                                                <td class="text-nowrap" style="min-width: 180px;">
                                                    @if((float) $purchase->due_amount > 0)
                                                        <a href="{{ route('admin.accounts.vendor-payments.record-payment', ['invoice_no' => $purchase->invoice_no]) }}" class="btn btn-dark btn-sm" title="Pay Vendor Invoice">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-primary btn-sm ml-1" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.purchases.view-invoice', $purchase->id) }}" target="_blank" class="btn btn-warning btn-sm" title="View Invoice"><i class="fas fa-file-invoice"></i></a>
                                                    <div class="btn-group ml-1">
                                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a href="{{ route('admin.purchases.download-pdf', $purchase->id) }}" class="dropdown-item">
                                                                <i class="fas fa-file-pdf text-danger"></i> PDF
                                                            </a>
                                                            <a href="{{ route('admin.purchases.download-excel', $purchase->id) }}" class="dropdown-item">
                                                                <i class="fas fa-file-excel text-success"></i> Excel
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                            class="btn btn-primary btn-sm ml-1 upload-attachment-btn"
                                                            data-toggle="modal"
                                                            data-target="#uploadAttachmentModal"
                                                            data-url="{{ route('admin.purchases.upload-attachments', $purchase->id) }}"
                                                            data-invoice="{{ $purchase->invoice_no }}"
                                                            title="Upload Invoice Attachment">
                                                        <i class="fas fa-file-upload"></i>
                                                    </button>
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
        </div>
    </section>

    <div class="modal fade" id="uploadAttachmentModal" tabindex="-1" role="dialog" aria-labelledby="uploadAttachmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="uploadAttachmentForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadAttachmentModalLabel">Upload Invoice Attachment</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Invoice No: <strong id="attachmentPurchaseNo">-</strong></p>
                        <div class="form-group mb-0">
                            <label>Only Invoice File (PDF, Excel, Image)</label>
                            <input type="file"
                                   class="form-control"
                                   name="invoice_attachments[]"
                                   multiple
                                   accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png">
                            <small class="text-muted">Max 50MB per file.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $("#table-1").dataTable({
            "order": [[0, "desc"]],
            "columnDefs": [
                { "sortable": false, "targets": [12] }
            ],
            "order": [[0, "desc"]]
        });

        $(document).on('click', '.upload-attachment-btn', function () {
            const uploadUrl = $(this).data('url');
            const invoiceNo = $(this).data('invoice');

            $('#uploadAttachmentForm').attr('action', uploadUrl);
            $('#attachmentPurchaseNo').text(invoiceNo);
        });

        $(document).on('submit', '.attachment-delete-form', function (e) {
            e.preventDefault();
            const form = this;

            Swal.fire({
                title: 'Delete Attachment?',
                text: "This file will be removed permanently.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    toastr.info('Delete cancelled');
                }
            });
        });
    </script>
@endpush
