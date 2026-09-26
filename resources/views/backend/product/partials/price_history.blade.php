<div class="card border shadow-sm mb-4" style="border-radius: 12px; border-color: #e2e8f0; overflow: hidden;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3" style="border-bottom: 1px solid #edf2f7;">
        <div>
            <h5 class="mb-0 font-weight-bold text-dark d-flex align-items-center" style="font-size: 15px;">
                <span class="d-inline-flex align-items-center justify-content-center bg-primary-light text-primary rounded-circle mr-2" style="width: 30px; height: 30px; background-color: #eef2ff;">
                    <i class="fas fa-history" style="color: #4f46e5; font-size: 13px;"></i>
                </span>
                Purchase Price & Landed Cost History
            </h5>
            <small class="text-muted">Track historical shipment costs, freight/duty variances, and supplier negotiation benchmarks.</small>
        </div>
        <div>
            <span class="badge badge-pill badge-primary px-3 py-2" style="background-color: #4f46e5; font-size: 11px;">
                <i class="fas fa-ship mr-1"></i> {{ $negotiationMetrics['shipment_count'] ?? 0 }} Shipments Recorded
            </span>
        </div>
    </div>

    <div class="card-body p-3" style="background-color: #f8fafc;">
        {{-- Executive KPI Metrics Cards --}}
        <div class="row mb-3">
            <div class="col-6 col-md-3 mb-2">
                <div class="bg-white p-3 rounded shadow-sm border h-100" style="border-color: #e2e8f0 !important;">
                    <div class="text-muted text-uppercase mb-1" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">
                        <i class="fas fa-arrow-down text-success mr-1"></i> Lowest Ever Cost
                    </div>
                    <div class="h5 font-weight-bold text-success mb-0" style="font-size: 18px;">
                        {!! formatConverted($negotiationMetrics['lowest_cost']) !!}
                    </div>
                    <small class="text-muted" style="font-size: 10px;">Best benchmark achieved</small>
                </div>
            </div>

            <div class="col-6 col-md-3 mb-2">
                <div class="bg-white p-3 rounded shadow-sm border h-100" style="border-color: #e2e8f0 !important;">
                    <div class="text-muted text-uppercase mb-1" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">
                        <i class="fas fa-arrow-up text-danger mr-1"></i> Highest Ever Cost
                    </div>
                    <div class="h5 font-weight-bold text-danger mb-0" style="font-size: 18px;">
                        {!! formatConverted($negotiationMetrics['highest_cost']) !!}
                    </div>
                    <small class="text-muted" style="font-size: 10px;">Peak landed expenditure</small>
                </div>
            </div>

            <div class="col-6 col-md-3 mb-2">
                <div class="bg-white p-3 rounded shadow-sm border h-100" style="border-color: #e2e8f0 !important;">
                    <div class="text-muted text-uppercase mb-1" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">
                        <i class="fas fa-balance-scale text-primary mr-1"></i> Weighted Avg Cost
                    </div>
                    <div class="h5 font-weight-bold text-primary mb-0" style="font-size: 18px;">
                        {!! formatConverted($negotiationMetrics['weighted_avg_cost']) !!}
                    </div>
                    <small class="text-muted" style="font-size: 10px;">Volume-weighted baseline</small>
                </div>
            </div>

            <div class="col-6 col-md-3 mb-2">
                <div class="bg-white p-3 rounded shadow-sm border h-100" style="border-color: #e2e8f0 !important;">
                    <div class="text-muted text-uppercase mb-1" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">
                        <i class="fas fa-clock text-info mr-1"></i> Latest Shipment
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="h5 font-weight-bold text-dark mb-0" style="font-size: 18px;">
                            {!! formatConverted($negotiationMetrics['latest_cost']) !!}
                        </div>
                        @if(isset($negotiationMetrics['trend_percent']) && $negotiationMetrics['trend_percent'] !== null)
                            @if($negotiationMetrics['trend_percent'] > 0)
                                <span class="badge badge-danger" style="font-size: 10px;" title="Cost increased vs prior shipment">
                                    <i class="fas fa-caret-up mr-1"></i>+{{ $negotiationMetrics['trend_percent'] }}%
                                </span>
                            @elseif($negotiationMetrics['trend_percent'] < 0)
                                <span class="badge badge-success" style="font-size: 10px;" title="Cost decreased vs prior shipment">
                                    <i class="fas fa-caret-down mr-1"></i>{{ $negotiationMetrics['trend_percent'] }}%
                                </span>
                            @else
                                <span class="badge badge-secondary" style="font-size: 10px;">
                                    0%
                                </span>
                            @endif
                        @endif
                    </div>
                    <small class="text-muted" style="font-size: 10px;">Most recent replacement cost</small>
                </div>
            </div>
        </div>

        {{-- Negotiation Advisory Banner --}}
        @if($purchaseHistory->isNotEmpty())
        <div class="alert alert-light border d-flex align-items-center py-2 px-3 mb-3" style="border-color: #cbd5e1 !important; border-radius: 8px; background-color: #ffffff;">
            <i class="fas fa-lightbulb text-warning mr-2" style="font-size: 16px;"></i>
            <div style="font-size: 12px; color: #334155;">
                <strong>Procurement Insight:</strong> Supplier negotiation range is recommended between <strong>{!! formatConverted($negotiationMetrics['lowest_cost']) !!}</strong> (lowest historical) and <strong>{!! formatConverted($negotiationMetrics['weighted_avg_cost']) !!}</strong> (volume-weighted average).
                Current product catalog purchase price is <strong>{!! formatConverted($product->purchase_price) !!}</strong>.
            </div>
        </div>
        @endif

        {{-- Historical Shipments Table --}}
        <div class="table-responsive bg-white rounded border shadow-sm" style="border-color: #e2e8f0 !important;">
            <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                <thead style="background-color: #f1f5f9; color: #475569;">
                    <tr>
                        <th class="py-2 pl-3">PO Number</th>
                        <th class="py-2">Date</th>
                        <th class="py-2">Supplier / Vendor</th>
                        <th class="py-2">Variant</th>
                        <th class="py-2 text-right">Qty</th>
                        <th class="py-2 text-right">Ex-Factory Cost</th>
                        <th class="py-2 text-right">Landed Cost / Unit</th>
                        <th class="py-2 text-center">Cost Trend</th>
                        <th class="py-2 text-center pr-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseHistory as $idx => $detail)
                        @php
                            $purchase = $detail->purchase;
                            $vendor = $purchase?->vendor;
                            $vendorName = $vendor?->shop_name ?? $vendor?->name ?? 'Supplier';
                            $poNo = $purchase?->po_no ?? ('PO-' . $detail->purchase_id);
                            $date = $purchase?->date ? $purchase->date->format('d M Y') : $detail->created_at->format('d M Y');
                            $unitCost = (float)$detail->unit_cost;
                            $landedCost = (float)($detail->landed_cost > 0 ? $detail->landed_cost : $detail->unit_cost);

                            // Trend vs previous chronological shipment (which is next in descending array)
                            $prevShipment = $purchaseHistory[$idx + 1] ?? null;
                            $prevEffectiveCost = $prevShipment ? (float)($prevShipment->landed_cost > 0 ? $prevShipment->landed_cost : $prevShipment->unit_cost) : null;
                            $shipmentTrend = null;
                            if ($prevEffectiveCost && $prevEffectiveCost > 0) {
                                $shipmentTrend = round((($landedCost - $prevEffectiveCost) / $prevEffectiveCost) * 100, 1);
                            }

                            $milestone = $purchase?->milestone_status ?? 'draft';
                            $milestoneBadge = match ($milestone) {
                                'shipped'                  => 'badge-primary',
                                'goods_received', 'completed' => 'badge-success',
                                'goods_partial'            => 'badge-info',
                                'approved', 'po_sent'      => 'badge-warning text-dark',
                                'pi_attached', 'lc_opened' => 'badge-secondary',
                                default                    => 'badge-light border text-dark',
                            };
                        @endphp
                        <tr>
                            <td class="pl-3 py-2 align-middle font-weight-bold">
                                @if($purchase)
                                    <a href="{{ route('admin.purchase-orders.show', $purchase->id) }}" target="_blank" class="text-primary" title="Open Purchase Order in Procurement">
                                        <i class="fas fa-external-link-alt mr-1" style="font-size: 10px;"></i>{{ $poNo }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ $poNo }}</span>
                                @endif
                            </td>
                            <td class="py-2 align-middle text-muted">{{ $date }}</td>
                            <td class="py-2 align-middle font-weight-600 text-dark">{{ $vendorName }}</td>
                            <td class="py-2 align-middle text-muted">
                                @if($detail->variant)
                                    {{ $detail->variant->name ?? ('#' . $detail->variant->id) }}
                                @else
                                    <span class="text-muted">Standard</span>
                                @endif
                            </td>
                            <td class="py-2 align-middle text-right font-weight-bold text-dark">
                                {{ number_format((float)$detail->qty) }} {{ $product->unit?->name ? (strtolower($product->unit->name) === 'pics' ? 'pcs' : $product->unit->name) : 'pcs' }}
                            </td>
                            <td class="py-2 align-middle text-right font-weight-600">
                                {!! formatConverted($unitCost) !!}
                            </td>
                            <td class="py-2 align-middle text-right font-weight-bold text-primary">
                                {!! formatConverted($landedCost) !!}
                            </td>
                            <td class="py-2 align-middle text-center">
                                @if($shipmentTrend !== null)
                                    @if($shipmentTrend > 0)
                                        <span class="badge badge-danger px-2 py-1" style="font-size: 10px;">
                                            <i class="fas fa-arrow-up mr-1"></i>+{{ $shipmentTrend }}%
                                        </span>
                                    @elseif($shipmentTrend < 0)
                                        <span class="badge badge-success px-2 py-1" style="font-size: 10px;">
                                            <i class="fas fa-arrow-down mr-1"></i>{{ $shipmentTrend }}%
                                        </span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1" style="font-size: 10px;">
                                            <i class="fas fa-minus mr-1"></i>0%
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted" style="font-size: 10px;">Initial PO</span>
                                @endif
                            </td>
                            <td class="py-2 align-middle text-center pr-3">
                                <span class="badge {{ $milestoneBadge }} px-2 py-1" style="font-size: 10px;">
                                    {{ ucfirst(str_replace('_', ' ', $milestone)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <div class="py-3">
                                    <i class="fas fa-boxes text-muted mb-2" style="font-size: 28px; opacity: 0.5;"></i>
                                    <p class="mb-0 font-weight-600" style="font-size: 13px;">No historical purchase shipments recorded for this product yet.</p>
                                    <small class="text-muted">Once purchase orders are created and received, multi-shipment cost analytics and negotiation benchmarks will automatically populate here.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
