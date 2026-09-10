@extends('backend.layouts.master')

@section('title', 'Universal Recycle Bin')

@push('css')
<style>
  .nav-pills .nav-link {
    border: 1px solid rgba(0, 0, 0, 0.06);
  }
  .nav-pills .nav-link:hover:not(.active) {
    background-color: #f1f3f8 !important;
  }
</style>
@endpush

@section('content')
<section class="section">
  {{-- Section Header matching ERP Dashboard & Index views --}}
  <div class="section-header d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="text-dark font-weight-bold mb-1">
        <i class="fas fa-recycle text-primary mr-2"></i> Universal Recycle Bin
      </h1>
      <p class="text-muted mb-0 small">Centralized restoration hub for soft-deleted records across products, orders, invoices, and contacts</p>
    </div>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
      <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
      <div class="breadcrumb-item active">Recycle Bin</div>
    </div>
  </div>

  <div class="section-body">
    {{-- Entity Filter Tabs --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
      <div class="card-body p-3">
        <ul class="nav nav-pills flex-column flex-sm-row" style="gap: 8px;">
          @foreach($entities as $type => $meta)
          <li class="nav-item">
            <a class="nav-link font-weight-bold {{ $activeType === $type ? 'active bg-primary text-white shadow-sm' : 'text-dark bg-light' }}" 
               href="{{ route('admin.recycle-bin.index', ['type' => $type]) }}"
               style="border-radius: 8px; font-size: 13.5px; padding: 10px 18px; transition: all 0.2s ease;">
              <i class="{{ $meta['icon'] }} mr-2"></i> {{ $meta['label'] }}
              <span class="badge {{ $activeType === $type ? 'badge-light text-primary font-weight-bold' : 'badge-secondary' }} ml-2" style="font-size: 11px; border-radius: 10px;">
                {{ $counts[$type] ?? 0 }}
              </span>
            </a>
          </li>
          @endforeach
        </ul>
      </div>
    </div>

    {{-- Main DataTable Card --}}
    <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
        <div class="d-flex align-items-center" style="gap: 10px;">
          <h6 class="font-weight-bold text-dark mb-0">
            <i class="{{ $entities[$activeType]['icon'] }} text-primary mr-2"></i> Trashed {{ $entities[$activeType]['label'] }}
          </h6>
          <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px; border-radius: 6px;">
            {{ $counts[$activeType] ?? 0 }} items
          </span>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
          @if(($counts[$activeType] ?? 0) > 0)
          <form action="{{ route('admin.recycle-bin.restore-all', $activeType) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to restore ALL deleted {{ $entities[$activeType]['label'] }}?')">
            @csrf
            <button type="submit" class="btn btn-success font-weight-bold btn-sm rounded-pill px-3 shadow-sm">
              <i class="fas fa-undo mr-1"></i> Restore All ({{ $counts[$activeType] }})
            </button>
          </form>
          @endif
          <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-th-large mr-1"></i> Dashboard
          </a>
        </div>
      </div>

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
