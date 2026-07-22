@if($customers->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover table-md mb-0">
            <thead class="bg-whitesmoke">
                <tr>
                    <th class="pl-4" style="width:70px;">Rank</th>
                    <th>User / Outlet</th>
                    <th>Email</th>
                    <th class="text-right pr-4">Total Value</th>
                    <th class="text-center">Total Orders</th>
                    <th class="text-center" style="width:120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $i => $customer)
                    @php
                        $displayName = optional($customer->user)->outlet_name ?: (optional($customer->user)->name ?? 'N/A');
                        $email       = optional($customer->user)->email ?? '—';
                        $rank        = ($customers->currentPage() - 1) * $customers->perPage() + $i + 1;
                    @endphp
                    <tr>
                        <td class="pl-4">
                            @if($rank == 1)
                                <span class="badge badge-warning" style="font-size:12px; font-weight:700; background-color:#ffc107; color:#000;"><i class="fas fa-trophy mr-1"></i> #1</span>
                            @elseif($rank == 2)
                                <span class="badge badge-secondary" style="font-size:12px; font-weight:700; background-color:#6c757d; color:#fff;"><i class="fas fa-medal mr-1"></i> #2</span>
                            @elseif($rank == 3)
                                <span class="badge" style="font-size:12px; font-weight:700; background-color:#cd7f32; color:#fff;"><i class="fas fa-award mr-1"></i> #3</span>
                            @else
                                <span class="text-muted font-weight-bold pl-2" style="font-size:13px;">#{{ $rank }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark" style="font-size:14px;">{{ $displayName }}</div>
                            @if(optional($customer->user)->outlet_name && optional($customer->user)->name)
                                <div class="text-muted" style="font-size:11px;"><i class="fas fa-user-circle mr-1"></i>{{ optional($customer->user)->name }}</div>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:13px;">{{ $email }}</td>
                        <td class="text-right pr-4 font-weight-bold text-success" style="font-size:14px;">
                            {!! formatWithCurrency($customer->total_value) !!}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-primary px-3 py-1" style="font-size:12px; border-radius:12px;">
                                {{ number_format($customer->total_orders) }} orders
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.reports.orders', ['user_id' => $customer->user_id]) }}"
                               class="btn btn-outline-primary btn-sm rounded-pill px-3"
                               title="View Detail Order Report">
                                <i class="fas fa-chart-bar mr-1"></i> Report
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-5 text-muted">
        <i class="fas fa-crown fa-3x mb-3 text-warning" style="opacity:0.3;"></i>
        <p class="mb-0 font-weight-bold">No customer records found for the selected filters.</p>
    </div>
@endif

