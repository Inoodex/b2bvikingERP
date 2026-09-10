@extends('backend.layouts.master')

@section('title', 'Enterprise Feature Toggles')

@push('css')
<style>
  /* Stisla Default Dashboard Toggle Switch */
  .custom-switch {
    cursor: pointer;
    user-select: none;
    -webkit-user-select: none;
    margin: 0;
  }
  .custom-switch-indicator {
    cursor: pointer;
    width: 2.35rem !important;
    height: 1.35rem !important;
    background: #e4e6fc !important;
    border: 1px solid rgba(0, 40, 100, 0.08) !important;
    border-radius: 50px !important;
    transition: background-color 0.25s ease, border-color 0.25s ease !important;
  }
  .custom-switch-indicator:before {
    top: 1px !important;
    left: 1px !important;
    height: calc(1.35rem - 4px) !important;
    width: calc(1.35rem - 4px) !important;
    background: #ffffff !important;
    border-radius: 50% !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2) !important;
    transition: left 0.22s cubic-bezier(.4, 0, .2, 1) !important;
  }
  .custom-switch-input:checked ~ .custom-switch-indicator {
    background-color: #6777ef !important;
    border-color: #6777ef !important;
  }
  .custom-switch-input:checked ~ .custom-switch-indicator:before {
    left: calc(1rem + 3px) !important;
  }
  .custom-switch-input:focus ~ .custom-switch-indicator {
    box-shadow: 0 0 0 2px rgba(103, 119, 239, 0.25) !important;
  }

  .feature-row {
    transition: background-color 0.15s ease;
  }
  .feature-row:hover {
    background-color: #fcfdfe;
  }
  .feature-row:last-child {
    border-bottom: none !important;
  }

  @media (max-width: 575.98px) {
    .card-statistic-1 .card-icon { width: 55px !important; min-width: 55px !important; font-size: 20px !important; }
    .card-statistic-1 .card-wrap { padding: 10px 8px 10px 12px !important; }
    .card-statistic-1 .card-header h4 { font-size: 11px !important; }
    .card-statistic-1 .card-body { font-size: 16px !important; }
  }
</style>
@endpush

@section('content')
<section class="section">
  {{-- Section Header matching Dashboard & Settings --}}
  <div class="section-header d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="text-dark font-weight-bold mb-1">
        <i class="fas fa-toggle-on text-primary mr-2"></i> Feature Toggles Switchboard
      </h1>
      <p class="text-muted mb-0 small">Runtime switches to dynamically activate or deactivate ERP system capabilities in real-time</p>
    </div>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
      <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
      <div class="breadcrumb-item active">Feature Toggles</div>
    </div>
  </div>

  <div class="section-body">
    {{-- KPI Summary Stats across the top (Dashboard card-statistic-1 styling) --}}
    @php
      $allItems = collect($groupedToggles)->flatten(1);
      $totalFeatures = $allItems->count();
      $activeFeatures = $allItems->where('enabled', true)->count();
      $disabledFeatures = $totalFeatures - $activeFeatures;

      $categoryMeta = [
        'Procurement & Inventory' => ['icon' => 'fas fa-boxes', 'color' => '#6777ef', 'bg' => 'rgba(103, 119, 239, 0.12)'],
        'Communications'          => ['icon' => 'fas fa-envelope-open-text', 'color' => '#3abaf4', 'bg' => 'rgba(58, 186, 244, 0.12)'],
        'Sales & Credit Control'  => ['icon' => 'fas fa-hand-holding-usd', 'color' => '#ffa426', 'bg' => 'rgba(255, 164, 38, 0.12)'],
        'Finance & Accounting'    => ['icon' => 'fas fa-book', 'color' => '#47c363', 'bg' => 'rgba(71, 195, 99, 0.12)'],
        'Payment Gateways'        => ['icon' => 'fas fa-credit-card', 'color' => '#fc544b', 'bg' => 'rgba(252, 84, 75, 0.12)'],
      ];
    @endphp

    <div class="row mb-4">
      <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3 mb-lg-0">
        <div class="card card-statistic-1 shadow-sm mb-0 h-100" style="border-radius: 12px;">
          <div class="card-icon bg-primary">
            <i class="fas fa-sliders-h"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>Total Features</h4>
            </div>
            <div class="card-body font-weight-bold">
              {{ $totalFeatures }}
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3 mb-lg-0">
        <div class="card card-statistic-1 shadow-sm mb-0 h-100" style="border-radius: 12px;">
          <div class="card-icon bg-success">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>Active Modules</h4>
            </div>
            <div class="card-body font-weight-bold text-success" id="activeFeaturesCount">
              {{ $activeFeatures }}
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3 mb-lg-0">
        <div class="card card-statistic-1 shadow-sm mb-0 h-100" style="border-radius: 12px;">
          <div class="card-icon bg-secondary">
            <i class="fas fa-power-off"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>Disabled</h4>
            </div>
            <div class="card-body font-weight-bold text-muted" id="disabledFeaturesCount">
              {{ $disabledFeatures }}
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3 mb-lg-0">
        <div class="card card-statistic-1 shadow-sm mb-0 h-100" style="border-radius: 12px;">
          <div class="card-icon bg-info">
            <i class="fas fa-bolt"></i>
          </div>
          <div class="card-wrap">
            <div class="card-header">
              <h4>Sync Mode</h4>
            </div>
            <div class="card-body font-weight-bold text-info" style="font-size: 15px; line-height: 32px;">
              Instant Cache
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Switchboard Form --}}
    <form action="{{ route('admin.settings.feature-toggles.update') }}" method="POST" id="featureTogglesForm">
      @csrf

      @foreach($groupedToggles as $categoryName => $items)
        @php
          $cat = $categoryMeta[$categoryName] ?? ['icon' => 'fas fa-layer-group', 'color' => '#6777ef', 'bg' => 'rgba(103, 119, 239, 0.12)'];
        @endphp
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
          <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
            <div class="d-flex align-items-center" style="gap: 10px;">
              <div style="width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: {{ $cat['bg'] }}; color: {{ $cat['color'] }};">
                <i class="{{ $cat['icon'] }}" style="font-size: 16px;"></i>
              </div>
              <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;">
                {{ $categoryName }}
              </h6>
            </div>
            <span class="badge badge-light text-muted border px-2 py-1 font-weight-bold" style="font-size: 11px; border-radius: 6px;">
              {{ count($items) }} {{ count($items) === 1 ? 'Switch' : 'Switches' }}
            </span>
          </div>

          <div class="card-body p-0">
            <div class="feature-list">
              @foreach($items as $item)
              <div class="feature-row d-flex align-items-center justify-content-between p-3 border-bottom flex-wrap" style="gap: 14px;">
                <div class="d-flex align-items-center" style="gap: 14px; flex: 1; min-width: 260px;">
                  <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center;">
                    <i class="{{ $item['icon'] }} text-primary" style="font-size: 16px;"></i>
                  </div>
                  <div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                      <span class="font-weight-bold text-dark" style="font-size: 14px;">{{ $item['title'] }}</span>
                      <code class="text-muted" style="font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                        {{ $item['key'] }}
                      </code>
                    </div>
                    <p class="text-muted mb-0 mt-1" style="font-size: 12px; line-height: 1.4;">
                      {{ $item['description'] }}
                    </p>
                  </div>
                </div>

                {{-- Dashboard Default Custom Switch --}}
                <div class="d-flex align-items-center">
                  <label class="custom-switch m-0" for="toggle_{{ $item['key'] }}" title="Toggle {{ $item['title'] }}">
                    <input 
                      type="checkbox" 
                      class="custom-switch-input feature-toggle-input" 
                      id="toggle_{{ $item['key'] }}" 
                      name="toggles[{{ $item['key'] }}]" 
                      value="1" 
                      data-feature="{{ $item['key'] }}"
                      {{ $item['enabled'] ? 'checked' : '' }}
                    >
                    <span class="custom-switch-indicator"></span>
                  </label>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      @endforeach

      <!-- Bottom actions card -->
      <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; background: #ffffff;">
        <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
          <div class="text-muted small">
            <i class="fas fa-check-circle text-success mr-1"></i> Switches toggle in real-time with instant cache invalidation.
          </div>
          <div class="d-flex align-items-center" style="gap: 8px;">
            <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
              <i class="fas fa-arrow-left mr-1"></i> Back to Settings
            </a>
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold shadow-sm">
              <i class="fas fa-save mr-1"></i> Save All Changes
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
  function updateStats() {
    var total = $('.feature-toggle-input').length;
    var active = $('.feature-toggle-input:checked').length;
    $('#activeFeaturesCount').text(active);
    $('#disabledFeaturesCount').text(total - active);
  }

  $('.feature-toggle-input').on('change', function() {
    var $checkbox = $(this);
    var featureKey = $checkbox.data('feature');
    var isEnabled = $checkbox.is(':checked') ? 1 : 0;

    $checkbox.prop('disabled', true);

    $.ajax({
      url: "{{ route('admin.settings.feature-toggles.toggle') }}",
      type: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        feature: featureKey,
        enabled: isEnabled
      },
      dataType: "json",
      success: function(res) {
        $checkbox.prop('disabled', false);
        if (res.success) {
          updateStats();
          if (typeof toastr !== 'undefined') {
            toastr.success(res.message);
          }
        } else {
          $checkbox.prop('checked', !isEnabled);
          updateStats();
          if (typeof toastr !== 'undefined') {
            toastr.error(res.message || 'Failed to update feature toggle.');
          }
        }
      },
      error: function(xhr) {
        $checkbox.prop('disabled', false);
        $checkbox.prop('checked', !isEnabled);
        updateStats();
        var err = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Server error occurred.';
        if (typeof toastr !== 'undefined') {
          toastr.error(err);
        }
      }
    });
  });
});
</script>
@endpush
