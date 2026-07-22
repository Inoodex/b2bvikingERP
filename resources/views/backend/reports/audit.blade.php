@extends('backend.layouts.master')

@section('title', 'Audit Report')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Audit Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item active">Audit Report</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Logs</h4>
                            </div>
                            <div class="card-body">{{ number_format($summary['count']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Today</h4>
                            </div>
                            <div class="card-body">{{ number_format($summary['today_count']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Modules</h4>
                            </div>
                            <div class="card-body">{{ number_format($summary['modules']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Staff Involved</h4>
                            </div>
                            <div class="card-body">{{ number_format($summary['users']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h4>Audit Trail</h4>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4" id="audit-filter-form">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Module</label>
                                <select name="module" class="form-control">
                                    <option value="">All</option>
                                    @foreach($modules as $module)
                                        <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>{{ ucfirst($module) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Action</label>
                                <select name="action" class="form-control">
                                    <option value="">All</option>
                                    @foreach($actions as $action)
                                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Staff</label>
                                <select name="user_id" class="form-control">
                                    <option value="">All</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Vendor</label>
                                <select name="vendor_id" class="form-control">
                                    <option value="">All</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ (string) request('vendor_id') === (string) $vendor->id ? 'selected' : '' }}>{{ $vendor->shop_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>From</label>
                                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>To</label>
                                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label>Reference</label>
                                <input type="text" name="reference" value="{{ request('reference') }}" class="form-control" placeholder="Invoice, order no, vendor">
                            </div>
                            <div class="col-md-8 mt-3 d-flex align-items-end justify-content-end">
                                <a href="{{ route('admin.reports.audit') }}" class="btn btn-light mr-2">Reset</a>
                                <small class="text-muted">Filters apply automatically.</small>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Module</th>
                                    <th>Action</th>
                                    <th>Reference</th>
                                    <th>Vendor</th>
                                    <th>Done By</th>
                                    <th>Description</th>
                                    <th>Changes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    @php
                                        $oldValues = is_array($log->old_values) ? $log->old_values : [];
                                        $newValues = is_array($log->new_values) ? $log->new_values : [];
                                        $allKeys = collect(array_unique(array_merge(array_keys($oldValues), array_keys($newValues))));
                                    @endphp
                                    <tr>
                                        <td>{{ $log->created_at?->format('d M, Y h:i A') }}</td>
                                        <td><span class="badge badge-primary">{{ strtoupper($log->module ?? 'N/A') }}</span></td>
                                        <td>{{ str_replace('_', ' ', ucfirst($log->action ?? 'n/a')) }}</td>
                                        <td>{{ $log->reference_no ?: 'N/A' }}</td>
                                        <td>{{ $log->vendor?->shop_name ?: 'N/A' }}</td>
                                        <td>{{ $log->user?->name ?: 'System' }}</td>
                                        <td>{{ $log->description ?: 'N/A' }}</td>
                                        <td>
                                            @if($log->old_values || $log->new_values)
                                                <button class="btn btn-sm btn-outline-primary audit-open-modal" type="button" data-target="#audit-modal-{{ $log->id }}">
                                                    View
                                                </button>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No audit logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </section>

    @foreach($logs as $log)
        @php
            $oldValues = is_array($log->old_values) ? $log->old_values : [];
            $newValues = is_array($log->new_values) ? $log->new_values : [];
            $allKeys = collect(array_unique(array_merge(array_keys($oldValues), array_keys($newValues))));
        @endphp
        <div class="audit-modal" id="audit-modal-{{ $log->id }}">
            <div class="audit-modal-backdrop audit-close-modal"></div>
            <div class="audit-modal-dialog">
                <div class="audit-modal-content">
                    <div class="audit-modal-header">
                        <div>
                            <div class="audit-modal-kicker">Audit Details</div>
                            <h4 class="audit-modal-title">{{ str_replace('_', ' ', ucfirst($log->action ?? 'n/a')) }}</h4>
                            <p class="audit-modal-subtitle mb-0">{{ $log->description ?: 'No description available.' }}</p>
                        </div>
                        <button type="button" class="audit-modal-close audit-close-modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="audit-modal-body">
                        <div class="audit-summary-grid">
                            <div class="audit-summary-item">
                                <span>Date</span>
                                <strong>{{ $log->created_at?->format('d M, Y h:i A') }}</strong>
                            </div>
                            <div class="audit-summary-item">
                                <span>Module</span>
                                <strong>{{ strtoupper($log->module ?? 'N/A') }}</strong>
                            </div>
                            <div class="audit-summary-item">
                                <span>Reference</span>
                                <strong>{{ $log->reference_no ?: 'N/A' }}</strong>
                            </div>
                            <div class="audit-summary-item">
                                <span>Done By</span>
                                <strong>{{ $log->user?->name ?: 'System' }}</strong>
                            </div>
                            <div class="audit-summary-item">
                                <span>Vendor</span>
                                <strong>{{ $log->vendor?->shop_name ?: 'N/A' }}</strong>
                            </div>
                            <div class="audit-summary-item">
                                <span>Action</span>
                                <strong>{{ str_replace('_', ' ', ucfirst($log->action ?? 'n/a')) }}</strong>
                            </div>
                        </div>

                        <div class="audit-section">
                            <div class="audit-section-head">
                                <h5>Changed Fields</h5>
                                <small>Only important values shown in a compact format</small>
                            </div>

                            @if($allKeys->isEmpty())
                                <div class="audit-empty-state">No structured changes available.</div>
                            @else
                                <div class="audit-change-grid">
                                    @foreach($allKeys as $key)
                                        @php
                                            $oldExists = array_key_exists($key, $oldValues);
                                            $newExists = array_key_exists($key, $newValues);
                                            $oldValue = $oldValues[$key] ?? null;
                                            $newValue = $newValues[$key] ?? null;
                                            $changed = !$oldExists || !$newExists || $oldValue !== $newValue;
                                            $label = ucwords(str_replace('_', ' ', $key));
                                        @endphp
                                        @if($changed)
                                            <div class="audit-change-card">
                                                <div class="audit-change-label">{{ $label }}</div>
                                                <div class="audit-change-row">
                                                    <div class="audit-change-box audit-change-old">
                                                        <span class="audit-mini-label">Old</span>
                                                        <strong>{{ $oldExists ? (is_array($oldValue) ? json_encode($oldValue) : ($oldValue === null ? 'N/A' : (string) $oldValue)) : 'N/A' }}</strong>
                                                    </div>
                                                    <div class="audit-change-arrow">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </div>
                                                    <div class="audit-change-box audit-change-new">
                                                        <span class="audit-mini-label">New</span>
                                                        <strong>{{ $newExists ? (is_array($newValue) ? json_encode($newValue) : ($newValue === null ? 'N/A' : (string) $newValue)) : 'N/A' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <details class="audit-raw-details">
                            <summary>View raw JSON</summary>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="audit-raw-card">
                                        <h6>Old Values</h6>
                                        <pre class="audit-json mb-0">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <div class="audit-raw-card">
                                        <h6>New Values</h6>
                                        <pre class="audit-json mb-0">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('css')
<style>
    .audit-modal {
        position: fixed;
        inset: 0;
        display: none;
        z-index: 1050;
    }

    .audit-modal.is-open {
        display: block;
    }

    .audit-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.62);
        backdrop-filter: blur(3px);
    }

    .audit-modal-dialog {
        position: relative;
        z-index: 2;
        width: min(1040px, calc(100vw - 32px));
        margin: 32px auto;
        max-height: calc(100vh - 64px);
    }

    .audit-modal-content {
        background: #f8fafc;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.25);
        max-height: calc(100vh - 64px);
        display: flex;
        flex-direction: column;
    }

    .audit-modal-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        padding: 24px 28px;
        background: linear-gradient(135deg, #ffffff 0%, #eef4ff 100%);
        border-bottom: 1px solid #e5edf9;
    }

    .audit-modal-kicker {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #5b6b83;
        margin-bottom: 8px;
    }

    .audit-modal-title {
        margin: 0 0 6px;
        font-size: 24px;
        font-weight: 800;
        color: #19253a;
    }

    .audit-modal-subtitle {
        font-size: 14px;
        color: #66758c;
    }

    .audit-modal-close {
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 999px;
        background: #fff;
        color: #516076;
        box-shadow: 0 8px 24px rgba(22, 34, 51, 0.08);
    }

    .audit-modal-body {
        padding: 24px 28px 28px;
        overflow-y: auto;
    }

    .audit-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .audit-summary-item {
        background: #fff;
        border: 1px solid #e8eef7;
        border-radius: 16px;
        padding: 14px 16px;
    }

    .audit-summary-item span {
        display: block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #7c8ba1;
        margin-bottom: 6px;
    }

    .audit-summary-item strong {
        display: block;
        font-size: 15px;
        line-height: 1.45;
        color: #1f2c44;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .audit-section {
        background: #fff;
        border: 1px solid #e8eef7;
        border-radius: 20px;
        padding: 18px;
    }

    .audit-section-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: end;
        margin-bottom: 16px;
    }

    .audit-section-head h5 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #21314b;
    }

    .audit-section-head small {
        color: #7b8aa0;
    }

    .audit-change-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .audit-change-card {
        border: 1px solid #e8eef7;
        border-radius: 16px;
        background: linear-gradient(135deg, #fcfdff 0%, #f6f9ff 100%);
        padding: 14px;
    }

    .audit-change-label {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #67768d;
        margin-bottom: 12px;
    }

    .audit-change-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 28px minmax(0, 1fr);
        gap: 10px;
        align-items: center;
    }

    .audit-change-box {
        border-radius: 14px;
        padding: 12px;
        min-height: 78px;
    }

    .audit-change-old {
        background: #fff1f1;
        border: 1px solid #ffd9d9;
    }

    .audit-change-new {
        background: #eefcf3;
        border: 1px solid #d3f3de;
    }

    .audit-mini-label {
        display: block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #73829a;
        margin-bottom: 7px;
    }

    .audit-change-box strong {
        display: block;
        color: #1f2c44;
        font-size: 15px;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .audit-change-arrow {
        text-align: center;
        color: #73829a;
        font-size: 14px;
    }

    .audit-empty-state {
        padding: 18px;
        border-radius: 14px;
        background: #f8fafc;
        color: #728197;
    }

    .audit-raw-details {
        margin-top: 18px;
        background: #fff;
        border: 1px solid #e8eef7;
        border-radius: 18px;
        padding: 14px 16px;
    }

    .audit-raw-details summary {
        cursor: pointer;
        font-weight: 700;
        color: #334155;
        outline: none;
    }

    .audit-raw-card {
        background: #0f172a;
        border-radius: 16px;
        padding: 14px;
    }

    .audit-raw-card h6 {
        color: #dbe7ff;
        margin-bottom: 10px;
        font-size: 13px;
        font-weight: 700;
    }

    .audit-json {
        color: #d7e3ff;
        font-size: 12px;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
    }

    @media (max-width: 991.98px) {
        .audit-summary-grid,
        .audit-change-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .audit-modal-dialog {
            width: calc(100vw - 16px);
            margin: 8px auto;
            max-height: calc(100vh - 16px);
        }

        .audit-modal-content {
            max-height: calc(100vh - 16px);
            border-radius: 18px;
        }

        .audit-modal-header,
        .audit-modal-body {
            padding: 18px;
        }

        .audit-change-row {
            grid-template-columns: 1fr;
        }

        .audit-change-arrow {
            transform: rotate(90deg);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        const $form = $('#audit-filter-form');
        const $reference = $form.find('input[name="reference"]');
        let referenceTimer = null;

        $form.find('select, input[type="date"]').on('change', function() {
            $form.trigger('submit');
        });

        $reference.on('input', function() {
            clearTimeout(referenceTimer);
            referenceTimer = setTimeout(function() {
                $form.trigger('submit');
            }, 450);
        });

        $reference.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(referenceTimer);
                $form.trigger('submit');
            }
        });

        $(document).on('click', '.audit-open-modal', function() {
            const target = $(this).data('target');
            $('body').addClass('modal-open');
            $(target).addClass('is-open');
        });

        $(document).on('click', '.audit-close-modal', function() {
            $(this).closest('.audit-modal').removeClass('is-open');
            $('body').removeClass('modal-open');
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.audit-modal.is-open').removeClass('is-open');
                $('body').removeClass('modal-open');
            }
        });
    });
</script>
@endpush
