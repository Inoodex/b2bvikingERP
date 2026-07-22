@props([
    'product' => [],
    'variants' => [],
    'displayPath' => null,
    'categoryName' => 'General',
    'productType' => null,
    'currencyIcon' => 'Tk',
    'isOutletUser' => false,
    'isStandardUser' => false,
    'alpineComponent' => 'productCardItem',
    'detailsUrl' => null,
])

@php
    $productName = (string) ($product['name'] ?? 'Product');
    $resolvedCategoryName = trim((string) $categoryName) !== ''
        ? (string) $categoryName
        : (string) ($product['category'] ?? 'General');

    $resolvedDisplayPath = is_string($displayPath) && $displayPath !== ''
        ? $displayPath
        : (string) ($product['thumb_image'] ?? '');

    $resolvedDetailsUrl = is_string($detailsUrl) && $detailsUrl !== ''
        ? $detailsUrl
        : '#';

    $cardClasses = 'group relative flex flex-col rounded-xl border border-slate-200 bg-white p-4 transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md';
    $discountBadgeClasses = 'border-slate-200 bg-slate-100 text-slate-600';
@endphp

<article x-data="{{ $alpineComponent }}(@js($product), @js($variants), '{{ $productType }}')"
    {{ $attributes->merge(['class' => $cardClasses]) }}>
    <div class="relative mb-4 aspect-square overflow-hidden rounded-lg border border-slate-200 bg-[#f6f4ef]">
        @if ($resolvedDisplayPath !== '')
            <a href="{{ $resolvedDetailsUrl }}"><img src="{{ $resolvedDisplayPath }}" alt="{{ $productName }}"
                class="h-full w-full object-contain transition-transform duration-700 group-hover:scale-105"></a>
        @else
            <div class="flex h-full w-full items-center justify-center text-[10px] font-bold uppercase tracking-wider text-slate-400">
                No image uploaded
            </div>
        @endif

        <div class="absolute left-3 top-3 flex flex-col gap-1">
            <span class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[10px] font-semibold uppercase tracking-wider text-slate-600">
                {{ $resolvedCategoryName }}
            </span>
            @if ($productType)
                @php
                    $productTypeLower = strtolower($productType);
                    if (str_contains($productTypeLower, 'upcoming')) {
                        $badgeClass = 'border-amber-200 bg-amber-50 text-amber-600';
                    } elseif (str_contains($productTypeLower, 'new') || str_contains($productTypeLower, 'arrival')) {
                        $badgeClass = 'border-emerald-200 bg-emerald-50 text-emerald-600';
                    } else {
                        $badgeClass = 'border-slate-200 bg-white text-slate-600';
                    }
                @endphp
                <span class="rounded-md border px-2 py-1 text-[10px] font-semibold uppercase tracking-wider {{ $badgeClass }}">
                    {{ str_replace('_', ' ', $productType) }}
                </span>
            @endif
            @auth
                <template x-if="hasProductDiscount">
                    <span class="rounded-md border {{ $discountBadgeClasses }} px-2 py-1 text-[10px] font-semibold uppercase tracking-wider">
                        <span x-text="discountBadgeText"></span>
                    </span>
                </template>
            @endauth
        </div>

        <div class="absolute right-3 top-3">
            @auth
                <button @click="toggleWishlist(product.id)"
                    :class="isWishlisted(product.id)
                        ? 'border-rose-300 text-rose-500'
                        : 'border-slate-200 text-slate-500 hover:border-slate-400'"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border bg-white transition-colors active:scale-95"
                    :title="isWishlisted(product.id) ? 'Remove from Wishlist' : 'Add to Wishlist'">
                    <template x-if="isWishlisted(product.id)">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                    </template>
                    <template x-if="!isWishlisted(product.id)">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </template>
                </button>
            @else
                <a href="{{ route('login') }}"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors hover:border-slate-400 hover:text-slate-900">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </a>
            @endauth
        </div>
    </div>

    <div class="flex flex-1 flex-col px-1">
        <h3 class="mb-3 line-clamp-2 text-sm font-semibold leading-tight text-slate-900">
            <a href="{{ $resolvedDetailsUrl }}" class="transition-colors hover:text-slate-900">{{ $productName }}</a>
        </h3>

        <template x-if="hasVariants">
            <div class="mb-4">
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="(v, index) in variants" :key="v.id">
                        <button type="button"
                            @click="selectVariant(index)"
                            :disabled="!canSelectVariant(index)"
                            :class="!canSelectVariant(index)
                                ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-300'
                                : (selectedVariantIndex === String(index)
                                    ? 'border-blue-400 bg-blue-50 text-blue-700'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-blue-700')"
                            class="rounded-md border px-2 py-1 text-[10px] font-semibold uppercase tracking-wider leading-none transition-colors">
                            <span x-text="variantLabel(v)"></span>
                        </button>
                    </template>
                </div>
            </div>
        </template>

        <div class="mt-auto">
            @auth
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 w-full flex flex-col">
                            @if ($isOutletUser)
                                <div class="flex flex-col items-start space-y-2">
                                    <div class="flex flex-col">
                                        <span class="mb-1 whitespace-nowrap text-[10px] font-medium uppercase leading-none tracking-wider text-slate-500">Whole sale Price</span>
                                        <span class="text-[16px] font-semibold leading-none text-slate-800">
                                            {{ $currencyIcon }}<span x-text="outletDisplayPrice"></span>
                                        </span>
                                        <template x-if="showOutletOriginalPrice">
                                            <span class="mt-1 text-[11px] font-medium leading-none text-slate-400 line-through">
                                                {{ $currencyIcon }}<span x-text="outletOriginalDisplayPrice"></span>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="flex flex-col mt-2">
                                        <span class="mb-1 whitespace-nowrap text-[10px] font-semibold uppercase leading-none tracking-wider text-slate-500">Outlet Price</span>
                                        <span class="text-[16px] font-semibold leading-none text-slate-800">
                                            {{ $currencyIcon }}<span x-text="retailDisplayPrice"></span>
                                        </span>
                                        <template x-if="showRetailOriginalPrice">
                                            <span class="mt-1 text-[11px] font-medium leading-none text-slate-400 line-through">
                                                {{ $currencyIcon }}<span x-text="retailOriginalDisplayPrice"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            @elseif ($isStandardUser)
                                <div class="flex flex-col">
                                    <span class="mb-1 whitespace-nowrap text-[10px] font-medium uppercase leading-none tracking-wider text-slate-500">Wholesale</span>
                                    <span class="text-lg font-semibold leading-none text-slate-800">
                                        {{ $currencyIcon }}<span x-text="outletDisplayPrice"></span>
                                    </span>
                                    <template x-if="showOutletOriginalPrice">
                                        <span class="mt-1 text-[11px] font-medium leading-none text-slate-400 line-through">
                                            {{ $currencyIcon }}<span x-text="outletOriginalDisplayPrice"></span>
                                        </span>
                                    </template>
                                </div>
                            @else
                                <div class="flex flex-col">
                                    <span class="mb-1 whitespace-nowrap text-[10px] font-semibold uppercase leading-none tracking-wider text-slate-500">Price</span>
                                    <span class="text-lg font-semibold leading-none text-slate-800">
                                        {{ $currencyIcon }}<span x-text="retailDisplayPrice"></span>
                                    </span>
                                    <template x-if="showRetailOriginalPrice">
                                        <span class="mt-1 text-[11px] font-medium leading-none text-slate-400 line-through">
                                            {{ $currencyIcon }}<span x-text="retailOriginalDisplayPrice"></span>
                                        </span>
                                    </template>
                                </div>
                            @endif
                        </div>

                        <div class="max-w-[140px] shrink-0 text-right">
                            {{-- <span class="inline-flex max-w-full items-center truncate rounded-md border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider"
                                :class="stockPillClass"
                                x-text="stockPillText"></span> --}}
                            {{-- <template x-if="(!hasVariants || selectedVariant) && maxAddableQty > 0">
                                <p class="mt-1 break-words text-[9px] font-semibold leading-tight text-slate-400" x-text="`Max add: ${maxAddableQty}`"></p>
                            </template> --}}
                            <template x-if="(!hasVariants || selectedVariant) && currentStock > 0 && maxAddableQty === 0">
                                <p class="mt-1 break-words text-[9px] font-semibold leading-tight text-amber-600" x-text="`MIM ${minimumOrderQty}, stock ${currentStock}`"></p>
                            </template>
                        </div>
                    </div>


                        <div class="rounded-md bg-white">
                            {{-- <div class="mb-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[9px] font-semibold uppercase tracking-wider text-slate-500">
                                <span>MIM: <span class="text-slate-600" x-text="minimumOrderQty"></span></span>
                                <template x-if="isInCart">
                                    <span class="text-red-600">Total in cart: <span class="text-red-600" x-text="inCartQty"></span></span>
                                </template>
                                <template x-if="hasVariants && selectedVariant">
                                    <span class="text-red-600">This variant in cart: <span class="text-red-600" x-text="variantInCartQty"></span></span>
                                </template>
                                <template x-if="!hasVariants || selectedVariant">
                                    <span class="ml-auto text-right">In stock: <span class="text-slate-600" x-text="currentStock"></span></span>
                                </template>
                                <template x-if="hasVariants && !selectedVariant">
                                    <span class="w-full text-right normal-case tracking-normal text-slate-500">Select variant first</span>
                            </template>
                        </div> --}}
   {{-- working code  --}}
{{-- <div class="rounded-md border border-slate-200 bg-white p-3 text-[9px] font-semibold uppercase tracking-wider text-slate-500">
    <!-- Line 1: MIM + Total in cart -->
    <div class="flex items-center justify-between gap-3 mb-1.5">
        <div class="flex items-center gap-3">
            <span>MIM: <span class="text-slate-700" x-text="minimumOrderQty"></span></span>

            <template x-if="isInCart">
                <span class="text-red-600">
                    Total in cart: <span class="text-red-600" x-text="inCartQty"></span>
                </span>
            </template>
        </div>

        <!-- Small spacer / alignment helper -->
        <div class="flex-1 min-w-[1px]"></div>
    </div>

    <!-- Line 2: This variant in cart + In stock -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <template x-if="hasVariants && selectedVariant && variantInCartQty > 0">
                <span class="text-red-600">
                     in cart: <span class="text-red-600" x-text="variantInCartQty"></span>
                </span>
            </template>

            <template x-if="hasVariants && selectedVariant && variantInCartQty === 0">
                <span class="text-slate-500">in cart: 0</span>
            </template>
        </div>

        <div class="text-right">
            <template x-if="!hasVariants || selectedVariant">
                <span>
                    In stock: <span class="text-slate-700" x-text="currentStock"></span>
                </span>
            </template>

            <template x-if="hasVariants && !selectedVariant">
                <span class="text-slate-400 normal-case tracking-normal">
                    Select variant first
                </span>
            </template>
        </div>
    </div>
</div> --}}

{{-- new code --}}
<div class="rounded-md border border-slate-200 bg-black/3 p-3 text-[9.5px] font-semibold uppercase tracking-wide text-slate-600 mb-1">

    <!-- লাইন ১: MIM + TOTAL IN CART (যদি থাকে) -->
    <div class="flex justify-between items-center mb-1">
        <span>MIM: <span class="text-slate-800" x-text="minimumOrderQty"></span></span>

        {{-- <template x-if="inCartQty >= 0">
            <span class="text-red-600">TOTAL IN CART: <span x-text="inCartQty ?? 0"></span></span>
        </template> --}}
        <span :class="inCartQty > 0 ? 'text-red-600' : 'text-slate-400'">
        TOTAL IN CART: <span x-text="inCartQty ?? 0"></span>
    </span>
    </div>

    <!-- লাইন ২: THIS VARIANT + IN STOCK -->
    <div class="flex justify-between items-center">

        <!-- বাম দিক: variant specific qty — selected থাকলে সবসময় দেখাবে (0 হলেও) -->
        <template x-if="hasVariants">
            <span x-show="selectedVariant" class="text-red-600">
                 IN CART: <span x-text="variantInCartQty ?? 0"></span>
            </span>

            <span x-show="!selectedVariant" class="text-slate-400 normal-case">
                Select variant first
            </span>
        </template>

        <!-- ডান দিক: stock -->
        <span x-show="!hasVariants || selectedVariant">
            IN STOCK: <span class="text-slate-800 font-bold" x-text="currentStock"></span>
        </span>
    </div>

</div>
                        <div class="grid grid-cols-5 gap-2">
                            <input type="number"
                                x-model.number="qty"
                                :min="minimumOrderQty"
                                :max="maxAddableQty > 0 ? maxAddableQty : minimumOrderQty"
                                :step="minimumOrderQty"
                                @input="lastRawQty = $event.target.value"
                                @change="normalizeQty()"
                                class="h-11 w-full col-span-2 rounded-md border border-slate-300 bg-black/3 p-0 text-center text-sm font-semibold text-slate-900 focus:border-slate-900 focus:outline-none">

                            <button @click="canAdd ? addToCart(product, selectedVariant, qty) : notify(cannotAddMessage, 'error')"
                                :class="canAdd
                                    ? 'bg-slate-900 text-white hover:bg-black'
                                    : 'cursor-not-allowed bg-slate-200 text-slate-400'"
                                class="flex h-11 w-full col-span-3 items-center justify-center gap-1.5 rounded-md text-[12px] font-semibold uppercase tracking-wider transition-colors active:scale-95">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span x-show="isInCart && canAdd">Added More</span>
                                <span x-show="!isInCart || !canAdd">Add</span>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex flex-col">
                    <span class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Price</span>
                    <span class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-600"><a href="{{route('login')}}">Login</a></span>
                </div>
            @endauth
        </div>
    </div>

    {{ $slot }}
</article>
