@extends('backend.layouts.master')

@section('title', 'Enterprise Feature Toggles')

@section('content')
<section class="section">
  <div class="section-header">
    <h1><i class="fas fa-toggle-on text-primary mr-2"></i> Feature Toggles Switchboard</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
      <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
      <div class="breadcrumb-item active">Feature Toggles</div>
    </div>
  </div>

  <div class="section-body">
    <!-- Notice Banner -->
    <div class="card shadow-sm border-0 mb-4" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; border-radius: 12px;">
      <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center mb-2 mb-md-0">
          <div class="rounded-circle p-3 mr-3" style="background: rgba(205, 160, 90, 0.2); border: 1px solid rgba(205, 160, 90, 0.4);">
            <i class="fas fa-sliders-h fa-2x" style="color: #cda05a;"></i>
          </div>
          <div>
            <h5 class="mb-1 text-white font-weight-bold">Runtime Feature Configuration</h5>
            <p class="mb-0 text-muted" style="color: #94a3b8 !important; font-size: 13.5px;">
              Dynamically activate or deactivate ERP system capabilities in real-time. Changes propagate instantly to active sessions via cache tags.
            </p>
          </div>
        </div>
        <div>
          <span class="badge badge-warning px-3 py-2" style="font-size: 12px; border-radius: 20px;">
            <i class="fas fa-bolt mr-1"></i> Instant In-Memory Sync
          </span>
        </div>
      </div>
    </div>

    <!-- Switchboard Form -->
    <form action="{{ route('admin.settings.feature-toggles.update') }}" method="POST" id="featureTogglesForm">
      @csrf

      @foreach($groupedToggles as $categoryName => $items)
      <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;">
            <i class="fas fa-layer-group text-primary mr-2"></i> {{ $categoryName }}
          </h6>
          <span class="badge badge-light text-muted border" style="font-size: 12px;">
            {{ count($items) }} Switches
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <tbody>
                @foreach($items as $item)
                <tr>
                  <td style="width: 55px;" class="text-center pl-4">
                    <div class="avatar-icon-wrap" style="width: 42px; height: 42px; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center;">
                      <i class="{{ $item['icon'] }} text-primary" style="font-size: 17px;"></i>
                    </div>
                  </td>
                  <td style="vertical-align: middle;">
                    <div class="font-weight-bold text-dark" style="font-size: 14.5px;">
                      {{ $item['title'] }}
                      <code class="text-muted ml-2 font-weight-normal" style="font-size: 11px;">{{ $item['key'] }}</code>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 12.5px;">
                      {{ $item['description'] }}
                    </div>
                  </td>
                  <td class="text-right pr-4" style="width: 140px; vertical-align: middle;">
                    <div class="custom-control custom-switch d-inline-block">
                      <input 
                        type="checkbox" 
                        class="custom-control-input feature-toggle-input" 
                        id="toggle_{{ $item['key'] }}" 
                        name="toggles[{{ $item['key'] }}]" 
                        value="1" 
                        data-feature="{{ $item['key'] }}"
                        {{ $item['enabled'] ? 'checked' : '' }}
                      >
                      <label class="custom-control-label font-weight-bold" for="toggle_{{ $item['key'] }}" id="label_{{ $item['key'] }}" style="cursor: pointer;">
                        {{ $item['enabled'] ? 'Active' : 'Disabled' }}
                      </label>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endforeach

      <!-- Sticky bottom actions -->
      <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px;">
        <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap">
          <div class="text-muted small mb-2 mb-md-0">
            <i class="fas fa-info-circle mr-1"></i> Toggles update instantly on click via AJAX, or click save below to apply in bulk.
          </div>
          <div>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary mr-2">
              <i class="fas fa-arrow-left mr-1"></i> Back to Settings
            </a>
            <button type="submit" class="btn btn-primary px-4">
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
  $('.feature-toggle-input').on('change', function() {
    var $checkbox = $(this);
    var featureKey = $checkbox.data('feature');
    var isEnabled = $checkbox.is(':checked') ? 1 : 0;
    var $label = $('#label_' + featureKey);

    $label.text(isEnabled ? 'Updating...' : 'Updating...');
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
          $label.text(res.enabled ? 'Active' : 'Disabled');
          if (typeof toastr !== 'undefined') {
            toastr.success(res.message);
          }
        } else {
          $checkbox.prop('checked', !isEnabled);
          $label.text(!isEnabled ? 'Active' : 'Disabled');
          if (typeof toastr !== 'undefined') {
            toastr.error(res.message || 'Failed to update feature toggle.');
          }
        }
      },
      error: function(xhr) {
        $checkbox.prop('disabled', false);
        $checkbox.prop('checked', !isEnabled);
        $label.text(!isEnabled ? 'Active' : 'Disabled');
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
