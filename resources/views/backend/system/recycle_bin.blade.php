@extends('backend.layouts.master')

@section('title', 'Universal Recycle Bin')

@section('content')
<section class="section">
  <div class="section-header">
    <h1><i class="fas fa-trash-restore text-primary mr-2"></i> Universal Recycle Bin</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
      <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
      <div class="breadcrumb-item active">Recycle Bin</div>
    </div>
  </div>

  <div class="section-body">
    <!-- Header Notice Card -->
    <div class="card shadow-sm border-0 mb-4" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; border-radius: 12px;">
      <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center mb-2 mb-md-0">
          <div class="rounded-circle p-3 mr-3" style="background: rgba(205, 160, 90, 0.2); border: 1px solid rgba(205, 160, 90, 0.4);">
            <i class="fas fa-recycle fa-2x" style="color: #cda05a;"></i>
          </div>
          <div>
            <h5 class="mb-1 text-white font-weight-bold">Soft-Deleted Records Restoration Center</h5>
            <p class="mb-0" style="color: #94a3b8; font-size: 13.5px;">
              Easily restore accidentally deleted products, orders, invoices, customers, and vendors back to the active catalog.
            </p>
          </div>
        </div>
        <div>
          <span class="badge badge-success px-3 py-2" style="font-size: 12px; border-radius: 20px;">
            <i class="fas fa-shield-alt mr-1"></i> Foreign Key Guard Active
          </span>
        </div>
      </div>
    </div>

    <!-- Entity Navigation Tabs -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
      <div class="card-body py-2 px-3">
        <ul class="nav nav-pills nav-fill flex-column flex-sm-row">
          @foreach($entities as $type => $meta)
          <li class="nav-item">
            <a class="nav-link {{ $activeType === $type ? 'active bg-primary font-weight-bold shadow-sm' : 'text-dark' }}" 
               href="{{ route('admin.recycle-bin.index', ['type' => $type]) }}"
               style="border-radius: 8px; font-size: 14px;">
              <i class="{{ $meta['icon'] }} mr-2"></i> {{ $meta['label'] }}
              <span class="badge {{ $activeType === $type ? 'badge-light text-primary' : 'badge-secondary' }} ml-2">
                {{ $counts[$type] ?? 0 }}
              </span>
            </a>
          </li>
          @endforeach
        </ul>
      </div>
    </div>

    <!-- Active Entity Header & Restore All Action -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
      <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap">
        <div>
          <h6 class="mb-0 font-weight-bold text-dark">
            <i class="{{ $entities[$activeType]['icon'] }} mr-2 text-primary"></i> Trashed {{ $entities[$activeType]['label'] }}
          </h6>
        </div>
        <div>
          @if(($counts[$activeType] ?? 0) > 0)
          <form action="{{ route('admin.recycle-bin.restore-all', $activeType) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to restore ALL deleted items in this category?')">
            @csrf
            <button type="submit" class="btn btn-outline-success font-weight-bold">
              <i class="fas fa-undo-alt mr-1"></i> Restore All {{ $entities[$activeType]['label'] }}
            </button>
          </form>
          @endif
        </div>
      </div>
    </div>

    <!-- DataTable Container -->
    <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
      <div class="card-body p-4">
        <div class="table-responsive">
          {{ $dataTable->table(['class' => 'table table-striped table-hover align-middle mb-0 w-100', 'id' => 'recyclebin-table']) }}
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
  {{ $dataTable->scripts() }}
  <script>
    $(document).on('click', '.btn-restore-item', function (e) {
      e.preventDefault();
      var url = $(this).data('url');
      if (!confirm('Are you sure you want to restore this record?')) return;

      $.ajax({
        url: url,
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}'
        },
        success: function (res) {
          if (typeof toastr !== 'undefined') {
            toastr.success(res.message || 'Record restored successfully!');
          } else {
            alert(res.message || 'Record restored successfully!');
          }
          if (window.LaravelDataTables && window.LaravelDataTables['recyclebin-table']) {
            window.LaravelDataTables['recyclebin-table'].ajax.reload();
          } else {
            window.location.reload();
          }
        },
        error: function (xhr) {
          var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to restore item.';
          if (typeof toastr !== 'undefined') {
            toastr.error(msg);
          } else {
            alert(msg);
          }
        }
      });
    });

    $(document).on('click', '.btn-force-delete-item', function (e) {
      e.preventDefault();
      var url = $(this).data('url');
      if (!confirm('CAUTION: This will permanently purge this record from the database. This action CANNOT be undone! Continue?')) return;

      $.ajax({
        url: url,
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          _method: 'DELETE'
        },
        success: function (res) {
          if (typeof toastr !== 'undefined') {
            toastr.success(res.message || 'Record permanently deleted.');
          } else {
            alert(res.message || 'Record permanently deleted.');
          }
          if (window.LaravelDataTables && window.LaravelDataTables['recyclebin-table']) {
            window.LaravelDataTables['recyclebin-table'].ajax.reload();
          } else {
            window.location.reload();
          }
        },
        error: function (xhr) {
          var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Deletion failed.';
          if (typeof toastr !== 'undefined') {
            toastr.error(msg);
          } else {
            alert(msg);
          }
        }
      });
    });
  </script>
@endpush
