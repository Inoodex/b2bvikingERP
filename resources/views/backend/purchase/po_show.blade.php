@extends('backend.layouts.master')
@section('title', 'Purchase Order - ' . ($po->po_no ?? ('PO-' . $po->id)))

@push('css')
<style>
    .milestone-stepper {
        display: flex;
        justify-content: space-between;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px 24px;
    }
    .milestone-step {
        text-align: center;
        flex: 1;
        position: relative;
    }
    .milestone-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 14px;
        right: -50%;
        width: 100%;
        height: 3px;
        background: #cbd5e1;
        z-index: 1;
    }
    .milestone-step.completed:not(:last-child)::after {
        background: #47c363;
    }
    .milestone-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 13px;
        position: relative;
        z-index: 2;
    }
    .milestone-step.active .milestone-icon {
        background: #6777ef;
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(103,119,239,0.2);
    }
    .milestone-step.completed .milestone-icon {
        background: #47c363;
        color: #ffffff;
    }
    .milestone-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        margin-top: 6px;
        color: #64748b;
    }
    .milestone-step.active .milestone-label {
        color: #6777ef;
    }
    .milestone-step.completed .milestone-label {
        color: #47c363;
    }
</style>
@endpush

@section('content')
    <section class="section">
        <!-- Seamless Header -->
        <div class="section-header d-block p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <div class="section-header-back mr-3">
                        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
                    </div>
                    <h1 class="mb-0 text-dark font-weight-bold" style="font-size: 22px;">Purchase Order: {{ $po->po_no ?? ('PO-' . $po->id) }}</h1>
                </div>
                <div class="section-header-breadcrumb" style="position: relative; top: 0; right: 0;">
                    <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.purchase-orders.index') }}">Procurement</a></div>
                    <div class="breadcrumb-item">PO Details</div>
                </div>
            </div>

            <div class="pt-3 border-top d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <span class="badge badge-success py-2 px-3 mr-2"><i class="fas fa-check-circle mr-1"></i> {{ ucfirst($po->approval_status ?? 'Approved') }}</span>
                    <span class="badge badge-info py-2 px-3"><i class="fas fa-globe mr-1"></i> {{ strtoupper($po->purchase_type ?? 'LOCAL') }} PURCHASE</span>
                </div>
                <div class="mt-2 mt-sm-0">
                    <a href="{{ route('admin.purchase-orders.pdf.view', $po->id) }}" class="btn btn-outline-secondary btn-sm font-weight-bold mr-1" target="_blank"><i class="fas fa-eye mr-1"></i> View PDF</a>
                    <a href="{{ route('admin.purchase-orders.pdf.download', $po->id) }}" class="btn btn-outline-danger btn-sm font-weight-bold mr-1"><i class="fas fa-file-pdf mr-1"></i> Download PDF</a>
                    <form action="{{ route('admin.purchase-orders.send-email', $po->id) }}" method="POST" class="d-inline mr-1">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold"><i class="fas fa-paper-plane mr-1"></i> Send PO Email</button>
                    </form>
                    @if($po->milestone_status !== 'cancelled')
                        <form action="{{ route('admin.purchase-orders.cancel', $po->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this PO?');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm font-weight-bold"><i class="fas fa-ban mr-1"></i> Cancel PO</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="section-body">
            <!-- Milestone Stepper Bar -->
            @php
                $milestones = ['draft', 'approved', 'po_sent', 'pi_attached', 'lc_opened'];
                $currentMilestone = $po->milestone_status ?? 'approved';
                $currentIndex = array_search($currentMilestone, $milestones);
                if ($currentIndex === false) $currentIndex = 1;
            @endphp
            <div class="milestone-stepper mb-4">
                @foreach($milestones as $mIndex => $mKey)
                    @php
                        $isCompleted = $mIndex < $currentIndex;
                        $isActive = $mIndex === $currentIndex;
                    @endphp
                    <div class="milestone-step {{ $isCompleted ? 'completed' : ($isActive ? 'active' : '') }}">
                        <div class="milestone-icon">
                            @if($isCompleted)<i class="fas fa-check"></i>@else{{ $mIndex + 1 }}@endif
                        </div>
                        <div class="milestone-label">{{ str_replace('_', ' ', $mKey) }}</div>
                    </div>
                @endforeach
            </div>

            <!-- Supplier & Value Meta Cards -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-store text-info mr-2"></i> Supplier / Vendor Profile</h6>
                        </div>
                        <div class="card-body">
                            <h5 class="font-weight-bold text-dark mb-1">{{ $po->vendor->shop_name ?? 'N/A' }}</h5>
                            <p class="text-muted small mb-2"><i class="fas fa-envelope mr-1"></i> {{ $po->vendor->email ?? 'No Email' }} | <i class="fas fa-phone mr-1"></i> {{ $po->vendor->phone ?? 'N/A' }}</p>
                            <p class="text-muted mb-0 small"><i class="fas fa-map-marker-alt mr-1"></i> {{ $po->vendor->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-coins text-warning mr-2"></i> Financial & Currency Valuation</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted d-block font-weight-bold">FOREIGN VALUE</small>
                                    <h4 class="font-weight-bold text-primary mb-0">{{ $po->currency ? $po->currency->symbol : 'kr.' }} {{ number_format($po->foreign_amount ?? $po->total_amount, 2) }}</h4>
                                </div>
                                <div class="col-6 border-left">
                                    <small class="text-muted d-block font-weight-bold">BASE CONVERTED VALUE</small>
                                    <h4 class="font-weight-bold text-success mb-0">kr. {{ number_format($po->total_amount, 2) }}</h4>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block"><i class="fas fa-exchange-alt mr-1"></i> Exchange Rate Used: 1 {{ $po->currency ? $po->currency->code : 'DKK' }} = {{ $po->exchange_rate_used ?? 1.0 }} kr.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-boxes text-primary mr-2"></i> Purchase Order Line Items</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive p-3">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Description</th>
                                    <th>Variant</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($po->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="font-weight-bold">{{ $item->product ? $item->product->name : 'N/A' }}</td>
                                        <td>{{ $item->variant ? $item->variant->name : 'N/A' }}</td>
                                        <td class="text-center font-weight-bold">{{ number_format($item->qty ?? $item->quantity ?? 0, 2) }}</td>
                                        <td class="text-right">{{ $po->currency ? $po->currency->symbol : 'kr.' }} {{ number_format($item->unit_cost ?? $item->unit_price ?? 0, 2) }}</td>
                                        <td class="text-right font-weight-bold">{{ $po->currency ? $po->currency->symbol : 'kr.' }} {{ number_format($item->total ?? $item->total_price ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Proforma Invoice (PI) & LC Action Cards -->
            <div class="row">
                <!-- PI Upload Card -->
                <div class="col-md-6 mb-3">
                    <div class="card border shadow-sm">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Proforma Invoice (PI)</h6>
                            @if($po->proformaInvoice)
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Attached</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($po->proformaInvoice)
                                <p class="mb-1"><strong>PI Number:</strong> {{ $po->proformaInvoice->pi_no }}</p>
                                <p class="mb-1"><strong>Issue Date:</strong> {{ $po->proformaInvoice->issue_date ? $po->proformaInvoice->issue_date->format('d M, Y') : 'N/A' }}</p>
                                <p class="mb-3"><strong>Total Amount:</strong> {{ $po->currency ? $po->currency->symbol : 'kr.' }} {{ number_format($po->proformaInvoice->total_amount, 2) }}</p>
                                @if($po->proformaInvoice->attachment_path)
                                    <a href="{{ asset('storage/' . $po->proformaInvoice->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-paperclip mr-1"></i> View PI Document</a>
                                @endif
                            @else
                                <form action="{{ route('admin.proforma-invoices.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="purchase_id" value="{{ $po->id }}">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">PI Number *</label>
                                        <input type="text" name="pi_no" class="form-control form-control-sm" required placeholder="e.g. PI-2026-089">
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Issue Date *</label>
                                                <input type="date" name="issue_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Total Amount *</label>
                                                <input type="number" name="total_amount" class="form-control form-control-sm" step="0.01" value="{{ $po->foreign_amount ?? $po->total_amount }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold">PI PDF/File Attachment</label>
                                        <input type="file" name="attachment" class="form-control-file border p-1 rounded">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm btn-block font-weight-bold"><i class="fas fa-upload mr-1"></i> Upload & Attach PI</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- LC Creation Card -->
                <div class="col-md-6 mb-3">
                    <div class="card border shadow-sm">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-dark font-weight-bold"><i class="fas fa-university text-success mr-2"></i> Letter of Credit (LC) Register</h6>
                            @if($po->letterOfCredit)
                                <span class="badge badge-success"><i class="fas fa-lock mr-1"></i> LC Opened</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($po->letterOfCredit)
                                <p class="mb-1"><strong>LC Number:</strong> <a href="{{ route('admin.letters-of-credit.show', $po->letterOfCredit->id) }}" class="font-weight-bold text-primary">{{ $po->letterOfCredit->lc_no }}</a></p>
                                <p class="mb-1"><strong>Issuing Bank:</strong> {{ $po->letterOfCredit->issuing_bank }}</p>
                                <p class="mb-1"><strong>LC Amount:</strong> {{ $po->currency ? $po->currency->symbol : 'USD' }} {{ number_format($po->letterOfCredit->amount, 2) }} (Margin: {{ $po->letterOfCredit->margin_percent }}%)</p>
                                <p class="mb-3"><strong>Expiry Date:</strong> {{ $po->letterOfCredit->expiry_date ? $po->letterOfCredit->expiry_date->format('d M, Y') : 'N/A' }}</p>
                                <a href="{{ route('admin.letters-of-credit.show', $po->letterOfCredit->id) }}" class="btn btn-sm btn-success"><i class="fas fa-external-link-alt mr-1"></i> View LC Register & Expenses</a>
                            @else
                                <form action="{{ route('admin.letters-of-credit.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="purchase_id" value="{{ $po->id }}">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">LC Number *</label>
                                                <input type="text" name="lc_no" class="form-control form-control-sm" required placeholder="e.g. LC-BANK-009">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Issuing Bank *</label>
                                                <input type="text" name="issuing_bank" class="form-control form-control-sm" required placeholder="e.g. HSBC Denmark">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">LC Amount *</label>
                                                <input type="number" name="amount" class="form-control form-control-sm" step="0.01" value="{{ $po->foreign_amount ?? $po->total_amount }}" required>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Margin %</label>
                                                <input type="number" name="margin_percent" class="form-control form-control-sm" step="0.1" value="10.0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Issue Date *</label>
                                                <input type="date" name="issue_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Expiry Date *</label>
                                                <input type="date" name="expiry_date" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+90 days')) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ১৩টি Import Expense Breakdown --}}
                                    <div class="mt-2 mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="font-weight-bold text-muted text-uppercase" style="font-size: 10px;"><i class="fas fa-coins text-warning mr-1"></i> Normalized Import Cost Breakdown (13 Elements)</small>
                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" id="toggleExpenses" style="font-size: 11px;">
                                                <i class="fas fa-chevron-down" id="expenseChevron"></i> Show Expenses
                                            </button>
                                        </div>
                                        <div id="expenseFields" class="border rounded p-2 bg-light" style="display: none;">
                                            @php
                                                $expenseItems = ['CD' => 'Custom Duty (CD)', 'RD' => 'Regulatory Duty (RD)', 'SD' => 'Supplementary Duty (SD)', 'VAT' => 'Value Added Tax (VAT)', 'AIT' => 'Advance Income Tax (AIT)', 'AT' => 'Advance Tax (AT)', 'LC Margin' => 'LC Margin Deposit', 'Opening Charge' => 'LC Opening Charge', 'Doc Handling' => 'Document Handling', 'Insurance' => 'Insurance', 'Transport' => 'Local Transport', 'Freight' => 'Sea / Air Freight', 'C&F' => 'C&F Agent Fee'];
                                            @endphp
                                            <div class="row">
                                                @foreach($expenseItems as $key => $label)
                                                    <div class="col-6 mb-1">
                                                        <label class="small text-muted mb-0" style="font-size: 11px;">{{ $label }}</label>
                                                        <input type="number" name="expenses[{{ $key }}]" class="form-control form-control-sm" step="0.01" min="0" placeholder="0.00">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-sm btn-block font-weight-bold"><i class="fas fa-university mr-1"></i> Register LC</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggleExpenses');
        const expenseFields = document.getElementById('expenseFields');
        const chevron = document.getElementById('expenseChevron');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const isHidden = expenseFields.style.display === 'none' || expenseFields.style.display === '';
                expenseFields.style.display = isHidden ? 'block' : 'none';
                chevron.classList.toggle('fa-chevron-down', !isHidden);
                chevron.classList.toggle('fa-chevron-up', isHidden);
                toggleBtn.innerHTML = (isHidden ? '<i class="fas fa-chevron-up" id="expenseChevron"></i> Hide Expenses' : '<i class="fas fa-chevron-down" id="expenseChevron"></i> Show Expenses');
            });
        }
    });
</script>
@endpush
