@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Current Inventory Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Inventory Stock</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Stock Levels</h4>
                        </div>
                        <div class="card-body">
                            <form id="search-form" action="{{ route('admin.inventory-reports.index') }}" method="GET">
                                <div class="row mb-3">
                                    <div class="col-md-5">
                                        <div class="form-group mb-0">
                                            <select name="category_id" class="form-control select2">
                                                <option value="">All Categories</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group mb-0">
                                            <input type="text" name="search" class="form-control" placeholder="Search product name or number..." value="{{ request('search') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="d-flex">
                                            <button type="submit" class="btn btn-primary mr-2" title="Search"><i class="fas fa-search"></i></button>
                                            <a href="{{ route('admin.inventory-reports.index') }}" class="btn btn-danger" title="Reset"><i class="fas fa-sync-alt"></i> Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-inventory">
                                    <thead>
                                        <tr>
                                            <th width="80">Image</th>
                                            <th>Product Name</th>
                                            <th>Variant</th>
                                            <th>Item Number</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stocks as $stock)
                                            <tr>
                                                <td>
                                                    @if ($stock->product && $stock->product->thumb_image)
                                                        <img src="{{ asset('storage/' . $stock->product->thumb_image) }}"
                                                            alt=""
                                                            style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px;">
                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small"
                                                            style="width: 45px; height: 45px;">N/A</div>
                                                    @endif
                                                </td>
                                                <td>{{ $stock->product->name ?? 'N/A' }}</td>
                                                <td>{{ $stock->variant ? $stock->variant->name : '-' }}</td>
                                                <td>{{ $stock->product->product_number ?? '-' }}</td>
                                                <td>{{ $stock->product->category->name ?? '-' }}</td>
                                                <td
                                                    class="font-weight-bold {{ $stock->quantity <= 5 ? 'text-danger' : 'text-success' }}">
                                                    {{ $stock->quantity }}
                                                </td>
                                                <td>
                                                    @if ($stock->quantity > 0)
                                                        <div class="badge badge-success">In Stock</div>
                                                    @else
                                                        <div class="badge badge-danger">Out of Stock</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>

                                </table>
                                {{-- <div class="d-flex justify-content-center flex-wrap custom-pagination">
                                    {{ $stocks->links() }}
                                </div> --}}
                                

        {{-- <p class="text-muted mb-3 text-center" style="font-size: 14px; font-weight: 500;">
            Showing <span class="text-dark font-weight-bold">{{ $stocks->firstItem() ?? 0 }} - {{ $stocks->lastItem() ?? 0 }}</span> 
            of <span class="text-dark font-weight-bold">{{ $stocks->total() }}</span> products and in stock {{$stocks->sum('quantity')}}
        </p>
        <div class="d-flex justify-content-center flex-wrap custom-pagination">
            {{ $stocks->links() }}
        </div> --}}
        <p class="text-muted mb-3 text-center" style="font-size: 14px; font-weight: 500;">
    Showing 
    <span class="text-dark font-weight-bold">
        {{ $stocks->firstItem() ?? 0 }} - {{ $stocks->lastItem() ?? 0 }}
    </span> 
    of 
    <span class="text-dark font-weight-bold">
        {{ $stocks->total() }}
    </span> 
    products |

    Page Qty: <strong>{{ $pageQuantity }}</strong> |

    Total Qty: <strong>{{ $totalQuantity }}</strong>
</p>

<div class="d-flex justify-content-center flex-wrap custom-pagination">
    {{ $stocks->links() }}
</div>


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
        // $("#table-inventory").dataTable({
        //     dom: 'Bfrtip',
        //     buttons: [
        //         {
        //             extend: 'copy',
        //             className: 'btn btn-primary'
        //         },
        //         {
        //             extend: 'csv',
        //             className: 'btn btn-primary'
        //         },
        //         {
        //             extend: 'excel',
        //             className: 'btn btn-primary',
        //             title: '{{ \App\Models\GeneralSetting::first()->site_name ?? 'Inventory System' }} - Current Inventory Report'
        //         },
        //         {
        //             extend: 'pdf',
        //             className: 'btn btn-primary',
        //             title: '{{ \App\Models\GeneralSetting::first()->site_name ?? 'Inventory System' }} - Current Inventory Report'
        //         },
        //         {
        //             extend: 'print',
        //             className: 'btn btn-primary',
        //             title: '{{ \App\Models\GeneralSetting::first()->site_name ?? 'Inventory System' }} - Current Inventory Report'
        //         }
        //     ]
        // });
        $("#table-inventory").dataTable({
            paging: false,
            searching: false, // Set to false since we use server-side search
            info: false,
            order: [],
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'copy',
                    className: 'btn btn-primary'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-primary'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-primary',
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? 'Inventory System' }} - Current Inventory Report'
                },
                {
                    text: 'PDF',
                    className: 'btn btn-primary',
                    action: function (e, dt, node, config) {
                        const search = $('input[name="search"]').val();
                        const category_id = $('select[name="category_id"]').val();
                        const url = "{{ route('admin.inventory-reports.export-pdf') }}?" + 
                                    $.param({ search: search, category_id: category_id });
                        window.location.href = url;
                    }
                },
                {
                    extend: 'print',
                    className: 'btn btn-primary',
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? 'Inventory System' }} - Current Inventory Report'
                }
            ]
        });

        // 🔹 Live Search (Auto-fetch) implementation
        $(document).ready(function() {
            let searchTimer;
            const $form = $('#search-form');
            const $searchInput = $form.find('input[name="search"]');
            const $categorySelect = $form.find('select[name="category_id"]');

            // Handle keyword search with debounce
            $searchInput.on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    $form.submit();
                }, 500); // Wait 500ms after typing stops
            });

            // Handle category selection change
            $categorySelect.on('change', function() {
                $form.submit();
            });
        });
    </script>
@endpush
