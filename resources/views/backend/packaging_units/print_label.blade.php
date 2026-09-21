<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4x6 Logistics Label - {{ $handlingUnit->hu_code }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background-color: #f1f5f9;
            color: #000;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px 0;
            min-height: 100vh;
        }

        /* 4x6 Inch Label Dimensions: 100mm x 150mm */
        .shipping-label-page {
            width: 100mm;
            min-height: 150mm;
            max-height: 150mm;
            background: #ffffff;
            border: 2px solid #000000;
            padding: 4mm 5mm;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .label-header {
            border-bottom: 2px solid #000;
            padding-bottom: 3mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .shipper-title {
            font-size: 13pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .shipper-sub {
            font-size: 7.5pt;
            color: #333;
            margin-top: 1px;
        }

        .package-type-badge {
            border: 2px solid #000;
            padding: 2px 6px;
            font-size: 9pt;
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
        }

        /* Barcode Section */
        .barcode-section {
            border-bottom: 2px solid #000;
            padding: 3mm 0;
            text-align: center;
        }

        .barcode-svg-container {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .barcode-svg-container svg {
            max-width: 90mm;
            height: 22mm !important;
        }

        .hu-code-text {
            font-family: "Courier New", Courier, monospace;
            font-size: 12pt;
            font-weight: 900;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        /* Meta Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 2px solid #000;
            font-size: 8pt;
        }

        .meta-cell {
            padding: 2mm 3mm;
            border-right: 1px solid #000;
        }

        .meta-cell:last-child {
            border-right: none;
        }

        .meta-label {
            font-size: 6.5pt;
            text-transform: uppercase;
            color: #555;
            font-weight: bold;
            display: block;
        }

        .meta-val {
            font-size: 9pt;
            font-weight: 800;
        }

        /* Contents Table */
        .contents-section {
            flex-grow: 1;
            padding: 2mm 0;
            overflow: hidden;
        }

        .contents-title {
            font-size: 7pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1.5mm;
        }

        .contents-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        .contents-table th {
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 1.5px 2px;
            font-size: 6.5pt;
            text-transform: uppercase;
        }

        .contents-table td {
            padding: 1.5px 2px;
            border-bottom: 1px dotted #ccc;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Footer */
        .label-footer {
            border-top: 2px solid #000;
            padding-top: 2mm;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 7.5pt;
        }

        .total-qty-box {
            border: 2px solid #000;
            padding: 2px 8px;
            font-size: 13pt;
            font-weight: 900;
            text-align: right;
        }

        /* Action Toolbar */
        .no-print-toolbar {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            gap: 10px;
            z-index: 9999;
        }

        .btn-action {
            padding: 8px 16px;
            font-weight: bold;
            font-size: 13px;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
        }

        .btn-close {
            background: #e2e8f0;
            color: #334155;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .shipping-label-page {
                box-shadow: none !important;
                border: 2px solid #000 !important;
                width: 100mm !important;
                height: 150mm !important;
                max-height: 150mm !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }

            @page {
                size: 100mm 150mm;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <button onclick="window.print()" class="btn-action btn-print">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Print 4×6 Label
        </button>
        <button onclick="window.close()" class="btn-action btn-close">Close</button>
    </div>

    <div class="shipping-label-page">
        {{-- Header --}}
        <div class="label-header">
            <div>
                <div class="shipper-title">B2B VIKING LOGISTICS</div>
                <div class="shipper-sub">Copenhagen Tourist Point • Central Hub</div>
            </div>
            <div class="package-type-badge">
                {{ $handlingUnit->packagingType?->name ?? 'CONTAINER' }}<br>
                <span style="font-size: 7.5pt; font-weight: normal;">LEVEL {{ $handlingUnit->packagingType?->level_order ?? 1 }}</span>
            </div>
        </div>

        {{-- Main Handling Unit Barcode & 2D QR Code (Hybrid GS1 Logistics) --}}
        <div class="barcode-section" style="display: flex; align-items: center; justify-content: space-between; padding: 2mm 3mm;">
            <div style="flex: 1; min-width: 0; text-align: center; padding-right: 3mm;">
                <div class="barcode-svg-container" style="height: 18mm;">
                    {!! $barcodeSvg !!}
                </div>
                <div class="hu-code-text" style="font-size: 11pt;">
                    (00) {{ $handlingUnit->hu_code }}
                </div>
            </div>
            @php
                $huScanUrl = route('public.barcode.scan', $handlingUnit->hu_code);
                $huQrSvg = app(\App\Services\Barcode\NativeBarcodeGenerator::class)->getQrCodeSvg($huScanUrl, 68, 0);
            @endphp
            <div style="width: 22mm; text-align: center; flex-shrink: 0; border-left: 1px dashed #000; padding-left: 2mm;" title="Smartphone Scan Manifest">
                <div style="width: 18mm; height: 18mm; margin: 0 auto;">
                    {!! $huQrSvg !!}
                </div>
                <div style="font-size: 5.5pt; font-weight: bold; text-transform: uppercase; margin-top: 1px; letter-spacing: 0.5px;">
                    Scan Manifest
                </div>
            </div>
        </div>

        {{-- Meta Grid --}}
        <div class="meta-grid">
            <div class="meta-cell">
                <span class="meta-label">GS1 GPC Classification</span>
                <span class="meta-val">
                    @php
                        $firstCategory = $handlingUnit->items->first()?->product?->category;
                    @endphp
                    {{ $firstCategory?->gpc_code ?? '10000000' }}
                    <span style="font-size: 7pt; font-weight: normal; display: block;">{{ $firstCategory?->name ?? 'General Merchandise' }}</span>
                </span>
            </div>
            <div class="meta-cell">
                <span class="meta-label">Batch / Lot Number</span>
                <span class="meta-val">{{ $handlingUnit->batch_no ?: 'STANDARD-LOT' }}</span>
            </div>
        </div>

        <div class="meta-grid" style="border-top: none;">
            <div class="meta-cell">
                <span class="meta-label">Parent Container</span>
                <span class="meta-val" style="font-size: 7.5pt;">
                    {{ $handlingUnit->parentUnit ? $handlingUnit->parentUnit->hu_code : 'ROOT / TOP-TIER' }}
                </span>
            </div>
            <div class="meta-cell">
                <span class="meta-label">Gross Weight</span>
                <span class="meta-val">
                    {{ $handlingUnit->gross_weight ? number_format($handlingUnit->gross_weight, 3).' KG' : 'N/A' }}
                </span>
            </div>
        </div>

        {{-- Contents Table (Up to 8 lines) --}}
        <div class="contents-section">
            <div class="contents-title">Container Contents / Packing Manifest:</div>
            <table class="contents-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Item / Description</th>
                        <th style="width: 30%;">SKU / Barcode</th>
                        <th style="width: 20%; text-align: right;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $displayContents = array_slice($flattenedContents, 0, 7);
                        $remainingCount = count($flattenedContents) - count($displayContents);
                    @endphp
                    @foreach ($displayContents as $row)
                        <tr>
                            <td>
                                <strong>{{ Str::limit($row['product_name'], 22) }}</strong>
                                @if (!empty($row['variant_name']))
                                    <span style="font-size: 6.5pt; color: #444;">({{ $row['variant_name'] }})</span>
                                @endif
                            </td>
                            <td style="font-family: monospace; font-size: 7pt;">
                                {{ Str::limit($row['sku'] ?: $row['handling_unit_code'], 16) }}
                            </td>
                            <td style="text-align: right; font-weight: bold;">
                                {{ $row['quantity'] }} pcs
                            </td>
                        </tr>
                    @endforeach
                    @if ($remainingCount > 0)
                        <tr>
                            <td colspan="3" style="text-align: center; font-style: italic; color: #555; padding-top: 2px;">
                                + {{ $remainingCount }} more item line(s) in this package...
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="label-footer">
            <div>
                <div><strong>Packed Date:</strong> {{ $handlingUnit->packed_at ? $handlingUnit->packed_at->format('Y-m-d H:i') : date('Y-m-d H:i') }}</div>
                <div><strong>Operator:</strong> {{ $handlingUnit->packer?->name ?? 'System' }} | <strong>Status:</strong> {{ strtoupper($handlingUnit->status) }}</div>
            </div>
            <div class="total-qty-box">
                <div style="font-size: 6.5pt; text-transform: uppercase; letter-spacing: 0.5px;">Total Quantity</div>
                {{ number_format($handlingUnit->total_quantity) }} <span style="font-size: 8pt; font-weight: normal;">PCS</span>
            </div>
        </div>
    </div>

</body>
</html>
