<div class="card shadow-sm border-0" id="shipments-table-card" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-truck-loading mr-2 text-primary"></i>Historical Multi-Shipment Records</h6>
        <span class="badge badge-light border text-muted px-2.5 py-1.5" style="font-size: 11px;">
            Showing {{ $shipments->firstItem() ?? 0 }} to {{ $shipments->lastItem() ?? 0 }} of {{ number_format($shipments->total()) }} shipments
        </span>
    </div>
    <div class="card-body p-0 position-relative">
        {{-- AJAX Loading Overlay --}}
        <div class="table-loading-overlay" style="display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.7); z-index: 10; align-items: center; justify-content: center;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 w-100">
                <thead class="bg-light" style="font-size: 0.75rem; text-transform: uppercase; color: #475569;">
                    <tr>
                        <th class="pl-4 py-3">PO / Invoice</th>
                        <th class="py-3">Date</th>
                        <th class="py-3">Supplier</th>
                        <th class="py-3">Product / Variant</th>
                        <th class="text-center py-3">Quantity</th>
                        <th class="text-right py-3">Ex-Factory Unit Cost</th>
                        <th class="text-right py-3">Landed Cost / Unit</th>
                        <th class="text-right py-3">Line Total</th>
                        <th class="text-center pr-4 py-3" style="width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $detail)
                        @php
                            $po = $detail->purchase;
                            $unitCost = (float) $detail->unit_cost;
                            $landedCost = (float) ($detail->landed_cost > 0 ? $detail->landed_cost : $unitCost);
                            $qty = (float) $detail->qty;
                            $lineTotal = $qty * $landedCost;
                            $poNo = $po?->purchase_no ?? ($po?->invoice_no ?? ('#'.$detail->purchase_id));
                            $dateFormatted = $po?->date ? \Carbon\Carbon::parse($po->date)->format('M d, Y') : ($detail->created_at ? $detail->created_at->format('M d, Y') : '—');
                            $vendorName = $po?->vendor?->shop_name ?? ($po?->vendor?->name ?? 'General Supplier');
                            $prodName = $detail->product?->name ?? 'Product';
                            $variantName = $detail->variant ? $detail->variant->name : '';
                            $fullUrl = $po ? route('admin.purchase-orders.show', $po->id) : '';
                            $rawUnit = $detail->product?->unit?->name;
                            $unitName = $rawUnit ? (strtolower($rawUnit) === 'pics' ? 'pcs' : $rawUnit) : 'pcs';
                        @endphp
                        <tr>
                            <td class="pl-4 font-weight-bold">
                                <a href="javascript:void(0)" class="text-primary view-shipment-detail-btn" 
                                   data-po-no="{{ $poNo }}"
                                   data-date="{{ $dateFormatted }}"
                                   data-vendor="{{ $vendorName }}"
                                   data-product="{{ $prodName }}"
                                   data-variant="{{ $variantName }}"
                                   data-qty="{{ number_format($qty) }} {{ $unitName }}"
                                   data-unit-cost="kr. {{ number_format($unitCost, 2) }}"
                                   data-raw-cost="kr. {{ number_format((float)($detail->raw_material_cost ?? 0), 2) }}"
                                   data-tax-cost="kr. {{ number_format((float)($detail->tax_cost ?? 0), 2) }}"
                                   data-transport-cost="kr. {{ number_format((float)($detail->transport_cost ?? 0), 2) }}"
                                   data-landed-cost="kr. {{ number_format($landedCost, 2) }}"
                                   data-total="kr. {{ number_format($lineTotal, 2) }}"
                                   data-status="{{ ucfirst($po?->milestone_status ?? 'completed') }}"
                                   data-url="{{ $fullUrl }}"
                                   style="font-family: monospace; text-decoration: none;">
                                    <i class="fas fa-file-invoice mr-1"></i>{{ $poNo }}
                                </a>
                            </td>
                            <td class="text-muted small">
                                {{ $dateFormatted }}
                            </td>
                            <td>
                                <strong class="text-dark">{{ $vendorName }}</strong>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $prodName }}</strong>
                                @if($variantName)
                                    <small class="badge badge-light border mt-0.5">{{ $variantName }}</small>
                                @endif
                            </td>
                            <td class="text-center font-weight-bold text-dark">
                                {{ number_format($qty) }} {{ $unitName }}
                            </td>
                            <td class="text-right text-muted font-weight-semibold">
                                kr. {{ number_format($unitCost, 2) }}
                            </td>
                            <td class="text-right align-middle">
                                <div class="font-weight-bold text-primary" style="font-size: 13.5px; white-space: nowrap; font-variant-numeric: tabular-nums;">
                                    kr. {{ number_format($landedCost, 2) }}
                                </div>
                                @php
                                    $wac = (float)($negotiationMetrics['weighted_avg_cost'] ?? 0);
                                @endphp
                                @if($landedCost > 0 && $wac > 0)
                                    @php
                                        $costDiff = $landedCost - $wac;
                                        $pctDiff = round(($costDiff / $wac) * 100);
                                    @endphp
                                    @if($pctDiff <= -1)
                                        <div class="mt-0.5 d-flex justify-content-end">
                                            <span class="badge badge-light border border-success text-success px-1.5 py-0.5 d-inline-flex align-items-center shadow-xs" 
                                                  style="font-size: 9.5px; font-weight: 600; border-radius: 10px; background-color: #f0fdf4; cursor: help;"
                                                  data-toggle="tooltip" 
                                                  data-placement="top" 
                                                  title="Procured below average: {{ abs($pctDiff) }}% cheaper than weighted average (kr. {{ number_format($wac, 2) }}) — Saves kr. {{ number_format(abs($costDiff), 2) }}/pc">
                                                <i class="fas fa-arrow-down mr-1" style="font-size: 8px;"></i>{{ abs($pctDiff) }}%
                                            </span>
                                        </div>
                                    @elseif($pctDiff >= 1)
                                        <div class="mt-0.5 d-flex justify-content-end">
                                            <span class="badge badge-light border border-danger text-danger px-1.5 py-0.5 d-inline-flex align-items-center shadow-xs" 
                                                  style="font-size: 9.5px; font-weight: 600; border-radius: 10px; background-color: #fef2f2; cursor: help;"
                                                  data-toggle="tooltip" 
                                                  data-placement="top" 
                                                  title="Procured above average: +{{ $pctDiff }}% higher than weighted average (kr. {{ number_format($wac, 2) }}) — Costs kr. {{ number_format($costDiff, 2) }}/pc more">
                                                <i class="fas fa-arrow-up mr-1" style="font-size: 8px;"></i>+{{ $pctDiff }}%
                                            </span>
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-right font-weight-bold text-dark">
                                kr. {{ number_format($lineTotal, 2) }}
                            </td>
                            <td class="text-center pr-4">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary view-shipment-detail-btn font-weight-bold shadow-sm"
                                        data-po-no="{{ $poNo }}"
                                        data-date="{{ $dateFormatted }}"
                                        data-vendor="{{ $vendorName }}"
                                        data-product="{{ $prodName }}"
                                        data-variant="{{ $variantName }}"
                                        data-qty="{{ number_format($qty) }} {{ $unitName }}"
                                        data-unit-cost="kr. {{ number_format($unitCost, 2) }}"
                                        data-raw-cost="kr. {{ number_format((float)($detail->raw_material_cost ?? 0), 2) }}"
                                        data-tax-cost="kr. {{ number_format((float)($detail->tax_cost ?? 0), 2) }}"
                                        data-transport-cost="kr. {{ number_format((float)($detail->transport_cost ?? 0), 2) }}"
                                        data-landed-cost="kr. {{ number_format($landedCost, 2) }}"
                                        data-total="kr. {{ number_format($lineTotal, 2) }}"
                                        data-status="{{ ucfirst($po?->milestone_status ?? 'completed') }}"
                                        data-url="{{ $fullUrl }}"
                                        title="View Shipment Details" 
                                        style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No historical shipment records found for the selected scope.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- DataTables Style Compact Footer --}}
    <div class="card-footer bg-white py-3 border-top">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center flex-wrap" style="gap: 12px;">
            <div class="text-muted font-weight-normal" style="font-size: 13.5px; color: #64748b;">
                Showing {{ $shipments->firstItem() ?? 0 }} to {{ $shipments->lastItem() ?? 0 }} of {{ number_format($shipments->total()) }} entries
            </div>
            <div class="negotiation-pagination-wrapper">
                {{ $shipments->appends(request()->query())->links('vendor.pagination.custom-report') }}
            </div>
        </div>
    </div>
</div>
