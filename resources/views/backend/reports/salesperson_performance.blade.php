@extends('backend.layouts.master')

@section('title', 'Salesperson & Account Manager Performance Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-user-tie text-primary mr-2"></i> Salesperson & Account Manager Analytics</h1>
    </div>

    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white">
                <h4 class="text-primary font-weight-bold mb-0">Sales Representative Revenue & Collection Matrix</h4>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="salesperson-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Sales Representative / Creator</th>
                                <th class="text-center">Total Orders</th>
                                <th class="text-right">Gross Sales Revenue</th>
                                <th class="text-right">Collected Revenue</th>
                                <th class="text-right">Outstanding Dues</th>
                                <th class="text-right font-weight-bold">Avg Order Value (AOV)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($performance as $row)
                                <tr>
                                    <td class="font-weight-bold text-dark">
                                        <i class="fas fa-user-circle text-info mr-2"></i> {{ $row['rep_name'] }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-primary font-weight-bold">{{ $row['order_count'] }}</span>
                                    </td>
                                    <td class="text-right font-weight-bold text-dark">
                                        kr. {{ number_format((float)$row['total_sales'], 2) }}
                                    </td>
                                    <td class="text-right font-weight-bold text-success">
                                        kr. {{ number_format((float)$row['total_paid'], 2) }}
                                    </td>
                                    <td class="text-right font-weight-bold text-danger">
                                        kr. {{ number_format((float)$row['total_due'], 2) }}
                                    </td>
                                    <td class="text-right font-weight-bold text-primary">
                                        kr. {{ number_format((float)$row['avg_deal_size'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No sales performance data recorded.</td>
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
        if ($('#salesperson-table tbody tr').length > 0 && !$('#salesperson-table tbody td').hasClass('dataTables_empty')) {
            $('#salesperson-table').DataTable({
                "pageLength": 25,
                "order": [[2, "desc"]],
                "language": {
                    "search": "Filter Sales Rep:"
                }
            });
        }
    });
</script>
@endpush
