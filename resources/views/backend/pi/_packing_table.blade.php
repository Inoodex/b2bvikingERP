@php
    $piType = $piInfo['pi_type'] ?? 'simple';
    $rows = $piInfo['rows'] ?? [];
    $blocks = $piInfo['blocks'] ?? [];
    $rowsCollection = collect($rows);
    $showSimpleOrderedQty = $rowsCollection->contains(fn ($row) => isset($row['ordered_qty']) && $row['ordered_qty'] !== null && $row['ordered_qty'] !== '');
    $showSimpleCtnNo = $rowsCollection->contains(fn ($row) => !empty($row['ctn_no']));
    $showSimpleCtnSize = $rowsCollection->contains(fn ($row) => !empty($row['ctn_size']));
    $showSimplePcsPerCtn = $rowsCollection->contains(fn ($row) => isset($row['pcs_per_ctn']) && $row['pcs_per_ctn'] !== null && $row['pcs_per_ctn'] !== '');
    $showSimpleCtnQty = $rowsCollection->contains(fn ($row) => isset($row['ctn_qty']) && $row['ctn_qty'] !== null && $row['ctn_qty'] !== '');
    $showSimpleTotalPcs = $rowsCollection->contains(fn ($row) => isset($row['total_pcs']) && $row['total_pcs'] !== null && $row['total_pcs'] !== '');
    $showSimpleNw = $rowsCollection->contains(fn ($row) => isset($row['nw_kg']) && $row['nw_kg'] !== null && $row['nw_kg'] !== '');
    $showSimpleGw = $rowsCollection->contains(fn ($row) => isset($row['gw_kg']) && $row['gw_kg'] !== null && $row['gw_kg'] !== '');
    $showSimpleNote = $rowsCollection->contains(fn ($row) => !empty($row['note']));
    $visibleSimpleColumns = 1
        + ($showSimpleOrderedQty ? 1 : 0)
        + ($showSimpleCtnNo ? 1 : 0)
        + ($showSimpleCtnSize ? 1 : 0)
        + ($showSimplePcsPerCtn ? 1 : 0)
        + ($showSimpleCtnQty ? 1 : 0)
        + ($showSimpleTotalPcs ? 1 : 0)
        + ($showSimpleNw ? 1 : 0)
        + ($showSimpleGw ? 1 : 0)
        + ($showSimpleNote ? 1 : 0);
@endphp

<div style="margin-top: 28px;">
    @if($piType === 'advanced')
        <table cellpadding="6" style="margin-bottom: 18px; border: 1px solid #222; font-size: 13px; width: 100%; border-collapse: collapse; table-layout: fixed;">
            <tbody>
                <tr>
                    <td style="border: 1px solid #222; padding: 8px 12px; width: 18%; font-weight: bold;">SHIPMENT QTY :</td>
                    <td style="border: 1px solid #222; padding: 8px 12px; width: 32%; font-weight: bold;">{{ number_format($piInfo['shipment_qty'] ?? 0) }} PCS</td>
                    <td style="border: 1px solid #222; padding: 8px 12px; width: 18%; font-weight: bold;">DATE :</td>
                    <td style="border: 1px solid #222; padding: 8px 12px; font-weight: bold;">
                        {{ !empty($piInfo['shipment_date']) ? \Illuminate\Support\Carbon::parse($piInfo['shipment_date'])->format('d-M-Y') : 'N/A' }}
                    </td>
                </tr>
            </tbody>
        </table>

        @foreach($blocks as $block)
            @php
                $variantHeaders = $block['variant_headers'] ?? [];
                $rowsToShow = collect($block['rows'] ?? [])->filter(function ($row) {
                    return ($row['ctn_qty'] ?? null) !== null
                        || !empty($row['ctn_no'])
                        || collect($row['variants'] ?? [])->contains(fn ($value) => $value !== null && $value !== '')
                        || ($row['pcs'] ?? null) !== null
                        || ($row['total_pcs'] ?? null) !== null
                        || ($row['nw_kg'] ?? null) !== null
                        || ($row['gw_kg'] ?? null) !== null;
                })->values();
                $blockCtn = $rowsToShow->sum(fn ($row) => max(0, (int) ($row['ctn_qty'] ?? 0)));
                $blockTotalPcs = $rowsToShow->sum(fn ($row) => max(0, (int) ($row['total_pcs'] ?? \App\Support\PiInfoSupport::rowPcs($row))));
                $blockNw = $rowsToShow->sum(fn ($row) => max(0, (float) ($row['nw_kg'] ?? 0)));
                $blockGw = $rowsToShow->sum(fn ($row) => max(0, (float) ($row['gw_kg'] ?? 0)));
                $activeVariantIndexes = collect($variantHeaders)->keys()->filter(function ($index) use ($rowsToShow) {
                    return $rowsToShow->sum(fn ($row) => max(0, (int) ($row['variants'][$index] ?? 0))) > 0;
                })->values()->all();
                $activeVariantMap = array_map(fn ($index) => $variantHeaders[$index], $activeVariantIndexes);
                $variantHeaderCount = count($activeVariantMap);
                $showCtnQty = $rowsToShow->contains(fn ($row) => $row['ctn_qty'] !== null && $row['ctn_qty'] !== '');
                $showCtnNo = $rowsToShow->contains(fn ($row) => !empty($row['ctn_no']));
                $showPcs = $rowsToShow->contains(fn ($row) => \App\Support\PiInfoSupport::rowPcs($row) > 0);
                $showTotalPcs = $rowsToShow->contains(fn ($row) => $row['total_pcs'] !== null && $row['total_pcs'] !== '');
                $showNw = $rowsToShow->contains(fn ($row) => $row['nw_kg'] !== null && $row['nw_kg'] !== '');
                $showGw = $rowsToShow->contains(fn ($row) => $row['gw_kg'] !== null && $row['gw_kg'] !== '');
                $showImageColumn = !empty($block['optimized_image']) || !empty($block['image']);
                $visibleColumns = 2
                    + ($showImageColumn ? 1 : 0)
                    + ($showCtnQty ? 1 : 0)
                    + ($showCtnNo ? 1 : 0)
                    + $variantHeaderCount
                    + ($showPcs ? 1 : 0)
                    + ($showTotalPcs ? 1 : 0)
                    + ($showNw ? 1 : 0)
                    + ($showGw ? 1 : 0);
                $blockCtnSize = trim((string) ($block['ctn_size'] ?? ''));
                if ($blockCtnSize === '') {
                    $blockCtnSize = collect($block['rows'] ?? [])
                        ->map(fn ($row) => trim((string) ($row['ctn_size'] ?? '')))
                        ->filter()
                        ->first() ?? '';
                }
                $imageBase64 = $block['optimized_image'] ?? null;
                if (!$imageBase64) {
                    $imagePath = (string) ($block['image'] ?? '');
                    if ($imagePath !== '') {
                        $normalized = ltrim(str_replace('storage/', '', $imagePath), '/');
                        $candidates = [
                            public_path(ltrim($imagePath, '/')),
                            public_path('storage/' . $normalized),
                            storage_path('app/public/' . $normalized),
                        ];
                        foreach ($candidates as $candidate) {
                            if (is_file($candidate)) {
                                $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION) ?: 'jpg');
                                $mime = match ($ext) {
                                    'png' => 'png',
                                    'gif' => 'gif',
                                    'webp' => 'webp',
                                    default => 'jpeg',
                                };
                                $imageBase64 = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($candidate));
                                break;
                            }
                        }
                    }
                }
            @endphp
            <table cellpadding="6" style="margin-bottom: 22px; border: 1px solid #222; font-size: 12px; width: 100%; border-collapse: collapse; table-layout: fixed;">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 26%; border: 1px solid #222; text-align: center; padding: 6px 4px;">COLOR</th>
                        @if($showImageColumn)
                            <th rowspan="2" style="width: 12%; border: 1px solid #222; text-align: center; padding: 6px 4px;">Picture</th>
                        @endif
                        @if($showCtnQty)
                            <th rowspan="2" style="width: 8%; border: 1px solid #222; text-align: center; padding: 6px 4px;">CTN QTY</th>
                        @endif
                        @if($showCtnNo)
                            <th rowspan="2" style="width: 10%; border: 1px solid #222; text-align: center; padding: 6px 4px;">CTN NO.</th>
                        @endif
                        @if($variantHeaderCount > 0)
                            <th colspan="{{ $variantHeaderCount }}" style="text-align: center; border: 1px solid #222; padding: 6px 4px;">VARIANT QTY</th>
                        @endif
                        @if($showPcs)
                            <th rowspan="2" style="width: 8%; border: 1px solid #222; text-align: center; padding: 6px 4px;">PCS</th>
                        @endif
                        @if($showTotalPcs)
                            <th rowspan="2" style="width: 10%; border: 1px solid #222; text-align: center; padding: 6px 4px;">TOTAL PCS</th>
                        @endif
                        @if($showNw)
                            <th rowspan="2" style="width: 8%; border: 1px solid #222; text-align: center; padding: 6px 4px;">N.W(KG)</th>
                        @endif
                        @if($showGw)
                            <th rowspan="2" style="width: 8%; border: 1px solid #222; text-align: center; padding: 6px 4px;">G.W(KG)</th>
                        @endif
                    </tr>
                    @if($variantHeaderCount > 0)
                        <tr>
                            @foreach($activeVariantMap as $header)
                                <th style="text-align: center; border: 1px solid #222; padding: 6px 4px; font-size: 11px;">{{ $header }}</th>
                            @endforeach
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse($rowsToShow as $rowIndex => $row)
                        <tr>
                            @if($rowIndex === 0)
                                <td rowspan="{{ $rowsToShow->count() + 1 }}" style="font-weight: bold; vertical-align: middle; border: 1px solid #222; text-align: center;">
                                    <div>{{ $block['color_label'] ?: 'N/A' }}</div>
                                    @if(!empty($block['title']))
                                        <div style="font-size: 10px; color: #444; margin-top: 6px;">{{ $block['title'] }}</div>
                                    @endif
                                    @if($blockCtnSize !== '')
                                        <div style="font-size: 10px; color: #444; margin-top: 6px;">
                                            <strong>CTN MEASUREMENT:</strong> {{ $blockCtnSize }}
                                        </div>
                                    @endif
                                </td>
                            @endif
                            @if($showImageColumn)
                                <td rowspan="{{ $rowsToShow->count() + 1 }}" style="text-align: center; vertical-align: middle; border: 1px solid #222;">
                                    @if($imageBase64)
                                        <img src="{{ $imageBase64 }}" alt="" style="max-width: 72px; max-height: 72px; object-fit: contain;">
                                    @else
                                        <span style="font-size: 11px; color: #888;">No Image</span>
                                    @endif
                                </td>
                            @endif
                            @if($showCtnQty)
                                <td style="border: 1px solid #222; text-align: center;">{{ $row['ctn_qty'] !== null ? number_format((int) $row['ctn_qty']) : '-' }}</td>
                            @endif
                            @if($showCtnNo)
                                <td style="border: 1px solid #222; text-align: center;">{{ $row['ctn_no'] ?: '-' }}</td>
                            @endif
                            @foreach($activeVariantIndexes as $variantIndex)
                                <td style="text-align: center; border: 1px solid #222;">{{ isset($row['variants'][$variantIndex]) && $row['variants'][$variantIndex] !== null ? number_format((int) $row['variants'][$variantIndex]) : '' }}</td>
                            @endforeach
                            @if($showPcs)
                                <td style="border: 1px solid #222; text-align: center;">{{ number_format(\App\Support\PiInfoSupport::rowPcs($row)) }}</td>
                            @endif
                            @if($showTotalPcs)
                                <td style="border: 1px solid #222; text-align: center;">{{ $row['total_pcs'] !== null ? number_format((int) $row['total_pcs']) : number_format(\App\Support\PiInfoSupport::rowPcs($row)) }}</td>
                            @endif
                            @if($showNw)
                                <td style="border: 1px solid #222; text-align: center;">{{ $row['nw_kg'] !== null ? number_format((float) $row['nw_kg'], 2) : '-' }}</td>
                            @endif
                            @if($showGw)
                                <td style="border: 1px solid #222; text-align: center;">{{ $row['gw_kg'] !== null ? number_format((float) $row['gw_kg'], 2) : '-' }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $visibleColumns }}" style="text-align: center; color: #777; border: 1px solid #222;">No matrix rows saved yet.</td>
                        </tr>
                    @endforelse
                    @if($rowsToShow->isNotEmpty())
                        <tr style="background: #f3f4f6; font-weight: bold;">
                            @if($showCtnQty)
                                <td style="border: 1px solid #222; text-align: center;">{{ number_format($blockCtn) }} CTN</td>
                            @endif
                            @if($showCtnNo)
                                <td style="border: 1px solid #222; text-align: center;">TOTAL</td>
                            @endif
                            @foreach($activeVariantIndexes as $variantIndex)
                                <td style="text-align: center; border: 1px solid #222;">
                                    {{ number_format($rowsToShow->sum(fn ($row) => max(0, (int) ($row['variants'][$variantIndex] ?? 0)))) }}
                                </td>
                            @endforeach
                            @if($showPcs)
                                <td style="border: 1px solid #222; text-align: center;">{{ number_format($rowsToShow->sum(fn ($row) => \App\Support\PiInfoSupport::rowPcs($row))) }}</td>
                            @endif
                            @if($showTotalPcs)
                                <td style="border: 1px solid #222; text-align: center;">{{ number_format($blockTotalPcs) }}</td>
                            @endif
                            @if($showNw)
                                <td style="border: 1px solid #222; text-align: center;">{{ number_format($blockNw, 2) }}</td>
                            @endif
                            @if($showGw)
                                <td style="border: 1px solid #222; text-align: center;">{{ number_format($blockGw, 2) }}</td>
                            @endif
                        </tr>
                    @endif
                </tbody>
            </table>
        @endforeach

        <table style="margin-bottom: 0; border: 1px solid #222; font-size: 12px;">
            <tbody>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td style="border: 1px solid #222; padding: 8px 10px; width: 25%;">ORDER QTY</td>
                    <td style="border: 1px solid #222; padding: 8px 10px; width: 25%;">{{ number_format($piInfo['order_qty_total'] ?? 0) }} PCS</td>
                    <td style="border: 1px solid #222; padding: 8px 10px; width: 25%;">TOTAL CTN</td>
                    <td style="border: 1px solid #222; padding: 8px 10px;">{{ number_format($piTotals['ctn_qty'] ?? 0) }}</td>
                </tr>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td style="border: 1px solid #222; padding: 8px 10px;">TOTAL PCS</td>
                    <td style="border: 1px solid #222; padding: 8px 10px;">{{ number_format($piTotals['total_pcs'] ?? 0) }}</td>
                    <td style="border: 1px solid #222; padding: 8px 10px;">TOTAL N.W / G.W</td>
                    <td style="border: 1px solid #222; padding: 8px 10px;">{{ number_format($piTotals['nw_kg'] ?? 0, 2) }} / {{ number_format($piTotals['gw_kg'] ?? 0, 2) }} KG</td>
                </tr>
            </tbody>
        </table>
    @else
        <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
            <div style="background: #f8fafc; padding: 14px 18px; border-bottom: 1px solid #e5e7eb;">
                <h3 style="margin: 0; font-size: 18px;">Packing / CTN Summary</h3>
            </div>
            <div style="padding: 18px;">
                <table style="margin: 0;">
                    <tbody>
                        <tr>
                            <td style="border: none; padding: 6px 0; width: 22%;"><strong>Order Qty</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ number_format($piInfo['order_qty_total'] ?? 0) }} PCS</td>
                            <td style="border: none; padding: 6px 0; width: 22%;"><strong>Shipment Qty</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ number_format($piInfo['shipment_qty'] ?? 0) }} PCS</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 6px 0;"><strong>Shipment Date</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ !empty($piInfo['shipment_date']) ? \Illuminate\Support\Carbon::parse($piInfo['shipment_date'])->format('d-M-Y') : 'N/A' }}</td>
                            <td style="border: none; padding: 6px 0;"><strong>Total CTN</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ number_format($piTotals['ctn_qty'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 6px 0;"><strong>Total PCS</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ number_format($piTotals['total_pcs'] ?? 0) }}</td>
                            <td style="border: none; padding: 6px 0;"><strong>Total N.W / G.W</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ number_format($piTotals['nw_kg'] ?? 0, 2) }} / {{ number_format($piTotals['gw_kg'] ?? 0, 2) }} KG</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    @if($showSimpleOrderedQty)
                        <th style="width: 10%;" class="text-right">Ord. Qty</th>
                    @endif
                    @if($showSimpleCtnNo)
                        <th style="width: 12%;">CTN No</th>
                    @endif
                    @if($showSimpleCtnSize)
                        <th style="width: 17%;">CTN Size</th>
                    @endif
                    @if($showSimplePcsPerCtn)
                        <th style="width: 10%;" class="text-right">PCS/CTN</th>
                    @endif
                    @if($showSimpleCtnQty)
                        <th style="width: 10%;" class="text-right">CTN Qty</th>
                    @endif
                    @if($showSimpleTotalPcs)
                        <th style="width: 10%;" class="text-right">Total PCS</th>
                    @endif
                    @if($showSimpleNw)
                        <th style="width: 8%;" class="text-right">N.W</th>
                    @endif
                    @if($showSimpleGw)
                        <th style="width: 8%;" class="text-right">G.W</th>
                    @endif
                    @if($showSimpleNote)
                        <th style="width: 17%;">Remarks</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        @if($showSimpleOrderedQty)
                            <td class="text-right">{{ isset($row['ordered_qty']) ? number_format((int) $row['ordered_qty']) : '-' }}</td>
                        @endif
                        @if($showSimpleCtnNo)
                            <td>{{ $row['ctn_no'] ?: '-' }}</td>
                        @endif
                        @if($showSimpleCtnSize)
                            <td>{{ $row['ctn_size'] ?: '-' }}</td>
                        @endif
                        @if($showSimplePcsPerCtn)
                            <td class="text-right">{{ isset($row['pcs_per_ctn']) ? number_format((int) $row['pcs_per_ctn']) : '-' }}</td>
                        @endif
                        @if($showSimpleCtnQty)
                            <td class="text-right">{{ isset($row['ctn_qty']) ? number_format((int) $row['ctn_qty']) : '-' }}</td>
                        @endif
                        @if($showSimpleTotalPcs)
                            <td class="text-right">{{ isset($row['total_pcs']) ? number_format((int) $row['total_pcs']) : '-' }}</td>
                        @endif
                        @if($showSimpleNw)
                            <td class="text-right">{{ isset($row['nw_kg']) ? number_format((float) $row['nw_kg'], 2) : '-' }}</td>
                        @endif
                        @if($showSimpleGw)
                            <td class="text-right">{{ isset($row['gw_kg']) ? number_format((float) $row['gw_kg'], 2) : '-' }}</td>
                        @endif
                        @if($showSimpleNote)
                            <td>{{ $row['note'] ?: '-' }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $visibleSimpleColumns }}" style="text-align: center; color: #777;">No PI packing rows saved yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if(!empty($piInfo['packing_note']))
        <div style="margin-top: 14px; padding: 12px 14px; background: #fff7ed; border-left: 4px solid #fb923c;">
            <strong>Packing Note:</strong> {{ $piInfo['packing_note'] }}
        </div>
    @endif
</div>
