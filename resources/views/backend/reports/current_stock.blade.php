@extends('backend.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-boxes mr-2"></i>Current Stock Report</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
            <div class="breadcrumb-item">Current Stock</div>
        </div>
    </div>

    <div class="section-body">

        {{-- ── Filter Card ── --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h4><i class="fas fa-filter mr-2 text-primary"></i>Filter Options</h4>
                <div class="card-header-action">
                    <a href="{{ route('admin.reports.current-stock') }}" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="stock-filter-form" action="{{ route('admin.reports.current-stock') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-600">Category</label>
                                <select name="category_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ request()->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-600">Company / Vendor</label>
                                <select name="vendor_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Vendors</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request()->vendor_id == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->shop_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-600">Stock Status</label>
                                <select name="stock_status" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Stock</option>
                                    <option value="in_stock"     {{ request()->stock_status == 'in_stock'     ? 'selected' : '' }}>In Stock</option>
                                    <option value="out_of_stock" {{ request()->stock_status == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Table Card ── --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h4>
                    <i class="fas fa-table mr-2 text-primary"></i>Detailed Stock List
                </h4>
                <div class="card-header-action d-flex align-items-center" style="gap:8px;">
                    {{-- <span class="badge badge-primary px-3 py-2" style="font-size:12px;">
                        {{ number_format($products->total()) }} Products
                    </span> --}}
                    <a href="{{ route('admin.reports.current-stock.export', request()->all()) }}"
                       class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel mr-1"></i>Export Excel
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="current-stock-table">
                        <thead style="background: #f8f9ff;">
                            <tr>
                                <th style="width:30px;padding:14px 6px;font-size:11px;color:#6c757d;font-weight:700;border-bottom:2px solid #e9ecef;text-align:center;">#</th>
                                <th style="width:75px;padding:14px 12px;font-size:12px;color:#6c757d;font-weight:700;border-bottom:2px solid #e9ecef;">Photo</th>
                                <th style="padding:14px 12px;font-size:12px;color:#6c757d;font-weight:700;border-bottom:2px solid #e9ecef;">Product / Item Code</th>
                                <th style="padding:14px 12px;font-size:12px;color:#6c757d;font-weight:700;border-bottom:2px solid #e9ecef;">Category</th>
                                <th style="padding:14px 12px;font-size:12px;color:#6c757d;font-weight:700;border-bottom:2px solid #e9ecef;">Company / Vendor</th>
                                <th style="width:100px;padding:14px 12px;font-size:12px;color:#6c757d;font-weight:700;border-bottom:2px solid #e9ecef;text-align:center;">Stock Qty</th>
                                <th style="width:130px;padding:14px 12px;font-size:12px;color:#6c757d;font-weight:700;border-bottom:2px solid #e9ecef;text-align:right;">Buying Price</th>
                                <th style="width:150px;padding:14px 12px;font-size:12px;color:#6c757d;font-weight:700;border-bottom:2px solid #e9ecef;text-align:right;">Total Buying Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $currencyIcon = $settings->currency_icon ?? 'Kr.'; @endphp

                            @forelse($products as $index => $product)
                            @php
                                $qty         = $product->inventory_stocks_sum_quantity ?? 0;
                                $buyingPrice = $product->purchase_price ?? 0;
                                $totalBuying = $qty * $buyingPrice;
                                $serial      = ($products->currentPage() - 1) * $products->perPage() + $index + 1;
                            @endphp
                            <tr style="border-bottom:1px solid #f1f3f7;transition:background .15s;">

                                {{-- Serial --}}
                                <td style="padding:12px 6px;vertical-align:middle;color:#adb5bd;font-size:11px;text-align:center;">{{ $serial }}</td>

                                {{-- Photo --}}
                                <td style="padding:10px 12px;vertical-align:middle;">
                                    @if($product->thumb_image)
                                        <img src="{{ asset('storage/' . $product->thumb_image) }}"
                                             alt="{{ $product->name }}"
                                             loading="lazy"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                                             style="width:50px;height:50px;object-fit:cover;border-radius:8px;border:1px solid #e9ecef;background:#f8f9fa;">
                                        <div class="rounded align-items-center justify-content-center text-muted"
                                             style="display:none;width:50px;height:50px;border:1px solid #e9ecef;background:#f8f9fa;">
                                            <i class="fas fa-image" style="font-size:14px;"></i>
                                        </div>
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center text-muted"
                                             style="width:50px;height:50px;border:1px solid #e9ecef;background:#f8f9fa;">
                                            <i class="fas fa-image" style="font-size:14px;"></i>
                                        </div>
                                    @endif
                                </td>

                                {{-- Product name + code --}}
                                <td style="padding:12px;vertical-align:middle;">
                                    <div style="font-size:13px;font-weight:600;color:#2d3748;line-height:1.3;">{{ $product->name }}</div>
                                    @if($product->sku || $product->product_number)
                                        <div style="font-size:11px;color:#adb5bd;margin-top:2px;">
                                            <i class="fas fa-barcode mr-1"></i>{{ $product->sku ?? $product->product_number }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Category --}}
                                <td style="padding:12px;vertical-align:middle;">
                                    <span style="display:inline-block;padding:3px 10px;background:#f0f4ff;color:#4361ee;border-radius:20px;font-size:11px;font-weight:600;">
                                        {{ $product->category->name ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- Vendor --}}
                                <td style="padding:12px;vertical-align:middle;font-size:12px;color:#495057;">
                                    {{ $product->vendor->shop_name ?? '—' }}
                                </td>

                                {{-- Stock Qty --}}
                                <td style="padding:12px;vertical-align:middle;text-align:center;">
                                    @if($qty > 0)
                                        <span style="display:inline-block;padding:4px 12px;background:#d4edda;color:#155724;border-radius:20px;font-size:12px;font-weight:700;">
                                            {{ number_format($qty) }}
                                        </span>
                                    @else
                                        <span style="display:inline-block;padding:4px 12px;background:#f8d7da;color:#721c24;border-radius:20px;font-size:12px;font-weight:700;">
                                            0
                                        </span>
                                    @endif
                                </td>

                                {{-- Buying Price --}}
                                <td style="padding:12px;vertical-align:middle;text-align:right;font-size:13px;color:#495057;">
                                    {{ $currencyIcon }}{{ number_format($buyingPrice, 2) }}
                                </td>

                                {{-- Total Buying Price --}}
                                <td style="padding:12px;vertical-align:middle;text-align:right;">
                                    <span style="font-size:13px;font-weight:700;color:{{ $totalBuying > 0 ? '#2d3748' : '#adb5bd' }};">
                                        {{ $currencyIcon }}{{ number_format($totalBuying, 2) }}
                                    </span>
                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div style="padding:40px 0;">
                                        <i class="fas fa-box-open fa-3x mb-3" style="color:#dee2e6;"></i>
                                        <div style="font-size:15px;color:#adb5bd;font-weight:500;">No products found</div>
                                        <div style="font-size:12px;color:#ced4da;margin-top:4px;">Try adjusting your filters</div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination bar --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap px-4 py-3"
                     style="border-top:1px solid #f1f3f7;background:#fafbff;">
                    <p class="mb-0 text-muted" style="font-size:13px;">
                        Showing
                        <strong class="text-dark">{{ $products->firstItem() ?? 0 }}</strong>
                        –
                        <strong class="text-dark">{{ $products->lastItem() ?? 0 }}</strong>
                        of
                        <strong class="text-primary">{{ number_format($products->total()) }}</strong>
                        products
                    </p>
                    <div class="custom-pagination">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
#current-stock-table tbody tr:hover {
    background-color: #f8f9ff !important;
}
</style>
@endsection
