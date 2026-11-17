@if($products->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="best-sellers-table">
            <thead class="bg-light" style="font-size: 0.75rem; text-transform: uppercase; color: #475569; letter-spacing: 0.5px;">
                <tr>
                    <th class="pl-4 text-center" style="width: 70px;">Rank</th>
                    <th>Product</th>
                    <th class="text-center" style="width: 140px;">Times Ordered</th>
                    <th class="text-center" style="width: 130px;">Total Sold</th>
                    <th class="text-right" style="width: 150px;">Total Value</th>
                    <th class="text-center" style="width: 130px;">Demand Share</th>
                    <th class="text-right pr-4" style="width: 140px;">Velocity vs Next</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $noImageFallback = asset('uploads/no-image.svg');
                @endphp
                @foreach($products as $i => $product)
                    @php
                        $rank = ($products->currentPage() - 1) * $products->perPage() + $i + 1;
                        $imgSrc = (!empty($product->thumb_image) && $product->thumb_image !== 'null') 
                            ? asset(ltrim($product->thumb_image, '/')) 
                            : $noImageFallback;
                    @endphp
                    <tr>
                        <td class="pl-4 text-center">
                            @if($rank === 1)
                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-0.5" style="background: #fbbf24; font-size: 11px;">
                                    <i class="fas fa-crown mr-1"></i>#1
                                </span>
                            @elseif($rank === 2)
                                <span class="badge badge-secondary font-weight-bold px-2 py-0.5" style="font-size: 11px;">
                                    <i class="fas fa-medal mr-1"></i>#2
                                </span>
                            @elseif($rank === 3)
                                <span class="badge font-weight-bold px-2 py-0.5 text-white" style="background: #b45309; font-size: 11px;">
                                    <i class="fas fa-medal mr-1"></i>#3
                                </span>
                            @else
                                <span class="text-muted font-weight-bold">#{{ $rank }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <img src="{{ $imgSrc }}" 
                                     onerror="this.onerror=null;this.src='{{ $noImageFallback }}';" 
                                     style="width: 38px; height: 38px; object-fit: contain; border-radius: 6px; background: #f8fafc; padding: 2px;" 
                                     class="border flex-shrink-0" alt="">
                                <div class="min-w-0">
                                    <strong class="text-dark d-block text-truncate" style="font-size: 13px; line-height: 1.25; margin-bottom: 2px;" title="{{ $product->product_name }}">
                                        {{ $product->product_name }}
                                    </strong>
                                    @if(!empty($product->category_name))
                                        <span class="badge badge-light border text-muted" style="font-size: 9.5px; padding: 1.5px 6px; font-weight: 600;">
                                            {{ $product->category_name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @if(($rankBy ?? 'frequency') === 'quantity')
                            <td class="text-center">
                                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">
                                    <i class="fas fa-shopping-cart mr-1 text-muted"></i>{{ number_format($product->times_ordered) }} orders
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success px-2.5 py-1 font-weight-bold shadow-sm" style="font-size: 11.5px; background: #059669;">
                                    <i class="fas fa-boxes mr-1"></i>{{ number_format($product->total_qty) }} pcs
                                </span>
                            </td>
                        @else
                            <td class="text-center">
                                <span class="badge badge-danger px-2.5 py-1 font-weight-bold shadow-sm" style="font-size: 11px;">
                                    <i class="fas fa-shopping-cart mr-1"></i>{{ number_format($product->times_ordered) }} orders
                                </span>
                            </td>
                            <td class="text-center font-weight-bold text-dark">{{ number_format($product->total_qty) }} pcs</td>
                        @endif
                        <td class="text-right font-weight-bold text-dark">{!! formatWithCurrency($product->total_value) !!}</td>
                        <td class="text-center">
                            <span class="badge badge-info px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                {{ number_format($product->category_share ?? 0, 1) }}%
                            </span>
                        </td>
                        <td class="text-right pr-4 font-weight-bold">
                            @if(isset($product->velocity_vs_next))
                                @if($product->velocity_vs_next > 0)
                                    <span class="text-success small" title="Ordered {{ $product->velocity_vs_next }}% more than rank #{{ $rank + 1 }}">
                                        <i class="fas fa-arrow-up mr-0.5"></i>+{{ $product->velocity_vs_next }}%
                                    </span>
                                @elseif($product->velocity_vs_next < 0)
                                    <span class="text-danger small">
                                        <i class="fas fa-arrow-down mr-0.5"></i>{{ $product->velocity_vs_next }}%
                                    </span>
                                @else
                                    <span class="text-muted small">0%</span>
                                @endif
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-body py-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-center flex-wrap" style="gap:12px;">
        <div class="text-muted font-weight-normal" style="font-size: 13.5px; color: #64748b;">
            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ number_format($products->total()) }} entries
        </div>
        <div>
            {{ $products->links('vendor.pagination.custom-report') }}
        </div>
    </div>
@else
    <div class="text-center py-5 text-muted">
        <i class="fas fa-fire fa-3x mb-3 text-danger" style="opacity:0.3;"></i>
        <p class="mb-0">
            @if(request('search') || request('category_id') || request('sub_category_id') || request('child_category_id'))
                No products found matching your filters.
            @else
                No order data available yet.
            @endif
        </p>
    </div>
@endif
