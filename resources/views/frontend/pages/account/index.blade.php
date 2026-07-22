@extends('layouts.frontend')
@section('title', 'My Account')

@section('head')
@if(($panel ?? '') === 'order-form')
    <link rel="stylesheet" href="{{ asset('backend/assets/modules/select2/dist/css/select2.min.css') }}">
    <style>
        .order-form-select2 + .select2-container { width: 100% !important; }
        .order-form-select2 + .select2-container .select2-selection--single {
            border: 1px solid rgb(203 213 225);
            border-radius: 2px;
            min-height: 40px;
            display: flex;
            align-items: center;
            background-color: #fff;
        }
        .order-form-select2 + .select2-container .select2-selection__rendered {
            color: rgb(30 41 59);
            font-size: 13px;
            line-height: 1.3;
            padding-left: 10px;
            padding-right: 24px;
        }
        .order-form-select2 + .select2-container .select2-selection__arrow {
            height: 100%;
            right: 6px;
        }
        .select2-dropdown.order-form-dropdown {
            border-color: rgb(203 213 225);
            border-radius: 2px;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid rgb(203 213 225);
            border-radius: 2px;
            padding: 6px 8px;
            font-size: 12px;
        }
    </style>
@endif
@endsection

@section('content')
@php
    $currentPanel = $panel ?? 'dashboard';
    $currency = $settings->currency_icon ?? '$';
    $heroTitle = $currentPanel === 'orders' ? 'Orders' : 'MY ACCOUNT';
@endphp

<section class="relative border-b border-slate-200 bg-gradient-to-b from-slate-100 to-slate-50 overflow-hidden">
    <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 12% 25%, #d1d5db 0, transparent 35%), radial-gradient(circle at 88% 20%, #e5e7eb 0, transparent 35%), radial-gradient(circle at 80% 82%, #d1d5db 0, transparent 30%);"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative text-center">
        <h1 class="text-4xl font-black tracking-tight text-slate-900 uppercase">{{ $heroTitle }}</h1>
        <p class="text-sm text-slate-500 mt-2">Home @if($currentPanel !== 'dashboard') > MY ACCOUNT @endif</p>
    </div>
</section>

<section class="bg-slate-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <aside class="lg:col-span-4 xl:col-span-3 space-y-4">
                <div class="bg-white border border-slate-200 rounded-sm p-4">
                    <div class="flex items-center gap-3">
                        @if(!empty($user->image))
                            <img src="{{ asset($user->image) }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-sm object-cover border border-slate-200">
                        @else
                            <div class="w-14 h-14 rounded-sm bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5.121 17.804A9 9 0 1118.879 17.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-lg font-semibold text-slate-900 truncate tracking-[0.05em]">{{ $user->name }}</p>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-[11px] tracking-[0.08em] font-semibold text-slate-500 hover:text-rose-600">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-sm overflow-hidden">
                    @php
                        $menuBase = 'flex items-center px-4 py-3 border-b border-slate-200 text-[12px] tracking-[0.08em] font-semibold transition-colors';
                        $menuActive = 'bg-slate-100 text-slate-900';
                        $menuIdle = 'text-slate-500 hover:bg-slate-50';
                    @endphp
                    <a href="{{ route('account.index', ['panel' => 'dashboard']) }}" class="{{ $menuBase }} {{ $currentPanel === 'dashboard' ? $menuActive : $menuIdle }}">Control Panel</a>
                    <a href="{{ route('account.index', ['panel' => 'orders']) }}" class="{{ $menuBase }} {{ $currentPanel === 'orders' ? $menuActive : $menuIdle }}">Orders</a>
                    {{-- <a href="{{ route('account.index', ['panel' => 'downloads']) }}" class="{{ $menuBase }} {{ $currentPanel === 'downloads' ? $menuActive : $menuIdle }}">Downloads</a> --}}
                    {{-- <a href="{{ route('account.index', ['panel' => 'addresses']) }}" class="{{ $menuBase }} {{ $currentPanel === 'addresses' ? $menuActive : $menuIdle }}">Addresses</a> --}}
                    @if(($user->hasRole('Outlet User') || $user->hasRole('User')) && $hasProductRequests)
                        <a href="{{ route('account.index', ['panel' => 'product-requests']) }}" class="{{ $menuBase }} {{ $currentPanel === 'product-requests' ? $menuActive : $menuIdle }}">Product Requests</a>
                    @endif
                    <a href="{{ route('account.index', ['panel' => 'order-form']) }}" class="{{ $menuBase }} {{ $currentPanel === 'order-form' ? $menuActive : $menuIdle }}">Order Form</a>
                    @if($user->hasRole('Outlet User') || $user->hasRole('User'))
                        <a href="{{ route('account.index', ['panel' => 'custom-requests']) }}" class="{{ $menuBase }} {{ $currentPanel === 'custom-requests' ? $menuActive : $menuIdle }}">Custom Product Request</a>
                    @endif
                    <a href="{{ route('account.index', ['panel' => 'saved-forms']) }}" class="{{ $menuBase }} {{ $currentPanel === 'saved-forms' ? $menuActive : $menuIdle }}">Saved Forms</a>
                    {{-- <a href="{{ route('wishlist.index') }}" class="{{ $menuBase }} {{ $menuIdle }}">Shopping List</a> --}}
                    <a href="{{ route('account.index', ['panel' => 'profile']) }}" class="{{ $menuBase }} {{ $currentPanel === 'profile' ? $menuActive : $menuIdle }}">Account Information</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-[12px] tracking-[0.08em] font-semibold text-slate-500 hover:bg-slate-50">Log Out</button>
                    </form>
                </div>

                @if($recentOrders->isNotEmpty())
                    <div class="bg-white border border-slate-200 rounded-sm p-3">
                        <p class="text-[11px] uppercase tracking-[0.14em] font-black text-slate-400 mb-2">Recent Orders</p>
                        <div class="space-y-2 max-h-64 overflow-auto pr-1">
                            @foreach($recentOrders as $rOrder)
                                <a href="{{ route('account.index', ['panel' => 'orders', 'page' => 1]) }}" class="block border border-slate-100 rounded-sm px-2 py-2 hover:bg-slate-50">
                                    <p class="text-xs font-black text-slate-800">{{ $rOrder->order_no }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $rOrder->created_at?->format('d M Y') }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>

            <div class="lg:col-span-8 xl:col-span-9">
                @if($currentPanel === 'orders')
                    <div class="bg-white border border-slate-200 rounded-sm p-4 md:p-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-4">
                            <div>
                                <h2 class="text-4xl font-light text-slate-900 uppercase tracking-wide">Orders</h2>
                                <p class="text-xs text-slate-500 mt-1">Search by order no or status.</p>
                            </div>
                            <form id="orders-search-form" method="GET" action="{{ route('account.index') }}" class="flex items-center gap-2">
                                <input type="hidden" name="panel" value="orders">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..."
                                    class="w-full md:w-64 rounded-sm border border-slate-300 px-3 py-2 text-xs uppercase tracking-[0.12em] font-bold text-slate-700 placeholder:text-slate-400 focus:border-slate-900 focus:outline-none">
                                <button type="button" id="orders-search-reset" class="px-4 py-2 border border-slate-300 text-slate-700 text-xs font-black uppercase tracking-[0.12em] hover:bg-slate-100">
                                    Reset
                                </button>
                            </form>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[780px]">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-sm uppercase tracking-[0.12em] text-slate-600">
                                        <th class="py-3 pr-4 font-black">Order</th>
                                        <th class="py-3 px-4 font-black">Date</th>
                                        <th class="py-3 px-4 font-black">Status</th>
                                        <th class="py-3 px-4 font-black">Total</th>
                                        <th class="py-3 pl-4 font-black">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="orders-tbody">
                                    @include('frontend.pages.account.partials.orders_rows', ['orders' => $orders, 'currency' => $currency])
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div id="orders-summary" class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-600">
                                @include('frontend.pages.account.partials.orders_summary', ['orders' => $orders])
                            </div>
                            <div id="orders-pagination">
                                @include('frontend.pages.account.partials.orders_pagination', ['orders' => $orders])
                            </div>
                        </div>
                    </div>
                @elseif($currentPanel === 'product-requests')
                    <div class="bg-white border border-slate-200 rounded-sm p-4 md:p-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-4">
                            <div>
                                <h2 class="text-3xl font-light text-slate-900 uppercase tracking-wide">Product Requests</h2>
                                <p class="text-xs text-slate-500 mt-1">Your previous product requests.</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[780px]">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-[0.12em] text-slate-600">
                                        <th class="py-3 pr-4 font-black">Request No</th>
                                        <th class="py-3 px-4 font-black">Qty</th>
                                        <th class="py-3 px-4 font-black">Total</th>
                                        <th class="py-3 px-4 font-black">Status</th>
                                        <th class="py-3 px-4 font-black">Date</th>
                                        <th class="py-3 pl-4 font-black text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productRequests as $pRequest)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-3 pr-4 text-sm font-semibold text-slate-800">{{ $pRequest->request_no }}</td>
                                            <td class="py-3 px-4 text-sm text-slate-700">{{ (int) $pRequest->total_qty }}</td>
                                            <td class="py-3 px-4 text-sm text-slate-700">{{ $currency }}{{ number_format((float) $pRequest->total_amount, 2) }}</td>
                                            <td class="py-3 px-4 text-sm">
                                                <span class="px-2 py-1 text-xs font-bold rounded-sm
                                                    {{ $pRequest->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                    {{ $pRequest->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}
                                                    {{ $pRequest->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                                                    {{ ucfirst($pRequest->status) }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-slate-700">{{ $pRequest->created_at?->format('d M Y') }}</td>
                                            <td class="py-3 pl-4 text-right text-[11px] uppercase tracking-[0.12em] font-black">
                                                <a href="{{ route('product-requests.show', $pRequest->id) }}" class="text-slate-700 hover:text-indigo-600">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-6 text-center text-sm text-slate-500">No product requests yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($productRequests, 'links'))
                            <div class="mt-4">
                                {{ $productRequests->links('vendor.pagination.tailwind') }}
                            </div>
                        @endif
                    </div>
                @elseif($currentPanel === 'order-form')
                    <div class="space-y-6">
                        <div class="bg-white border border-slate-200 rounded-sm p-4 md:p-6" x-data="orderFormPanel(@js($productsForOrderForm), @js($reorderSeedRows), @js($currency))">
                            <h2 class="text-4xl font-light text-slate-900 mb-3 uppercase tracking-wide">Order Form</h2>

                            <div class="border border-slate-300 rounded-sm overflow-hidden">
                                <div class="bg-slate-500 text-white text-sm px-3 py-2 font-bold">Order form</div>
                                <div class="p-3 bg-slate-100/70">
                                    <div class="grid grid-cols-12 gap-3 text-xs uppercase tracking-[0.12em] font-black text-slate-600 mb-2">
                                        <div class="col-span-5">Search/Product</div>
                                        <div class="col-span-3">Variant</div>
                                        <div class="col-span-2 text-center">Number</div>
                                        <div class="col-span-2 text-right">Total</div>
                                    </div>

                                    <div class="space-y-2">
                                        <template x-for="(row, idx) in rows" :key="idx">
                                            <div class="grid grid-cols-12 gap-3 items-center" :data-order-row="idx">
                                                <div class="col-span-5">
                                                    <select x-model.number="row.product_id"
                                                            @change="onProductChange(idx)"
                                                            class="order-form-select2 order-form-product-select w-full border border-slate-300 rounded-sm bg-white px-3 py-2 text-sm text-slate-800"
                                                            :data-row-index="idx"
                                                            data-placeholder="Search product">
                                                        <option value="">Select product</option>
                                                        <template x-for="p in products" :key="p.id">
                                                            <option :value="p.id" x-text="p.name"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="col-span-3">
                                                    <select x-model.number="row.variant_id"
                                                            @change="syncRow(idx)"
                                                            :disabled="!row.product_id || rowVariantOptions(idx).length === 0"
                                                            class="order-form-select2 order-form-variant-select w-full border border-slate-300 rounded-sm bg-white px-2 py-2 text-xs text-slate-800 disabled:bg-slate-100 disabled:text-slate-400"
                                                            :data-row-index="idx"
                                                            data-placeholder="Select variant">
                                                        <option value="" x-text="rowVariantOptions(idx).length ? 'Select variant' : 'No variant available'"></option>
                                                        <template x-for="v in rowVariantOptions(idx)" :key="v.id">
                                                            <option :value="v.id" x-text="variantLabel(v)"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="col-span-2">
                                                    <input type="number" min="0" x-model.number="row.qty" @input="syncRow(idx)" class="w-full border border-slate-300 rounded-sm bg-white px-2 py-2 text-sm text-center text-slate-800">
                                                </div>
                                                <div class="col-span-2 text-right text-sm font-semibold text-slate-700" x-text="formatMoney(row.line_total)"></div>
                                            </div>
                                        </template>
                                    </div>

                                    <button type="button" @click="addRow()" class="mt-3 px-3 py-1.5 bg-slate-500 text-white text-xs uppercase tracking-[0.12em] font-bold rounded-sm hover:bg-slate-600">+Add Product</button>

                                    <div class="mt-4 pt-3 border-t border-slate-300 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                    @click="addAllToCart()"
                                                    :disabled="isAddingToCart"
                                                    :class="isAddingToCart ? 'bg-rose-400 cursor-wait' : 'bg-rose-700 hover:bg-rose-800'"
                                                    class="px-4 py-2 text-white text-sm font-black uppercase tracking-[0.12em] rounded-sm transition-colors">
                                                <span x-text="isAddingToCart ? 'Adding...' : 'Add To Cart'"></span>
                                            </button>
                                            <button type="button"
                                                    @click="savePurchaseForm()"
                                                    :disabled="isSavingForm"
                                                    :class="isSavingForm ? 'bg-slate-400 cursor-wait' : 'bg-slate-700 hover:bg-slate-800'"
                                                    class="px-4 py-2 text-white text-sm font-black uppercase tracking-[0.12em] rounded-sm transition-colors">
                                                <span x-text="isSavingForm ? 'Saving...' : 'Save Purchase Form'"></span>
                                            </button>
                                        </div>
                                        <p class="text-2xl font-light text-slate-900">Total: <span class="font-black" x-text="formatMoney(grandTotal)"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($currentPanel === 'custom-requests')
                    <div class="space-y-6">
                        <div class="bg-white border border-slate-200 rounded-sm p-4 md:p-6">
                            <h2 class="text-3xl font-light text-slate-900 mb-2 uppercase tracking-wide">Custom Product Request</h2>
                            <p class="text-sm text-slate-500 mb-6">Tell us what product you need. We will review and contact you.</p>
                            <form method="POST" action="{{ route('account.custom-product-requests.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @csrf
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Product Name (Optional)</label>
                                    <input type="text" name="product_name" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm" placeholder="Product name if you know it" value="{{ old('product_name') }}">
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Quantity Needed</label>
                                    <input type="number" name="quantity_needed" min="1" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm" value="{{ old('quantity_needed', 1) }}" required>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Expected Price (Optional)</label>
                                    <input type="number" name="expected_price" min="0" step="0.01" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm" value="{{ old('expected_price') }}">
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Example Image (Optional)</label>
                                    <input type="file" name="example_image[]" id="custom_request_images" multiple accept="image/*"
                                           class="w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 border-1 border-slate-300">
                                    <div id="custom_request_preview" class="mt-3 flex flex-wrap gap-2"></div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Product Description</label>
                                    <textarea name="product_description" rows="4" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm" required>{{ old('product_description') }}</textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <button type="submit" class="px-6 py-2 bg-slate-900 text-white text-xs font-semibold uppercase tracking-wider">Submit Request</button>
                                </div>
                            </form>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-sm p-4 md:p-6">
                            <h3 class="text-xl font-semibold text-slate-800 mb-4">My Requests</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[780px]">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-[0.12em] text-slate-600">
                                            <th class="py-3 pr-4 font-black">Request No</th>
                                            <th class="py-3 px-4 font-black">Product</th>
                                            <th class="py-3 px-4 font-black">Qty</th>
                                            <th class="py-3 px-4 font-black">Status</th>
                                            <th class="py-3 px-4 font-black">Date</th>
                                            <th class="py-3 pl-4 font-black text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customProductRequests as $request)
                                            <tr class="border-b border-slate-100">
                                                <td class="py-3 pr-4 text-sm font-semibold text-slate-800">{{ $request->request_no }}</td>
                                                <td class="py-3 px-4 text-sm text-slate-700">{{ $request->product_name ?: 'Custom Product' }}</td>
                                                <td class="py-3 px-4 text-sm text-slate-700">{{ $request->quantity_needed }}</td>
                                                <td class="py-3 px-4 text-sm">
                                                    <span class="px-2 py-1 text-xs font-bold rounded-sm
                                                        {{ $request->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                        {{ $request->status === 'rejected' ? 'bg-rose-100 text-rose-700' : '' }}
                                                        {{ $request->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                                                        {{ ucfirst($request->status) }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 text-sm text-slate-700">{{ $request->created_at?->format('d M Y') }}</td>
                                                <td class="py-3 pl-4 text-right text-[11px] uppercase tracking-[0.12em] font-black">
                                                    <a href="{{ route('account.custom-product-requests.show', $request->id) }}" class="text-slate-700 hover:text-indigo-600">View</a>
                                                    <span class="text-slate-300 mx-1">|</span>
                                                    <form method="POST" action="{{ route('account.custom-product-requests.reorder', $request->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-slate-700 hover:text-indigo-600">Reorder</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="py-6 text-center text-sm text-slate-500">No custom requests yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if(method_exists($customProductRequests, 'links'))
                                <div class="mt-4">
                                    {{ $customProductRequests->links('vendor.pagination.tailwind') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($currentPanel === 'saved-forms')
                    <div class="bg-white border border-slate-200 rounded-sm p-4 md:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
                            <div>
                                <h2 class="text-3xl font-light text-slate-900 uppercase tracking-wide">Saved Purchase Forms</h2>
                                <p class="text-sm text-slate-500">Load a saved form, then add to cart to checkout.</p>
                            </div>
                            <p class="text-xs uppercase tracking-[0.12em] font-black text-slate-400">Latest {{ method_exists($savedPurchaseForms, 'total') ? (int) $savedPurchaseForms->total() : (int) ($savedPurchaseForms->count() ?? 0) }} forms</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[720px]">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-[0.12em] text-slate-500">
                                        <th class="py-2 pr-3 font-black">Request</th>
                                        <th class="py-2 px-3 font-black">Date</th>
                                        <th class="py-2 px-3 font-black text-center">Rows</th>
                                        <th class="py-2 px-3 font-black text-center">Qty</th>
                                        <th class="py-2 px-3 font-black text-right">Total</th>
                                        <th class="py-2 px-3 font-black">Status</th>
                                        <th class="py-2 pl-3 font-black text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($savedPurchaseForms as $savedForm)
                                        @php
                                            $savedStatus = strtolower((string) $savedForm->status);
                                            $isSelectedSaved = ((int) $selectedSavedRequestId === (int) $savedForm->id);
                                        @endphp
                                        <tr class="border-b border-slate-100 {{ $isSelectedSaved ? 'bg-sky-50' : '' }}">
                                            <td class="py-2 pr-3">
                                                <p class="text-sm font-semibold text-slate-800">{{ $savedForm->request_no }}</p>
                                            </td>
                                            <td class="py-2 px-3 text-sm text-slate-700">{{ $savedForm->created_at?->format('d M Y, h:i A') }}</td>
                                            <td class="py-2 px-3 text-sm text-center text-slate-700">{{ (int) ($savedForm->items_count ?? 0) }}</td>
                                            <td class="py-2 px-3 text-sm text-center text-slate-700">{{ (int) ($savedForm->total_qty ?? 0) }}</td>
                                            <td class="py-2 px-3 text-sm text-right font-semibold text-slate-800">{{ $currency }}{{ number_format((float) ($savedForm->total_amount ?? 0), 2) }}</td>
                                            <td class="py-2 px-3">
                                                <span class="text-[11px] font-black px-2 py-1 rounded
                                                    {{ $savedStatus === 'approved' ? 'bg-sky-100 text-sky-700' : '' }}
                                                    {{ $savedStatus === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}
                                                    {{ $savedStatus === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                                    {{ $savedStatus === 'saved' ? 'bg-slate-200 text-slate-700' : '' }}">
                                                    {{ ucfirst((string) $savedForm->status) }}
                                                </span>
                                            </td>
                                            <td class="py-2 pl-3 text-right">
                                                <div class="inline-flex items-center gap-2">
                                                    <a href="{{ route('account.index', ['panel' => 'order-form', 'saved' => $savedForm->id]) }}"
                                                       class="inline-flex items-center px-3 py-1.5 border border-slate-300 rounded-sm text-xs font-black uppercase tracking-[0.12em] text-slate-700 hover:bg-slate-100">
                                                        Load To Form
                                                    </a>
                                                    <form method="POST" action="{{ route('account.saved-forms.checkout', $savedForm->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-emerald-700 bg-emerald-700 rounded-sm text-xs font-black uppercase tracking-[0.12em] text-white hover:bg-emerald-800">
                                                            Checkout
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('account.saved-forms.delete', $savedForm->id) }}" class="inline" onsubmit="return confirm('Delete this saved form?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-rose-700 bg-rose-700 rounded-sm text-xs font-black uppercase tracking-[0.12em] text-white hover:bg-rose-800">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-8 text-center text-sm text-slate-500">No saved purchase forms yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($savedPurchaseForms, 'links'))
                            <div class="mt-4">
                                {{ $savedPurchaseForms->links('vendor.pagination.tailwind') }}
                            </div>
                        @endif
                    </div>
                @elseif($currentPanel === 'profile')
                    <div class="space-y-5">
                        <div class="bg-white border border-slate-200 rounded-sm p-5">
                            <h2 class="text-3xl font-light text-slate-900 mb-1 uppercase tracking-wide">Account Information</h2>
                            <p class="text-sm text-slate-500 mb-5">Manage your profile details and keep contact information updated.</p>

                            <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="profile_image" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Profile Image</label>
                                    <div class="flex items-start gap-3">
                                        @if(!empty($user->image))
                                            <img src="{{ asset($user->image) }}" alt="{{ $user->name }}" class="w-12 h-12 rounded-sm object-cover border border-slate-200">
                                        @endif
                                        <div class="flex-1">
                                            <input id="profile_image" name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-700 file:mr-3 file:px-3 file:py-1.5 file:border-0 file:bg-slate-200 file:text-slate-700 file:rounded-sm">
                                            <div id="profile_image_preview" class="mt-3 hidden">
                                                <div class="relative inline-block">
                                                    <img id="profile_image_preview_img" src="" alt="Selected profile image preview" class="h-24 w-24 rounded-full object-cover border border-slate-200">
                                                    <button type="button" id="profile_image_clear" class="absolute -top-2 -right-2 h-6 w-6 rounded-full bg-rose-600 text-white text-xs font-bold leading-none shadow hover:bg-rose-700" aria-label="Remove selected image">×</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @error('image', 'profileUpdate')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="profile_name" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Name</label>
                                        <input id="profile_name" name="name" type="text" value="{{ old('name', $user->name) }}" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('name', 'profileUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="profile_email" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Email</label>
                                        <input id="profile_email" name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('email', 'profileUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="profile_phone" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Phone</label>
                                        <input id="profile_phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('phone', 'profileUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="profile_outlet_name" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Outlet / Shop</label>
                                        <input id="profile_outlet_name" name="outlet_name" type="text" value="{{ old('outlet_name', $user->outlet_name) }}" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('outlet_name', 'profileUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- <div>
                                    <label for="profile_address" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Address</label>
                                    <textarea id="profile_address" name="address" rows="3" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('address', $user->address) }}</textarea>
                                    @error('address', 'profileUpdate')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div> --}}

                                <div class="pt-2">
                                    <button type="submit" class="px-5 py-2 bg-slate-900 text-white text-xs font-black uppercase tracking-[0.12em] rounded-sm hover:bg-slate-800">Save Changes</button>
                                </div>
                            </form>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-sm p-5">
                            <h3 class="text-2xl font-light text-slate-900 mb-1 uppercase tracking-wide">Change Password</h3>
                            <p class="text-sm text-slate-500 mb-5">Use a strong password with at least 8 characters.</p>

                            <form method="POST" action="{{ route('account.password.update') }}" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label for="current_password" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Current Password</label>
                                        <input id="current_password" name="current_password" type="password" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('current_password', 'passwordUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="password" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">New Password</label>
                                        <input id="password" name="password" type="password" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('password', 'passwordUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="password_confirmation" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Confirm Password</label>
                                        <input id="password_confirmation" name="password_confirmation" type="password" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="px-5 py-2 bg-rose-700 text-white text-xs font-black uppercase tracking-[0.12em] rounded-sm hover:bg-rose-800">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-white border border-slate-200 rounded-sm p-5 mb-4">
                        <p class="text-lg text-slate-800">
                            Hello <span class="font-black">{{ $user->name }}</span>
                            <span class="text-sm text-slate-500">(if you are not {{ $user->name }}, please log out)</span>
                        </p>
                        <p class="text-sm text-slate-600 mt-3 leading-7">
                            In the "My Account" control panel, you can view your recent orders, manage shipping and billing address, and edit account information.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="{{ route('account.index', ['panel' => 'orders']) }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Orders</p>
                        </a>
                        {{-- <a href="{{ route('account.index', ['panel' => 'downloads']) }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Downloads</p>
                        </a> --}}
                        {{-- <a href="{{ route('account.index', ['panel' => 'addresses']) }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Addresses</p>
                        </a> --}}
                        <a href="{{ route('account.index', ['panel' => 'order-form']) }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Order form</p>
                        </a>
                        {{-- <a href="{{ route('wishlist.index') }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Shopping list</p>
                        </a> --}}
                        <a href="{{ route('account.index', ['panel' => 'profile']) }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Account information</p>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
@php
    $accountToasts = [];
    if (session('success_profile')) {
        $accountToasts[] = ['type' => 'success', 'message' => session('success_profile')];
    }
    if (session('error_profile')) {
        $accountToasts[] = ['type' => 'error', 'message' => session('error_profile')];
    }
    if (session('success_password')) {
        $accountToasts[] = ['type' => 'success', 'message' => session('success_password')];
    }
    if ($errors->getBag('profileUpdate')->any()) {
        foreach ($errors->getBag('profileUpdate')->all() as $msg) {
            $accountToasts[] = ['type' => 'error', 'message' => $msg];
        }
    }
    if ($errors->getBag('passwordUpdate')->any()) {
        foreach ($errors->getBag('passwordUpdate')->all() as $msg) {
            $accountToasts[] = ['type' => 'error', 'message' => $msg];
        }
    }
@endphp
@if(($panel ?? '') === 'order-form')
    <script src="{{ asset('backend/assets/modules/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/assets/modules/select2/dist/js/select2.full.min.js') }}"></script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('custom_request_images');
        const preview = document.getElementById('custom_request_preview');
        if (input && preview) {
            const renderCustomRequestPreviews = () => {
                preview.innerHTML = '';
                const files = Array.from(input.files || []);
                files.forEach((file, index) => {
                    if (!file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'relative inline-block';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Preview';
                        img.className = 'h-16 w-16 rounded-full object-cover border border-slate-200';

                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.textContent = '×';
                        btn.setAttribute('aria-label', 'Remove selected image');
                        btn.className = 'absolute -top-2 -right-2 h-6 w-6 rounded-full bg-rose-600 text-white text-xs font-bold leading-none shadow hover:bg-rose-700';
                        btn.addEventListener('click', () => {
                            const dt = new DataTransfer();
                            const currentFiles = Array.from(input.files || []);
                            currentFiles.forEach((f, i) => {
                                if (i !== index) dt.items.add(f);
                            });
                            input.files = dt.files;
                            renderCustomRequestPreviews();
                        });

                        wrapper.appendChild(img);
                        wrapper.appendChild(btn);
                        preview.appendChild(wrapper);
                    };
                    reader.readAsDataURL(file);
                });
            };

            input.addEventListener('change', renderCustomRequestPreviews);
        }

        const profileInput = document.getElementById('profile_image');
        const profilePreview = document.getElementById('profile_image_preview');
        const profilePreviewImg = document.getElementById('profile_image_preview_img');
        const profileClear = document.getElementById('profile_image_clear');
        if (profileInput && profilePreview && profilePreviewImg && profileClear) {
            const clearProfilePreview = () => {
                profileInput.value = '';
                profilePreviewImg.src = '';
                profilePreview.classList.add('hidden');
            };

            profileInput.addEventListener('change', function () {
                const file = profileInput.files?.[0];
                if (!file || !file.type?.startsWith('image/')) {
                    clearProfilePreview();
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    profilePreviewImg.src = e.target.result;
                    profilePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            });

            profileClear.addEventListener('click', clearProfilePreview);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const accountToasts = @json($accountToasts);
        if (!Array.isArray(accountToasts) || !accountToasts.length) return;

        const bodyEl = document.querySelector('[x-data*="globalApp"]');
        const globalApp = bodyEl?._x_dataStack?.[0];
        if (!globalApp?.notify) return;

        accountToasts.forEach((toast, idx) => {
            setTimeout(() => {
                globalApp.notify(toast.message, toast.type || 'success');
            }, 150 * (idx + 1));
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('orders-search-form');
        const resetBtn = document.getElementById('orders-search-reset');
        const tbody = document.getElementById('orders-tbody');
        const summary = document.getElementById('orders-summary');
        const pagination = document.getElementById('orders-pagination');

        if (!form || !tbody || !summary || !pagination) return;

        const fetchOrders = async (url) => {
            try {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) {
                    return;
                }

                const data = await res.json();
                tbody.innerHTML = data.tbody || '';
                summary.innerHTML = data.summary || '';
                pagination.innerHTML = data.pagination || '';
            } catch (err) {
                console.error(err);
            }
        };

        const buildUrl = () => {
            const params = new URLSearchParams(new FormData(form));
            return form.action + '?' + params.toString();
        };

        let searchTimer = null;
        const triggerSearch = () => {
            if (searchTimer) clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                fetchOrders(buildUrl());
            }, 300);
        };

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            fetchOrders(buildUrl());
        });

        const searchInput = form.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                triggerSearch();
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                const url = form.action + '?panel=orders';
                fetchOrders(url);
            });
        }

        pagination.addEventListener('click', function (e) {
            const link = e.target.closest('a');
            if (!link) return;
            e.preventDefault();
            fetchOrders(link.href);
        });
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('orderFormPanel', (products, initialRows = [], currency = '$') => ({
            products: Array.isArray(products) ? products : [],
            rows: [],
            currency,
            isAddingToCart: false,
            isSavingForm: false,

            init() {
                if (Array.isArray(initialRows) && initialRows.length > 0) {
                    this.rows = initialRows.map((r) => {
                        const pid = parseInt(r.product_id) || null;
                        const vid = parseInt(r.variant_id) || null;
                        const qty = Math.max(0, parseInt(r.qty) || 0);
                        return {
                            product_id: pid,
                            variant_id: vid,
                            qty: qty,
                            unit_price: this.getUnitPrice(pid, vid),
                            line_total: 0,
                        };
                    });
                } else {
                    this.rows = Array.from({ length: 5 }, () => ({
                        product_id: null,
                        variant_id: null,
                        qty: 0,
                        unit_price: 0,
                        line_total: 0,
                    }));
                }
                this.rows.forEach((_, idx) => this.syncRow(idx));
                this.$nextTick(() => this.setupSelect2());
            },

            get grandTotal() {
                return this.rows.reduce((sum, row) => sum + (parseFloat(row.line_total) || 0), 0);
            },

            getProduct(productId) {
                return this.products.find((p) => parseInt(p.id) === parseInt(productId)) || null;
            },

            setupSelect2() {
                const $ = window.jQuery;
                if (!$ || !$.fn?.select2 || !this.$el) return;

                const initOne = (el) => {
                    const $el = $(el);
                    if ($el.hasClass('select2-hidden-accessible')) {
                        this.bindSelect2Events(el);
                        return;
                    }

                    $el.select2({
                        width: '100%',
                        dropdownCssClass: 'order-form-dropdown',
                        placeholder: el.dataset.placeholder || 'Select',
                        allowClear: false,
                    });
                    this.bindSelect2Events(el);
                };

                this.$el.querySelectorAll('.order-form-select2').forEach(initOne);
                this.$nextTick(() => this.syncSelect2Values());
            },

            bindSelect2Events(el) {
                const $ = window.jQuery;
                if (!$ || !$.fn?.select2 || !el) return;

                const $el = $(el);
                $el.off('.orderFormSelect2');
                $el.on('select2:select.orderFormSelect2 select2:clear.orderFormSelect2', () => {
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                });
            },

            rebuildSelect2ForRow(index) {
                const $ = window.jQuery;
                if (!$ || !$.fn?.select2 || !this.$el) return;

                const rowEl = this.$el.querySelector(`[data-order-row="${index}"]`);
                if (!rowEl) return;

                rowEl.querySelectorAll('.order-form-select2').forEach((el) => {
                    const $el = $(el);
                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.select2('destroy');
                    }
                });

                this.$nextTick(() => {
                    rowEl.querySelectorAll('.order-form-select2').forEach((el) => {
                        const $el = $(el);
                        $el.select2({
                            width: '100%',
                            dropdownCssClass: 'order-form-dropdown',
                            placeholder: el.dataset.placeholder || 'Select',
                            allowClear: false,
                        });
                        this.bindSelect2Events(el);
                    });
                    this.syncSelect2Values(index);
                });
            },

            syncSelect2Values(index = null) {
                const $ = window.jQuery;
                if (!$ || !$.fn?.select2 || !this.$el) return;

                const syncElement = (el, row) => {
                    if (!el || !row) return;
                    const $el = $(el);
                    if (!$el.hasClass('select2-hidden-accessible')) return;

                    let value = '';
                    if (el.classList.contains('order-form-product-select')) {
                        value = row.product_id ? String(parseInt(row.product_id)) : '';
                    } else if (el.classList.contains('order-form-variant-select')) {
                        value = row.variant_id ? String(parseInt(row.variant_id)) : '';
                    }

                    $el.val(value).trigger('change.select2');
                };

                if (index !== null) {
                    const idx = parseInt(index);
                    const row = this.rows[idx];
                    const rowEl = this.$el.querySelector(`[data-order-row="${idx}"]`);
                    if (!row || !rowEl) return;
                    syncElement(rowEl.querySelector('.order-form-product-select'), row);
                    syncElement(rowEl.querySelector('.order-form-variant-select'), row);
                    return;
                }

                this.rows.forEach((row, idx) => {
                    const rowEl = this.$el.querySelector(`[data-order-row="${idx}"]`);
                    if (!rowEl) return;
                    syncElement(rowEl.querySelector('.order-form-product-select'), row);
                    syncElement(rowEl.querySelector('.order-form-variant-select'), row);
                });
            },

            rowVariants(index) {
                const row = this.rows[index];
                if (!row || !row.product_id) return [];
                const product = this.getProduct(row.product_id);
                return Array.isArray(product?.variants) ? product.variants : [];
            },

            rowVariantOptions(index) {
                const row = this.rows[index];
                const variants = this.rowVariants(index);
                if (!row) return variants;

                const selectedVariantId = parseInt(row.variant_id) || null;
                if (!selectedVariantId) return variants;
                if (variants.some((v) => parseInt(v.id) === selectedVariantId)) return variants;

                return [
                    {
                        id: selectedVariantId,
                        label: `Saved variant #${selectedVariantId} (Unavailable)`,
                        price: 0,
                        unavailable: true,
                    },
                    ...variants,
                ];
            },

            onProductChange(index) {
                const row = this.rows[index];
                if (!row) return;

                const variants = this.rowVariants(index);

                if (!variants.length) {
                    row.variant_id = null;
                    this.syncRow(index);
                    this.$nextTick(() => this.rebuildSelect2ForRow(index));
                    return;
                }

                const selectedVariantId = parseInt(row.variant_id) || null;
                const hasSelectedVariant = variants.some((v) => parseInt(v.id) === selectedVariantId);
                if (!hasSelectedVariant) {
                    row.variant_id = parseInt(variants[0].id) || null;
                }
                this.syncRow(index);
                this.$nextTick(() => this.rebuildSelect2ForRow(index));
            },

            getUnitPrice(productId, variantId = null) {
                const product = this.getProduct(productId);
                if (!product) return 0;

                const variants = Array.isArray(product.variants) ? product.variants : [];
                if (variantId !== null && variantId !== '' && variants.length > 0) {
                    const variant = variants.find((v) => parseInt(v.id) === parseInt(variantId));
                    if (variant) {
                        return parseFloat(variant.price) || 0;
                    }
                }

                return parseFloat(product.price) || 0;
            },

            getMinimumQty(productId) {
                const product = this.getProduct(productId);
                return product ? Math.max(1, parseInt(product.minimum_order_qty) || 1) : 1;
            },

            syncRow(index) {
                const row = this.rows[index];
                if (!row) return;

                if (!row.product_id) {
                    row.variant_id = null;
                    row.unit_price = 0;
                    row.line_total = 0;
                    return;
                }

                const variants = this.rowVariants(index);
                if (!variants.length) {
                    if (!row.variant_id) {
                        row.variant_id = null;
                    }
                } else {
                    const selectedVariantId = parseInt(row.variant_id) || null;
                    const hasSelectedVariant = variants.some((v) => parseInt(v.id) === selectedVariantId);
                    if (!hasSelectedVariant) {
                        row.variant_id = parseInt(variants[0].id) || null;
                    }
                }

                row.unit_price = this.getUnitPrice(row.product_id, row.variant_id);
                const minQty = this.getMinimumQty(row.product_id);
                const qty = Math.max(0, parseInt(row.qty) || 0);
                row.qty = qty > 0 && qty < minQty ? minQty : qty;
                row.line_total = (parseFloat(row.unit_price) || 0) * (parseInt(row.qty) || 0);
                this.$nextTick(() => this.syncSelect2Values(index));
            },

            addRow() {
                this.rows.push({
                    product_id: null,
                    variant_id: null,
                    qty: 0,
                    unit_price: 0,
                    line_total: 0,
                });
                this.$nextTick(() => this.setupSelect2());
            },

            formatMoney(value) {
                const amount = parseFloat(value || 0);
                return `${this.currency}${amount.toFixed(2)}`;
            },

            variantLabel(variant) {
                if (!variant) return '';
                if (variant.unavailable) return variant.label || 'Saved variant (Unavailable)';
                const name = (variant.label || variant.name || '').toString().trim();
                const price = parseFloat(variant.price || 0);
                return `${name || 'Variant'} (${this.currency}${price.toFixed(2)})`;
            },

            normalizeRows() {
                const normalized = [];

                for (let index = 0; index < this.rows.length; index++) {
                    const row = this.rows[index];
                    const rowEl = this.$el?.querySelector(`[data-order-row="${index}"]`);
                    const productSelectEl = rowEl?.querySelector('.order-form-product-select');
                    const variantSelectEl = rowEl?.querySelector('.order-form-variant-select');
                    const qtyInputEl = rowEl?.querySelector('input[type="number"]');

                    const productId = parseInt(row.product_id || productSelectEl?.value) || 0;
                    const qty = parseInt(row.qty || qtyInputEl?.value) || 0;
                    if (!productId || qty <= 0) continue;

                    row.product_id = productId;
                    row.qty = qty;

                    const product = this.getProduct(productId);
                    let fixedQty = qty;
                    let variantId = parseInt(row.variant_id || variantSelectEl?.value) || null;

                    // If product is missing from current frontend list, still send the row.
                    // Backend will do authoritative product/variant validation.
                    if (product) {
                        const minQty = Math.max(1, parseInt(product.minimum_order_qty) || 1);
                        fixedQty = qty > 0 && qty < minQty ? minQty : qty;
                        const variants = Array.isArray(product.variants) ? product.variants : [];

                        if (variants.length > 0) {
                            const validVariant = variants.some((v) => parseInt(v.id) === variantId);
                            if (!validVariant) {
                                return {
                                    error: `Please select a variant for ${product.name}.`,
                                };
                            }
                        } else {
                            variantId = null;
                        }
                    }

                    row.variant_id = variantId;
                    row.qty = fixedQty;

                    normalized.push({
                        product_id: productId,
                        variant_id: variantId,
                        qty: fixedQty,
                    });
                }

                if (!normalized.length) {
                    return { error: 'Please select at least one product with quantity.' };
                }

                const merged = Object.values(
                    normalized.reduce((acc, item) => {
                        const key = `${item.product_id}|${item.variant_id || 0}`;
                        if (!acc[key]) {
                            acc[key] = { ...item };
                        } else {
                            acc[key].qty += item.qty;
                        }
                        return acc;
                    }, {})
                );

                return { items: merged };
            },

            async postJson(url, payload) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json().catch(() => null);
                if (!response.ok || (data && data.success === false)) {
                    throw new Error(data?.message || 'Request failed.');
                }

                return data || { success: true };
            },

            async addAllToCart() {
                if (this.isAddingToCart) return;

                const prepared = this.normalizeRows();
                if (prepared.error) {
                    this.notify(prepared.error, 'warning');
                    return;
                }

                this.isAddingToCart = true;
                try {
                    const data = await this.postJson("{{ route('account.order-form.add-to-cart') }}", {
                        items: prepared.items,
                    });

                    if (window.Alpine && Alpine.store('cart')) {
                        await Alpine.store('cart').loadFromDB();
                    }
                    this.notify(data?.message || 'Products added to cart.', 'success');
                } catch (e) {
                    this.notify(e?.message || 'Failed to add products to cart.', 'error');
                } finally {
                    this.isAddingToCart = false;
                }
            },

            async savePurchaseForm() {
                if (this.isSavingForm) return;

                const prepared = this.normalizeRows();
                if (prepared.error) {
                    this.notify(prepared.error, 'warning');
                    return;
                }

                this.isSavingForm = true;
                try {
                    const data = await this.postJson("{{ route('account.order-form.save') }}", {
                        items: prepared.items,
                    });
                    this.notify(data?.message || 'Purchase form saved successfully.', 'success');
                    if (data?.request_id) {
                        setTimeout(() => {
                            window.location.href = "{{ route('account.index', ['panel' => 'saved-forms']) }}" + `&saved=${data.request_id}`;
                        }, 350);
                    }
                } catch (e) {
                    this.notify(e?.message || 'Failed to save purchase form.', 'error');
                } finally {
                    this.isSavingForm = false;
                }
            },

            notify(message, type = 'success') {
                const bodyEl = document.querySelector('[x-data*=\"globalApp\"]');
                if (bodyEl && bodyEl._x_dataStack && bodyEl._x_dataStack[0]?.notify) {
                    bodyEl._x_dataStack[0].notify(message, type);
                } else {
                    alert(message);
                }
            },
        }));
    });
</script>
@endsection
