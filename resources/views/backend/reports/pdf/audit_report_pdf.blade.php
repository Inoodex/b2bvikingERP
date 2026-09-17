<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $settings->site_name ?? 'B2B Viking ERP' }} — Audit Log Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #1e293b; line-height: 1.35; padding: 15px; }

        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #0f172a; padding-bottom: 8px; }
        .header h1 { margin: 0; color: #0f172a; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header .sub { font-size: 11px; font-weight: bold; margin-top: 2px; color: #334155; }
        .header .info { font-size: 8.5px; color: #64748b; margin-top: 3px; }

        .summary-grid { width: 100%; margin-bottom: 12px; border-collapse: separate; border-spacing: 5px; }
        .summary-cell { width: 25%; padding: 7px 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; text-align: center; vertical-align: top; }
        .summary-cell .lbl { font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        .summary-cell .val { font-size: 13px; font-weight: bold; margin-top: 2px; color: #0f172a; }
        .val-primary { color: #2563eb; }
        .val-success { color: #059669; }
        .val-danger { color: #dc2626; }
        .val-warning { color: #d97706; }

        .section-title { font-size: 10px; font-weight: bold; margin: 10px 0 5px; padding: 3px 0; border-bottom: 1px solid #cbd5e1; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data-table th { background: #0f172a; color: #fff; border: 1px solid #0f172a; padding: 5px 6px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.4px; }
        table.data-table td { border: 1px solid #e2e8f0; padding: 4px 6px; vertical-align: middle; font-size: 8.5px; }
        table.data-table tr:nth-child(even) td { background: #f8fafc; }
        
        .tc { text-align: center; }
        .tr { text-align: right; }
        .font-bold { font-weight: bold; }

        .badge { display: inline-block; padding: 2px 4px; font-size: 7.5px; font-weight: bold; border-radius: 3px; text-transform: uppercase; }
        .badge-create { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-update { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-delete { background-color: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
        .badge-security { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-module { background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-weight: 700; }

        .ip-text { font-family: 'Courier', monospace; font-size: 8px; color: #475569; }

        .footer { margin-top: 15px; text-align: center; font-size: 7.5px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $settings->site_name ?? 'B2B Viking ERP' }}</h1>
        <div class="sub">System Audit Trail & Compliance Forensic Report</div>
        <div class="info">
            Generated: {{ now()->format('d M Y, h:i A') }}
            @if(!empty($filters['start_date'])) | From: {{ $filters['start_date'] }} @endif
            @if(!empty($filters['end_date'])) | To: {{ $filters['end_date'] }} @endif
            @if(!empty($filters['module'])) | Module: {{ ucfirst($filters['module']) }} @endif
            @if(!empty($filters['action'])) | Action: {{ ucfirst(str_replace('_', ' ', $filters['action'])) }} @endif
            @if($selectedUser) | Staff: {{ $selectedUser->name }} @endif
            @if(!empty($filters['critical_only'])) | Filter: Critical Mutations Only @endif
        </div>
    </div>

    <!-- Executive KPI Grid -->
    <table class="summary-grid">
        <tr>
            <td class="summary-cell">
                <div class="lbl">Total Logged Events</div>
                <div class="val val-primary">{{ number_format($summary['total_logs']) }}</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Affected Modules</div>
                <div class="val">{{ number_format($summary['modules_count']) }}</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Active Operators / Staff</div>
                <div class="val val-success">{{ number_format($summary['staff_count']) }}</div>
            </td>
            <td class="summary-cell">
                <div class="lbl">Critical Mutations (Deletes/Cancels)</div>
                <div class="val {{ $summary['critical_count'] > 0 ? 'val-danger' : 'val-success' }}">
                    {{ number_format($summary['critical_count']) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Itemized Forensic Audit Trail</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 14%;">Date & Time</th>
                <th style="width: 10%;">Module</th>
                <th style="width: 13%;">Action</th>
                <th style="width: 14%;">Reference / Entity</th>
                <th style="width: 14%;">Operator (Staff)</th>
                <th style="width: 13%;">IP Address</th>
                <th style="width: 22%;">Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                @php
                    $act = strtolower($log->action ?? '');
                    $badgeClass = 'badge-update';
                    if (str_contains($act, 'created') || str_contains($act, 'create')) {
                        $badgeClass = 'badge-create';
                    } elseif (str_contains($act, 'deleted') || str_contains($act, 'delete') || str_contains($act, 'void') || str_contains($act, 'cancel')) {
                        $badgeClass = 'badge-delete';
                    } elseif (str_contains($act, 'auth') || str_contains($act, 'login') || str_contains($act, 'password') || str_contains($act, 'role')) {
                        $badgeClass = 'badge-security';
                    }
                @endphp
                <tr>
                    <td>{{ $log->created_at?->format('d M Y, h:i A') }}</td>
                    <td>
                        <span class="badge badge-module">{{ strtoupper($log->module ?? 'SYSTEM') }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $badgeClass }}">
                            {{ str_replace('_', ' ', strtoupper($log->action ?? 'UPDATED')) }}
                        </span>
                    </td>
                    <td class="font-bold">{{ $log->reference_no ?: ($log->entity_type ? ucfirst($log->entity_type) . ' #' . $log->entity_id : 'N/A') }}</td>
                    <td>{{ $log->user?->name ?: 'Automated System' }}</td>
                    <td class="ip-text">{{ $log->ip_address ?: '—' }}</td>
                    <td>{{ $log->description ?: 'No event summary' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="tc" style="padding: 15px; color: #64748b;">No audit logs matched the specified criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $settings->site_name ?? 'B2B Viking' }} ERP &copy; {{ date('Y') }}. Confidential Enterprise Compliance Audit Log. Generated by {{ auth()->user()->name ?? 'System' }}.
    </div>

</body>
</html>
