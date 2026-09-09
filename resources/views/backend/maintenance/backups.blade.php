@extends('backend.layouts.master')

@section('title', 'Database Backups & Disaster Recovery')

@section('content')
<section class="section">
  <div class="section-header">
    <h1><i class="fas fa-database text-primary mr-2"></i> Database Backups & Disaster Recovery</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
      <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
      <div class="breadcrumb-item active">Database Backups</div>
    </div>
  </div>

  <div class="section-body">
    <!-- Action & Overview Header Card -->
    <div class="card shadow-sm border-0 mb-4" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; border-radius: 12px;">
      <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center mb-3 mb-md-0">
          <div class="rounded-circle p-3 mr-3" style="background: rgba(205, 160, 90, 0.2); border: 1px solid rgba(205, 160, 90, 0.4);">
            <i class="fas fa-shield-alt fa-2x" style="color: #cda05a;"></i>
          </div>
          <div>
            <h5 class="mb-1 text-white font-weight-bold">Enterprise Disaster Recovery & Database Snapshots</h5>
            <p class="mb-0" style="color: #94a3b8; font-size: 13.5px;">
              Generates standalone, portable SQL database snapshots that can be securely downloaded and restored on any MySQL/MariaDB server or VPS.
            </p>
          </div>
        </div>
        <div>
          <form action="{{ route('admin.backups.create') }}" method="POST" id="generateBackupForm">
            @csrf
            <button type="submit" class="btn btn-warning btn-lg font-weight-bold px-4" id="btnBackupNow" style="border-radius: 10px; color: #1e1e1e;">
              <i class="fas fa-plus-circle mr-2"></i> Generate Backup Now
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Backups DataTable Card -->
    <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 font-weight-bold text-dark">
          <i class="fas fa-history text-primary mr-2"></i> Backup Archive History
        </h6>
      </div>
      <div class="card-body p-4">
        <div class="table-responsive">
          {{ $dataTable->table(['class' => 'table table-striped table-hover align-middle mb-0 w-100', 'id' => 'backup-table']) }}
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
  {{ $dataTable->scripts() }}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var form = document.getElementById('generateBackupForm');
      var btn = document.getElementById('btnBackupNow');
      if (form && btn) {
        form.addEventListener('submit', function () {
          btn.disabled = true;
          btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Exporting SQL Stream...';
        });
      }
    });
  </script>
@endpush
