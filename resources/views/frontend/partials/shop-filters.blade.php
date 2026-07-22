@php
    $filterCardClass = $filterCardClass ?? 'bg-white rounded-lg p-6 shadow-sm border border-slate-200';
@endphp

<div class="{{ $filterCardClass }}">
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
                                        <div class="pl-4 space-y-1.5 border-l-2 border-slate-100 mt-1 ml-1">
                                            @foreach ($sub->childCategories as $child)
                                                <button
                                                    @click="toggleChildCategory({{ $category->id }}, {{ $sub->id }}, {{ $child->id }})"
                                                    class="block text-[11px] font-medium uppercase tracking-[0.06em] transition-colors text-left"
                                                    :class="{{ request('childcategory') == $child->id ? 'true' : 'false' }} ?
                                                        'text-slate-900' :
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
