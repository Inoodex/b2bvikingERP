<style>
    .add-to-basket.added, .add-to-request-basket.added {
        background: #28a745 !important;
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        color: #fff !important;
    }
</style>
<div class="row" style="margin: 0 -6px;">
    @foreach ($products as $key => $product)
    <div class="col-6 col-md-4 col-lg-5th col-xl-5th mb-3" style="padding: 0 6px;">
        <div class="card h-100 border-0 shadow-sm pp-card">
            <div class="pp-card-img-wrap d-flex align-items-center justify-content-center">
                @php
                    $imgSrc = $product->thumb_image 
                        ? (strpos($product->thumb_image, 'http') === 0 ? $product->thumb_image : asset('storage/'.$product->thumb_image)) 
                        : asset('uploads/no-image.svg');
                @endphp
                <img alt="{{ $product->name }}" 
                     src="{{ $imgSrc }}" 
                     onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';"
                     class="img-fluid" 
                     loading="{{ $key < 4 ? 'eager' : 'lazy' }}"
                     style="max-height: 100%; max-width: 100%; object-fit: contain;">

                <div class="pp-card-badges" style="top: 6px; left: 6px; gap: 3px;">
                    @if(Auth::user()->hasRole('Admin'))
                        @if($product->custom_label)
                            <span class="badge badge-info shadow-sm pp-badge-type">
                                {{ $product->custom_label }}
                            </span>
                        @endif
                    @endif
                    @if($product->productType)
                        @php
                            $badgeClass = 'badge-info';
                            $textStyle = '';
                            if($product->productType->slug == 'new-arrival'){
                                $badgeClass = 'badge-success';
                            } elseif($product->productType->slug == 'upcoming'){
                                $badgeClass = 'badge-warning';
                                $textStyle = 'color: #000;';
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }} pp-badge-type shadow-sm" style="{{ $textStyle }}">
                            {{ $product->productType->name }}
                        </span>
                    @endif
                    @if($product->product_type)
                        <span class="badge {{ $product->product_type == 'new_arrival' ? 'badge-success' : ($product->product_type == 'upcoming' ? 'badge-warning' : 'badge-primary') }} pp-badge-type shadow-sm" style="{{ $product->product_type == 'upcoming' ? 'color: #000;' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $product->product_type)) }} (Legacy)
                        </span>
                    @endif
                </div>

                <div class="pp-card-actions" style="top: 6px; right: 6px; gap: 4px;">
                    @if(Auth::user()->can('Manage Products'))
                        <label class="custom-switch m-0 pp-status-switch">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="{{ $product->id }}" class="custom-switch-input change-status" {{ $product->status ? 'checked' : '' }}>
                            <span class="custom-switch-indicator shadow-sm"></span>
                        </label>
                    @else
                        <span class="badge {{ $product->status ? 'badge-success' : 'badge-danger' }} shadow-sm px-2 py-1" style="border-radius: 6px; font-size: 9px;">{{ $product->status ? 'Active' : 'Inactive' }}</span>
                    @endif
                </div>
            </div>

            <div class="pp-card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="pp-badge-category">{{ $product->category->name ?? 'Uncategorized' }}</span>
                    @php $stock = $product->inventory_stock; @endphp
                    <span class="pp-badge-stock {{ $stock > 0 ? 'in-stock' : 'out-of-stock' }}">
                        {{ (float)$stock }}
                    </span>
                </div>

                <h6 class="pp-card-title" title="{{ $product->name }}">
                    {{ $product->name }}
                </h6>

                @php
                    $avgRating = 0;
                    $reviewCount = 0;
                    $isOutletUserRole = Auth::user()->hasRole(['Outlet User', 'User']);
                    try {
                        $avgRating = round($product->reviews()->avg('rating') ?? 0, 1);
                        $reviewCount = $product->reviews()->count();
                    } catch (\Exception $e) { }
                @endphp

                <div class="d-flex align-items-center mb-2" style="margin-top: 3px;">
                    <div class="d-flex align-items-center" style="gap: 2px;">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star" style="color: {{ $i <= round($avgRating) ? '#f59e0b' : '#d1d5db' }}; font-size: 9px;"></i>
                        @endfor
                        @if($reviewCount > 0)
                            <span style="color: var(--pp-muted); font-size: 9px; font-weight: 600; margin-left: 3px;">({{ $reviewCount }})</span>
                        @endif
                    </div>
                </div>

                <div class="pp-price-box">
                    @php
                        $hasVariants = $product->variants->count() > 0;
                        $userCanManage = Auth::user()->can('Manage Products');
                        $isOutletUser = Auth::user()->hasRole(['Outlet User', 'User']);
                    @endphp

                    @if($hasVariants)
                        <div class="pp-variant-scroll">
                            @foreach($product->variants as $v)
                                @php
                                    $variantLabel = trim((string) ($v->name ?? ''));
                                    if ($variantLabel === '') {
                                        $variantLabel = trim(implode(' ', array_filter([
                                            is_object($v->getRelation('color') ?? null) ? optional($v->getRelation('color'))->name : null,
                                            is_object($v->getRelation('size') ?? null) ? optional($v->getRelation('size'))->name : null,
                                            $v->color ?? null,
                                            $v->size ?? null,
                                        ])));
                                    }
                                    $variantLabel = $variantLabel !== '' ? $variantLabel : ('#' . $v->id);
                                @endphp
                                <div class="d-flex justify-content-between align-items-center py-1 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: var(--pp-border) !important;">
                                    <span style="font-size: 9px; font-weight: 600; color: var(--pp-muted);">{{ $variantLabel }}</span>
                                    <span style="font-size: 11px; font-weight: 700; color: var(--pp-ink);">
                                        @if($userCanManage || $isOutletUser)
                                            {{ formatConverted($v->price > 0 ? $v->price : $product->price) }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        @if($userCanManage)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="border-color: var(--pp-border) !important;">
                                <span class="pp-price-label">Purchase</span>
                                <span class="pp-price-value" style="color: #6b7280; font-size: 11px;">{{ formatConverted($product->purchase_price) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="border-color: var(--pp-border) !important;">
                                <span class="pp-price-label">Wholesale</span>
                                <span class="pp-price-value" style="color: #16a34a; font-size: 11px;">{{ formatConverted($product->outlet_price) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-1">
                                <span class="pp-price-label">Retail</span>
                                <span class="pp-price-value" style="font-size: 11px;">{{ formatConverted($product->price) }}</span>
                            </div>
                        @elseif($isOutletUser)
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="pp-price-label">Selling Price</span>
                                <span class="pp-price-value" style="font-size: 11px;">{{ formatConverted($product->price) }}</span>
                            </div>
                        @endif
                    @endif

                    <div class="mt-2 d-flex" style="gap: 6px;">
                        @can('Manage Order Place')
                        <button type="button" class="btn btn-sm pp-btn-card pp-btn-amber flex-fill add-to-basket" data-id="{{ $product->id }}" data-has-variants="{{ $hasVariants ? 1 : 0 }}" title="Procurement Basket" style="padding: 7px 0; font-size: 13px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shopping-basket"></i>
                        </button>
                        @endcan
                        @can('Create Product Requests')
                        <button type="button" class="btn btn-sm pp-btn-card pp-btn-outline flex-fill add-to-request-basket" data-id="{{ $product->id }}" data-has-variants="{{ $hasVariants ? 1 : 0 }}" title="Stock Transfer Basket" style="padding: 7px 0; font-size: 13px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-file-import"></i>
                        </button>
                        @endcan
                    </div>
                </div>

                @can('Manage Products')
                <div class="mt-2 d-flex" style="gap: 4px;">
                    <a href="{{ route('admin.products.edit', ['product' => $product->id] + request()->query()) }}" class="btn btn-outline-primary btn-sm flex-fill pp-edit-btn">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <a href="{{ route('admin.products.destroy', $product->id) }}" class="btn btn-outline-danger btn-sm flex-fill pp-edit-btn delete-item">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row mt-3 mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pp-pagination-wrapper" style="gap: 8px;">
            <span class="pp-count-badge">
                <i class="fas fa-list-ul mr-1" style="opacity: 0.5; font-size: 9px;"></i>
                Showing <strong>{{ $products->firstItem() ?? 0 }}</strong>–<strong>{{ $products->lastItem() ?? 0 }}</strong> of <strong>{{ $products->total() }}</strong>
            </span>
            <div class="pp-pagination">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
