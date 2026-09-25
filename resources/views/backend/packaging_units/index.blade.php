@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-boxes text-primary mr-2"></i> Packaging & Handling Units (WMS)</h1>
            <div class="section-header-breadcrumb">
                <a href="{{ route('admin.barcode-labels.index') }}" class="btn btn-outline-primary mr-2">
                    <i class="fas fa-barcode mr-1"></i> Barcode Labels Hub
                </a>
                <a href="{{ route('admin.packaging-units.create') }}" class="btn btn-primary shadow-sm font-weight-bold">
                    <i class="fas fa-plus-circle mr-1"></i> Pack New Handling Unit
                </a>
            </div>
        </div>

        <div class="section-body">
            {{-- Stat Cards --}}
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Handling Units</h4>
                            </div>
                            <div class="card-body font-weight-bold">
                                {{ number_format($stats['total_units']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Items in Containers</h4>
                            </div>
                            <div class="card-body font-weight-bold text-success">
                                {{ number_format($stats['total_items_packed']) }} pcs
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-archive"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Master Cartons</h4>
                            </div>
                            <div class="card-body font-weight-bold text-warning">
                                {{ number_format($stats['total_cartons']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-info">
                            <i class="fas fa-pallet"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Pallets / Containers</h4>
                            </div>
                            <div class="card-body font-weight-bold text-info">
                                {{ number_format($stats['total_pallets']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main List Card --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h4 class="text-dark font-weight-bold mb-0">
                        <i class="fas fa-layer-group text-primary mr-2"></i> Handling Units Repository
                    </h4>
                    <span class="badge badge-light border text-muted">Showing up to 50 latest units</span>
                </div>

                <div class="card-body">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('admin.packaging-units.index') }}" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-5 mb-2">
                                <label class="small font-weight-bold text-muted">Search Barcode / Batch / Product</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" name="q" value="{{ $query }}" class="form-control border-left-0" placeholder="e.g. CTN-10001363, batch no, product name...">
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small font-weight-bold text-muted">Packaging Level / Type</label>
                                <select name="packaging_type_id" class="form-control select2">
                                    <option value="">All Packaging Types</option>
                                    @foreach ($packagingTypes as $pt)
                                        <option value="{{ $pt->id }}" {{ $packagingTypeId == $pt->id ? 'selected' : '' }}>
                                            Level {{ $pt->level_order }}: {{ $pt->name }} ({{ $pt->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold text-muted">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="packed" {{ $status === 'packed' ? 'selected' : '' }}>Packed</option>
                                    <option value="sealed" {{ $status === 'sealed' ? 'selected' : '' }}>Sealed</option>
                                    <option value="shipped" {{ $status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="received" {{ $status === 'received' ? 'selected' : '' }}>Received</option>
                                    <option value="unpacked" {{ $status === 'unpacked' ? 'selected' : '' }}>Unpacked</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter mr-1"></i> Filter</button>
                            </div>
                        </div>
                    </form>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>HU Barcode Code</th>
                                    <th>Packaging Level</th>
                                    <th>Hierarchy Parent</th>
                                    <th>Nested Units</th>
                                    <th class="text-center">Total Pieces</th>
                                    <th class="text-center">Status</th>
                                    <th>Packed Date</th>
                                    <th class="text-center" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($units as $u)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.packaging-units.show', $u->id) }}" class="font-weight-bold font-monospace text-primary" style="font-size: 13.5px;">
                                                <i class="fas fa-barcode mr-1"></i> {{ $u->hu_code }}
                                            </a>
                                            @if ($u->batch_no)
                                                <div class="small text-muted"><span class="font-weight-bold">Batch:</span> {{ $u->batch_no }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info py-1 px-2">
                                                L{{ $u->packagingType?->level_order ?? 1 }}: {{ $u->packagingType?->name ?? 'Package' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($u->parentUnit)
                                                <a href="{{ route('admin.packaging-units.show', $u->parentUnit->id) }}" class="badge badge-light border text-dark">
                                                    <i class="fas fa-arrow-up text-muted mr-1"></i> {{ $u->parentUnit->hu_code }}
                                                </a>
                                            @else
                                                <span class="badge badge-light text-muted">Root Container</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($u->child_units_count > 0)
                                                <span class="badge badge-primary py-1 px-2 font-weight-bold">
                                                    <i class="fas fa-boxes mr-1"></i> {{ $u->child_units_count }} Sub-Units
                                                </span>
                                            @else
                                                <span class="text-muted small">None (Items only)</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success font-weight-bold py-1 px-2" style="font-size: 13px;">
                                                {{ number_format($u->total_quantity) }} pcs
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClasses = [
                                                    'draft' => 'badge-secondary',
                                                    'packed' => 'badge-primary',
                                                    'sealed' => 'badge-warning',
                                                    'shipped' => 'badge-info',
                                                    'received' => 'badge-success',
                                                    'unpacked' => 'badge-dark',
                                                ];
                                            @endphp
                                            <span class="badge {{ $statusClasses[$u->status] ?? 'badge-primary' }} text-uppercase" style="font-size: 11px;">
                                                {{ $u->status }}
                                            </span>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $u->packed_at ? $u->packed_at->format('d M Y, H:i') : $u->created_at->format('d M Y') }}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.packaging-units.show', $u->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.packaging-units.print', $u->id) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Print 4x6 Label">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-unit" data-url="{{ route('admin.packaging-units.destroy', $u->id) }}" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <div class="mb-3"><i class="fas fa-boxes fa-3x text-light"></i></div>
                                            <h6 class="font-weight-bold">No Handling Units Found</h6>
                                            <p class="small text-muted mb-3">Pack products into boxes, master cartons, or shipping containers with GS1 logistics labels.</p>
                                            <a href="{{ route('admin.packaging-units.create') }}" class="btn btn-primary px-4">
                                                <i class="fas fa-plus-circle mr-1"></i> Pack Your First Handling Unit
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        $(document).on('click', '.btn-delete-unit', function(e) {
            e.preventDefault();
            const url = $(this).data('url');
            Swal.fire({
                title: 'Delete Handling Unit?',
                text: 'Nested child boxes will be detached and marked as unassigned.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(resp) {
                            if (resp && (resp.status === 'success' || resp.success === true)) {
                                Swal.fire(
                                    'Deleted',
                                    resp.message || 'Deleted Successfully!',
                                    'success'
                                ).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire("Can't Delete!", resp.message || 'Failed to delete handling unit', 'error');
                            }
                        },
                        error: function(xhr) {
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete handling unit';
                            Swal.fire("Can't Delete!", msg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
