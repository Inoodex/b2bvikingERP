@extends('layouts.frontend')

@section('title', 'B2B Home')

@section('content')
    @php
        $currencyIcon = optional($settings)->currency_icon ?? 'Tk';
        $roleContext = $roleContext ?? [];

        $isOutletUser = (bool) data_get($roleContext, 'isOutletUser', false);
        $isStandardUser = (bool) data_get($roleContext, 'isStandardUser', false);
        $isOutletCustomer = (bool) data_get($roleContext, 'isOutletCustomer', false);
        $sliders = collect($sliders ?? []);
        $popularCards = collect($popularCards ?? []);
        $latestCategoryBlocks = collect($latestCategoryBlocks ?? []);
    @endphp

    <div class="bg-slate-100">
        @include('frontend.partials.home-slider', ['sliders' => $sliders, 'currencyIcon' => $currencyIcon])

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            {{-- Point 23: Frequently Ordered / Popular Items Highlight --}}
            @if ($popularCards->isNotEmpty())
                <section class="rounded-lg border border-amber-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="mb-4 flex items-end justify-between gap-2 border-b border-amber-100 pb-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-amber-600 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.527.82-1.125 1.91-1.745 3.123C7.54 7.79 6.78 9.39 6.25 10.74c-.53 1.35-.75 2.56-.75 3.26 0 3.31 2.69 6 6 6s6-2.69 6-6c0-1.66-.67-3.16-1.76-4.24l-.17-.16c-.47-.44-.9-.81-1.3-1.19-.78-.75-1.45-1.55-1.87-2.857z" clip-rule="evenodd"></path></svg>
                                Frequently Ordered by Outlets
                            </p>
                            <h2 class="text-lg font-bold text-slate-900">Popular Items</h2>
                        </div>
                        <a href="{{ route('shop', ['sort' => 'popular']) }}"
                            class="text-[11px] font-bold uppercase tracking-[0.12em] text-amber-700 hover:text-amber-800 flex items-center gap-1">
                            View All
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($popularCards as $card)
                            <x-frontend.product-card
                                :product="$card['product']"
                                :variants="$card['variants']"
                                :display-path="$card['display_path']"
                                :category-name="$card['category_name']"
                                :product-type="$card['product_type']"
                                :currency-icon="$currencyIcon"
                                :is-outlet-user="$isOutletUser"
                                :is-standard-user="$isStandardUser"
                                :is-popular="true"
                                :details-url="$card['details_url']"
                                class="p-3 shadow-xs" />
                        @endforeach
                    </div>
                </section>
            @endif
            @if ($latestCategoryBlocks->isEmpty())
                <section class="rounded-lg border border-slate-200 bg-white p-6 text-center text-sm text-slate-500 shadow-sm">
                    No active categories with products found.
                </section>
            @else
                @foreach ($latestCategoryBlocks as $block)
                    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-4 flex items-end justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Latest Products</p>
                                <h2 class="text-lg font-bold text-slate-900">{{ $block['category']->name }}</h2>
                            </div>
                            <a href="{{ route('shop', ['category' => $block['category']->id]) }}"
                                class="text-[11px] font-bold uppercase tracking-[0.12em] text-indigo-600 hover:text-indigo-500">
                                View All
                            </a>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach ($block['cards'] as $card)
                                <x-frontend.product-card
                                    :product="$card['product']"
                                    :variants="$card['variants']"
                                    :display-path="$card['display_path']"
                                    :category-name="$card['category_name']"
                                    :product-type="$card['product_type']"
                                    :currency-icon="$currencyIcon"
                                    :is-outlet-user="$isOutletUser"
                                    :is-standard-user="$isStandardUser"
                                    :is-popular="$card['is_popular'] ?? false"
                                    :details-url="$card['details_url']"
                                    class="p-3" />
                            @endforeach
                        </div>
                    </section>
                @endforeach

                {{-- Pagination --}}
                @if($latestCategories->hasPages())
                    <div class="flex items-center justify-center gap-4 pt-4">
                        @if($latestCategories->onFirstPage())
                            <button disabled class="rounded-lg border border-slate-200 bg-slate-100 px-6 py-2 text-sm font-semibold text-slate-400 cursor-not-allowed">
                                Previous
                            </button>
                        @else
                            <a href="{{ $latestCategories->previousPageUrl() }}" class="rounded-lg border border-slate-200 bg-white px-6 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                                Previous
                            </a>
                        @endif

                        <span class="text-sm text-slate-600">
                            Page {{ $latestCategories->currentPage() }} of {{ $latestCategories->lastPage() }}
                        </span>

                        @if($latestCategories->hasMorePages())
                            <a href="{{ $latestCategories->nextPageUrl() }}" class="rounded-lg border border-slate-200 bg-white px-6 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                                Next
                            </a>
                        @else
                            <button disabled class="rounded-lg border border-slate-200 bg-slate-100 px-6 py-2 text-sm font-semibold text-slate-400 cursor-not-allowed">
                                Next
                            </button>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    @include('frontend.partials.home-slider-script')
@endsection
