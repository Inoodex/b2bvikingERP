@extends('backend.layouts.master')

@section('title', 'Document Sequences')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-barcode text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Document Sequence Engine</h4>
                        <p class="text-muted mb-0 small">Configure auto-number formats for Sales Quotations, Orders, Invoices, Delivery Notes & Credit Notes</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden; background: #ffffff;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <h6 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-list-ol mr-2 text-primary"></i> Active Document Number Generators
                                </h6>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <div class="table-responsive">
                                {{ $dataTable->table(['class' => 'table table-hover align-middle mb-0 w-100', 'id' => 'document-sequence-table']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modals rendered outside table for zero layout clipping --}}
    @foreach($sequences as $seq)
        @php
            $numberStr = str_pad((string)$seq->next_number, $seq->padding, '0', STR_PAD_LEFT);
            $dateStr = ($seq->include_date && !empty($seq->date_format)) ? date($seq->date_format) . '-' : '';
            $sample = ($seq->prefix ?? '') . $dateStr . $numberStr . ($seq->suffix ?? '');
        @endphp
        <div class="modal fade sequence-modal" id="editModal{{ $seq->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $seq->id }}" aria-hidden="true" style="z-index: 99999;" data-seq-id="{{ $seq->id }}">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document" style="max-width: 620px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background: #ffffff;">
                    <form action="{{ route('admin.document-sequences.update', $seq->id) }}" method="POST" id="formSeq{{ $seq->id }}">
                        @csrf
                        @method('PUT')
                        
                        {{-- Modal Header --}}
                        <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #0a0e1a 0%, #161e33 100%); border-bottom: 2px solid rgba(205, 160, 90, 0.4);">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 rounded-circle" style="background: rgba(205, 160, 90, 0.15); color: #e3bd7c;">
                                    <i class="fas fa-barcode" style="font-size: 1.1rem;"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title font-weight-bold mb-0" id="editModalLabel{{ $seq->id }}" style="color: #f8f6f0; font-size: 1.1rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                                        Configure: {{ $seq->model_type }}
                                    </h5>
                                    <small style="color: rgba(226, 220, 205, 0.6);">Switch format style, padding, serial & reset rules</small>
                                </div>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #ffffff; opacity: 0.8; outline: none; font-size: 1.5rem; margin-top: -10px;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="modal-body p-4" style="background: #ffffff; color: #1e293b; max-height: calc(85vh - 130px); overflow-y: auto;">
                            
                            {{-- Live Sample Card --}}
                            <div class="p-3 mb-3 rounded border text-center shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.35) !important;">
                                <div class="d-flex align-items-center justify-content-between mb-1 px-1">
                                    <span class="text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.75px; color: #94a3b8;">
                                        <i class="fas fa-magic text-warning mr-1"></i> Live Number Preview
                                    </span>
                                    <span class="badge {{ $seq->include_date ? 'badge-primary' : 'badge-warning text-dark' }} px-2 py-1 font-weight-bold" style="font-size: 0.7rem; border-radius: 6px;" id="formatBadge{{ $seq->id }}">
                                        {{ $seq->include_date ? 'Date-Based Format' : 'Classic Serial' }}
                                    </span>
                                </div>
                                <div class="font-weight-bold text-warning my-1" style="font-family: 'JetBrains Mono', monospace; font-size: 1.45rem; letter-spacing: 1px;" id="modalPreview{{ $seq->id }}">
                                    {{ $sample }}
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem; color: #cbd5e1 !important;" id="previewHint{{ $seq->id }}">
                                    {{ $seq->include_date ? 'Includes current date format prefix' : 'Continuous increment without date prefix (e.g. SQ-0001 or SQ-01)' }}
                                </small>
                            </div>

                            {{-- Format Style Switcher --}}
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-2" style="font-size: 0.85rem;">
                                    <i class="fas fa-layer-group text-primary mr-1"></i> Choose Format Style:
                                </label>
                                <div class="row no-gutters">
                                    <div class="col-6 pr-1">
                                        <div class="border rounded p-2 text-center preset-btn preset-classic {{ !$seq->include_date ? 'border-primary bg-light shadow-sm' : '' }}" 
                                             data-seq-id="{{ $seq->id }}" data-type="classic" style="cursor: pointer; border-radius: 12px !important; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center justify-content-center mb-1">
                                                <i class="fas fa-hashtag text-info mr-1"></i>
                                                <span class="font-weight-bold text-dark small">Classic Format</span>
                                            </div>
                                            <span class="badge badge-secondary px-2 py-0" style="font-size: 0.72rem; font-family: monospace;">SQ-0001 / SQ-01</span>
                                            <div class="text-muted mt-1" style="font-size: 0.7rem;">No Date / Clean Serial</div>
                                        </div>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <div class="border rounded p-2 text-center preset-btn preset-date {{ $seq->include_date ? 'border-primary bg-light shadow-sm' : '' }}" 
                                             data-seq-id="{{ $seq->id }}" data-type="date" style="cursor: pointer; border-radius: 12px !important; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center justify-content-center mb-1">
                                                <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                                <span class="font-weight-bold text-dark small">Date-Based Format</span>
                                            </div>
                                            <span class="badge badge-primary px-2 py-0" style="font-size: 0.72rem; font-family: monospace;">SQ-{{ date('Ym') }}-0001</span>
                                            <div class="text-muted mt-1" style="font-size: 0.7rem;">Year/Month Audit Trail</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden Checkbox for Include Date (Synchronized with Switcher) --}}
                            <div class="custom-control custom-checkbox d-none">
                                <input type="checkbox" name="include_date" value="1" class="custom-control-input input-include-date" id="incDate{{ $seq->id }}" {{ $seq->include_date ? 'checked' : '' }}>
                                <label class="custom-control-label" for="incDate{{ $seq->id }}">Include Date</label>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 0.85rem;">Prefix</label>
                                    <input type="text" name="prefix" class="form-control form-control-lg input-prefix" value="{{ $seq->prefix }}" required placeholder="e.g. SQ-" style="border-radius: 10px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; color: #0f172a; background: #ffffff;">
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 0.85rem;">Suffix (Optional)</label>
                                    <input type="text" name="suffix" class="form-control form-control-lg input-suffix" value="{{ $seq->suffix }}" placeholder="e.g. -A (optional)" style="border-radius: 10px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; color: #0f172a; background: #ffffff;">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1 d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
                                        <span>Padding Digits</span>
                                        <span class="badge badge-light border text-primary font-weight-bold px-2 py-0" id="paddingDesc{{ $seq->id }}" style="font-size: 0.72rem;">
                                            {{ $seq->padding == 2 ? 'SQ-01 style' : ($seq->padding == 4 ? 'SQ-0001 style' : $seq->padding . ' digits') }}
                                        </span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="padding" class="form-control form-control-lg input-padding" value="{{ $seq->padding }}" min="1" max="10" required style="border-radius: 10px 0 0 10px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; color: #0f172a; background: #ffffff;">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-light border px-2 quick-padding font-weight-bold" data-seq-id="{{ $seq->id }}" data-pad="2" style="font-size: 0.75rem;" title="2 Digits: e.g. 01">2 (01)</button>
                                            <button type="button" class="btn btn-light border px-2 quick-padding font-weight-bold" data-seq-id="{{ $seq->id }}" data-pad="4" style="font-size: 0.75rem; border-radius: 0 10px 10px 0;" title="4 Digits: e.g. 0001">4 (0001)</button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">2 = 01, 3 = 001, 4 = 0001</small>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 0.85rem;">Next Serial Number</label>
                                    <input type="number" name="next_number" class="form-control form-control-lg input-next-number" value="{{ $seq->next_number }}" min="1" required style="border-radius: 10px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; color: #0f172a; background: #ffffff;">
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Starting serial counter</small>
                                </div>
                            </div>

                            {{-- Date Format Options (visible only if Date-Based is selected) --}}
                            <div class="date-format-box mb-3 {{ !$seq->include_date ? 'd-none' : '' }}" id="dateBox{{ $seq->id }}" style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 12px 14px;">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 0.85rem;">
                                    <i class="far fa-calendar mr-1 text-primary"></i> Date Format Pattern
                                </label>
                                <div class="input-group">
                                    <input type="text" name="date_format" class="form-control input-date-format" value="{{ $seq->date_format ?? 'Ym' }}" placeholder="e.g. Ym" style="border-radius: 8px 0 0 8px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem;">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-light border px-2 quick-date" data-seq-id="{{ $seq->id }}" data-format="Ym" style="font-size: 0.75rem;">Ym ({{ date('Ym') }})</button>
                                        <button type="button" class="btn btn-light border px-2 quick-date" data-seq-id="{{ $seq->id }}" data-format="Y" style="font-size: 0.75rem;">Y ({{ date('Y') }})</button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Ym = Year+Month (e.g. {{ date('Ym') }}), Y = Year only (e.g. {{ date('Y') }})</small>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark mb-1 d-flex justify-content-between" style="font-size: 0.85rem;">
                                    <span>Reset Policy</span>
                                    <small class="text-muted font-weight-normal" id="resetPolicyTip{{ $seq->id }}">
                                        {{ !$seq->include_date ? 'Recommended: Never for continuous sequence' : 'Recommended: Yearly for date sequence' }}
                                    </small>
                                </label>
                                <select name="reset_policy" class="form-control form-control-lg select-reset-policy" style="border-radius: 10px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; color: #0f172a; background: #ffffff;">
                                    <option value="never" {{ $seq->reset_policy === 'never' ? 'selected' : '' }}>Never (Continuous Increment — Recommended for SQ-0001 / SQ-01)</option>
                                    <option value="yearly" {{ $seq->reset_policy === 'yearly' ? 'selected' : '' }}>Yearly (Reset every Jan 1st — Recommended for Date Format)</option>
                                    <option value="monthly" {{ $seq->reset_policy === 'monthly' ? 'selected' : '' }}>Monthly (Reset 1st of each month)</option>
                                </select>
                            </div>

                        </div>

                        {{-- Modal Footer --}}
                        <div class="modal-footer py-3 px-4 border-top" style="background: #f8fafc;">
                            <button type="button" class="btn btn-light border px-4 font-weight-semibold" data-dismiss="modal" style="border-radius: 10px; color: #64748b;">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold" style="border-radius: 10px; background: #2563eb; border: none;">
                                <i class="fas fa-save mr-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
    <script>
        $(document).ready(function() {
            function updateSequencePreview(modal) {
                const prefix = modal.find('.input-prefix').val() || '';
                const suffix = modal.find('.input-suffix').val() || '';
                const padding = parseInt(modal.find('.input-padding').val()) || 4;
                const nextNumber = parseInt(modal.find('.input-next-number').val()) || 1;
                const includeDate = modal.find('.input-include-date').is(':checked');
                const dateFormat = modal.find('.input-date-format').val() || 'Ym';

                // Format number with leading zeros
                const numberStr = String(nextNumber).padStart(Math.min(Math.max(padding, 1), 10), '0');

                // Format mock date
                let dateStr = '';
                if (includeDate && dateFormat) {
                    const now = new Date();
                    const Y = String(now.getFullYear());
                    const y = Y.slice(-2);
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const d = String(now.getDate()).padStart(2, '0');
                    const formattedDate = dateFormat
                        .replace(/Y/g, Y)
                        .replace(/y/g, y)
                        .replace(/m/g, m)
                        .replace(/d/g, d);
                    dateStr = formattedDate + '-';
                }

                const sample = prefix + dateStr + numberStr + suffix;
                modal.find('[id^="modalPreview"]').text(sample);

                // Update padding label
                const seqId = modal.data('seq-id');
                const padText = padding === 2 ? 'SQ-01 style' : (padding === 4 ? 'SQ-0001 style' : padding + ' digits');
                $('#paddingDesc' + seqId).text(padText);
            }

            // Preset Button Clicks (Classic vs Date-based)
            $(document).on('click', '.preset-btn', function() {
                const btn = $(this);
                const seqId = btn.data('seq-id');
                const modal = $('#editModal' + seqId);
                const type = btn.data('type');

                if (type === 'classic') {
                    modal.find('.input-include-date').prop('checked', false);
                    modal.find('.preset-classic').addClass('border-primary bg-light shadow-sm');
                    modal.find('.preset-date').removeClass('border-primary bg-light shadow-sm');
                    $('#dateBox' + seqId).addClass('d-none');
                    $('#formatBadge' + seqId).text('Classic Serial').removeClass('badge-primary').addClass('badge-warning text-dark');
                    $('#previewHint' + seqId).text('Continuous increment without date prefix (e.g. SQ-0001 or SQ-01)');
                    modal.find('.select-reset-policy').val('never');
                    $('#resetPolicyTip' + seqId).text('Recommended: Never for continuous sequence');
                } else {
                    modal.find('.input-include-date').prop('checked', true);
                    modal.find('.preset-date').addClass('border-primary bg-light shadow-sm');
                    modal.find('.preset-classic').removeClass('border-primary bg-light shadow-sm');
                    $('#dateBox' + seqId).removeClass('d-none');
                    $('#formatBadge' + seqId).text('Date-Based Format').removeClass('badge-warning text-dark').addClass('badge-primary');
                    $('#previewHint' + seqId).text('Includes current date format prefix');
                    if (modal.find('.select-reset-policy').val() === 'never') {
                        modal.find('.select-reset-policy').val('yearly');
                    }
                    $('#resetPolicyTip' + seqId).text('Recommended: Yearly for date sequence');
                }

                updateSequencePreview(modal);
            });

            // Quick Padding Buttons
            $(document).on('click', '.quick-padding', function() {
                const seqId = $(this).data('seq-id');
                const pad = $(this).data('pad');
                const modal = $('#editModal' + seqId);
                modal.find('.input-padding').val(pad);
                updateSequencePreview(modal);
            });

            // Quick Date Buttons
            $(document).on('click', '.quick-date', function() {
                const seqId = $(this).data('seq-id');
                const format = $(this).data('format');
                const modal = $('#editModal' + seqId);
                modal.find('.input-date-format').val(format);
                updateSequencePreview(modal);
            });

            // Dynamic updates on typing or changing fields
            $(document).on('input change', '.input-prefix, .input-suffix, .input-padding, .input-next-number, .input-date-format, .input-include-date', function() {
                const modal = $(this).closest('.modal');
                updateSequencePreview(modal);
            });
        });
    </script>
@endpush
