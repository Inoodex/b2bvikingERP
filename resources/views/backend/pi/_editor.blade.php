@php
    $piInvoiceUrl = $piInvoiceUrl ?? null;
    $piType = $piInfo['pi_type'] ?? 'simple';
    $rows = $piInfo['rows'] ?? [];
    $row = $rows[0] ?? [
        'ordered_qty' => null,
        'ctn_no' => null,
        'ctn_size' => null,
        'pcs_per_ctn' => null,
        'ctn_qty' => null,
        'total_pcs' => null,
        'nw_kg' => null,
        'gw_kg' => null,
        'note' => null,
    ];
    $blocks = $piInfo['blocks'] ?? [];
    $editorId = 'pi-mode-' . uniqid();
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header border-bottom bg-white d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
        <div>
            <h4 class="mb-1"><i class="fas fa-file-signature text-primary mr-2"></i>{{ $title }}</h4>
            <div class="text-muted small">{{ $subtitle }}</div>
        </div>
        @if($piInvoiceUrl)
            <a href="{{ $piInvoiceUrl }}" target="_blank" class="btn btn-success btn-sm">
                <i class="fas fa-external-link-alt mr-1"></i> Open PI Invoice
            </a>
        @endif
    </div>
    <div class="card-body">
        <form action="{{ $formAction }}" method="POST">
            @csrf

            @if($errors->any())
                <div class="alert alert-danger">
                    <div class="font-weight-bold mb-1">Please fix the highlighted PI info fields.</div>
                    <ul class="mb-0 pl-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label font-weight-bold small text-muted text-uppercase">Order Qty</label>
                    <input type="number" class="form-control" value="{{ number_format($piInfo['order_qty_total'] ?? 0, 0, '.', '') }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold small text-muted text-uppercase">Shipment Qty</label>
                    <input type="number" min="0" name="shipment_qty" class="form-control @error('shipment_qty') is-invalid @enderror" value="{{ old('shipment_qty', $piInfo['shipment_qty'] ?? 0) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold small text-muted text-uppercase">Shipment Date</label>
                    <input type="date" name="shipment_date" class="form-control @error('shipment_date') is-invalid @enderror" value="{{ old('shipment_date', $piInfo['shipment_date'] ?? '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold small text-muted text-uppercase">PI Type</label>
                    <select name="pi_type" class="form-control" id="{{ $editorId }}-select">
                        <option value="simple" {{ old('pi_type', $piType) === 'simple' ? 'selected' : '' }}>Simple (Default)</option>
                        <option value="advanced" {{ old('pi_type', $piType) === 'advanced' ? 'selected' : '' }}>Advanced Matrix</option>
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <label class="form-label font-weight-bold small text-muted text-uppercase">Packing Note</label>
                    <input type="text" name="packing_note" class="form-control @error('packing_note') is-invalid @enderror" value="{{ old('packing_note', $piInfo['packing_note'] ?? '') }}" placeholder="Optional note">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="small text-muted text-uppercase mb-1">Entered Ord. Qty</div>
                        <div class="h5 mb-0">{{ number_format($piTotals['ordered_qty'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="small text-muted text-uppercase mb-1">Saved CTN Qty</div>
                        <div class="h5 mb-0">{{ number_format($piTotals['ctn_qty'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="small text-muted text-uppercase mb-1">Saved Total PCS</div>
                        <div class="h5 mb-0">{{ number_format($piTotals['total_pcs'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="small text-muted text-uppercase mb-1">Weight Summary</div>
                        <div class="h6 mb-0">{{ number_format($piTotals['nw_kg'] ?? 0, 2) }} / {{ number_format($piTotals['gw_kg'] ?? 0, 2) }} KG</div>
                    </div>
                </div>
            </div>

            <div id="{{ $editorId }}-simple" style="{{ old('pi_type', $piType) === 'advanced' ? 'display:none;' : '' }}">
                <div class="border rounded-lg mb-4 overflow-hidden" style="background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                        <div>
                            <div class="small text-muted text-uppercase mb-1">Simple PI</div>
                            <div class="font-weight-bold text-dark">Single Manual CTN Entry For This Order/Request</div>
                        </div>
                        <div class="small text-muted">Use this mode when one packing row is enough for the whole order.</div>
                    </div>

                    <div class="p-4">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label class="small text-muted text-uppercase font-weight-bold">Ord. Qty</label>
                                <input type="number" min="0" name="pi_rows[0][ordered_qty]" class="form-control" value="{{ old('pi_rows.0.ordered_qty', $row['ordered_qty'] ?? ($piInfo['order_qty_total'] ?? '')) }}" placeholder="Order qty">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label class="small text-muted text-uppercase font-weight-bold">CTN No</label>
                                <input type="text" name="pi_rows[0][ctn_no]" class="form-control" value="{{ old('pi_rows.0.ctn_no', $row['ctn_no'] ?? '') }}" placeholder="e.g. 1-3">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label class="small text-muted text-uppercase font-weight-bold">CTN Qty</label>
                                <input type="number" min="0" name="pi_rows[0][ctn_qty]" class="form-control text-center" value="{{ old('pi_rows.0.ctn_qty', $row['ctn_qty'] ?? '') }}" placeholder="0">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label class="small text-muted text-uppercase font-weight-bold">CTN Size</label>
                                <input type="text" name="pi_rows[0][ctn_size]" class="form-control" value="{{ old('pi_rows.0.ctn_size', $row['ctn_size'] ?? '') }}" placeholder="e.g. 20x30x10 cm">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label class="small text-muted text-uppercase font-weight-bold">PCS / CTN</label>
                                <input type="number" min="0" name="pi_rows[0][pcs_per_ctn]" class="form-control" value="{{ old('pi_rows.0.pcs_per_ctn', $row['pcs_per_ctn'] ?? '') }}" placeholder="PCS per CTN">
                            </div>
                            
                        {{-- </div>

                        <div class="row"> --}}
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label class="small text-muted text-uppercase font-weight-bold">Total PCS</label>
                                <input type="number" min="0" name="pi_rows[0][total_pcs]" class="form-control" value="{{ old('pi_rows.0.total_pcs', $row['total_pcs'] ?? '') }}" placeholder="Total pcs">
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label class="small text-muted text-uppercase font-weight-bold">N.W (KG)</label>
                                <input type="number" min="0" step="0.01" name="pi_rows[0][nw_kg]" class="form-control" value="{{ old('pi_rows.0.nw_kg', $row['nw_kg'] ?? '') }}" placeholder="Net weight">
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label class="small text-muted text-uppercase font-weight-bold">G.W (KG)</label>
                                <input type="number" min="0" step="0.01" name="pi_rows[0][gw_kg]" class="form-control" value="{{ old('pi_rows.0.gw_kg', $row['gw_kg'] ?? '') }}" placeholder="Gross weight">
                            </div>
                            <div class="col-lg-6 col-md-12 mb-3">
                                <label class="small text-muted text-uppercase font-weight-bold">Remarks</label>
                                <input type="text" name="pi_rows[0][note]" class="form-control" value="{{ old('pi_rows.0.note', $row['note'] ?? '') }}" placeholder="Optional carton or shipping remark">
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-2 small text-muted">
                            Fill `Ord. Qty`, `CTN Qty`, `PCS / CTN`, and `Total PCS` clearly. This layout is best when the whole order can be described by one carton summary row.
                        </div>
                    </div>
                </div>
            </div>

            <div id="{{ $editorId }}-advanced" style="{{ old('pi_type', $piType) === 'advanced' ? '' : 'display:none;' }}">
                <div class="alert alert-light border mb-4">
                    <strong>Advanced Matrix Mode:</strong> this follows the image-style PI layout. Each product block gets its own carton matrix with size columns, CTN numbers, PCS, and weights.
                </div>

                @foreach($blocks as $blockIndex => $block)
                    @php
                        $variantHeaders = $block['variant_headers'] ?? [];
                        $variantCount = count($variantHeaders);
                        $imagePath = trim((string) ($block['image'] ?? ''));
                        $imageUrl = null;
                        if ($imagePath !== '') {
                            if (\Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://', '//', 'data:'])) {
                                $imageUrl = $imagePath;
                            } else {
                                $normalizedImagePath = ltrim(str_replace('storage/', '', $imagePath), '/');
                                $publicCandidate = public_path(ltrim($imagePath, '/'));
                                $storageCandidate = storage_path('app/public/' . $normalizedImagePath);
                                if (is_file($publicCandidate)) {
                                    $imageUrl = asset(ltrim($imagePath, '/'));
                                } elseif (is_file($storageCandidate)) {
                                    $imageUrl = asset('storage/' . $normalizedImagePath);
                                } else {
                                    $imageUrl = asset('storage/' . $normalizedImagePath);
                                }
                            }
                        }
                    @endphp
                    <div class="border rounded-lg mb-4 overflow-hidden" style="background: #fff;">
                        <div class="px-4 py-3 border-bottom" style="background: #f8fafc;">
                            <div class="d-flex align-items-center flex-wrap" style="gap: 16px;">
                                <div style="width: 96px; height: 96px; border: 1px solid #dbe3ef; border-radius: 10px; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="" style="max-width: 88px; max-height: 88px; object-fit: contain;">
                                    @else
                                        <span class="small text-muted">No image</span>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small text-muted text-uppercase">Product Block</div>
                                    <div class="font-weight-bold text-dark" style="font-size: 16px;">{{ $block['title'] ?? 'Product Block' }}</div>
                                    <div class="small text-muted">Color / Group: {{ $block['color_label'] ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <input type="hidden" name="advanced_blocks[{{ $blockIndex }}][block_key]" value="{{ $block['block_key'] ?? '' }}">
                            <input type="hidden" name="advanced_blocks[{{ $blockIndex }}][product_id]" value="{{ $block['product_id'] ?? '' }}">
                            <input type="hidden" name="advanced_blocks[{{ $blockIndex }}][image]" value="{{ $block['image'] ?? '' }}">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="small text-muted text-uppercase font-weight-bold">Block Title</label>
                                    <input type="text" name="advanced_blocks[{{ $blockIndex }}][title]" class="form-control" value="{{ old("advanced_blocks.$blockIndex.title", $block['title'] ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted text-uppercase font-weight-bold">Color / Group</label>
                                    <input type="text" name="advanced_blocks[{{ $blockIndex }}][color_label]" class="form-control" value="{{ old("advanced_blocks.$blockIndex.color_label", $block['color_label'] ?? '') }}">
                                </div>
                                <div class="col-md-6 py-2">
                                    <label class="small text-muted text-uppercase font-weight-bold">CTN Measurement</label>
                                    <input type="text" name="advanced_blocks[{{ $blockIndex }}][ctn_size]" class="form-control" value="{{ old("advanced_blocks.$blockIndex.ctn_size", $block['ctn_size'] ?? '') }}" placeholder="Optional">
                                </div>
                                <div class="col-md-6 py-2">
                                    <label class="small text-muted text-uppercase font-weight-bold">Variant Headers</label>
                                    <input type="text" name="advanced_blocks[{{ $blockIndex }}][variant_headers_csv]" class="form-control" value="{{ old("advanced_blocks.$blockIndex.variant_headers_csv", $block['variant_headers_csv'] ?? '') }}" placeholder="e.g. Black L, Black XL, Blue L">
                                    <small class="text-muted">This follows the order variant labels. For color-only items, use values like `Black, Blue`.</small>
                                </div>
                            </div>
                            @if($variantCount === 0)
                                <div class="alert alert-secondary py-2 px-3">
                                    No variant headers were detected for this product. Add them manually if this item needs variant-wise PI entry.
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" style="min-width: {{ $variantCount > 0 ? (string) max(1280, 620 + ($variantCount * 150)) . 'px' : '860px' }};">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" style="min-width: 112px;">CTN Qty</th>
                                            <th class="text-center" style="min-width: 132px;">CTN No.</th>
                                            @foreach($variantHeaders as $variantIndex => $header)
                                            <th class="text-center" style="min-width: 128px;">{{ $header }}</th>
                                            @endforeach
                                            <th class="text-center" style="min-width: 112px;">PCS</th>
                                            <th class="text-center" style="min-width: 132px;">Total PCS</th>
                                            <th class="text-center" style="min-width: 124px;">N.W (KG)</th>
                                            <th class="text-center" style="min-width: 124px;">G.W (KG)</th>
                                            <th class="text-center" style="width: 96px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody data-matrix-rows data-block-index="{{ $blockIndex }}" data-next-index="{{ count($block['rows'] ?? []) }}">
                                        @foreach(($block['rows'] ?? []) as $rowIndex => $matrixRow)
                                            <tr data-matrix-row>
                                                <td><input type="number" min="0" name="advanced_blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][ctn_qty]" class="form-control text-center" style="min-width: 96px;" value="{{ old("advanced_blocks.$blockIndex.rows.$rowIndex.ctn_qty", $matrixRow['ctn_qty'] ?? '') }}"></td>
                                                <td><input type="text" name="advanced_blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][ctn_no]" class="form-control text-center" style="min-width: 116px;" value="{{ old("advanced_blocks.$blockIndex.rows.$rowIndex.ctn_no", $matrixRow['ctn_no'] ?? '') }}"></td>
                                                @foreach($variantHeaders as $variantIndex => $header)
                                                    <td><input type="number" min="0" name="advanced_blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][variants][{{ $variantIndex }}]" class="form-control text-center" style="min-width: 96px;" value="{{ old("advanced_blocks.$blockIndex.rows.$rowIndex.variants.$variantIndex", $matrixRow['variants'][$variantIndex] ?? '') }}"></td>
                                                @endforeach
                                                <td><input type="number" min="0" name="advanced_blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][pcs]" class="form-control text-center" style="min-width: 96px;" value="{{ old("advanced_blocks.$blockIndex.rows.$rowIndex.pcs", $matrixRow['pcs'] ?? '') }}"></td>
                                                <td><input type="number" min="0" name="advanced_blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][total_pcs]" class="form-control text-center" style="min-width: 108px;" value="{{ old("advanced_blocks.$blockIndex.rows.$rowIndex.total_pcs", $matrixRow['total_pcs'] ?? '') }}"></td>
                                                <td><input type="number" min="0" step="0.01" name="advanced_blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][nw_kg]" class="form-control text-center" style="min-width: 108px;" value="{{ old("advanced_blocks.$blockIndex.rows.$rowIndex.nw_kg", $matrixRow['nw_kg'] ?? '') }}"></td>
                                                <td><input type="number" min="0" step="0.01" name="advanced_blocks[{{ $blockIndex }}][rows][{{ $rowIndex }}][gw_kg]" class="form-control text-center" style="min-width: 108px;" value="{{ old("advanced_blocks.$blockIndex.rows.$rowIndex.gw_kg", $matrixRow['gw_kg'] ?? '') }}"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" data-remove-matrix-row>
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap" style="gap: 8px;">
                                <div class="small text-muted">The matrix follows the actual order variants. If a product was ordered as `Black L`, `Black XL`, `Blue L`, those become the matrix columns.</div>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-add-matrix-row data-block-index="{{ $blockIndex }}" data-variant-count="{{ $variantCount }}">
                                    <i class="fas fa-plus mr-1"></i> Add Matrix Row
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-save mr-1"></i> Save PI Info
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const selectEl = document.getElementById('{{ $editorId }}-select');
        const simpleEl = document.getElementById('{{ $editorId }}-simple');
        const advancedEl = document.getElementById('{{ $editorId }}-advanced');
        if (!selectEl || !simpleEl || !advancedEl) {
            return;
        }

        const syncMode = () => {
            const isAdvanced = selectEl.value === 'advanced';
            simpleEl.style.display = isAdvanced ? 'none' : '';
            advancedEl.style.display = isAdvanced ? '' : 'none';
        };

        selectEl.addEventListener('change', syncMode);
        syncMode();

        document.querySelectorAll('[data-add-matrix-row]').forEach((button) => {
            button.addEventListener('click', () => {
                const blockIndex = button.getAttribute('data-block-index');
                const variantCount = parseInt(button.getAttribute('data-variant-count') || '0', 10);
                const tbody = document.querySelector('[data-matrix-rows][data-block-index="' + blockIndex + '"]');
                if (!tbody) {
                    return;
                }

                const rowIndex = parseInt(tbody.getAttribute('data-next-index') || '0', 10);
                const tr = document.createElement('tr');
                let html = '';

                tr.setAttribute('data-matrix-row', '');
                html += '<td><input type="number" min="0" name="advanced_blocks[' + blockIndex + '][rows][' + rowIndex + '][ctn_qty]" class="form-control text-center" style="min-width: 96px;"></td>';
                html += '<td><input type="text" name="advanced_blocks[' + blockIndex + '][rows][' + rowIndex + '][ctn_no]" class="form-control text-center" style="min-width: 116px;"></td>';

                for (let variantIndex = 0; variantIndex < variantCount; variantIndex += 1) {
                    html += '<td><input type="number" min="0" name="advanced_blocks[' + blockIndex + '][rows][' + rowIndex + '][variants][' + variantIndex + ']" class="form-control text-center" style="min-width: 96px;"></td>';
                }

                html += '<td><input type="number" min="0" name="advanced_blocks[' + blockIndex + '][rows][' + rowIndex + '][pcs]" class="form-control text-center" style="min-width: 96px;"></td>';
                html += '<td><input type="number" min="0" name="advanced_blocks[' + blockIndex + '][rows][' + rowIndex + '][total_pcs]" class="form-control text-center" style="min-width: 108px;"></td>';
                html += '<td><input type="number" min="0" step="0.01" name="advanced_blocks[' + blockIndex + '][rows][' + rowIndex + '][nw_kg]" class="form-control text-center" style="min-width: 108px;"></td>';
                html += '<td><input type="number" min="0" step="0.01" name="advanced_blocks[' + blockIndex + '][rows][' + rowIndex + '][gw_kg]" class="form-control text-center" style="min-width: 108px;"></td>';
                html += '<td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" data-remove-matrix-row><i class="fas fa-trash-alt"></i></button></td>';

                tr.innerHTML = html;
                tbody.appendChild(tr);
                tbody.setAttribute('data-next-index', String(rowIndex + 1));
            });
        });

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-matrix-row]');
            if (!button) {
                return;
            }

            const row = button.closest('[data-matrix-row]');
            if (!row) {
                return;
            }

            row.remove();
        });
    })();
</script>
@endpush
