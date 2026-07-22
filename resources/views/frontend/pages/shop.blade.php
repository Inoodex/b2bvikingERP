@extends('layouts.frontend')
@section('content')
    @php
        $currencyIcon = optional($settings)->currency_icon ?? 'Tk';
        $roleContext = $roleContext ?? [];
        $isOutletUser = (bool) data_get($roleContext, 'isOutletUser', false);
        $isStandardUser = (bool) data_get($roleContext, 'isStandardUser', false);
        $shopCards = collect($shopCards ?? []);
        $productTypes = collect($productTypes ?? []);
    @endphp

    <div id="shop-page-root"
         class="bg-slate-50 min-h-screen"
         x-data="shopFilter($el)"
         @shop-open-filters.window="openMobileFilters()"
         @keydown.escape.window="mobileFiltersOpen = false"
         data-active-cat="{{ request('category', '') }}"
         data-active-sub="{{ request('subcategory', '') }}"
         data-min-range="{{ $min_range }}"
         data-max-range="{{ $max_range }}"
         data-min-price="{{ request('min_price', $min_range) }}"
        data-max-price="{{ request('max_price', $max_range) }}"
        data-sort="{{ request('sort', 'latest') }}"
        data-product-type="{{ request('product_type', '') }}"
         data-search="{{ request('search', '') }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col sm:flex-row gap-8">
                <!-- Sidebar Filters -->
                <aside class="shop-desktop-sidebar hidden lg:block w-full lg:w-72 shrink-0">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-slate-200 sm:sticky sm:top-24">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="text-[13px] font-semibold uppercase tracking-[0.12em] text-slate-900">Filters</h2>
                            <button type="button"
                                @click="resetFilters()"
                                class="text-[10px] font-semibold uppercase tracking-[0.12em] bg-red-50 px-3 py-1.5 rounded-md text-red-500 hover:text-red-600 transition-colors">
                                Clear
                            </button>
                        </div>

                        <!-- Categories -->
                        <div class="mb-9">
                            <h3 class="text-[12px] font-semibold text-slate-500 uppercase tracking-[0.12em] mb-4">Categories</h3>
                            <div class="shop-filter-categories space-y-3">
                                @foreach ($categories as $category)
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between group">
                                            <button @click="toggleCategory({{ $category->id }})"
                                                class="text-[13px] font-medium uppercase tracking-[0.08em] transition-colors text-left"
                                                :class="activeCat === {{ $category->id }} ||
                                                    {{ request('category') == $category->id ? 'true' : 'false' }} ?
                                                    'text-slate-900' : 'text-slate-600 hover:text-slate-900'">
                                                {{ $category->name }}
                                            </button>
                                            @if ($category->subCategories->count() > 0)
                                                <button
                                                    @click="activeCat = (activeCat === {{ $category->id }} ? null : {{ $category->id }})"
                                                    class="p-1 rounded-md hover:bg-slate-100 text-slate-400 transition-transform"
                                                    :class="{ 'rotate-180': activeCat === {{ $category->id }} }">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Subcategories -->
                                        <div x-show="activeCat === {{ $category->id }}" x-collapse>
                                            <div class="pl-4 space-y-2 border-l-2 border-slate-100 mt-2 ml-1">
                                                @foreach ($category->subCategories as $sub)
                                                    <div class="space-y-2">
                                                        <div class="flex items-center justify-between group">
                                                            <button
                                                                @click="toggleSubCategory({{ $category->id }}, {{ $sub->id }})"
                                                                class="text-[12px] font-medium uppercase tracking-[0.07em] transition-colors text-left"
                                                                :class="activeSub === {{ $sub->id }} ||
                                                                    {{ request('subcategory') == $sub->id ? 'true' : 'false' }} ?
                                                                    'text-slate-900' :
                                                                    'text-slate-500 hover:text-slate-900'">
                                                                {{ $sub->name }}
                                                            </button>
                                                            @if ($sub->childCategories->count() > 0)
                                                                <button
                                                                    @click="activeSub = (activeSub === {{ $sub->id }} ? null : {{ $sub->id }})"
                                                                    class="p-0.5 rounded-md hover:bg-slate-100 text-slate-300 transition-transform"
                                                                    :class="{ 'rotate-180': activeSub === {{ $sub->id }} }">
                                                                    <svg class="w-3 h-3" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                        </div>

                                                        <!-- Child Categories -->
                                                        <div x-show="activeSub === {{ $sub->id }}" x-collapse>
                                                            <div
                                                                class="pl-4 space-y-1.5 border-l-2 border-slate-100 mt-1 ml-1">
                                                                @foreach ($sub->childCategories as $child)
                                                                    <button
                                                                        @click="toggleChildCategory({{ $category->id }}, {{ $sub->id }}, {{ $child->id }})"
                                                                        class="block text-[11px] font-medium uppercase tracking-[0.06em] transition-colors text-left"
                                                                        :class="{{ request('childcategory') == $child->id ? 'true' : 'false' }}
                                                                            ? 'text-slate-900' :
                                                                            'text-slate-400 hover:text-slate-900'">
                                                                        {{ $child->name }}
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Filter Slider -->
                    @auth
                        <div class="pt-6 border-t border-slate-200">
                            <h3 class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.25em] mb-5">Price Range</h3>

                            <div class="relative w-full h-10 mt-4">
                                <div class="h-1.5 w-full bg-slate-200 rounded-full absolute top-1/2 -translate-y-1/2"></div>
                                <div class="h-1.5 bg-blue-600 rounded-full absolute top-1/2 -translate-y-1/2"
                                     :style="`left: ${((minPrice - minRange) / (maxRange - minRange)) * 100}%; right: ${100 - ((maxPrice - minRange) / (maxRange - minRange)) * 100}%`" class="text-blue-500"></div>

                                <input type="range"
                                       :min="minRange" :max="maxRange" step="1"
                                       x-model.number="minPrice"
                                       @input="if(minPrice > maxPrice) minPrice = maxPrice - 1"
                                       @change="applyFilters()"
                                       class="absolute w-full h-1.5 top-1/2 -translate-y-1/2 appearance-none bg-transparent pointer-events-none px-0 cursor-pointer [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-blue-600 [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:appearance-none [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-blue-600 [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-white">

                                <input type="range"
                                       :min="minRange" :max="maxRange" step="1"
                                       x-model.number="maxPrice"
                                       @input="if(maxPrice < minPrice) maxPrice = minPrice + 1"
                                       @change="applyFilters()"
                                       class="absolute w-full h-1.5 top-1/2 -translate-y-1/2 appearance-none bg-transparent pointer-events-none px-0 cursor-pointer [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-blue-600 [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:appearance-none [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-blue-600 [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-white">
                            </div>

                            <div class="flex items-center justify-between mt-6 px-1">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Min Price</span>
                                    <span class="text-sm font-bold text-slate-900" x-text="'{{ $currencyIcon }}' + minPrice"></span>
                                </div>
                                <div class="flex flex-col text-right">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Max Price</span>
                                    <span class="text-sm font-bold text-slate-900" x-text="'{{ $currencyIcon }}' + maxPrice"></span>
                                </div>
                            </div>
                        </div>
                    @endauth
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1">
                    <!-- Mobile Filters Trigger -->
                    <div class="shop-mobile-filters-trigger lg:hidden mb-4">
                        <div class="bg-white rounded-lg px-4 py-3 shadow-sm border border-slate-200 flex items-center justify-between gap-3">
                            <button type="button"
                                    @click="openMobileFilters()"
                                    class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-3.5 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-white hover:bg-black transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.586L3.293 6.707A1 1 0 013 6V4z"></path>
                                </svg>
                                Filters
                            </button>
                            <button type="button"
                                    @click="resetFilters()"
                                    class="text-[10px] font-semibold uppercase tracking-[0.12em] bg-red-50 px-3 py-2 rounded-md text-red-600 hover:text-red-700 transition-colors">
                                Clear
                            </button>
                        </div>
                    </div>
                    <!-- Top Toolbar -->
                    <div
                        class="bg-white rounded-lg px-4 py-3 mb-8 shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="flex items-center gap-4 px-1">
                            <span class="text-[12px] font-medium uppercase tracking-[0.08em] text-slate-500">Showing <span
                                    class="text-slate-900 font-semibold">{{ $products->count() }}</span> of <span class="text-slate-900 font-semibold">{{ $products->total() }}</span>
                                results</span>
                        </div>

                        <div class="flex items-center flex-wrap justify-between md:justify-end gap-3 w-full">
                            @auth
                            <div class="flex items-center gap-2">
                                <label
                                    class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.15em] hidden sm:block">Sort
                                    by:</label>
                                <select x-model="sort" @change="applyFilters()"
                                    class="bg-white border border-slate-200 rounded-md px-3.5 py-2 text-[12px] font-medium text-slate-700 outline-none focus:border-slate-900 cursor-pointer appearance-none pr-9 relative">
                                    <option value="stock_first">In Stock First</option>
                                    <option value="latest">Latest Product</option>
                                    <option value="price_low_high">Price: Low to High</option>
                                    <option value="price_high_low">Price: High to Low</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.12em] hidden sm:block">Occasion/Type:</label>
                                @php $selectedType = (string) request('product_type', ''); @endphp
                                <select x-model="productType" @change="applyFilters()"
                                        class="bg-white border border-slate-200 rounded-md px-3.5 py-2 text-[12px] font-medium text-slate-700 outline-none focus:border-slate-900 cursor-pointer appearance-none pr-9 relative">
                                    <option value="" {{ $selectedType === '' ? 'selected' : '' }}>All Types</option>
                                    <option value="new_arrival" {{ $selectedType === 'new_arrival' ? 'selected' : '' }}>New Arrival (Legacy)</option>
                                    <option value="upcoming" {{ $selectedType === 'upcoming' ? 'selected' : '' }}>Upcoming (Legacy)</option>
                                    @foreach($productTypes as $type)
                                        <option value="{{ $type->id }}" {{ $selectedType === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endauth

                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @forelse($shopCards as $card)
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
                                class="p-3" />

                        @empty
                            <div class="col-span-full py-32 text-center">
                                <div class="w-20 h-20 bg-slate-100 rounded-lg flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">No products found</h3>
                                <p class="text-sm text-slate-500">Try adjusting your filters or search terms.</p>
                                <button @click="resetFilters()" class="mt-6 px-6 py-2.5 bg-slate-900 text-white rounded-md text-[11px] font-semibold uppercase tracking-[0.3em] hover:bg-black transition-colors">Clear All Filters</button>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div id="shop-pagination" class="mt-24 border-t border-slate-200">
                        {{ $products->links('vendor.pagination.tailwind') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Filters Drawer -->
        <div x-show="mobileFiltersOpen"
             x-cloak
             class="fixed inset-0 z-[70] lg:hidden">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                 @click="closeMobileFilters()"></div>
            <div class="absolute inset-y-0 left-0 w-[min(92vw,380px)] bg-white shadow-2xl overflow-y-auto">
                <div class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-slate-100 px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Shop</p>
                        <h2 class="text-lg font-semibold text-slate-900">Filters</h2>
                    </div>
                    <button type="button"
                            @click="closeMobileFilters()"
                            class="grid h-10 w-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    @include('frontend.partials.shop-filters', ['filterCardClass' => 'bg-white rounded-2xl p-5 border border-slate-200 shadow-sm'])
                    <button type="button"
                            @click="closeMobileFilters()"
                            class="mt-4 w-full inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-700 hover:bg-slate-200 transition">
                        Done
                    </button>
                </div>
            </div>
        </div>

        <script>
            (function () {
                let loadingShopPage = false;
                const shopBaseUrl = @json(route('shop'));

                async function loadShopPage(url, pushState = true, scrollToTop = false) {
                    const currentRoot = document.getElementById('shop-page-root');
                    if (!currentRoot || loadingShopPage) {
                        return;
                    }

                    loadingShopPage = true;
                    currentRoot.classList.add('opacity-60', 'pointer-events-none');

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load shop page');
                        }

                        const html = await response.text();
                        const parsed = new DOMParser().parseFromString(html, 'text/html');
                        const nextRoot = parsed.getElementById('shop-page-root');

                        if (!nextRoot) {
                            window.location.href = url;
                            return;
                        }

                        const oldRoot = document.getElementById('shop-page-root');
                        oldRoot.outerHTML = nextRoot.outerHTML;

                        const refreshedRoot = document.getElementById('shop-page-root');
                        if (refreshedRoot && window.Alpine && typeof window.Alpine.initTree === 'function') {
                            window.Alpine.initTree(refreshedRoot);
                        }

                        if (pushState) {
                            window.history.pushState({ shopAjax: true }, '', url);
                        }

                        if (scrollToTop) {
                            requestAnimationFrame(() => {
                                const root = document.getElementById('shop-page-root');
                                if (root) {
                                    root.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                } else {
                                    window.scrollTo({ top: 0, behavior: 'smooth' });
                                }
                            });
                        }
                    } catch (error) {
                        window.location.href = url;
                    } finally {
                        loadingShopPage = false;
                        const refreshedRoot = document.getElementById('shop-page-root');
                        if (refreshedRoot) {
                            refreshedRoot.classList.remove('opacity-60', 'pointer-events-none');
                        }
                    }
                }

                window.shopFilter = function (rootEl) {
                    const parseIntOrNull = (value) => {
                        const parsed = parseInt(value, 10);
                        return Number.isFinite(parsed) ? parsed : null;
                    };
                    const parseFloatOr = (value, fallback) => {
                        const parsed = parseFloat(value);
                        return Number.isFinite(parsed) ? parsed : fallback;
                    };
                    const root = rootEl || document.getElementById('shop-page-root');
                    const minRange = parseFloatOr(root?.dataset?.minRange, 0);
                    const maxRange = parseFloatOr(root?.dataset?.maxRange, 0);
                    const minPrice = parseFloatOr(root?.dataset?.minPrice, minRange);
                    const maxPrice = parseFloatOr(root?.dataset?.maxPrice, maxRange);

                    return {
                        mobileFiltersOpen: false,
                        activeCat: parseIntOrNull(root?.dataset?.activeCat),
                        activeSub: parseIntOrNull(root?.dataset?.activeSub),
                        minRange: minRange,
                        maxRange: maxRange,
                        minPrice: minPrice,
                        maxPrice: maxPrice,
                        sort: (root?.dataset?.sort || 'latest'),
                        productType: (root?.dataset?.productType || ''),
                        search: (root?.dataset?.search || ''),

                        openMobileFilters() {
                            this.mobileFiltersOpen = true;
                        },

                        closeMobileFilters() {
                            this.mobileFiltersOpen = false;
                        },

                        toggleCategory(id) {
                            if (this.activeCat === id) {
                                this.activeCat = null;
                                this.updateUrl({
                                    category: null,
                                    subcategory: null,
                                    childcategory: null,
                                });
                                return;
                            }

                            this.updateUrl({
                                category: id,
                                subcategory: null,
                                childcategory: null
                            });
                        },

                        toggleSubCategory(catId, subId) {
                            this.updateUrl({
                                category: catId,
                                subcategory: subId,
                                childcategory: null
                            });
                        },

                        toggleChildCategory(catId, subId, childId) {
                            this.updateUrl({
                                category: catId,
                                subcategory: subId,
                                childcategory: childId
                            });
                        },

                        applyFilters() {
                            this.updateUrl({
                                min_price: this.minPrice,
                                max_price: this.maxPrice,
                                sort: this.sort,
                                product_type: this.productType
                            });
                        },

                        resetFilters() {
                            loadShopPage(shopBaseUrl, true);
                        },

                        updateUrl(params) {
                            this.mobileFiltersOpen = false;
                            const url = new URL(window.location.href);
                            Object.keys(params).forEach(key => {
                                if (params[key] === null || params[key] === undefined || params[key] === '') {
                                    url.searchParams.delete(key);
                                } else {
                                    url.searchParams.set(key, params[key]);
                                }
                            });

                            loadShopPage(url.toString(), true);
                        }
                    };
                };

                document.addEventListener('click', function (event) {
                    const paginationLink = event.target.closest('#shop-pagination a');
                    if (!paginationLink) {
                        return;
                    }

                    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                        return;
                    }

                    event.preventDefault();
                    loadShopPage(paginationLink.href, true, true);
                });

                window.addEventListener('popstate', function () {
                    const currentUrl = new URL(window.location.href);
                    const shopUrl = new URL(shopBaseUrl);

                    if (currentUrl.pathname !== shopUrl.pathname) {
                        return;
                    }

                    loadShopPage(window.location.href, false);
                });
            })();
        </script>
    @endsection
