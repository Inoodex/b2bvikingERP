@extends('layouts.frontend')

@section('content')
    @php
        $currencyIcon = optional($settings)->currency_icon ?? 'Tk';
        $siteName = optional($settings)->site_name ?? config('app.name', 'Inventory B2B');
        $roleContext = $roleContext ?? [];
        $isOutletUser = (bool) data_get($roleContext, 'isOutletUser', false);
        $isStandardUser = (bool) data_get($roleContext, 'isStandardUser', false);
        $canViewInventory = (bool) data_get($roleContext, 'canViewInventory', false);
        $displayPath = $displayPath ?? null;
        $productCategoryName = $productCategoryName ?? '';
        // $productBrandName = $productBrandName ?? '';
        $productSku = $productSku ?? '';
        $productNumber = $productNumber ?? '';
        $summaryText = $summaryText ?? null;
        $hasLongDescription = (bool) ($hasLongDescription ?? false);
        $detailProductData = $detailProductData ?? [];
        $detailVariantData = collect($detailVariantData ?? []);
        $isWishlisted = (bool) ($isWishlisted ?? false);
        $relatedCards = collect($relatedCards ?? []);
        $canSubmitReview = $isOutletUser || $isStandardUser;
        $reviewConfig = [
            'productId' => (int) $product->id,
            'listUrl' => route('frontend.reviews.product', $product->id),
            'userReviewUrl' => $canSubmitReview ? route('frontend.reviews.user-product', $product->id) : null,
            'storeUrl' => $canSubmitReview ? route('frontend.reviews.store') : null,
            'deleteUrlTemplate' => $canSubmitReview ? route('frontend.reviews.destroy', ['reviewId' => '__REVIEW_ID__']) : null,
            'canSubmit' => $canSubmitReview,
        ];
    @endphp

    <div class="bg-slate-100 py-6 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            <section class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
                <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-500">
                    <a href="{{ route('home') }}" class="transition hover:text-blue-600">Home</a>
                    <span>/</span>
                    <a href="{{ route('shop') }}" class="transition hover:text-blue-600">Catalog</a>
                    <span>/</span>
                    <span class="line-clamp-1 text-slate-700">{{ $product->name }}</span>
                    <span class="ml-auto hidden text-[10px] uppercase tracking-[0.16em] text-slate-400 sm:inline">{{ $siteName }}</span>
                </div>
            </section>

            <section x-data="productDetail(@js($detailProductData), @js($detailVariantData), @js($isWishlisted))"
                class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="grid gap-5 p-4 sm:p-5 lg:grid-cols-12 lg:gap-6 lg:p-6">
                    <div class="space-y-3.5 lg:col-span-5">
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            @if ($displayPath)
                                <img src="{{ $displayPath }}" alt="{{ $product->name }}" class="aspect-square w-full object-contain">
                            @else
                                <div class="flex aspect-square items-center justify-center">
                                    <span class="rounded-full border border-slate-300 bg-white px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        No image uploaded
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">
                                {{ $productCategoryName !== '' ? $productCategoryName : 'Category not set' }}
                            </span>
                            @if ($detailVariantData->isNotEmpty())
                                <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">
                                    {{ $detailVariantData->count() }} Variants
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-slate-500">MOQ</p>
                                <p class="mt-1 text-sm font-bold text-slate-900">{{ max(1, (int) ($product->minimum_order_qty ?? 1)) }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-slate-500">Stock</p>
                                @if ($canViewInventory)
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="currentStock"></p>
                                @else
                                    <p class="mt-1 text-sm font-semibold text-slate-600">Login required</p>
                                @endif
                            </div>
                            {{-- <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-slate-500">SKU</p>
                                <p class="mt-1 line-clamp-1 text-sm font-semibold text-slate-700">{{ $productSku !== '' ? $productSku : 'Not set' }}</p>
                            </div> --}}
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-slate-500">Product No</p>
                                <p class="mt-1 line-clamp-1 text-sm font-semibold text-slate-700">{{ $productNumber !== '' ? $productNumber : 'Not set' }}</p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Description</p>
                            @if ($hasLongDescription)
                                <div class="prose prose-sm mt-2 max-h-52 max-w-none overflow-auto pr-1 text-slate-600">
                                    {!! $product->long_description !!}
                                </div>
                            @else
                                <p class="mt-2 text-sm text-slate-500">No description provided for this product.</p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4 lg:col-span-7">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-600">B2B Product Profile</p>
                            <h1 class="mt-1.5 text-2xl font-bold leading-tight text-slate-900 sm:text-[30px]">{{ $product->name }}</h1>
                            @if ($summaryText)
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $summaryText }}</p>
                            @endif
                        </div>

                        <div class="grid gap-2 sm:grid-cols-3">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11px] text-slate-700">
                                <span class="font-bold text-slate-800">Category:</span>
                                {{ $productCategoryName !== '' ? $productCategoryName : 'Not set' }}
                            </div>
                            {{-- <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11px] text-slate-700">
                                <span class="font-bold text-slate-800">Brand:</span>
                                {{ $productBrandName !== '' ? $productBrandName : 'Not set' }}
                            </div> --}}
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11px] text-slate-700">
                                <span class="font-bold text-slate-800">Variant count:</span>
                                {{ $detailVariantData->count() }}
                            </div>
                        </div>

                        @auth
                            <div class="rounded-xl border border-slate-200 bg-white p-4 text-slate-900">
                                @if ($isOutletUser)
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Wholesale Price</p>
                                            <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $currencyIcon }}<span x-text="outletDisplayPrice"></span></p>
                                            <template x-if="showOutletOriginalPrice">
                                                <p class="mt-1 text-xs font-semibold text-slate-400 line-through">
                                                    {{ $currencyIcon }}<span x-text="outletOriginalDisplayPrice"></span>
                                                </p>
                                            </template>
                                        </div>
                                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Selling Price</p>
                                            <p class="mt-1 text-xl font-semibold text-slate-800">{{ $currencyIcon }}<span x-text="retailDisplayPrice"></span></p>
                                        </div>
                                    </div>
                                @elseif ($isStandardUser)
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Wholesale Price</p>
                                        <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $currencyIcon }}<span x-text="outletDisplayPrice"></span></p>
                                        <template x-if="showOutletOriginalPrice">
                                            <p class="mt-1 text-xs font-semibold text-slate-400 line-through">
                                                {{ $currencyIcon }}<span x-text="outletOriginalDisplayPrice"></span>
                                            </p>
                                        </template>
                                    </div>
                                @else
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Price</p>
                                        <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $currencyIcon }}<span x-text="retailDisplayPrice"></span></p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-amber-700">Pricing Access</p>
                                <p class="mt-1.5 text-sm text-slate-700">Login as customer account to see wholesale rate and place an order.</p>
                                <a href="{{ route('login') }}"
                                    class="mt-3 inline-flex h-9 items-center justify-center rounded-md bg-slate-900 px-3 text-[11px] font-bold uppercase tracking-[0.12em] text-white transition hover:bg-slate-800">
                                    Login Now
                                </a>
                            </div>
                        @endauth

                        <template x-if="hasVariants">
                            <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                                <div class="mb-2.5 flex items-center justify-between gap-2">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Select Variant</p>
                                    <template x-if="hasVariants && !selectedVariant">
                                        <span class="text-[11px] font-semibold text-amber-700">Required</span>
                                    </template>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="(v, index) in variants" :key="v.id">
                                        <button type="button"
                                            @click="selectVariant(index)"
                                            :disabled="!canSelectVariant(index)"
                                            :class="!canSelectVariant(index)
                                                ? 'border-slate-200 bg-slate-100 text-slate-300 cursor-not-allowed'
                                                : (selectedVariantIndex === String(index)
                                                    ? 'border-blue-400 bg-blue-50 text-blue-700'
                                                    : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-blue-700')"
                                            class="rounded-md border px-2.5 py-1 text-[11px] font-bold leading-none transition-colors">
                                            <span x-text="variantLabel(v)"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        @auth
                            <div class="rounded-xl border border-slate-200 bg-slate-50/90 p-4">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-bold"
                                        :class="stockPillClass" x-text="stockPillText"></span>
                                    <p class="text-[11px] font-semibold text-slate-500">
                                        MOQ: <span x-text="minimumOrderQty"></span>
                                    </p>
                                </div>

                                <div class="grid gap-2.5 sm:grid-cols-[160px_1fr_auto]">
                                    <div class="flex h-10 items-center rounded-md border border-slate-200 bg-white">
                                        <button type="button"
                                            @click="lastRawQty = null; qty = Math.max(minimumOrderQty, (parseInt(qty) || minimumOrderQty) - minimumOrderQty); normalizeQty()"
                                            class="h-full w-9 text-slate-500 transition hover:text-slate-800">
                                            -
                                        </button>
                                        <input type="number"
                                            x-model.number="qty"
                                            :min="minimumOrderQty"
                                            :max="inventoryVisible && maxAddableQty > 0 ? maxAddableQty : minimumOrderQty"
                                            :step="minimumOrderQty"
                                            @input="lastRawQty = $event.target.value"
                                            @change="normalizeQty()"
                                            class="h-full w-full border-x border-slate-200 bg-transparent p-0 text-center text-sm font-bold text-slate-900 focus:outline-none">
                                        <button type="button"
                                            @click="lastRawQty = null; qty = (parseInt(qty) || minimumOrderQty) + minimumOrderQty; normalizeQty()"
                                            class="h-full w-9 text-slate-500 transition hover:text-slate-800">
                                            +
                                        </button>
                                    </div>

                                    <button @click="canAdd ? addToCart() : notify(cannotAddMessage, 'error')"
                                        :disabled="adding"
                                        :class="canAdd
                                            ? 'bg-slate-900 text-white hover:bg-black'
                                            : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                        class="h-10 rounded-md px-4 text-[11px] font-bold uppercase tracking-[0.14em] transition">
                                        <span x-show="!adding">Add To Cart</span>
                                        <span x-show="adding">Adding...</span>
                                    </button>

                                    <button @click="toggleWishlist()"
                                        :disabled="wishlistBusy"
                                        class="h-10 w-10 rounded-md border-2 transition"
                                        :class="isInWishlist
                                            ? 'border-rose-300 bg-rose-50 text-rose-500 hover:bg-rose-100'
                                            : 'border-slate-200 bg-white text-slate-400 hover:border-rose-300 hover:text-rose-500'"
                                        :title="isInWishlist ? 'Remove from wishlist' : 'Add to wishlist'">
                                        <template x-if="isInWishlist">
                                            <svg class="mx-auto h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                                            </svg>
                                        </template>
                                        <template x-if="!isInWishlist">
                                            <svg class="mx-auto h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </template>
                                    </button>
                                </div>

                                <template x-if="inventoryVisible && (!hasVariants || selectedVariant) && maxAddableQty > 0">
                                    <p class="mt-2 text-[11px] font-semibold text-slate-500" x-text="`Max addable: ${maxAddableQty}`"></p>
                                </template>
                            </div>
                        @endauth

                        <div class="grid gap-2 sm:grid-cols-2">
                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold text-slate-600">Verified product quality</div>
                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold text-slate-600">Ready for outlet distribution</div>
                        </div>
                    </div>
                </div>
            </section>

            <section x-data="productReviews(@js($reviewConfig))"
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 lg:p-6">
                <div class="grid gap-5 lg:grid-cols-[320px_1fr]">
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Customer Feedback</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-900">Product Reviews</h2>
                            <p class="mt-1 text-sm text-slate-500">Verified customer ratings and comments for this product.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-end gap-2">
                                <span class="text-4xl font-bold leading-none text-slate-900" x-text="averageRatingDisplay"></span>
                                <span class="pb-1 text-sm font-semibold text-slate-500">/ 5</span>
                            </div>

                            <div class="mt-3 flex items-center gap-1">
                                <template x-for="star in starRange" :key="`average-star-${star}`">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                                        :class="star <= Math.round(averageRating) ? 'text-amber-400' : 'text-slate-200'">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81H7.03a1 1 0 00.951-.69l1.069-3.292z" />
                                    </svg>
                                </template>
                            </div>

                            <p class="mt-3 text-sm font-semibold text-slate-700" x-text="reviewCountText"></p>
                            <p class="mt-1 text-xs text-slate-500">Latest reviews are shown below. Submitting again updates your existing review.</p>
                        </div>

                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                            <p class="font-semibold">Quick note</p>
                            <p class="mt-1">Ratings use a 1 to 5 scale and comments are limited to 500 characters.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @if ($canSubmitReview)
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Write Review</p>
                                        <h3 class="mt-1 text-lg font-bold text-slate-900">Share your experience</h3>
                                        <p class="mt-1 text-sm text-slate-500">Your rating helps other outlet customers decide faster.</p>
                                    </div>
                                    <template x-if="userReview">
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700">
                                            Your review is saved
                                        </span>
                                    </template>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <template x-for="star in starRange" :key="`input-star-${star}`">
                                        <button type="button"
                                            @click="setRating(star)"
                                            :disabled="submitting || deleting"
                                            class="rounded-md p-1 transition"
                                            :class="star <= form.rating ? 'text-amber-400' : 'text-slate-300'">
                                            <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81H7.03a1 1 0 00.951-.69l1.069-3.292z" />
                                            </svg>
                                        </button>
                                    </template>
                                    <span class="text-sm font-semibold text-slate-600" x-text="ratingLabel"></span>
                                </div>

                                <label class="mt-4 block">
                                    <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Comment</span>
                                    <textarea x-model.trim="form.comment"
                                        maxlength="500"
                                        rows="4"
                                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-300 focus:bg-white"
                                        placeholder="Write a short review about product quality, packaging, or value."></textarea>
                                </label>

                                <div class="mt-2 flex items-center justify-between gap-3 text-xs text-slate-500">
                                    <span x-text="`${form.comment.length}/500 characters`"></span>
                                    <span x-show="loadingUserReview">Checking your existing review...</span>
                                </div>

                                <p x-show="formError" x-text="formError" class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"></p>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <button type="button"
                                        @click="submitReview()"
                                        :disabled="submitting || deleting || form.rating < 1"
                                        class="inline-flex h-10 items-center justify-center rounded-md bg-slate-900 px-4 text-[11px] font-bold uppercase tracking-[0.14em] text-white transition hover:bg-black disabled:cursor-not-allowed disabled:bg-slate-300"
                                        x-text="submitting ? 'Saving...' : (userReview ? 'Update Review' : 'Submit Review')"></button>

                                    <button type="button"
                                        x-show="userReview"
                                        @click="deleteReview()"
                                        :disabled="submitting || deleting"
                                        class="inline-flex h-10 items-center justify-center rounded-md border border-rose-200 bg-rose-50 px-4 text-[11px] font-bold uppercase tracking-[0.14em] text-rose-600 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-60"
                                        x-text="deleting ? 'Removing...' : 'Delete Review'"></button>
                                </div>
                            </div>
                        @elseif (auth()->check())
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Review Access</p>
                                <h3 class="mt-1 text-lg font-bold text-slate-900">This account cannot submit reviews</h3>
                                <p class="mt-1 text-sm text-slate-500">Only customer accounts with outlet access can add or update product reviews.</p>
                            </div>
                        @else
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-amber-700">Login Required</p>
                                <h3 class="mt-1 text-lg font-bold text-slate-900">Sign in to leave a review</h3>
                                <p class="mt-1 text-sm text-slate-600">Customer feedback can be submitted after login with an outlet or user account.</p>
                                <a href="{{ route('login') }}"
                                    class="mt-3 inline-flex h-10 items-center justify-center rounded-md bg-slate-900 px-4 text-[11px] font-bold uppercase tracking-[0.14em] text-white transition hover:bg-slate-800">
                                    Login Now
                                </a>
                            </div>
                        @endif

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">All Reviews</p>
                                    <h3 class="mt-1 text-lg font-bold text-slate-900">What customers are saying</h3>
                                </div>
                                <button type="button"
                                    @click="fetchReviews()"
                                    :disabled="loadingReviews"
                                    class="inline-flex h-9 items-center justify-center rounded-md border border-slate-200 px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-600 transition hover:border-blue-200 hover:text-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                                    Refresh
                                </button>
                            </div>

                            <p x-show="loadError" x-text="loadError" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"></p>

                            <div x-show="loadingReviews" class="mt-4 space-y-3">
                                <div class="h-24 animate-pulse rounded-2xl bg-slate-100"></div>
                                <div class="h-24 animate-pulse rounded-2xl bg-slate-100"></div>
                            </div>

                            <div x-show="!loadingReviews && reviews.length === 0" class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                                No reviews yet for this product.
                            </div>

                            <div x-show="!loadingReviews && reviews.length > 0" class="mt-4 space-y-3">
                                <template x-for="review in reviews" :key="review.id">
                                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="text-sm font-bold text-slate-900" x-text="review.user"></p>
                                                    <span x-show="isOwnReview(review.id)"
                                                        class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.12em] text-emerald-700">
                                                        You
                                                    </span>
                                                </div>
                                                <div class="mt-2 flex items-center gap-1">
                                                    <template x-for="star in starRange" :key="`review-${review.id}-star-${star}`">
                                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"
                                                            :class="star <= review.rating ? 'text-amber-400' : 'text-slate-200'">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81H7.03a1 1 0 00.951-.69l1.069-3.292z" />
                                                        </svg>
                                                    </template>
                                                </div>
                                            </div>

                                            <p class="text-xs font-semibold text-slate-400" x-text="review.created_at"></p>
                                        </div>

                                        <p x-show="review.comment" x-text="review.comment" class="mt-3 text-sm leading-relaxed text-slate-600"></p>
                                        <p x-show="!review.comment" class="mt-3 text-sm italic text-slate-400">No written comment provided.</p>
                                    </article>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            @if ($relatedCards->count() > 0)
                <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5 lg:p-6">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Same Category</p>
                            <h2 class="text-lg font-bold text-slate-900">Related Products</h2>
                        </div>
                        <a href="{{ route('shop', ['category' => $product->category_id]) }}"
                            class="hidden text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-600 transition hover:text-slate-900 md:inline-flex">
                            View All
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-5">
                        @foreach ($relatedCards as $card)
                            <x-frontend.product-card
                                :product="$card['product']"
                                :variants="$card['variants']"
                                :display-path="$card['display_path']"
                                :category-name="$card['category_name']"
                                :product-type="$card['product_type']"
                                :currency-icon="$currencyIcon"
                                :is-outlet-user="$isOutletUser"
                                :is-standard-user="$isStandardUser"
                                :details-url="$card['details_url']"
                                class="p-2.5" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productReviews', (config) => ({
                config,
                starRange: [1, 2, 3, 4, 5],
                averageRating: 0,
                totalReviews: 0,
                reviews: [],
                userReview: null,
                form: {
                    rating: 0,
                    comment: '',
                },
                loadingReviews: true,
                loadingUserReview: false,
                submitting: false,
                deleting: false,
                loadError: '',
                formError: '',

                init() {
                    this.fetchReviews();

                    if (this.config.canSubmit && this.config.userReviewUrl) {
                        this.fetchUserReview();
                    }
                },

                get averageRatingDisplay() {
                    return Number(this.averageRating || 0).toFixed(this.totalReviews > 0 ? 1 : 0);
                },

                get reviewCountText() {
                    if (this.totalReviews === 0) {
                        return 'No ratings yet';
                    }

                    return `${this.totalReviews} review${this.totalReviews === 1 ? '' : 's'}`;
                },

                get ratingLabel() {
                    const labels = {
                        0: 'Select rating',
                        1: 'Poor',
                        2: 'Fair',
                        3: 'Good',
                        4: 'Very good',
                        5: 'Excellent',
                    };

                    return labels[this.form.rating] || 'Select rating';
                },

                get csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                },

                setRating(star) {
                    if (this.submitting || this.deleting) {
                        return;
                    }

                    this.form.rating = Number(star) || 0;
                    this.formError = '';
                },

                isOwnReview(reviewId) {
                    return Number(this.userReview?.id || 0) === Number(reviewId || 0);
                },

                async request(url, options = {}) {
                    const response = await fetch(url, {
                        ...options,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(options.headers || {}),
                        },
                    });

                    const contentType = response.headers.get('content-type') || '';
                    const payload = contentType.includes('application/json')
                        ? await response.json()
                        : null;

                    if (!response.ok) {
                        const message = payload?.message
                            || (response.status === 401 ? 'Please login to continue.' : 'Unable to complete the review request.');
                        throw new Error(message);
                    }

                    return payload;
                },

                async fetchReviews() {
                    this.loadingReviews = true;
                    this.loadError = '';

                    try {
                        const payload = await this.request(this.config.listUrl);
                        this.averageRating = Number(payload?.average_rating || 0);
                        this.totalReviews = Number(payload?.total_reviews || 0);
                        this.reviews = Array.isArray(payload?.reviews) ? payload.reviews : [];
                    } catch (error) {
                        this.loadError = error.message || 'Unable to load reviews right now.';
                        this.reviews = [];
                        this.averageRating = 0;
                        this.totalReviews = 0;
                    } finally {
                        this.loadingReviews = false;
                    }
                },

                async fetchUserReview() {
                    this.loadingUserReview = true;

                    try {
                        const payload = await this.request(this.config.userReviewUrl);
                        this.userReview = payload?.review || null;
                        this.form.rating = Number(this.userReview?.rating || 0);
                        this.form.comment = this.userReview?.comment || '';
                    } catch (error) {
                        this.userReview = null;
                    } finally {
                        this.loadingUserReview = false;
                    }
                },

                async submitReview() {
                    if (!this.config.canSubmit || this.submitting || this.deleting) {
                        return;
                    }

                    if (this.form.rating < 1) {
                        this.formError = 'Please select a rating first.';
                        return;
                    }

                    this.submitting = true;
                    this.formError = '';

                    try {
                        const body = new URLSearchParams({
                            _token: this.csrfToken,
                            product_id: String(this.config.productId),
                            rating: String(this.form.rating),
                            comment: this.form.comment || '',
                        });

                        const payload = await this.request(this.config.storeUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            },
                            body: body.toString(),
                        });

                        this.notify(payload?.message || 'Review saved successfully.', 'success');
                        await Promise.all([this.fetchReviews(), this.fetchUserReview()]);
                    } catch (error) {
                        this.formError = error.message || 'Unable to save your review.';
                        this.notify(this.formError, 'error');
                    } finally {
                        this.submitting = false;
                    }
                },

                async deleteReview() {
                    if (!this.userReview?.id || this.submitting || this.deleting) {
                        return;
                    }

                    if (!window.confirm('Delete your review for this product?')) {
                        return;
                    }

                    this.deleting = true;
                    this.formError = '';

                    try {
                        await this.request(this.config.deleteUrlTemplate.replace('__REVIEW_ID__', this.userReview.id), {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                        });

                        this.userReview = null;
                        this.form.rating = 0;
                        this.form.comment = '';
                        this.notify('Review deleted successfully.', 'success');
                        await this.fetchReviews();
                    } catch (error) {
                        this.formError = error.message || 'Unable to delete your review.';
                        this.notify(this.formError, 'error');
                    } finally {
                        this.deleting = false;
                    }
                },

                notify(message, type = 'info') {
                    const bodyEl = document.querySelector('[x-data*="globalApp"]');
                    if (bodyEl?._x_dataStack?.[0]) {
                        bodyEl._x_dataStack[0].notify(message, type);
                    }
                },
            }));

            Alpine.data('productDetail', (product, variants, initiallyWishlisted = false) => ({
                qty: Math.max(1, parseInt(product.minimum_order_qty, 10) || 1),
                product,
                variants,
                selectedVariantIndex: '',
                adding: false,
                lastRawQty: null,
                moqToast: null,
                wishlistBusy: false,
                isInWishlist: !!initiallyWishlisted,

                init() {
                    if (!this.hasVariants || !this.inventoryVisible) {
                        return;
                    }

                    const firstAvailableIndex = this.variants.findIndex((variant) => (parseInt(variant.stock, 10) || 0) > 0);
                    this.selectedVariantIndex = firstAvailableIndex >= 0 ? String(firstAvailableIndex) : '';
                },

                get inventoryVisible() {
                    return !!this.product.inventory_visible;
                },

                get hasVariants() {
                    return this.variants.length > 0;
                },

                canSelectVariant(index) {
                    const variant = this.variants[index];
                    if (!variant) {
                        return false;
                    }

                    if (!this.inventoryVisible) {
                        return true;
                    }

                    return (parseInt(variant.stock, 10) || 0) > 0;
                },

                selectVariant(index) {
                    if (!this.canSelectVariant(index)) {
                        return;
                    }

                    this.selectedVariantIndex = String(index);
                    this.lastRawQty = null;
                    this.normalizeQty();
                },

                get selectedVariant() {
                    if (this.selectedVariantIndex === '') {
                        return null;
                    }

                    const index = parseInt(this.selectedVariantIndex, 10);
                    if (!this.canSelectVariant(index)) {
                        return null;
                    }

                    return this.variants[index] ?? null;
                },

                variantLabel(v) {
                    const base = v.name || [v.color, v.size].filter(Boolean).join(' ') || 'Variant';
                    if (!this.inventoryVisible) {
                        return base;
                    }

                    const stock = parseInt(v.stock, 10) || 0;
                    return stock > 0 ? `${base} - ${stock}` : `${base} - Out`;
                },

                get productNormalizedDiscountType() {
                    const type = String(this.product.discount_type || '').toLowerCase().trim();
                    return ['flat', 'percent'].includes(type) ? type : '';
                },

                get productDiscountValue() {
                    return Math.max(0, parseFloat(this.product.discount) || 0);
                },

                get globalNormalizedDiscountType() {
                    const type = String(this.product.global_discount_type || '').toLowerCase().trim();
                    return ['flat', 'percent'].includes(type) ? type : '';
                },

                get globalDiscountValue() {
                    return Math.max(0, parseFloat(this.product.global_discount) || 0);
                },

                get normalizedDiscountType() {
                    if (this.productNormalizedDiscountType !== '' && this.productDiscountValue > 0) {
                        return this.productNormalizedDiscountType;
                    }

                    if (this.globalNormalizedDiscountType !== '' && this.globalDiscountValue > 0) {
                        return this.globalNormalizedDiscountType;
                    }

                    return '';
                },

                get discountValue() {
                    if (this.productNormalizedDiscountType !== '' && this.productDiscountValue > 0) {
                        return this.productDiscountValue;
                    }

                    if (this.globalNormalizedDiscountType !== '' && this.globalDiscountValue > 0) {
                        return this.globalDiscountValue;
                    }

                    return 0;
                },

                get hasDiscount() {
                    return this.normalizedDiscountType !== '' && this.discountValue > 0;
                },

                applyDiscount(price) {
                    const numericPrice = Math.max(0, parseFloat(price) || 0);
                    if (!this.hasDiscount) {
                        return numericPrice;
                    }

                    if (this.normalizedDiscountType === 'percent') {
                        const percent = Math.min(100, this.discountValue);
                        return Math.max(0, numericPrice - ((numericPrice * percent) / 100));
                    }

                    return Math.max(0, numericPrice - this.discountValue);
                },

                get outletBasePrice() {
                    return this.selectedVariant
                        ? (this.selectedVariant.outlet_price || this.selectedVariant.price || this.product.outlet_price || this.product.price || 0)
                        : (this.product.outlet_price || this.product.price || 0);
                },

                get outletDisplayPrice() {
                    return Number(this.applyDiscount(this.outletBasePrice)).toFixed(2);
                },

                get outletOriginalDisplayPrice() {
                    return Number(this.outletBasePrice || 0).toFixed(2);
                },

                get showOutletOriginalPrice() {
                    return this.hasDiscount && (this.outletBasePrice > this.applyDiscount(this.outletBasePrice));
                },

                get retailDisplayPrice() {
                    const price = this.selectedVariant
                        ? (this.selectedVariant.price || this.product.price || 0)
                        : (this.product.price || 0);

                    return Number(price).toFixed(2);
                },

                get minimumOrderQty() {
                    return Math.max(1, parseInt(this.product.minimum_order_qty, 10) || 1);
                },

                get currentStock() {
                    if (this.hasVariants) {
                        return this.selectedVariant ? Math.max(0, parseInt(this.selectedVariant.stock, 10) || 0) : 0;
                    }

                    return Math.max(0, parseInt(this.product.stock, 10) || 0);
                },

                get maxAddableQty() {
                    if (!this.inventoryVisible) {
                        return Number.MAX_SAFE_INTEGER;
                    }

                    const stock = this.currentStock;
                    const moq = this.minimumOrderQty;
                    if (stock < moq) {
                        return 0;
                    }

                    return Math.floor(stock / moq) * moq;
                },

                get normalizedQty() {
                    const inputQty = Math.max(1, parseInt(this.qty, 10) || 1);
                    const moq = this.minimumOrderQty;

                    if (inputQty < moq) {
                        return moq;
                    }

                    if (inputQty > moq) {
                        return Math.ceil(inputQty / moq) * moq;
                    }

                    return moq;
                },

                normalizeQty() {
                    const rawSource = this.lastRawQty !== null ? this.lastRawQty : this.qty;
                    const rawQty = Math.max(1, parseInt(rawSource, 10) || 1);
                    const moq = this.minimumOrderQty;
                    let moqAdjusted = rawQty;

                    if (rawQty < moq) {
                        moqAdjusted = moq;
                    } else if (rawQty > moq) {
                        moqAdjusted = Math.ceil(rawQty / moq) * moq;
                    }

                    let adjustedQty = moqAdjusted;

                    if (this.inventoryVisible && this.maxAddableQty > 0 && adjustedQty > this.maxAddableQty) {
                        adjustedQty = this.maxAddableQty;
                    }

                    this.qty = adjustedQty;
                    if (moqAdjusted !== rawQty && adjustedQty === moqAdjusted) {
                        this.moqToast = { moq, adjustedQty, rawQty };
                    } else {
                        this.moqToast = null;
                        this.lastRawQty = null;
                    }

                    return this.qty;
                },

                get canAdd() {
                    if (this.hasVariants && !this.selectedVariant) {
                        return false;
                    }

                    if (!this.inventoryVisible) {
                        return true;
                    }

                    const requestedQty = this.normalizedQty;
                    return this.currentStock > 0 && this.maxAddableQty > 0 && requestedQty <= this.currentStock;
                },

                get cannotAddMessage() {
                    if (this.hasVariants && !this.selectedVariant) {
                        return 'Please select a variant';
                    }

                    if (!this.inventoryVisible) {
                        return 'Cannot add this item right now';
                    }

                    if (this.currentStock <= 0) {
                        return 'Out of stock';
                    }

                    if (this.maxAddableQty === 0) {
                        return `Minimum order is ${this.minimumOrderQty}, but stock is ${this.currentStock}`;
                    }

                    return `Available stock: ${this.currentStock}`;
                },

                get stockPillText() {
                    if (!this.inventoryVisible) {
                        return 'Available';
                    }

                    if (this.hasVariants && !this.selectedVariant) {
                        return 'Select variant';
                    }

                    if (this.currentStock <= 0) {
                        return 'Out of stock';
                    }

                    if (this.currentStock <= 5) {
                        return `Low stock: ${this.currentStock}`;
                    }

                    return `In stock: ${this.currentStock}`;
                },

                get stockPillClass() {
                    if (!this.inventoryVisible) {
                        return 'border-slate-200 bg-slate-50 text-slate-600';
                    }

                    if (this.hasVariants && !this.selectedVariant) {
                        return 'border-slate-200 bg-slate-100 text-slate-500';
                    }

                    if (this.currentStock <= 0) {
                        return 'border-rose-200 bg-rose-50 text-rose-600';
                    }

                    if (this.currentStock <= 5) {
                        return 'border-amber-200 bg-amber-50 text-amber-700';
                    }

                    return 'border-emerald-200 bg-emerald-50 text-emerald-700';
                },

                async addToCart() {
                    if (this.adding || !this.canAdd) {
                        return;
                    }

                    this.adding = true;
                    try {
                        const finalQty = this.normalizeQty();
                        await Alpine.store('cart').addItem(this.product, this.selectedVariant, finalQty);

                        const bodyEl = document.querySelector('[x-data*="globalApp"]');
                        if (bodyEl?._x_dataStack?.[0]) {
                            const notifier = bodyEl._x_dataStack[0];
                            const toast = this.moqToast;
                            if (toast) {
                                notifier.notify('Added to cart ✓', 'success');
                                setTimeout(() => {
                                    notifier.notify(`Minimum order quantity is ${toast.moq}. Your cart has been updated to ${toast.adjustedQty} items.`, 'warning');
                                }, 250);
                            } else {
                                notifier.notify('Added to cart ✓', 'success');
                            }
                        }
                        this.moqToast = null;
                        this.lastRawQty = null;
                    } catch (e) {
                        console.error('Add to cart error:', e);
                        this.notify(e?.message || 'Error adding to cart', 'error');
                    } finally {
                        this.adding = false;
                    }
                },

                async toggleWishlist() {
                    if (this.wishlistBusy) {
                        return;
                    }

                    this.wishlistBusy = true;
                    try {
                        const result = await Alpine.store('wishlist').toggle(this.product.id);
                        this.isInWishlist = Alpine.store('wishlist').isWishlisted(this.product.id);

                        const fallback = this.isInWishlist ? 'Added to wishlist' : 'Removed from wishlist';
                        this.notify(result?.message || fallback, this.isInWishlist ? 'success' : 'warning');
                    } catch (e) {
                        console.error('Wishlist toggle error:', e);
                        this.notify('Error updating wishlist', 'error');
                    } finally {
                        this.wishlistBusy = false;
                    }
                },

                notify(message, type = 'info') {
                    const bodyEl = document.querySelector('[x-data*="globalApp"]');
                    if (bodyEl?._x_dataStack?.[0]) {
                        bodyEl._x_dataStack[0].notify(message, type);
                    }
                },
            }));

        });
    </script>
@endsection
