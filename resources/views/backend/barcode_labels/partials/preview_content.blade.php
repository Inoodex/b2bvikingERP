@php
    $isA4 = str_starts_with($presetKey, 'a4_');
@endphp

@if($labels->isEmpty())
    <div class="text-center py-5 text-muted px-3">
        <div class="preview-empty-icon mb-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 56px; height: 56px; background: rgba(245, 158, 11, 0.1);">
                <i class="fas fa-barcode fa-2x text-warning"></i>
            </span>
        </div>
        <h6 class="font-weight-bold text-dark mb-1">No Ready Barcodes in Queue</h6>
        <p class="small text-muted mb-3" style="max-width: 340px; margin: 0 auto;">
            The items in your queue do not have barcodes generated in the catalog yet. Click below to generate their barcodes.
        </p>
        <button type="button" class="btn btn-primary font-weight-bold px-4 py-2" id="btnPreviewGenerateTrigger">
            <i class="fas fa-bolt mr-2 text-warning"></i> Generate Barcodes Now
        </button>
    </div>
@else
    @if(!empty($unassignedCount) && $unassignedCount > 0)
        <div class="unassigned-alert-box p-3 mb-3 rounded border text-left shadow-xs" style="background: #fffbeb; border-color: #fde68a;">
            <div class="d-flex align-items-start">
                <div class="mr-2 text-warning mt-0.5">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="font-weight-bold text-dark small" style="font-size: 12.5px;">
                        {{ $unassignedCount }} item(s) without barcode hidden from preview
                    </div>
                    <div class="text-muted mt-1" style="font-size: 11px; line-height: 1.4;">
                        Only products with saved barcodes are shown. Click below to generate barcodes for the remaining items.
                    </div>
                    <div class="mt-2.5">
                        <button type="button" class="btn btn-xs btn-warning text-dark font-weight-bold px-3 py-1 shadow-xs" id="btnPreviewGenerateTrigger" style="font-size: 11px; border-radius: 6px;">
                            <i class="fas fa-bolt mr-1 text-dark"></i> Generate {{ $unassignedCount }} Missing Barcode(s)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="label-preview-container {{ $preset['css_class'] }}">
        @if($isA4)
            @php
                $cols = $preset['columns'] ?? 3;
                $rows = $preset['rows'] ?? 8;
                $perPage = $cols * $rows;
                $totalCopies = 0;

                $allStickers = [];
                foreach ($labels as $lbl) {
                    $copies = max(1, (int)($lbl['copies'] ?? 1));
                    $totalCopies += $copies;
                    for ($c = 0; $c < $copies; $c++) {
                        if (count($allStickers) < $perPage) {
                            $allStickers[] = $lbl;
                        }
                    }
                }
                $pages = array_chunk($allStickers, $perPage);
            @endphp

            @foreach($pages as $pageIndex => $pageStickers)
                <div class="a4-sheet-page mb-4 shadow-sm">
                    <div class="a4-sheet-header text-muted small px-3 pt-2 d-flex justify-content-between align-items-center">
                        <span class="badge badge-info"><i class="fas fa-eye mr-1"></i> Sample Sheet Preview (Page {{ $pageIndex + 1 }})</span>
                        <span>Total Print Qty: <strong>{{ $totalCopies }}</strong> Labels</span>
                    </div>
                    <div class="a4-grid-wrapper cols-{{ $cols }}">
                        @foreach($pageStickers as $sticker)
                            <div class="a4-sticker-cell">
                                <div class="sticker-centered-layout">
                                    @if($toggles['show_brand'] && !empty($sticker['company']))
                                        <div class="stk-company text-center">{{ $sticker['company'] }}</div>
                                    @endif

                                    @if($toggles['show_name'])
                                        <div class="stk-title text-center" title="{{ $sticker['title'] }}">{{ $sticker['title'] }}</div>
                                    @endif

                                    @if(!empty($sticker['variant_spec']))
                                        <div class="stk-variant-tag badge badge-dark px-2 py-0 my-1 font-weight-bold" style="font-size: 9px;">
                                            <i class="fas fa-tag mr-1 text-warning"></i>{{ $sticker['variant_spec'] }}
                                        </div>
                                    @endif

                                    <div class="stk-barcode-box text-center">
                                        {!! $sticker['barcode_svg'] !!}
                                    </div>

                                    @if($toggles['show_barcode_text'])
                                        <div class="stk-code text-center">{{ $sticker['barcode'] }}</div>
                                    @endif

                                    <div class="stk-footer-centered text-center">
                                        @if($toggles['show_sku'])
                                            <span class="stk-sku mr-2">{{ $sticker['sku'] }}</span>
                                        @endif
                                        @if($toggles['show_price'])
                                            <span class="stk-price font-weight-bold">{{ $sticker['currency'] ?? 'kr.' }} {{ number_format($sticker['price'], 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        @else
            {{-- Thermal Continuous Roll: Single Centered Sample Card per Item --}}
            <div class="thermal-roll-wrapper">
                @foreach($labels as $index => $lbl)
                    @php $copies = max(1, (int)($lbl['copies'] ?? 1)); @endphp
                    <div class="thermal-sticker-card mb-3 shadow-sm {{ $preset['css_class'] }}">
                        <div class="sticker-centered-layout">
                            @if($toggles['show_brand'] && !empty($lbl['company']))
                                <div class="stk-company text-center">{{ $lbl['company'] }}</div>
                            @endif

                            <div class="stk-title text-center" title="{{ $lbl['title'] }}">{{ $lbl['title'] }}</div>

                            @if(!empty($lbl['variant_spec']))
                                <div class="stk-variant-tag badge badge-dark px-2 py-0 my-1 font-weight-bold" style="font-size: 11px;">
                                    <i class="fas fa-tag mr-1 text-warning"></i>{{ $lbl['variant_spec'] }}
                                </div>
                            @endif

                            <div class="stk-barcode-box text-center">
                                {!! $lbl['barcode_svg'] !!}
                            </div>

                            @if($toggles['show_barcode_text'])
                                <div class="stk-code text-center">{{ $lbl['barcode'] }}</div>
                            @endif

                            <div class="stk-footer-centered text-center">
                                @if($toggles['show_sku'])
                                    <span class="stk-sku mr-2">{{ $lbl['sku'] }}</span>
                                @endif
                                @if($toggles['show_price'])
                                    <span class="stk-price font-weight-bold">{{ $lbl['currency'] ?? 'kr.' }} {{ number_format($lbl['price'], 2) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="thermal-badge d-flex justify-content-between align-items-center px-2 py-1 bg-light border-top rounded-bottom small text-muted">
                            <span class="badge badge-success text-white" style="font-size: 9px;"><i class="fas fa-check-circle mr-1"></i> Saved Barcode</span>
                            <span class="badge badge-dark px-2 py-0.5 text-white" style="font-size: 9.5px;">Copies: {{ $copies }} pcs</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif

<style>
/* Centered Physical Label Layout */
.label-preview-container {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.sticker-centered-layout {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    width: 100%;
    height: 100%;
}

.thermal-sticker-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 12px 14px;
    margin: 0 auto 16px auto;
    position: relative;
    box-sizing: border-box;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

/* Preset Specific Sizing in Preview */
.label-thermal-50x30 {
    width: 280px;
    min-height: 170px;
}

.label-thermal-38x25 {
    width: 230px;
    min-height: 150px;
}

.stk-company {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    font-weight: 700;
    margin-bottom: 2px;
    width: 100%;
}

.stk-title {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.25;
    margin-bottom: 4px;
    max-height: 32px;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
}

.stk-barcode-box {
    margin: 4px 0;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.stk-barcode-box svg {
    max-width: 96%;
    height: auto;
    margin: 0 auto;
    display: block;
}

.stk-code {
    font-family: "Courier New", Courier, monospace;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    color: #1e293b;
    margin-bottom: 4px;
    width: 100%;
}

.stk-footer-centered {
    font-size: 11px;
    width: 100%;
}

.stk-sku {
    color: #64748b;
    font-family: monospace;
    font-weight: 600;
}

.stk-price {
    color: #047857;
    font-size: 13px;
    font-weight: 800;
}

/* A4 Sheet Preview Layout */
.a4-sheet-page {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding-bottom: 12px;
}

.a4-grid-wrapper {
    display: grid;
    gap: 6px;
    padding: 10px;
}

.a4-grid-wrapper.cols-3 {
    grid-template-columns: repeat(3, 1fr);
}

.a4-grid-wrapper.cols-2 {
    grid-template-columns: repeat(2, 1fr);
}

.a4-grid-wrapper.cols-1 {
    grid-template-columns: repeat(1, 1fr);
}

.a4-sticker-cell {
    border: 1px dashed #cbd5e1;
    border-radius: 4px;
    padding: 8px 6px;
    background: #fafafa;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
