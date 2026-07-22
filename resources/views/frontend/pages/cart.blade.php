@extends('layouts.frontend')
@section('title', 'Cart')

@section('content')
    @php
        $currencyIcon = optional($settings)->currency_icon ?? 'Tk';
    @endphp

    <div class="min-h-screen bg-slate-50 py-8 sm:py-10" x-data="cartPage(@js($currencyIcon))">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-[12px] font-semibold uppercase tracking-[0.12em] text-indigo-600">B2B Basket</p>
                    <h1 class="mt-1 text-2xl font-semibold text-slate-900 tracking-[0.04em]">Cart Summary</h1>
                    <p class="mt-1 text-sm text-slate-500">Check MOQ, adjust quantity, then continue to checkout.</p>
                </div>
                <a href="{{ route('shop') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-[11px] font-semibold uppercase tracking-widest text-slate-700 transition hover:border-indigo-300 hover:text-indigo-700">
                    Continue Shopping
                </a>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.65fr_1fr]">
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <template x-if="initializing">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-10 text-center">
                            <p class="text-base font-bold text-slate-800">Loading cart...</p>
                        </div>
                    </template>

                    <template x-if="!initializing && items.length === 0">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-10 text-center">
                            <p class="text-base font-bold text-slate-800">Your cart is empty.</p>
                            <p class="mt-1 text-sm text-slate-500">Add products from the catalog to create a B2B order.</p>
                            <a href="{{ route('shop') }}" class="mt-5 inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white hover:bg-slate-800">Browse Catalog</a>
                        </div>
                    </template>

                    <div class="space-y-3" x-show="!initializing && items.length > 0">
                        <template x-for="item in items" :key="item.id">
                            <article class="rounded-xl border border-slate-200 bg-white p-3 sm:p-4">
                                <div class="grid grid-cols-[84px_1fr] gap-3 sm:grid-cols-[92px_1fr] sm:gap-4">
                                    <img :src="item.image" :alt="item.name" class="h-20 w-20 rounded-lg border border-slate-200 object-cover sm:h-24 sm:w-24">

                                    <div class="flex min-w-0 flex-col">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-900" x-text="item.name"></p>
                                                <p class="text-[11px] text-slate-500" x-text="item.category"></p>
                                                <template x-if="item.variant_label">
                                                    <p class="text-[11px] text-slate-500" x-text="'Variant: ' + item.variant_label"></p>
                                                </template>
                                            </div>

                                            <div class="text-right">
                                                <p class="text-[11px] font-semibold text-slate-500">Line Total</p>
                                                <p class="text-sm font-bold text-slate-900" x-text="formatMoney(parseFloat(item.line_total_after_discount) || 0)"></p>
                                                <template x-if="item.has_discount">
                                                    <p class="mt-0.5 text-[11px] font-semibold text-slate-400 line-through" x-text="formatMoney(parseFloat(item.line_total) || 0)"></p>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="mt-3 grid gap-2 sm:grid-cols-[220px_1fr] sm:items-end">
                                            <div>
                                                <div class="mb-1 flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                                    <span>MOQ: <span class="text-slate-700" x-text="item.minimum_order_qty || 1"></span></span>
                                                    <template x-if="item.available_stock !== undefined && item.available_stock !== null">
                                                        <span>Stock: <span class="text-slate-700" x-text="item.available_stock"></span></span>
                                                    </template>
                                                </div>

                                                <div class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50">
                                                    <button type="button"
                                                        @click="decrease(item)"
                                                        :disabled="isBusy(item.id)"
                                                        class="h-full w-10 text-slate-500 transition hover:text-slate-900 disabled:cursor-not-allowed disabled:text-slate-300">-</button>
                                                    <input type="number"
                                                        :value="item.quantity"
                                                        min="1"
                                                        @change="setQty(item, $event.target.value)"
                                                        @keyup.enter="setQty(item, $event.target.value)"
                                                        class="h-full w-full border-x border-slate-200 bg-white p-0 text-center text-sm font-bold text-slate-900 focus:outline-none">
                                                    <button type="button"
                                                        @click="increase(item)"
                                                        :disabled="isBusy(item.id)"
                                                        class="h-full w-10 text-slate-500 transition hover:text-slate-900 disabled:cursor-not-allowed disabled:text-slate-300">+</button>
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap items-center justify-between gap-2 sm:justify-end">
                                                <div class="text-right">
                                                    <p class="text-[11px] font-semibold text-slate-500" x-text="formatMoney(parseFloat(item.display_price ?? item.price) || 0) + ' / unit'"></p>
                                                    <template x-if="item.has_discount">
                                                        <p class="text-[10px] font-semibold text-slate-400 line-through" x-text="formatMoney(parseFloat(item.original_price ?? item.price) || 0) + ' / unit'"></p>
                                                    </template>
                                                </div>
                                                <button type="button"
                                                    @click="removeItem(item.id)"
                                                    :disabled="isBusy(item.id)"
                                                    class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[10px] font-bold uppercase tracking-[0.12em] text-rose-700 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-60">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </section>

                <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-24 lg:h-fit">
                    <h2 class="text-lg font-bold text-slate-900">Order Summary</h2>

                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Total Units</span>
                            <span class="font-semibold text-slate-900" x-text="totalUnits"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-semibold text-slate-900" x-text="formatMoney(subtotal)"></span>
                        </div>
                        <div class="flex items-center justify-between" x-show="discountTotal > 0">
                            <span class="text-slate-500">Discount</span>
                            <span class="font-semibold text-emerald-600" x-text="'-' + formatMoney(discountTotal)"></span>
                        </div>
                        <div class="flex items-center justify-between" x-show="discountTotal > 0">
                            <span class="text-slate-500">Original Subtotal</span>
                            <span class="font-semibold text-slate-400 line-through" x-text="formatMoney(originalSubtotal)"></span>
                        </div>
                        <p class="text-xs text-slate-400">Tax and shipping are calculated in checkout.</p>
                        <div class="border-t border-slate-200 pt-3 flex items-center justify-between">
                            <span class="font-bold text-slate-900">Payable Now</span>
                            <span class="text-lg font-bold text-indigo-600" x-text="formatMoney(payableNow)"></span>
                        </div>
                    </div>

                    @auth
                        <a href="{{ route('checkout.index') }}"
                            :class="items.length > 0 ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed pointer-events-none'"
                            class="mt-5 inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-xs font-bold uppercase tracking-[0.14em] transition">
                            Proceed To Checkout
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-xs font-bold uppercase tracking-[0.14em] text-white transition hover:bg-slate-800">
                            Login To Checkout
                        </a>
                    @endauth
                </aside>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cartPage', (currencyIcon) => ({
                currencyIcon,
                initializing: true,
                busyIds: [],

                async init() {
                    try {
                        await Alpine.store('cart').ensureHydrated(true);
                    } finally {
                        this.initializing = false;
                    }
                },

                get items() {
                    return Alpine.store('cart').items;
                },

                get subtotal() {
                    return this.items.reduce((sum, item) => {
                        const lineDiscounted = parseFloat(item.line_total_after_discount);
                        if (!Number.isNaN(lineDiscounted)) {
                            return sum + lineDiscounted;
                        }

                        const unit = parseFloat(item.display_price ?? item.price) || 0;
                        const qty = parseInt(item.quantity, 10) || 0;
                        return sum + (unit * qty);
                    }, 0);
                },

                get originalSubtotal() {
                    return this.items.reduce((sum, item) => {
                        const lineOriginal = parseFloat(item.line_total);
                        if (!Number.isNaN(lineOriginal)) {
                            return sum + lineOriginal;
                        }

                        const unit = parseFloat(item.original_price ?? item.price) || 0;
                        const qty = parseInt(item.quantity, 10) || 0;
                        return sum + (unit * qty);
                    }, 0);
                },

                get totalUnits() {
                    return this.items.reduce((sum, item) => sum + (parseInt(item.quantity, 10) || 0), 0);
                },

                get discountTotal() {
                    return Math.max(0, this.originalSubtotal - this.subtotal);
                },

                get payableNow() {
                    return Math.max(0, this.subtotal);
                },

                isBusy(cartId) {
                    return this.busyIds.includes(parseInt(cartId, 10));
                },

                setBusy(cartId) {
                    const id = parseInt(cartId, 10);
                    if (!this.busyIds.includes(id)) {
                        this.busyIds.push(id);
                    }
                },

                clearBusy(cartId) {
                    const id = parseInt(cartId, 10);
                    this.busyIds = this.busyIds.filter((entry) => entry !== id);
                },

                normalizeQty(item, qtyInput) {
                    const moq = Math.max(1, parseInt(item.minimum_order_qty, 10) || 1);
                    const stock = item.available_stock !== undefined && item.available_stock !== null
                        ? Math.max(0, parseInt(item.available_stock, 10) || 0)
                        : null;

                    let qty = Math.max(1, parseInt(qtyInput, 10) || moq);

                    if (qty < moq) {
                        qty = moq;
                    } else if (qty > moq) {
                        qty = Math.ceil(qty / moq) * moq;
                    }

                    if (stock !== null) {
                        const maxAddable = Math.floor(stock / moq) * moq;
                        if (maxAddable <= 0) {
                            return 0;
                        }
                        if (qty > maxAddable) {
                            qty = maxAddable;
                        }
                    }

                    return qty;
                },

                async setQty(item, qtyInput) {
                    const cartId = item.id;
                    if (this.isBusy(cartId)) {
                        return;
                    }

                    const normalized = this.normalizeQty(item, qtyInput);

                    this.setBusy(cartId);
                    try {
                        if (normalized <= 0) {
                            await Alpine.store('cart').removeItem(cartId);
                            this.notify('Item removed (insufficient stock).', 'warning');
                            return;
                        }

                        await Alpine.store('cart').updateQuantity(cartId, normalized);
                    } catch (e) {
                        this.notify(e?.message || 'Failed to update quantity.', 'error');
                    } finally {
                        this.clearBusy(cartId);
                    }
                },

                async increase(item) {
                    const moq = Math.max(1, parseInt(item.minimum_order_qty, 10) || 1);
                    const nextQty = (parseInt(item.quantity, 10) || moq) + moq;
                    await this.setQty(item, nextQty);
                },

                async decrease(item) {
                    const moq = Math.max(1, parseInt(item.minimum_order_qty, 10) || 1);
                    const nextQty = (parseInt(item.quantity, 10) || moq) - moq;

                    if (nextQty < moq) {
                        await this.removeItem(item.id);
                        return;
                    }

                    await this.setQty(item, nextQty);
                },

                async removeItem(cartId) {
                    if (this.isBusy(cartId)) {
                        return;
                    }

                    this.setBusy(cartId);
                    try {
                        await Alpine.store('cart').removeItem(cartId);
                        this.notify('Item removed from cart.', 'warning');
                    } catch (e) {
                        this.notify(e?.message || 'Failed to remove item.', 'error');
                    } finally {
                        this.clearBusy(cartId);
                    }
                },

                formatMoney(value) {
                    const amount = Number(value || 0).toFixed(2);
                    return `${this.currencyIcon}${amount}`;
                },

                notify(message, type = 'success') {
                    const bodyEl = document.querySelector('[x-data*="globalApp"]');
                    if (bodyEl?._x_dataStack?.[0]) {
                        bodyEl._x_dataStack[0].notify(message, type);
                    }
                },
            }));
        });
    </script>
@endsection
