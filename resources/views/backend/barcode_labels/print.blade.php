<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcode Labels — {{ $preset['name'] }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f1f5f9;
            color: #000;
        }

        /* Non-Printable Top Control Bar */
        .print-control-bar {
            background: #0f172a;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .print-control-bar h4 {
            font-size: 15px;
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 9px 20px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(37,99,235,0.3);
        }

        .btn-print:hover { background: #1d4ed8; }

        .btn-close-win {
            background: #334155;
            color: #fff;
            border: none;
            padding: 9px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            margin-left: 8px;
        }

        .btn-close-win:hover { background: #475569; }

        .print-wrapper {
            padding: 24px;
            display: flex;
            justify-content: center;
        }

        /* Common Centered Typography */
        .stk-company { text-align: center; text-transform: uppercase; font-weight: bold; width: 100%; }
        .stk-title   { text-align: center; font-weight: 700; width: 100%; }
        .stk-variant-badge { text-align: center; font-weight: 800; border: 1pt solid #000; padding: 1px 4px; display: inline-block; margin: 2px 0; border-radius: 2px; }
        .stk-barcode-box { width: 100%; display: flex; justify-content: center; align-items: center; margin: 0 auto; text-align: center; }
        .stk-barcode-box svg { margin: 0 auto; display: block; }
        .stk-code    { text-align: center; font-family: monospace; font-weight: 800; width: 100%; }
        .stk-footer-centered { text-align: center; width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; }

        /* Media Print Direct Styling */
        @media print {
            .print-control-bar { display: none !important; }
            body, .print-wrapper {
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }

        /* -------------------------------------------------------------
           1. THERMAL ROLL PRESETS (Centered Output)
           ------------------------------------------------------------- */
        @if($presetKey === 'thermal_50x30')
            @page {
                size: 50mm 30mm;
                margin: 0mm;
            }
            .thermal-page {
                width: 48mm;
                height: 28mm;
                padding: 1mm 1.5mm;
                margin: 1mm auto;
                page-break-after: always;
                page-break-inside: avoid;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                overflow: hidden;
            }
            .stk-company { font-size: 6.5pt; line-height: 1; letter-spacing: 0.5px; }
            .stk-title   { font-size: 6.5pt; max-height: 15pt; overflow: hidden; line-height: 1.1; margin: 1px 0; }
            .stk-variant-badge { font-size: 6pt; }
            .stk-barcode-box { height: 11mm; }
            .stk-barcode-box svg { height: 100%; width: 92%; }
            .stk-code    { font-size: 6.5pt; letter-spacing: 1px; }
            .stk-footer-centered { font-size: 7pt; border-top: 0.5pt solid #000; padding-top: 1px; margin-top: 1px; }
            .stk-price   { font-weight: 900; font-size: 7.5pt; }

        @elseif($presetKey === 'thermal_38x25')
            @page {
                size: 38mm 25mm;
                margin: 0mm;
            }
            .thermal-page {
                width: 36mm;
                height: 23mm;
                padding: 0.8mm;
                margin: 1mm auto;
                page-break-after: always;
                page-break-inside: avoid;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                overflow: hidden;
            }
            .stk-company { font-size: 5.5pt; }
            .stk-title   { font-size: 5.5pt; max-height: 11pt; overflow: hidden; line-height: 1; margin: 1px 0; }
            .stk-variant-badge { font-size: 5pt; }
            .stk-barcode-box { height: 9mm; }
            .stk-barcode-box svg { height: 100%; width: 92%; }
            .stk-code    { font-size: 5.5pt; letter-spacing: 0.5px; }
            .stk-footer-centered { font-size: 6pt; border-top: 0.5pt solid #000; padding-top: 1px; }
            .stk-price   { font-weight: 900; font-size: 6.5pt; }

        @elseif($presetKey === 'thermal_100x150')
            @page {
                size: 100mm 150mm;
                margin: 0mm;
            }
            .thermal-page {
                width: 96mm;
                height: 144mm;
                padding: 4mm;
                margin: 2mm auto;
                page-break-after: always;
                page-break-inside: avoid;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                overflow: hidden;
            }
            .stk-company { font-size: 10pt; letter-spacing: 1px; }
            .stk-title   { font-size: 14pt; max-height: 32pt; overflow: hidden; margin: 3px 0; }
            .stk-variant-badge { font-size: 10pt; padding: 2px 8px; }
            .stk-barcode-box { height: 28mm; }
            .stk-barcode-box svg { height: 100%; width: 90%; }
            .stk-code    { font-size: 12pt; letter-spacing: 2px; }
            .stk-footer-centered { font-size: 10pt; border-top: 1pt solid #000; padding-top: 3px; }

        /* -------------------------------------------------------------
           2. A4 SHEET PRESETS
           ------------------------------------------------------------- */
        @elseif($presetKey === 'a4_3x8_sheet')
            @page {
                size: A4 portrait;
                margin-top: 10.7mm;
                margin-bottom: 10.7mm;
                margin-left: 7.2mm;
                margin-right: 7.2mm;
            }
            .a4-print-page {
                width: 195.6mm;
                height: 275.6mm;
                display: grid;
                grid-template-columns: repeat(3, 63.5mm);
                grid-auto-rows: 33.9mm;
                gap: 0mm 2.5mm;
                page-break-after: always;
                page-break-inside: avoid;
            }
            .a4-print-cell {
                width: 63.5mm;
                height: 33.9mm;
                padding: 1.5mm;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                overflow: hidden;
            }
            .stk-company { font-size: 7pt; }
            .stk-title   { font-size: 7.5pt; max-height: 14pt; overflow: hidden; margin: 1px 0; }
            .stk-variant-badge { font-size: 6.5pt; }
            .stk-barcode-box { height: 13mm; }
            .stk-barcode-box svg { height: 100%; width: 90%; }
            .stk-code    { font-size: 7pt; letter-spacing: 1px; }
            .stk-footer-centered { font-size: 7.5pt; border-top: 0.5pt solid #000; padding-top: 1px; }
            .stk-price   { font-weight: 900; font-size: 8pt; }

        @elseif($presetKey === 'a4_2x7_sheet')
            @page {
                size: A4 portrait;
                margin-top: 15mm;
                margin-bottom: 15mm;
                margin-left: 5mm;
                margin-right: 5mm;
            }
            .a4-print-page {
                width: 200mm;
                height: 267mm;
                display: grid;
                grid-template-columns: repeat(2, 99.1mm);
                grid-auto-rows: 38.1mm;
                gap: 0mm 3mm;
                page-break-after: always;
                page-break-inside: avoid;
            }
            .a4-print-cell {
                width: 99.1mm;
                height: 38.1mm;
                padding: 2mm;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                overflow: hidden;
            }
            .stk-company { font-size: 8pt; }
            .stk-title   { font-size: 8.5pt; max-height: 16pt; overflow: hidden; margin: 1px 0; }
            .stk-variant-badge { font-size: 7.5pt; }
            .stk-barcode-box { height: 15mm; }
            .stk-barcode-box svg { height: 100%; width: 90%; }
            .stk-code    { font-size: 8pt; letter-spacing: 1px; }
            .stk-footer-centered { font-size: 8.5pt; border-top: 0.5pt solid #000; padding-top: 1px; }
            .stk-price   { font-weight: 900; font-size: 9.5pt; }

        @elseif($presetKey === 'a4_master_2x1')
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            .a4-print-page {
                width: 190mm;
                height: 277mm;
                display: flex;
                flex-direction: column;
                justify-content: space-around;
                page-break-after: always;
                page-break-inside: avoid;
            }
            .a4-print-cell {
                width: 190mm;
                height: 132mm;
                border: 1pt dashed #000;
                padding: 6mm;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                overflow: hidden;
            }
            .stk-company { font-size: 11pt; }
            .stk-title   { font-size: 15pt; max-height: 35pt; overflow: hidden; margin: 4px 0; }
            .stk-variant-badge { font-size: 11pt; }
            .stk-barcode-box { height: 30mm; }
            .stk-barcode-box svg { height: 100%; width: 85%; }
            .stk-code    { font-size: 13pt; letter-spacing: 2px; }
            .stk-footer-centered { font-size: 11pt; border-top: 1pt solid #000; padding-top: 4px; }
        @endif
    </style>
</head>
<body>

    <!-- Non-Printable Action Bar -->
    <div class="print-control-bar">
        <div>
            <h4>🖨️ {{ $preset['name'] }} — Label Print</h4>
            <span style="font-size: 12px; color: #94a3b8;">All barcode lines and details are centered for thermal rolls and A4 sticker sheets.</span>
        </div>
        <div>
            <button type="button" class="btn-print" onclick="window.print();">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                Print Now (Ctrl + P)
            </button>
            <button type="button" class="btn-close-win" onclick="window.close();">Close</button>
        </div>
    </div>

    <div class="print-wrapper">
        @if(str_starts_with($presetKey, 'a4_'))
            {{-- A4 Sheet Grid Layout --}}
            @php
                $cols = $preset['columns'] ?? 3;
                $rows = $preset['rows'] ?? 8;
                $perPage = $cols * $rows;

                $allStickers = [];
                foreach ($labels as $lbl) {
                    $copies = max(1, (int)($lbl['copies'] ?? 1));
                    for ($c = 0; $c < $copies; $c++) {
                        $allStickers[] = $lbl;
                    }
                }
                $pages = array_chunk($allStickers, $perPage);
            @endphp

            @foreach($pages as $pageStickers)
                <div class="a4-print-page">
                    @foreach($pageStickers as $sticker)
                        <div class="a4-print-cell">
                            @if($toggles['show_brand'] && !empty($sticker['company']))
                                <div class="stk-company">{{ $sticker['company'] }}</div>
                            @endif

                            @if($toggles['show_name'])
                                <div class="stk-title">{{ $sticker['title'] }}</div>
                            @endif

                            @if(!empty($sticker['variant_spec']))
                                <div class="stk-variant-badge">{{ $sticker['variant_spec'] }}</div>
                            @endif

                            <div class="stk-barcode-box">
                                {!! $sticker['barcode_svg'] !!}
                            </div>

                            @if($toggles['show_barcode_text'])
                                <div class="stk-code">{{ $sticker['barcode'] }}</div>
                            @endif

                            <div class="stk-footer-centered">
                                @if($toggles['show_sku'])
                                    <span class="stk-sku">{{ $sticker['sku'] }}</span>
                                @endif
                                @if($toggles['show_price'])
                                    <span class="stk-price">{{ $sticker['currency'] ?? 'kr.' }} {{ number_format($sticker['price'], 2) }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

        @else
            {{-- Thermal Continuous Roll: Centered Stickers --}}
            <div>
                @foreach($labels as $lbl)
                    @php $copies = max(1, (int)($lbl['copies'] ?? 1)); @endphp
                    @for($c = 0; $c < $copies; $c++)
                        <div class="thermal-page">
                            @if($toggles['show_brand'] && !empty($lbl['company']))
                                <div class="stk-company">{{ $lbl['company'] }}</div>
                            @endif

                            @if($toggles['show_name'])
                                <div class="stk-title">{{ $lbl['title'] }}</div>
                            @endif

                            @if(!empty($lbl['variant_spec']))
                                <div class="stk-variant-badge">{{ $lbl['variant_spec'] }}</div>
                            @endif

                            <div class="stk-barcode-box">
                                {!! $lbl['barcode_svg'] !!}
                            </div>

                            @if($toggles['show_barcode_text'])
                                <div class="stk-code">{{ $lbl['barcode'] }}</div>
                            @endif

                            <div class="stk-footer-centered">
                                @if($toggles['show_sku'])
                                    <span class="stk-sku">{{ $lbl['sku'] }}</span>
                                @endif
                                @if($toggles['show_price'])
                                    <span class="stk-price">{{ $lbl['currency'] ?? 'kr.' }} {{ number_format($lbl['price'], 2) }}</span>
                                @endif
                            </div>
                        </div>
                    @endfor
                @endforeach
            </div>
        @endif
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
