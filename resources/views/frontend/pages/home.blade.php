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
        $latestCategoryBlocks = collect($latestCategoryBlocks ?? []);
    @endphp

    <div class="bg-slate-100">
        @include('frontend.partials.home-slider', ['sliders' => $sliders, 'currencyIcon' => $currencyIcon])

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
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
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
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
