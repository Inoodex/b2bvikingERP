<div class="stock-movement-wrapper">
    {{-- Product Header --}}
    <div class="d-flex align-items-center justify-content-between p-3 mb-3 bg-light rounded" style="border: 1px solid #e2e8f0;">
        <div class="d-flex align-items-center">
            <img src="{{ $thumbImage }}" alt="{{ $product->name }}" 
                 class="rounded shadow-sm mr-3" style="width: 55px; height: 55px; object-fit: cover; border: 2px solid #fff;">
            <div>
                <h5 class="mb-1 text-dark font-weight-bold" style="font-size: 16px;">{{ $product->name }}</h5>
                <div class="text-muted small">
                    <span class="mr-2"><i class="fas fa-barcode text-secondary mr-1"></i><strong>SKU:</strong> {{ $product->sku ?? 'N/A' }}</span>
                    <span class="mr-2"><i class="fas fa-folder text-secondary mr-1"></i><strong>Category:</strong> {{ optional($product->category)->name ?? 'General' }}</span>
                    @if($product->vendor)
                        <span><i class="fas fa-truck text-secondary mr-1"></i><strong>Vendor:</strong> {{ $product->vendor->shop_name ?? $product->vendor->name }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="text-right">
            <div class="small text-muted mb-1 font-weight-bold text-uppercase">Current Stock</div>
            <span class="badge {{ $currentStock > 0 ? 'badge-success' : 'badge-danger' }} px-3 py-2" style="font-size: 13px; border-radius: 8px;">
                <i class="fas {{ $currentStock > 0 ? 'fa-check-circle' : 'fa-exclamation-triangle' }} mr-1"></i>
                {{ number_format($currentStock) }} {{ optional($product->unit)->name ?? 'pcs' }}
            </span>
        </div>
    </div>

    {{-- Lifetime & Real-time KPI Ribbon --}}
    <div class="row mb-3">
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <div class="p-3 bg-white rounded shadow-sm text-center" style="border-left: 4px solid #10b981; border: 1px solid #e5e7eb; border-left-width: 4px;">
                <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Lifetime Inflow</div>
                <div class="h5 mb-0 font-weight-bold text-success mt-1" id="kpi-lifetime-inflow">
                    +{{ number_format($lifetimeInflow) }}
                </div>
                <div class="text-muted" style="font-size: 10px;">Total stock received</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <div class="p-3 bg-white rounded shadow-sm text-center" style="border-left: 4px solid #ef4444; border: 1px solid #e5e7eb; border-left-width: 4px;">
                <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Lifetime Outflow</div>
                <div class="h5 mb-0 font-weight-bold text-danger mt-1" id="kpi-lifetime-outflow">
                    -{{ number_format($lifetimeOutflow) }}
                </div>
                <div class="text-muted" style="font-size: 10px;">Sold / dispatched</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center" style="border-left: 4px solid #3b82f6; border: 1px solid #e5e7eb; border-left-width: 4px;">
                <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Period Inflow</div>
                <div class="h5 mb-0 font-weight-bold text-primary mt-1" id="kpi-period-inflow">
                    {{ number_format($velocity['inflow_qty']) }}
                </div>
                <div class="text-muted" style="font-size: 10px;" id="kpi-period-inflow-sub">In selected window</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center" style="border-left: 4px solid #f59e0b; border: 1px solid #e5e7eb; border-left-width: 4px;">
                <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Period Sold</div>
                <div class="h5 mb-0 font-weight-bold text-warning mt-1" id="kpi-period-sold">
                    {{ number_format($velocity['sold_qty']) }}
                </div>
                <div class="text-muted" style="font-size: 10px;">Out of period stock</div>
            </div>
        </div>
    </div>

    {{-- Dynamic Velocity Engine Filter Bar (Module 6) --}}
    <div class="card shadow-sm border-0 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0 !important; border-radius: 10px;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <span class="mr-2 text-dark font-weight-bold small"><i class="fas fa-chart-line text-primary mr-1"></i>Velocity Window:</span>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons" id="velocity-preset-group">
                        <label class="btn btn-sm px-2 py-1 {{ $velocity['preset'] == '30_days' ? 'active' : '' }}">
                            <input type="radio" name="velocity_preset" value="30_days" autocomplete="off" {{ $velocity['preset'] == '30_days' ? 'checked' : '' }}> 30D
                        </label>
                        <label class="btn btn-sm px-2 py-1 {{ $velocity['preset'] == '90_days' ? 'active' : '' }}">
                            <input type="radio" name="velocity_preset" value="90_days" autocomplete="off" {{ $velocity['preset'] == '90_days' ? 'checked' : '' }}> 90D (3 Mo)
                        </label>
                        <label class="btn btn-sm px-2 py-1 {{ $velocity['preset'] == '180_days' ? 'active' : '' }}">
                            <input type="radio" name="velocity_preset" value="180_days" autocomplete="off" {{ $velocity['preset'] == '180_days' ? 'checked' : '' }}> 180D (6 Mo)
                        </label>
                        <label class="btn btn-sm px-2 py-1 {{ $velocity['preset'] == '365_days' ? 'active' : '' }}">
                            <input type="radio" name="velocity_preset" value="365_days" autocomplete="off" {{ $velocity['preset'] == '365_days' ? 'checked' : '' }}> 1 Year
                        </label>
                        <label class="btn btn-sm px-2 py-1" id="btn-toggle-custom-filter">
                            <input type="radio" name="velocity_preset" value="custom" autocomplete="off"> Custom / Month
                        </label>
                    </div>
                </div>

                {{-- Velocity Pill Badge --}}
                <div id="velocity-badge-container">
                    <span class="badge {{ $velocity['classification']['badge'] }} px-3 py-1 font-weight-bold" style="font-size: 12px; border-radius: 20px;">
                        <i class="{{ $velocity['classification']['icon'] }} mr-1"></i>
                        <span id="velocity-label-text">{{ $velocity['classification']['label'] }}</span>: 
                        <span id="velocity-rate-text">{{ $velocity['sell_through_rate'] }}%</span> Sold
                    </span>
                </div>
            </div>

            {{-- Expandable Custom Month/Year & Date Range Selector --}}
            <div id="velocity-custom-filter-box" class="p-2 mt-2 bg-white rounded border" style="display: none;">
                <div class="row align-items-center">
                    <div class="col-md-3 col-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-0 font-weight-bold">Select Month</label>
                        <select id="vel_filter_month" class="form-control form-control-sm">
                            <option value="">-- All Months --</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 col-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-0 font-weight-bold">Select Year</label>
                        <select id="vel_filter_year" class="form-control form-control-sm">
                            @for ($y = date('Y'); $y >= date('Y') - 4; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4 col-12 mb-2 mb-md-0">
                        <label class="small text-muted mb-0 font-weight-bold">Or Exact Date Range</label>
                        <div class="d-flex">
                            <input type="date" id="vel_filter_start" class="form-control form-control-sm mr-1">
                            <input type="date" id="vel_filter_end" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-2 col-12 text-right">
                        <button type="button" class="btn btn-primary btn-sm btn-block mt-3" id="btn-apply-custom-velocity">
                            <i class="fas fa-check mr-1"></i> Apply
                        </button>
                    </div>
                </div>
            </div>

            {{-- Sell-through Visual Progress Bar --}}
            <div class="mt-2">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span><strong>Sell-Through Rate:</strong> <span id="progress-rate-label">{{ $velocity['sell_through_rate'] }}%</span></span>
                    <span id="velocity-advice-text" class="font-weight-bold text-dark">{{ $velocity['classification']['advice'] }}</span>
                </div>
                <div class="progress" style="height: 8px; border-radius: 4px; background: #e2e8f0;">
                    <div id="velocity-progress-bar" class="progress-bar" role="progressbar" 
                         style="width: {{ $velocity['sell_through_rate'] }}%; background-color: {{ $velocity['classification']['bg_color'] }};" 
                         aria-valuenow="{{ $velocity['sell_through_rate'] }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-3" id="stockMovementTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" id="inflow-tab" data-toggle="pill" href="#tab-inflows" role="tab" style="border-radius: 8px; padding: 6px 16px;">
                <i class="fas fa-truck-loading mr-1 text-success"></i> Stock Inflows 
                <span class="badge badge-light ml-1">{{ $purchaseInflows->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="ledger-tab" data-toggle="pill" href="#tab-ledger" role="tab" style="border-radius: 8px; padding: 6px 16px;">
                <i class="fas fa-exchange-alt mr-1 text-primary"></i> Stock Movement Ledger 
                <span class="badge badge-light ml-1">{{ $movements->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="visibility-tab" data-toggle="pill" href="#tab-visibility" role="tab" style="border-radius: 8px; padding: 6px 16px;">
                <i class="fas fa-users-cog mr-1 text-warning"></i> B2B Customer Stock Rules 
                <span class="badge badge-light ml-1" id="badge-visibilities-count">{{ isset($visibilities) ? $visibilities->count() : 0 }}</span>
            </a>
        </li>
    </ul>

    {{-- Tabs Content --}}
    <div class="tab-content" id="stockMovementTabContent">
        {{-- Tab 1: Stock Inflows (Purchases & Shipments) --}}
        <div class="tab-pane fade show active" id="tab-inflows" role="tabpanel">
            <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                <table class="table table-sm table-striped table-hover mb-0" style="font-size: 12.5px;">
                    <thead class="thead-light sticky-top" style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th>Date Arrived</th>
                            <th>PO / Invoice #</th>
                            <th>Vendor / Supplier</th>
                            <th class="text-right">Qty Received</th>
                            <th class="text-right">Unit Cost</th>
                            <th class="text-right">Landed Cost</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseInflows as $inflow)
                            @php
                                $purchase = $inflow->purchase;
                                $vendor = optional($purchase)->vendor;
                                $receivedDate = optional($purchase)->purchase_date 
                                    ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') 
                                    : $inflow->created_at->format('d M Y');
                            @endphp
                            <tr>
                                <td class="font-weight-bold text-dark">
                                    <i class="far fa-calendar-alt text-muted mr-1"></i>{{ $receivedDate }}
                                </td>
                                <td>
                                    @if($purchase)
                                        <a href="{{ route('admin.purchase.show', $purchase->id) }}" target="_blank" class="font-weight-bold text-primary">
                                            {{ $purchase->invoice_no ?? ('PO #' . $purchase->id) }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <i class="fas fa-building text-muted mr-1"></i>
                                    {{ $vendor->shop_name ?? ($vendor->name ?? 'Standard Vendor') }}
                                </td>
                                <td class="text-right font-weight-bold text-success">
                                    +{{ number_format($inflow->qty) }} {{ optional($product->unit)->name ?? 'pcs' }}
                                </td>
                                <td class="text-right font-weight-bold">
                                    {!! formatWithCurrency($inflow->unit_cost ?? 0) !!}
                                </td>
                                <td class="text-right text-muted">
                                    {!! formatWithCurrency($inflow->landed_cost ?? ($inflow->unit_cost ?? 0)) !!}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success badge-sm px-2 py-1">
                                        <i class="fas fa-check-circle mr-1"></i>{{ ucfirst(str_replace('_', ' ', $purchase->status ?? 'Received')) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-box-open fa-2x mb-2 text-secondary d-block"></i>
                                    No purchase inflow records recorded yet for this product.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab 2: Complete Movement Ledger --}}
        <div class="tab-pane fade" id="tab-ledger" role="tabpanel">
            <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                <table class="table table-sm table-striped table-hover mb-0" style="font-size: 12px;">
                    <thead class="thead-light sticky-top" style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th>Date & Time</th>
                            <th>Movement Type</th>
                            <th>Reference #</th>
                            <th>Outlet / Store</th>
                            <th class="text-right">In (+)</th>
                            <th class="text-right">Out (-)</th>
                            <th class="text-right">Balance After</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $ledger)
                            @php
                                $refDisplay = class_basename($ledger->reference_type);
                                if ($ledger->reference_id) {
                                    $refDisplay .= ' #' . $ledger->reference_id;
                                }
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($ledger->date)->format('d M Y') }}</td>
                                <td>
                                    @if($ledger->in_qty > 0)
                                        <span class="badge badge-light text-success font-weight-bold">
                                            <i class="fas fa-arrow-down mr-1"></i>Stock Inflow
                                        </span>
                                    @elseif($ledger->out_qty > 0)
                                        <span class="badge badge-light text-danger font-weight-bold">
                                            <i class="fas fa-arrow-up mr-1"></i>Stock Outflow
                                        </span>
                                    @else
                                        <span class="badge badge-light text-muted">Adjustment</span>
                                    @endif
                                </td>
                                <td><span class="text-dark font-weight-bold">{{ $refDisplay }}</span></td>
                                <td>{{ optional($ledger->outlet)->name ?? 'Main Warehouse' }}</td>
                                <td class="text-right font-weight-bold text-success">
                                    {{ $ledger->in_qty > 0 ? ('+' . number_format($ledger->in_qty)) : '-' }}
                                </td>
                                <td class="text-right font-weight-bold text-danger">
                                    {{ $ledger->out_qty > 0 ? ('-' . number_format($ledger->out_qty)) : '-' }}
                                </td>
                                <td class="text-right font-weight-bold text-dark">
                                    {{ number_format($ledger->balance_qty) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-clipboard-list fa-2x mb-2 text-secondary d-block"></i>
                                    No ledger movements logged yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab 3: B2B Customer Visibility & Availability Overrides (Module 7) --}}
        <div class="tab-pane fade" id="tab-visibility" role="tabpanel">
            {{-- Add Rule Card --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-primary px-2 py-1 mr-2" style="border-radius: 5px; font-size: 10px; font-weight: 700;">
                            <i class="fas fa-plus mr-1"></i> NEW RULE
                        </span>
                        <strong class="text-dark" style="font-size: 13px;">Add Custom Availability Override for Buyer / Outlet</strong>
                    </div>
                    <span class="text-muted small" style="font-size: 11px;">
                        <i class="fas fa-shield-alt text-success mr-1"></i> Overrides real stock display for targeted account
                    </span>
                </div>
                <div class="card-body p-3">
                    <form id="form-add-b2b-visibility">
                        @csrf
                        {{-- Target Selector Row --}}
                        <div class="row align-items-center mb-2">
                            <div class="col-md-5 col-12 mb-2 mb-md-0">
                                <label class="small text-muted font-weight-bold mb-1 d-block">1. Target Scope</label>
                                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons" id="b2b-scope-pill-group">
                                    <label class="btn btn-sm active b2b-scope-pill" data-target="company">
                                        <input type="radio" name="b2b_scope_type" value="company" checked>
                                        <i class="fas fa-building mr-1"></i> Company
                                    </label>
                                    <label class="btn btn-sm b2b-scope-pill" data-target="outlet">
                                        <input type="radio" name="b2b_scope_type" value="outlet">
                                        <i class="fas fa-store mr-1"></i> Outlet
                                    </label>
                                    <label class="btn btn-sm b2b-scope-pill" data-target="phone">
                                        <input type="radio" name="b2b_scope_type" value="phone">
                                        <i class="fas fa-phone mr-1"></i> Buyer / Phone
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-7 col-12">
                                <label class="small text-muted font-weight-bold mb-1 d-block">2. Select Account</label>
                                
                                {{-- Scope 1: Company --}}
                                <div class="b2b-scope-container" id="scope-box-company">
                                    <select name="company_id" id="b2b_company_id" class="form-control select2 b2b-select2" style="width: 100%;">
                                        <option value="">-- Search & Choose Target Company --</option>
                                        @foreach($companies ?? [] as $comp)
                                            <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Scope 2: Outlet --}}
                                <div class="b2b-scope-container" id="scope-box-outlet" style="display: none;">
                                    <select name="outlet_id" id="b2b_outlet_id" class="form-control select2 b2b-select2" style="width: 100%;">
                                        <option value="">-- Search & Choose Target Outlet / Branch (23 Outlets) --</option>
                                        @foreach($outlets ?? [] as $out)
                                            <option value="{{ $out->id }}">{{ $out->name }} [{{ $out->code }}]</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Scope 3: Phone / Buyer --}}
                                <div class="b2b-scope-container" id="scope-box-phone" style="display: none;">
                                    <div class="row no-gutters">
                                        <div class="col-7 pr-1">
                                            <select name="user_id" id="b2b_user_id" class="form-control select2 b2b-select2" style="width: 100%;">
                                                <option value="">-- Choose Registered Buyer --</option>
                                                @foreach($customers ?? [] as $cust)
                                                    <option value="{{ $cust->id }}" data-phone="{{ $cust->phone }}">
                                                        {{ $cust->name }} {{ $cust->phone ? '('.$cust->phone.')' : ($cust->email ? '('.$cust->email.')' : '') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-5 pl-1">
                                            <div class="b2b-phone-input-wrap">
                                                <i class="fas fa-phone-alt"></i>
                                                <input type="text" name="phone_number" id="b2b_phone" 
                                                       placeholder="Buyer phone / ID" 
                                                       autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Rule Mode and Notes Row --}}
                        <div class="row align-items-end mt-2">
                            <div class="col-md-5 col-12 mb-2 mb-md-0">
                                <label class="small text-muted font-weight-bold mb-1">3. Availability Rule</label>
                                <select name="visibility_mode" id="b2b_mode" class="form-control select2 b2b-select2 font-weight-bold" style="width: 100%;">
                                    <option value="force_in_stock">🟢 Priority Available (In Stock for this Buyer)</option>
                                    <option value="force_out_of_stock">🔴 Restricted (Out of Stock for this Buyer)</option>
                                    <option value="hide_product">🚫 Catalog Exclusion (Hide Product Completely)</option>
                                </select>
                            </div>
                            <div class="col-md-5 col-12 mb-2 mb-md-0">
                                <label class="small text-muted font-weight-bold mb-1">Reason / Notes</label>
                                <input type="text" name="notes" id="b2b_notes" class="form-control form-control-sm" 
                                       placeholder="e.g. Reserved for VIP contract / Exclusive territory"
                                       style="border-radius: 8px; height: 38px; border-color: #cbd5e1; font-size: 12.5px;">
                            </div>
                            <div class="col-md-2 col-12 text-right">
                                <button type="button" class="btn btn-primary btn-sm btn-block font-weight-bold shadow-sm" id="btn-save-b2b-visibility" style="border-radius: 8px; height: 38px;">
                                    <i class="fas fa-check mr-1"></i> Apply Rule
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Existing Rules Table Card --}}
            <div class="card border shadow-sm mb-0" style="border-radius: 10px; overflow: hidden; border-color: #e2e8f0 !important;">
                <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-list-check text-primary mr-2"></i>
                        <strong class="text-dark" style="font-size: 13px;">Active Customer Overrides for this Product</strong>
                    </div>
                    <span class="badge badge-light border text-muted px-2 py-1 font-weight-bold" id="b2b-rules-count-badge" style="font-size: 11px;">
                        {{ count($visibilities ?? []) }} Rules
                    </span>
                </div>
                <div class="table-responsive" style="max-height: 260px; overflow-y: auto; overflow-x: hidden;">
                    <table class="table table-hover align-middle mb-0" id="table-b2b-visibilities" style="font-size: 12px; table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 32%; padding: 10px 14px; background: #f8fafc !important; color: #475569 !important; font-weight: 700; border-bottom: 2px solid #e2e8f0 !important;">Target Entity</th>
                                <th style="width: 28%; padding: 10px 14px; background: #f8fafc !important; color: #475569 !important; font-weight: 700; border-bottom: 2px solid #e2e8f0 !important;">Availability Rule</th>
                                <th style="width: 26%; padding: 10px 14px; background: #f8fafc !important; color: #475569 !important; font-weight: 700; border-bottom: 2px solid #e2e8f0 !important;">Reason / Notes</th>
                                <th style="width: 14%; padding: 10px 14px; background: #f8fafc !important; color: #475569 !important; font-weight: 700; border-bottom: 2px solid #e2e8f0 !important;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visibilities ?? [] as $rule)
                                <tr id="b2b-rule-row-{{ $rule->id }}" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 10px 12px; vertical-align: middle;">
                                        @if($rule->company)
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2 text-primary" style="width: 24px; height: 24px; background: rgba(59, 130, 246, 0.1); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <div class="text-truncate" style="max-width: 180px;">
                                                    <div class="font-weight-bold text-dark text-truncate" title="{{ $rule->company->name }}">{{ $rule->company->name }}</div>
                                                    <small class="text-muted" style="font-size: 10px;">Company Account</small>
                                                </div>
                                            </div>
                                        @elseif($rule->outlet)
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2 text-info" style="width: 24px; height: 24px; background: rgba(6, 182, 212, 0.1); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                                    <i class="fas fa-store"></i>
                                                </div>
                                                <div class="text-truncate" style="max-width: 180px;">
                                                    <div class="font-weight-bold text-dark text-truncate" title="{{ $rule->outlet->name }}">{{ $rule->outlet->name }}</div>
                                                    <small class="text-muted" style="font-size: 10px;">Outlet Branch</small>
                                                </div>
                                            </div>
                                        @elseif($rule->phone_number)
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2 text-secondary" style="width: 24px; height: 24px; background: rgba(100, 116, 139, 0.1); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                                    <i class="fas fa-phone"></i>
                                                </div>
                                                <div class="text-truncate" style="max-width: 180px;">
                                                    <div class="font-weight-bold text-dark">{{ $rule->phone_number }}</div>
                                                    <small class="text-muted" style="font-size: 10px;">Buyer Phone</small>
                                                </div>
                                            </div>
                                        @elseif($rule->user)
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2 text-dark" style="width: 24px; height: 24px; background: rgba(15, 23, 42, 0.1); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="text-truncate" style="max-width: 180px;">
                                                    <div class="font-weight-bold text-dark">{{ $rule->user->name }}</div>
                                                    <small class="text-muted" style="font-size: 10px;">Registered User</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted font-italic">Global Customer</span>
                                        @endif
                                    </td>
                                    <td style="padding: 10px 12px; vertical-align: middle;">
                                        @if($rule->visibility_mode == 'force_in_stock')
                                            <span class="badge px-2 py-1 font-weight-bold" style="background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 6px; font-size: 11px;">
                                                <i class="fas fa-check-circle mr-1"></i> Priority In-Stock
                                            </span>
                                        @elseif($rule->visibility_mode == 'force_out_of_stock')
                                            <span class="badge px-2 py-1 font-weight-bold" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 6px; font-size: 11px;">
                                                <i class="fas fa-ban mr-1"></i> Restricted (OOS)
                                            </span>
                                        @else
                                            <span class="badge px-2 py-1 font-weight-bold" style="background: rgba(100, 116, 139, 0.12); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25); border-radius: 6px; font-size: 11px;">
                                                <i class="fas fa-eye-slash mr-1"></i> Hidden
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding: 10px 12px; vertical-align: middle;">
                                        <div class="text-dark small text-truncate" title="{{ $rule->notes ?? '' }}" style="max-width: 200px;">
                                            {{ $rule->notes ?: '—' }}
                                        </div>
                                        <div class="text-muted" style="font-size: 10px;">
                                            Set {{ $rule->created_at->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td style="padding: 10px 12px; vertical-align: middle;" class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 btn-delete-b2b-rule" data-id="{{ $rule->id }}" title="Delete this rule" style="border-radius: 5px;">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="no-b2b-rules-row">
                                    <td colspan="4" class="text-center py-4 bg-white">
                                        <div class="py-2">
                                            <div class="mb-2" style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-shield-alt text-muted" style="font-size: 18px;"></i>
                                            </div>
                                            <div class="font-weight-bold text-dark" style="font-size: 13px;">Standard Inventory Rules Active</div>
                                            <div class="text-muted small mt-1" style="max-width: 400px; margin: 0 auto; font-size: 11px;">
                                                No customer overrides set for this item. All buyers and outlets see actual warehouse stock.
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Scoped Button Group & Focus Outline Removal */
    #velocity-preset-group,
    #b2b-scope-pill-group {
        background: #f1f5f9 !important;
        padding: 3px !important;
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
    }
    #velocity-preset-group .btn,
    #b2b-scope-pill-group .b2b-scope-pill {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
        color: #64748b !important;
        font-weight: 600 !important;
        transition: all 0.16s ease-in-out !important;
        cursor: pointer !important;
    }
    #velocity-preset-group .btn:hover,
    #b2b-scope-pill-group .b2b-scope-pill:hover {
        color: #0f172a !important;
        background: rgba(255, 255, 255, 0.8) !important;
        outline: none !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    }
    #velocity-preset-group .btn.active,
    #b2b-scope-pill-group .b2b-scope-pill.active {
        background: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3) !important;
        outline: none !important;
        border: none !important;
    }
    #velocity-preset-group .btn:focus,
    #velocity-preset-group .btn.focus,
    #b2b-scope-pill-group .b2b-scope-pill:focus,
    #b2b-scope-pill-group .b2b-scope-pill.focus,
    #stockMovementDrawer .btn:focus,
    #stockMovementDrawer button:focus {
        outline: none !important;
        box-shadow: none !important;
    }

    /* Unified Seamless Phone Input Wrap */
    .b2b-phone-input-wrap {
        display: flex;
        align-items: center;
        height: 38px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0 12px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease-in-out;
        width: 100%;
    }
    .b2b-phone-input-wrap:focus-within {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }
    .b2b-phone-input-wrap i {
        color: #64748b;
        font-size: 13px;
        margin-right: 10px;
        flex-shrink: 0;
    }
    .b2b-phone-input-wrap input {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
        width: 100%;
        height: 100%;
        font-size: 12.5px;
        color: #1e293b;
        font-weight: 500;
        padding: 0;
    }
    .b2b-phone-input-wrap input::placeholder {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 400;
    }
</style>

<script>
    // Hidden data storage for AJAX recalculation
    window.currentStockMovementProductId = {{ $product->id }};

    // Auto-initialize Select2 for newly rendered drawer content
    setTimeout(function() {
        if (typeof initB2bSelect2 === 'function') {
            initB2bSelect2();
        } else if ($.fn.select2) {
            $('#stockMovementDrawerBody .b2b-select2').select2({
                dropdownParent: $('#stockMovementDrawer'),
                width: '100%'
            });
        }
    }, 50);
</script>
