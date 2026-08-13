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
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <span class="badge p-2 px-3 shadow-sm" style="background: #0a0e1a; color: #e3bd7c; border: 1px solid rgba(205, 160, 90, 0.3); border-radius: 50rem; font-weight: 600;">
                        <i class="fas fa-shield-alt mr-1 text-warning"></i> Enterprise Standard
                    </span>
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
            $dateStr = $seq->include_date ? date($seq->date_format) . '-' : '';
            $sample = ($seq->prefix ?? '') . $dateStr . $numberStr . ($seq->suffix ?? '');
        @endphp
        <div class="modal fade" id="editModal{{ $seq->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $seq->id }}" aria-hidden="true" style="z-index: 99999;">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 540px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background: #ffffff;">
                    <form action="{{ route('admin.document-sequences.update', $seq->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Modal Header --}}
                        <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #0a0e1a 0%, #161e33 100%); border-bottom: 2px solid rgba(205, 160, 90, 0.4);">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 rounded-circle" style="background: rgba(205, 160, 90, 0.15); color: #e3bd7c;">
                                    <i class="fas fa-edit" style="font-size: 1.1rem;"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title font-weight-bold mb-0" id="editModalLabel{{ $seq->id }}" style="color: #f8f6f0; font-size: 1.1rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                                        Edit Sequence: {{ $seq->model_type }}
                                    </h5>
                                    <small style="color: rgba(226, 220, 205, 0.6);">Configure number formatting & reset parameters</small>
                                </div>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #ffffff; opacity: 0.8; outline: none; font-size: 1.5rem; margin-top: -10px;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="modal-body p-4" style="background: #ffffff; color: #1e293b;">
                            
                            {{-- Live Sample Card --}}
                            <div class="p-3 mb-4 rounded border" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                                <small class="text-uppercase font-weight-bold text-muted d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Live Generated Number Preview</small>
                                <div class="font-weight-bold text-primary" style="font-family: monospace; font-size: 1.2rem;" id="modalPreview{{ $seq->id }}">
                                    {{ $sample }}
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 0.875rem;">Prefix</label>
                                <input type="text" name="prefix" class="form-control form-control-lg" value="{{ $seq->prefix }}" required placeholder="e.g. SQ-" style="border-radius: 10px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; color: #0f172a; background: #ffffff;">
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 0.875rem;">Number Padding (Digits)</label>
                                    <input type="number" name="padding" class="form-control form-control-lg" value="{{ $seq->padding }}" min="1" max="10" required style="border-radius: 10px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; color: #0f172a; background: #ffffff;">
                                    <small class="text-muted">4 digits = 0001</small>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 0.875rem;">Next Serial Number</label>
                                    <input type="number" name="next_number" class="form-control form-control-lg" value="{{ $seq->next_number }}" min="1" required style="border-radius: 10px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; color: #0f172a; background: #ffffff;">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 0.875rem;">Reset Policy</label>
                                <select name="reset_policy" class="form-control form-control-lg" style="border-radius: 10px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; color: #0f172a; background: #ffffff;">
                                    <option value="yearly" {{ $seq->reset_policy === 'yearly' ? 'selected' : '' }}>Yearly (Reset every Jan 1st)</option>
                                    <option value="monthly" {{ $seq->reset_policy === 'monthly' ? 'selected' : '' }}>Monthly (Reset 1st of month)</option>
                                    <option value="never" {{ $seq->reset_policy === 'never' ? 'selected' : '' }}>Never (Continuous Increment)</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="include_date" value="1" class="custom-control-input" id="incDate{{ $seq->id }}" {{ $seq->include_date ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-dark" for="incDate{{ $seq->id }}" style="font-size: 0.9rem; cursor: pointer;">
                                        Include Date Prefix in Number
                                    </label>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 0.875rem;">Date Format</label>
                                <input type="text" name="date_format" class="form-control form-control-lg" value="{{ $seq->date_format }}" required placeholder="e.g. Ym" style="border-radius: 10px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; color: #0f172a; background: #ffffff;">
                                <small class="text-muted">Ym = Year+Month (e.g. 202608), Y = Year (e.g. 2026)</small>
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
@endpush
