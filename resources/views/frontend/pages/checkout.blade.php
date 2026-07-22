@extends('layouts.frontend')
@section('title', 'Checkout')

@section('content')
<div class="min-h-screen bg-slate-50 py-10" x-data="{ shipDifferent: {{ old('ship_different') ? 'true' : 'false' }} }">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900">Checkout</h1>
            <p class="text-sm text-slate-500 mt-2">Review your items and billing details before placing order.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-bold text-slate-900">Product Info</h2>
                        <span class="text-xs font-black uppercase tracking-widest text-indigo-500">Summary</span>
                    </div>

                    @if($cartItems->isEmpty())
                        <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-amber-700 text-sm">
                            Your cart is empty. Please add products before checkout.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($cartItems as $item)
                                <div class="flex items-center justify-between border border-slate-100 rounded-xl p-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-14 h-14 rounded-lg border border-slate-100 object-cover">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-900 truncate">{{ $item['name'] }}</p>
                                            @if(!empty($item['variant_label']))
                                                <p class="text-xs text-slate-500 truncate">Variant: {{ $item['variant_label'] }}</p>
                                            @endif
                                            <p class="text-xs text-slate-500">Qty: {{ $item['quantity'] }}</p>
                                            <p class="text-xs text-slate-500">
                                                Unit: {{ $settings->currency_icon ?? '$' }}{{ number_format($item['display_price'] ?? $item['price'], 2) }}
                                                @if(!empty($item['has_discount']))
                                                    <span class="ml-1 text-slate-400 line-through">{{ $settings->currency_icon ?? '$' }}{{ number_format($item['original_price'] ?? $item['price'], 2) }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-slate-900">
                                            {{ $settings->currency_icon ?? '$' }}{{ number_format($item['line_total_after_discount'] ?? ($item['price'] * $item['quantity']), 2) }}
                                        </p>
                                        @if(!empty($item['has_discount']))
                                            <p class="text-xs font-semibold text-slate-400 line-through">
                                                {{ $settings->currency_icon ?? '$' }}{{ number_format($item['line_total'] ?? ($item['price'] * $item['quantity']), 2) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Shipping & Billing Info</h2>

                    <form id="checkout-form" action="{{ route('checkout.place-order') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ship_different" :value="shipDifferent ? 1 : 0">
                        @php($resolvedSavedFormId = old('saved_form_id', $savedFormId ?? null))
                        @if(!empty($resolvedSavedFormId))
                            <input type="hidden" name="saved_form_id" value="{{ $resolvedSavedFormId }}">
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Full Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name', $user->name) }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200"  />
                            </div>
                            
                            {{-- <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $lastName) }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div> --}}
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">PI Email (Optional)</label>
                                <input type="email" name="pi_email" value="{{ old('pi_email') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" placeholder="For Product Info Invoice" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Outlet/Shop Name</label>
                                <input type="text" name="outlet_name" value="{{ old('outlet_name', $user->outlet_name ?? '') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Address</label>
                                <input type="text" name="address" value="{{ old('address', $user->address) }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            {{-- <div class="md:col-span-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Street Address</label>
                                <input type="text" name="street_address" value="{{ old('street_address', $user->address) }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                            </div> --}}
                        </div>

                        <div class="mt-6 border-t border-slate-100 pt-5">
                            <label class="inline-flex items-center gap-3 cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <input type="checkbox" x-model="shipDifferent" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-2 focus:ring-indigo-300" />
                                <span class="text-sm font-semibold text-slate-700">Ship to a different address?</span>
                            </label>
                        </div>

                        <div x-show="shipDifferent" x-transition class="mt-4 rounded-2xl border border-indigo-100 bg-indigo-50/40 p-4 md:p-5">
                            <div class="mb-4">
                                <h3 class="text-sm font-bold text-indigo-700">Shipping Address</h3>
                                <p class="text-xs text-slate-500">Fill this only if shipping address is different from billing address.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">First Name</label>
                                <input type="text" name="shipping_first_name" value="{{ old('shipping_first_name') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200"  />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Last Name</label>
                                <input type="text" name="shipping_last_name" value="{{ old('shipping_last_name') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Email</label>
                                <input type="email" name="shipping_email" value="{{ old('shipping_email') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Phone</label>
                                <input type="text" name="shipping_phone" value="{{ old('shipping_phone') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Street Address</label>
                                <input type="text" name="shipping_street_address" value="{{ old('shipping_street_address') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">City</label>
                                <input type="text" name="shipping_city" value="{{ old('shipping_city') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">State</label>
                                <input type="text" name="shipping_state" value="{{ old('shipping_state') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Zip Code</label>
                                <input type="text" name="shipping_zip_code" value="{{ old('shipping_zip_code') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Country</label>
                                <input type="text" name="shipping_country" value="{{ old('shipping_country') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Outlet/Shop Name</label>
                                <input type="text" name="shipping_outlet_name" value="{{ old('shipping_outlet_name') }}" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-indigo-200" />
                            </div>
                        </div>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-slate-900 mb-5">Order Summary</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-semibold text-slate-900">{{ $settings->currency_icon ?? '$' }}{{ number_format($subtotal, 2) }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">
                                Discount
                                @if(!empty($discountBreakdown['product_rate_label']))
                                    <span class="ml-1 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">{{ $discountBreakdown['product_rate_label'] }}</span>
                                @endif
                            </span>
                            <span class="font-semibold text-emerald-600">-{{ $settings->currency_icon ?? '$' }}{{ number_format((float) ($discountBreakdown['product_discount'] ?? 0), 2) }}</span>
                        </div>

                        {{-- User Level Discount --}}
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">
                                User Level Discount
                                @if(!empty($discountBreakdown['user_rate_label']))
                                    <span class="ml-1 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">{{ $discountBreakdown['user_rate_label'] }}</span>
                                @endif
                            </span>
                            <span class="font-semibold text-indigo-600">-{{ $settings->currency_icon ?? '$' }}{{ number_format((float) ($discountBreakdown['user_discount'] ?? 0), 2) }}</span>
                        </div>
                        @if(((float) ($discountBreakdown['default_discount'] ?? 0) > 0) && ((float) ($discountBreakdown['product_discount'] ?? 0) > 0))
                            <details class="group rounded-lg border border-emerald-200 bg-emerald-50/60 px-3 py-2 text-xs">
                                <summary class="flex cursor-pointer list-none items-center justify-between font-semibold text-emerald-700">
                                    <span>Discount details</span>
                                    <span class="transition-transform group-open:rotate-180">&#9662;</span>
                                </summary>
                                <div class="mt-2 space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">
                                            Default Discount
                                            @if(!empty($discountBreakdown['default_rate_label']))
                                                <span class="text-[11px] text-slate-400">({{ $discountBreakdown['default_rate_label'] }})</span>
                                            @endif
                                        </span>
                                        <span class="font-semibold text-emerald-700">-{{ $settings->currency_icon ?? '$' }}{{ number_format((float) ($discountBreakdown['default_discount'] ?? 0), 2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">
                                            Product Discount
                                            @if(!empty($discountBreakdown['product_rate_label']))
                                                <span class="text-[11px] text-slate-400">({{ $discountBreakdown['product_rate_label'] }})</span>
                                            @endif
                                        </span>
                                        <span class="font-semibold text-emerald-700">-{{ $settings->currency_icon ?? '$' }}{{ number_format((float) ($discountBreakdown['product_discount'] ?? 0), 2) }}</span>
                                    </div>

                                    @if((float) ($discountBreakdown['user_discount'] ?? 0) > 0)
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">
                                            User Discount
                                            @if(!empty($discountBreakdown['user_rate_label']))
                                                <span class="text-[11px] text-slate-400">({{ $discountBreakdown['user_rate_label'] }})</span>
                                            @endif
                                        </span>
                                        <span class="font-semibold text-indigo-700">-{{ $settings->currency_icon ?? '$' }}{{ number_format((float) ($discountBreakdown['user_discount'] ?? 0), 2) }}</span>
                                    </div>
                                    @endif
                                </div>
                            </details>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">
                                Vat
                                @if(!empty($taxBreakdown['total_rate_label']))
                                    <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">{{ $taxBreakdown['total_rate_label'] }}</span>
                                @endif
                            </span>
                            <span class="font-semibold text-slate-900">{{ $settings->currency_icon ?? '$' }}{{ number_format($vatAmount, 2) }}</span>
                        </div>
                        @if(((float) ($taxBreakdown['default_vat'] ?? 0) > 0) && ((float) ($taxBreakdown['product_vat'] ?? 0) > 0))
                            <details class="group rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                                {{-- <summary class="flex cursor-pointer list-none items-center justify-between font-semibold text-slate-600">
                                    <span>Tax details</span>
                                    <span class="transition-transform group-open:rotate-180">&#9662;</span>
                                </summary> --}}
                                <div class="mt-2 space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">
                                            Default VAT
                                            @if(!empty($taxBreakdown['default_rate_label']))
                                                <span class="text-[11px] text-slate-400">({{ $taxBreakdown['default_rate_label'] }})</span>
                                            @endif
                                        </span>
                                        <span class="font-semibold text-slate-700">{{ $settings->currency_icon ?? '$' }}{{ number_format((float) ($taxBreakdown['default_vat'] ?? 0), 2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">
                                            Product VAT
                                            @if(!empty($taxBreakdown['product_rate_label']))
                                                <span class="text-[11px] text-slate-400">({{ $taxBreakdown['product_rate_label'] }})</span>
                                            @endif
                                        </span>
                                        <span class="font-semibold text-slate-700">{{ $settings->currency_icon ?? '$' }}{{ number_format((float) ($taxBreakdown['product_vat'] ?? 0), 2) }}</span>
                                    </div>
                                </div>
                            </details>
                        @endif
                        <div class="border-t border-slate-100 pt-3 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900">Grand Total</span>
                            <span class="text-lg font-black text-indigo-600">{{ $settings->currency_icon ?? '$' }}{{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <button type="submit" form="checkout-form" class="w-full mt-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 transition-colors disabled:opacity-50" {{ $cartItems->isEmpty() ? 'disabled' : '' }}>
                        Place Order
                    </button>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
