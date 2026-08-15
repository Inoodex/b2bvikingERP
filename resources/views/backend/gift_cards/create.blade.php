@extends('backend.layouts.master')

@section('title', 'Issue Gift Card')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-plus text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Issue New Gift Card</h4>
                        <p class="text-muted mb-0 small">Create a new gift card with custom monetary balance</p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.gift-cards.index') }}" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" style="border-radius: 10px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-credit-card mr-2 text-primary"></i> Gift Card Parameters</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.gift-cards.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Gift Card Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" name="code" id="cardCode" class="form-control text-uppercase font-weight-bold" value="{{ old('code', $autoCode) }}" required style="border-radius: 8px 0 0 8px; letter-spacing: 1px; font-family: monospace;">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-primary font-weight-bold" id="genCardCodeBtn" style="border-radius: 0 8px 8px 0;">
                                                    <i class="fas fa-sync-alt mr-1"></i> Regenerate
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Initial Value <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="1" name="initial_value" class="form-control font-weight-bold text-success" placeholder="e.g. 1000.00" value="{{ old('initial_value') }}" required style="border-radius: 8px; font-size: 1.1rem;">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Currency (Optional)</label>
                                        <select name="currency_id" class="form-control" style="border-radius: 8px;">
                                            <option value="">-- Base System Currency --</option>
                                            @foreach($currencies as $curr)
                                                <option value="{{ $curr->id }}">{{ $curr->name }} ({{ $curr->symbol }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Expiration Date (Optional)</label>
                                        <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}" style="border-radius: 8px;">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Status</label>
                                        <select name="status" class="form-control" style="border-radius: 8px;">
                                            <option value="1" selected>Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="text-right border-top pt-4 mt-3">
                                    <button type="submit" class="btn btn-success px-5 py-2 font-weight-bold shadow-sm" style="border-radius: 10px;">
                                        <i class="fas fa-save mr-2"></i> Issue Gift Card
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#genCardCodeBtn').on('click', function() {
                let r1 = Math.floor(1000 + Math.random() * 9000);
                let r2 = Math.floor(1000 + Math.random() * 9000);
                let r3 = Math.floor(1000 + Math.random() * 9000);
                $('#cardCode').val(`GC-${r1}-${r2}-${r3}`);
            });
        });
    </script>
    @endpush
@endsection
