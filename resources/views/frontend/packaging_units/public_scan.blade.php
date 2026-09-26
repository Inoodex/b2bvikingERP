<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handling Unit Manifest: {{ $handlingUnit->hu_code }} — B2B Viking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            padding: 16px 8px;
        }
        .manifest-card {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .manifest-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            padding: 24px 20px;
            text-align: center;
        }
        .code-pill {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            letter-spacing: 1px;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 15px;
            display: inline-block;
            margin-top: 6px;
        }
        .stat-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        .stat-value {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }
        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="manifest-card">
        {{-- Header --}}
        <div class="manifest-header">
            <div class="small font-weight-bold text-uppercase" style="opacity: 0.9; letter-spacing: 1.5px;">
                <i class="fas fa-shield-alt mr-1"></i> B2B Viking ERP Logistics Verified
            </div>
            <h4 class="mt-2 mb-1 font-weight-bold">
                {{ $handlingUnit->packagingType?->name ?? 'Handling Unit' }}
            </h4>
            <div class="code-pill">
                <i class="fas fa-qrcode mr-1"></i> {{ $handlingUnit->hu_code }}
            </div>
        </div>

        <div class="p-3 p-md-4">
            {{-- Quick Stats Row --}}
            <div class="row no-gutters mb-3">
                <div class="col-4 pr-1">
                    <div class="stat-box">
                        <div class="stat-value text-primary">{{ number_format($handlingUnit->total_quantity) }}</div>
                        <div class="stat-label">Total Pieces</div>
                    </div>
                </div>
                <div class="col-4 px-1">
                    <div class="stat-box">
                        <div class="stat-value text-success text-uppercase" style="font-size: 15px; padding-top: 4px;">
                            {{ $handlingUnit->status }}
                        </div>
                        <div class="stat-label">Status</div>
                    </div>
                </div>
                <div class="col-4 pl-1">
                    <div class="stat-box">
                        <div class="stat-value text-dark" style="font-size: 16px; padding-top: 3px;">
                            {{ $handlingUnit->gross_weight ? number_format($handlingUnit->gross_weight, 2).' kg' : 'N/A' }}
                        </div>
                        <div class="stat-label">Weight</div>
                    </div>
                </div>
            </div>

            {{-- Specifications Details --}}
            <div class="bg-light p-3 rounded mb-3 border small">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Hierarchy Level:</span>
                    <strong>Level {{ $handlingUnit->packagingType?->level_order ?? 1 }} ({{ $handlingUnit->packagingType?->code ?? 'UNIT' }})</strong>
                </div>
                @if($handlingUnit->parentUnit)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Parent Container:</span>
                        <span class="font-monospace font-weight-bold text-primary">{{ $handlingUnit->parentUnit->hu_code }}</span>
                    </div>
                @endif
                @if($handlingUnit->batch_no)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Batch / Lot:</span>
                        <strong class="font-monospace">{{ $handlingUnit->batch_no }}</strong>
                    </div>
                @endif
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Packaging Date:</span>
                    <strong>{{ $handlingUnit->created_at?->format('d M Y, h:i A') }}</strong>
                </div>
            </div>

            {{-- Products Inside --}}
            @if($handlingUnit->items->isNotEmpty())
                <div class="mb-3">
                    <h6 class="font-weight-bold text-dark mb-2">
                        <i class="fas fa-boxes text-primary mr-1"></i> Products Inside Container ({{ $handlingUnit->items->count() }})
                    </h6>
                    <div class="list-group shadow-sm">
                        @foreach($handlingUnit->items as $item)
                            <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
                                <div class="min-w-0 pr-2">
                                    <div class="font-weight-bold text-dark text-truncate" style="max-width: 320px;">
                                        {{ $item->product?->name }}
                                    </div>
                                    <div class="small text-muted">
                                        @if($item->variant)
                                            <span class="badge badge-light border">{{ $item->variant->spec ?? $item->variant->name }}</span>
                                        @endif
                                        <span class="font-monospace ml-1">SKU: {{ $item->variant?->sku ?? $item->product?->sku }}</span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="badge badge-primary px-3 py-2 font-weight-bold" style="font-size: 13px;">
                                        {{ $item->quantity }} pcs
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Nested Child Containers Inside --}}
            @if($handlingUnit->childUnits->isNotEmpty())
                <div class="mb-3">
                    <h6 class="font-weight-bold text-dark mb-2">
                        <i class="fas fa-cubes text-info mr-1"></i> Child Boxes Inside ({{ $handlingUnit->childUnits->count() }})
                    </h6>
                    <div class="list-group shadow-sm">
                        @foreach($handlingUnit->childUnits as $child)
                            <a href="{{ route('public.barcode.scan', $child->hu_code) }}" class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="font-monospace font-weight-bold text-dark">
                                        <i class="fas fa-box text-secondary mr-1"></i> {{ $child->hu_code }}
                                    </div>
                                    <div class="small text-muted">{{ $child->packagingType?->name }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-info px-2 py-1 font-weight-bold">{{ $child->total_quantity }} pcs</span>
                                    <i class="fas fa-chevron-right text-muted ml-1 small"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm px-4">
                    <i class="fas fa-home mr-1"></i> Visit B2B Viking Home
                </a>
            </div>
        </div>

        <div class="bg-light py-2 text-center small text-muted border-top">
            B2B Viking ERP &bull; Verified Logistics Manifest
        </div>
    </div>
</body>
</html>
