<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} — Verified Product Info</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            padding: 16px 8px;
        }
        .product-scan-card {
            max-width: 520px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .brand-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 18px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .verified-badge {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .product-img-wrapper {
            background: #f1f5f9;
            text-align: center;
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        .product-img-wrapper img {
            max-height: 220px;
            max-width: 100%;
            object-fit: contain;
            border-radius: 8px;
        }
        .price-badge {
            font-size: 24px;
            font-weight: 800;
            color: #0284c7;
        }
        .barcode-pill {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            display: inline-block;
            border: 1px solid #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="product-scan-card">
        {{-- Header --}}
        <div class="brand-header">
            <div>
                <span class="font-weight-bold text-white small" style="letter-spacing: 1px;">
                    <i class="fas fa-barcode text-primary mr-1"></i> B2B VIKING
                </span>
            </div>
            <div class="verified-badge">
                <i class="fas fa-check-circle mr-1"></i> Authentic Item
            </div>
        </div>

        {{-- Product Image --}}
        @php
            $imgSrc = null;
            if (!empty($product->thumb_image)) {
                if (file_exists(public_path($product->thumb_image))) {
                    $imgSrc = asset($product->thumb_image);
                } elseif (file_exists(public_path('storage/' . $product->thumb_image))) {
                    $imgSrc = asset('storage/' . $product->thumb_image);
                }
            }
        @endphp

        @if($imgSrc)
            <div class="product-img-wrapper">
                <img src="{{ $imgSrc }}" alt="{{ $product->name }}">
            </div>
        @endif

        {{-- Details Body --}}
        <div class="p-3 p-md-4">
            {{-- Category & Brand Tags --}}
            <div class="mb-2 d-flex flex-wrap align-items-center" style="gap: 6px;">
                @if($product->category)
                    <span class="badge badge-light border text-secondary font-weight-normal py-1 px-2">
                        <i class="fas fa-folder mr-1 text-muted"></i> {{ $product->category->name }}
                    </span>
                @endif
                @if($product->brand)
                    <span class="badge badge-light border text-secondary font-weight-normal py-1 px-2">
                        <i class="fas fa-tag mr-1 text-muted"></i> {{ $product->brand->name }}
                    </span>
                @endif
                @if($selectedVariant)
                    <span class="badge badge-primary py-1 px-2 font-weight-bold">
                        <i class="fas fa-check mr-1"></i> Scanned: {{ $selectedVariant->spec ?? $selectedVariant->name }}
                    </span>
                @endif
            </div>

            {{-- Product Title --}}
            <h5 class="font-weight-bold text-dark mb-1">
                {{ $product->name }}
            </h5>

            {{-- SKU & Barcode --}}
            <div class="mb-3 d-flex flex-wrap align-items-center small text-muted" style="gap: 8px;">
                <span>SKU: <strong class="text-dark font-monospace">{{ $selectedVariant?->sku ?? $product->sku ?? ('PRD-'.$product->id) }}</strong></span>
                <span>&bull;</span>
                <span class="barcode-pill">
                    <i class="fas fa-barcode mr-1"></i> {{ $selectedVariant?->barcode ?? $product->barcode ?? 'N/A' }}
                </span>
            </div>

            {{-- Price Display --}}
            <div class="bg-light p-3 rounded mb-3 d-flex justify-content-between align-items-center border">
                <div>
                    <span class="small text-muted font-weight-bold d-block text-uppercase">Recommended Retail Price</span>
                    <div class="price-badge">
                        ${{ number_format((float) ($selectedVariant?->price ?? $product->price), 2) }}
                    </div>
                </div>
                <div class="text-right">
                    <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 13px;">
                        <i class="fas fa-box-check mr-1"></i> Genuine Product
                    </span>
                </div>
            </div>

            {{-- Variants / Sizes if any --}}
            @if($product->variants->isNotEmpty())
                <div class="mb-3">
                    <label class="small text-muted font-weight-bold text-uppercase d-block mb-2">
                        Available Sizes / Options ({{ $product->variants->count() }})
                    </label>
                    <div class="d-flex flex-wrap" style="gap: 6px;">
                        @foreach($product->variants as $var)
                            @php $isCurrent = $selectedVariant && $selectedVariant->id === $var->id; @endphp
                            <span class="badge {{ $isCurrent ? 'badge-primary' : 'badge-light border text-dark' }} py-2 px-3 font-weight-bold" style="font-size: 12px;">
                                {{ $var->spec ?? $var->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Short Description --}}
            @if(!empty($product->short_description))
                <div class="mb-3 small text-muted">
                    {!! nl2br(e(strip_tags($product->short_description))) !!}
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="mt-4 pt-3 border-top text-center">
                @if(auth()->check())
                    <a href="{{ route('product.details', $product->slug) }}" class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm">
                        <i class="fas fa-shopping-bag mr-1"></i> View & Order in B2B Portal
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm">
                        <i class="fas fa-sign-in-alt mr-1"></i> B2B Wholesale Login / Order
                    </a>
                @endif
                <div class="mt-2">
                    <a href="{{ url('/') }}" class="small text-muted">
                        <i class="fas fa-home mr-1"></i> Visit B2B Viking
                    </a>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-light py-2 text-center small text-muted border-top">
            GS1 Digital Link &bull; Scanned via Smartphone Camera
        </div>
    </div>
</body>
</html>
