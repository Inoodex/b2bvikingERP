@extends('backend.layouts.master')
@section('title', 'Create Manual Journal Voucher — GL Posting')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-pen-alt text-primary mr-2"></i> Manual Journal Voucher</h1>
            <p class="text-muted mb-0 small">Post a balanced double-entry directly to General Ledger</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.journal-vouchers.index') }}">Journal Vouchers</a></div>
            <div class="breadcrumb-item active">Create JV</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-lg-9">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-primary text-white py-3" style="border-radius: 12px 12px 0 0;">
                        <h5 class="font-weight-bold text-white mb-0"><i class="fas fa-balance-scale mr-2"></i> New Journal Entry — Balanced Debit & Credit</h5>
                    </div>
                    <div class="card-body p-4">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <strong>Validation Error:</strong>
                                <ul class="mb-0 mt-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.journal-vouchers.store') }}" method="POST" id="jv-form">
                            @csrf

                            <div class="row mb-4">
                                <div class="col-md-4 form-group">
                                    <label class="font-weight-bold text-dark">Entry Date <span class="text-danger">*</span></label>
                                    <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" class="form-control" required style="border-radius:6px;">
                                </div>
                                <div class="col-md-8 form-group">
                                    <label class="font-weight-bold text-dark">Narration / Description <span class="text-danger">*</span></label>
                                    <input type="text" name="narration" value="{{ old('narration') }}" class="form-control" required
                                        placeholder="e.g. Depreciation adjustment for Q3 2026, Accrual entry for outstanding salaries..." style="border-radius:6px;">
                                </div>
                            </div>

                            <!-- JV Lines Table -->
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered align-middle mb-0" id="jv-lines-table">
                                    <thead class="bg-light text-dark">
                                        <tr>
                                            <th style="width: 40%;">GL Account Head</th>
                                            <th style="width: 20%;" class="text-right">Debit (DKK)</th>
                                            <th style="width: 20%;" class="text-right">Credit (DKK)</th>
                                            <th style="width: 15%;">Description</th>
                                            <th style="width: 5%;" class="text-center">✕</th>
                                        </tr>
                                    </thead>
                                    <tbody id="jv-lines-body">
                                        <!-- Row 1 (Debit) -->
                                        <tr class="jv-line-row">
                                            <td>
                                                <select name="lines[0][account_id]" class="form-control form-control-sm select2" required style="border-radius:4px;">
                                                    <option value="">-- Select GL Account --</option>
                                                    @foreach($accounts as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->account_code }} — {{ $acc->account_name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.01" min="0" name="lines[0][debit]" value="0.00" class="form-control form-control-sm text-right debit-field" style="border-radius:4px;"></td>
                                            <td><input type="number" step="0.01" min="0" name="lines[0][credit]" value="0.00" class="form-control form-control-sm text-right credit-field" style="border-radius:4px;"></td>
                                            <td><input type="text" name="lines[0][description]" class="form-control form-control-sm" placeholder="Optional"></td>
                                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-line py-0 px-1"><i class="fas fa-times"></i></button></td>
                                        </tr>
                                        <!-- Row 2 (Credit) -->
                                        <tr class="jv-line-row">
                                            <td>
                                                <select name="lines[1][account_id]" class="form-control form-control-sm select2" required style="border-radius:4px;">
                                                    <option value="">-- Select GL Account --</option>
                                                    @foreach($accounts as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->account_code }} — {{ $acc->account_name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.01" min="0" name="lines[1][debit]" value="0.00" class="form-control form-control-sm text-right debit-field" style="border-radius:4px;"></td>
                                            <td><input type="number" step="0.01" min="0" name="lines[1][credit]" value="0.00" class="form-control form-control-sm text-right credit-field" style="border-radius:4px;"></td>
                                            <td><input type="text" name="lines[1][description]" class="form-control form-control-sm" placeholder="Optional"></td>
                                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-line py-0 px-1"><i class="fas fa-times"></i></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <button type="button" id="btn-add-line" class="btn btn-outline-secondary btn-sm font-weight-bold mb-4" style="border-radius:6px;">
                                <i class="fas fa-plus mr-1"></i> Add Line
                            </button>

                            <!-- Running Balance Checker -->
                            <div class="p-3 rounded border mb-4" id="balance-checker" style="background:#f8f9ff;">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="text-muted small font-weight-bold text-uppercase">Total Debit</div>
                                        <div class="h5 font-weight-bold text-success mb-0" id="total-debit">kr. 0.00</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small font-weight-bold text-uppercase">Total Credit</div>
                                        <div class="h5 font-weight-bold text-danger mb-0" id="total-credit">kr. 0.00</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small font-weight-bold text-uppercase">Difference</div>
                                        <div class="h5 font-weight-bold mb-0" id="total-diff">kr. 0.00</div>
                                    </div>
                                </div>
                                <div class="text-center mt-2 small font-weight-bold" id="balance-status"></div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('admin.journal-vouchers.index') }}" class="btn btn-light border font-weight-bold mr-2" style="border-radius:8px;">Cancel</a>
                                <button type="submit" id="btn-submit-jv" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius:8px;" disabled>
                                    <i class="fas fa-check-double mr-1"></i> Post Journal Entry to GL
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Help Panel -->
            <div class="col-lg-3">
                <div class="card shadow-sm border-0 bg-primary text-white" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <h6 class="font-weight-bold text-white mb-3"><i class="fas fa-info-circle mr-2"></i> Double-Entry Rules</h6>
                        <ul class="small text-white-50 pl-3 mb-0">
                            <li class="mb-2">Every JV must have <strong class="text-white">at least 2 lines</strong></li>
                            <li class="mb-2"><strong class="text-white">Total Debit = Total Credit</strong> (Zero Imbalance Rule)</li>
                            <li class="mb-2">Posting is blocked for <strong class="text-white">Closed Fiscal Periods</strong></li>
                            <li class="mb-2">JV entries use <strong class="text-white">MJV prefix</strong> in GL</li>
                            <li>Cannot reverse — contact admin for corrections</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let lineIndex = 2;

    const accountOptions = `
        @foreach($accounts as $acc)
            <option value="{{ $acc->id }}">{{ $acc->account_code }} — {{ addslashes($acc->account_name) }}</option>
        @endforeach
    `;

    // Add new line
    $('#btn-add-line').on('click', function() {
        const row = `
        <tr class="jv-line-row">
            <td>
                <select name="lines[${lineIndex}][account_id]" class="form-control form-control-sm select2" required style="border-radius:4px;">
                    <option value="">-- Select GL Account --</option>
                    ${accountOptions}
                </select>
            </td>
            <td><input type="number" step="0.01" min="0" name="lines[${lineIndex}][debit]" value="0.00" class="form-control form-control-sm text-right debit-field" style="border-radius:4px;"></td>
            <td><input type="number" step="0.01" min="0" name="lines[${lineIndex}][credit]" value="0.00" class="form-control form-control-sm text-right credit-field" style="border-radius:4px;"></td>
            <td><input type="text" name="lines[${lineIndex}][description]" class="form-control form-control-sm" placeholder="Optional"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-line py-0 px-1"><i class="fas fa-times"></i></button></td>
        </tr>`;
        $('#jv-lines-body').append(row);
        $('#jv-lines-body .select2:last').select2({ width: '100%' });
        lineIndex++;
        recalculate();
    });

    // Remove line
    $(document).on('click', '.btn-remove-line', function() {
        if ($('.jv-line-row').length > 2) {
            $(this).closest('tr').remove();
            recalculate();
        }
    });

    // Recalculate on input change
    $(document).on('input', '.debit-field, .credit-field', function() {
        recalculate();
    });

    function recalculate() {
        let totalDebit = 0, totalCredit = 0;
        $('.debit-field').each(function() { totalDebit += parseFloat($(this).val()) || 0; });
        $('.credit-field').each(function() { totalCredit += parseFloat($(this).val()) || 0; });

        const diff = Math.abs(totalDebit - totalCredit);
        $('#total-debit').text('kr. ' + totalDebit.toFixed(2));
        $('#total-credit').text('kr. ' + totalCredit.toFixed(2));
        $('#total-diff').text('kr. ' + diff.toFixed(2));

        const isBalanced = diff < 0.01 && totalDebit > 0;
        if (isBalanced) {
            $('#total-diff').removeClass('text-danger').addClass('text-success');
            $('#balance-status').html('<i class="fas fa-check-circle text-success mr-1"></i><span class="text-success">Balanced — Ready to Post</span>');
            $('#btn-submit-jv').prop('disabled', false);
            $('#balance-checker').css('border-color', '#28a745');
        } else {
            $('#total-diff').removeClass('text-success').addClass('text-danger');
            $('#balance-status').html('<i class="fas fa-exclamation-triangle text-danger mr-1"></i><span class="text-danger">Imbalanced — Cannot Post</span>');
            $('#btn-submit-jv').prop('disabled', true);
            $('#balance-checker').css('border-color', '#dc3545');
        }
    }

    recalculate();
});
</script>
@endpush
