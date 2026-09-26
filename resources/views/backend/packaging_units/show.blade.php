@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-barcode text-primary mr-2"></i> Handling Unit: {{ $handlingUnit->hu_code }}</h1>
            <div class="section-header-breadcrumb">
                <a href="{{ route('admin.packaging-units.index') }}" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
                <a href="{{ route('admin.packaging-units.print', $handlingUnit->id) }}" target="_blank" class="btn btn-info shadow-sm font-weight-bold">
                    <i class="fas fa-print mr-1"></i> Print 4×6 Logistics Label
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                {{-- Left: Barcode Card & Specs --}}
                <div class="col-lg-5 col-md-6">
                    {{-- 4x6 Label Visual Preview --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-dark font-weight-bold mb-0">
                                <i class="fas fa-tag text-primary mr-1"></i> GS1 Logistics Label Preview
                            </h6>
                            <span class="badge badge-light border text-dark font-monospace">4" × 6" (100×150mm)</span>
                        </div>
                        <div class="card-body text-center p-4">
                            <div class="p-3 border rounded bg-white shadow-sm mb-3 text-center mx-auto" style="max-width: 320px;">
                                <div class="font-weight-bold text-dark text-uppercase letter-spacing-1 mb-1" style="font-size: 13px;">
                                    B2B Viking ERP Logistics
                                </div>
                                <div class="badge badge-dark text-white text-uppercase px-2 py-1 mb-3">
                                    {{ $handlingUnit->packagingType?->name ?? 'Handling Unit' }}
                                </div>

                                @php
                                    $huScanUrl = route('public.barcode.scan', $handlingUnit->hu_code);
                                    $huQrSvg = app(\App\Services\Barcode\NativeBarcodeGenerator::class)->getQrCodeSvg($huScanUrl, 80, 0);
                                @endphp
                                <div class="barcode-container my-3 p-2 bg-light rounded d-flex align-items-center justify-content-between text-center" style="gap: 8px;">
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="max-height: 48px; overflow: hidden;">
                                            {!! $barcodeSvg !!}
                                        </div>
                                        <div class="font-monospace font-weight-bold text-dark mt-1" style="font-size: 13px; letter-spacing: 0.5px;">
                                            {{ $handlingUnit->hu_code }}
                                        </div>
                                        <span class="badge badge-secondary py-0 px-1" style="font-size: 9px;">1D Code-128</span>
                                    </div>
                                    <div style="width: 70px; flex-shrink: 0; border-left: 1px dashed #cbd5e1; padding-left: 6px;">
                                        <div style="width: 50px; height: 50px; margin: 0 auto;">
                                            {!! $huQrSvg !!}
                                        </div>
                                        <a href="{{ $huScanUrl }}" target="_blank" class="badge badge-info py-0 px-1 text-white text-decoration-none mt-1 d-block" style="font-size: 8.5px;" title="Test customer smartphone scan">
                                            <i class="fas fa-external-link-alt mr-1"></i> Scan
                                        </a>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between text-left small border-top pt-2 mt-2">
                                    <div>
                                        <span class="text-muted">Total Pcs:</span> <strong>{{ number_format($handlingUnit->total_quantity) }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted">Weight:</span> <strong>{{ $handlingUnit->gross_weight ? number_format($handlingUnit->gross_weight, 3).' kg' : 'N/A' }}</strong>
                                    </div>
                                </div>
                                @if ($handlingUnit->batch_no)
                                    <div class="text-left small mt-1 text-muted">
                                        <span>Batch / Lot:</span> <strong class="text-dark">{{ $handlingUnit->batch_no }}</strong>
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('admin.packaging-units.print', $handlingUnit->id) }}" target="_blank" class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm">
                                <i class="fas fa-print mr-1"></i> Open & Print 4×6 Shipping Label
                            </a>
                        </div>
                    </div>

                    {{-- Container Info --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="text-dark font-weight-bold mb-0">
                                <i class="fas fa-info-circle text-primary mr-1"></i> Container Specifications
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <tr>
                                    <th class="border-top-0 text-muted" style="width: 40%;">Packaging Type</th>
                                    <td class="border-top-0 font-weight-bold text-dark">
                                        {{ $handlingUnit->packagingType?->name }} (Level {{ $handlingUnit->packagingType?->level_order }})
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Hierarchy Parent</th>
                                    <td>
                                        @if ($handlingUnit->parentUnit)
                                            <a href="{{ route('admin.packaging-units.show', $handlingUnit->parentUnit->id) }}" class="font-weight-bold text-primary">
                                                <i class="fas fa-level-up-alt mr-1"></i> {{ $handlingUnit->parentUnit->hu_code }}
                                            </a>
                                            <div class="small text-muted">{{ $handlingUnit->parentUnit->packagingType?->name }}</div>
                                        @else
                                            <span class="badge badge-light text-muted">Root Top-Level Container</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Status</th>
                                    <td>
                                        <span class="badge badge-success text-uppercase">{{ $handlingUnit->status }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Packed By</th>
                                    <td>{{ $handlingUnit->packer?->name ?? 'System Admin' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Packed At</th>
                                    <td>{{ $handlingUnit->packed_at ? $handlingUnit->packed_at->format('d M Y, H:i:s') : $handlingUnit->created_at->format('d M Y, H:i:s') }}</td>
                                </tr>
                                @if ($handlingUnit->notes)
                                    <tr>
                                        <th class="text-muted">Notes</th>
                                        <td>{{ $handlingUnit->notes }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Right: Nested Containers & Items Breakdown --}}
                <div class="col-lg-7 col-md-6">
                    {{-- Nested Child Handling Units (if any) --}}
                    @if ($handlingUnit->childUnits->isNotEmpty())
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-dark font-weight-bold mb-0">
                                    <i class="fas fa-sitemap text-primary mr-1"></i> Nested Sub-Containers ({{ $handlingUnit->childUnits->count() }})
                                </h6>
                                <span class="badge badge-primary py-1 px-2 font-weight-bold">Tier 2/3 Nesting</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Sub-Container Code</th>
                                            <th>Type</th>
                                            <th class="text-center">Items Inside</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($handlingUnit->childUnits as $child)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.packaging-units.show', $child->id) }}" class="font-weight-bold font-monospace text-primary">
                                                        <i class="fas fa-box text-secondary mr-1"></i> {{ $child->hu_code }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light border text-dark">{{ $child->packagingType?->name }}</span>
                                                </td>
                                                <td class="text-center font-weight-bold text-success">
                                                    {{ number_format($child->total_quantity) }} pcs
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.packaging-units.show', $child->id) }}" class="btn btn-xs btn-outline-primary">
                                                        View <i class="fas fa-arrow-right ml-1"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Direct Items inside this Container --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-dark font-weight-bold mb-0">
                                <i class="fas fa-list-ul text-primary mr-1"></i> Direct Packed Products & Variants ({{ $handlingUnit->items->count() }})
                            </h6>
                            <span class="badge badge-success font-weight-bold py-1 px-3">
                                Total: {{ number_format($handlingUnit->items->sum('quantity')) }} pcs
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product / Variant</th>
                                        <th>Barcode / SKU</th>
                                        <th>Batch</th>
                                        <th class="text-center">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($handlingUnit->items as $item)
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold text-dark small">{{ $item->product?->name }}</div>
                                                @if ($item->variant)
                                                    <span class="badge badge-dark py-0" style="font-size: 10.5px;">
                                                        <i class="fas fa-tag text-warning mr-1"></i> {{ $item->variant->name }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="small font-monospace text-muted">
                                                {{ $item->variant?->barcode ?? $item->product?->barcode ?? $item->variant?->sku ?? $item->product?->sku ?? 'N/A' }}
                                            </td>
                                            <td class="small text-muted">{{ $item->batch_no ?? '—' }}</td>
                                            <td class="text-center font-weight-bold text-dark">{{ number_format($item->quantity) }} pcs</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted small">
                                                <i class="fas fa-info-circle mr-1"></i> No direct loose items. Contents are packaged in sub-containers.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Full Flattened Manifest (All Items inside this tree) --}}
                    @if (count($flattenedContents) > 0 && $handlingUnit->childUnits->isNotEmpty())
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-dark font-weight-bold mb-0">
                                    <i class="fas fa-file-invoice text-info mr-1"></i> Complete Manifest (Direct & Sub-Containers)
                                </h6>
                                <span class="badge badge-dark py-1 px-3">Grand Total: {{ number_format($handlingUnit->total_quantity) }} pcs</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="bg-light small">
                                        <tr>
                                            <th>Location / Box</th>
                                            <th>Product & Spec</th>
                                            <th class="text-center">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @foreach ($flattenedContents as $fc)
                                            <tr>
                                                <td class="font-monospace text-muted">{{ $fc['handling_unit_code'] }} ({{ $fc['packaging_type'] }})</td>
                                                <td>
                                                    <strong>{{ $fc['product_name'] }}</strong>
                                                    @if (!empty($fc['variant_name']))
                                                        <span class="badge badge-light border ml-1">{{ $fc['variant_name'] }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center font-weight-bold">{{ $fc['quantity'] }} pcs</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
