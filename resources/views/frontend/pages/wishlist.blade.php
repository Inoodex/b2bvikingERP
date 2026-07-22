@extends('layouts.frontend')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="wishlistPage({{ count($wishlistItems) }})">

    {{-- Page Header --}}
    <div class="mb-10 flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tighter">My Wishlist</h1>
            <p class="text-slate-400 font-medium mt-1">
                <span x-text="wishlistCount"></span>
                <span x-text="wishlistCount === 1 ? 'item' : 'items'"></span>
                saved
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button x-show="wishlistCount > 0"
                    @click="showClearConfirm = true"
                    class="flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 font-bold hover:bg-rose-100 transition-colors text-sm rounded-xl border border-rose-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Clear All
            </button>
            <a href="{{ route('shop') }}" class="flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-700 transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path></svg>
                Continue Shopping
            </a>
        </div>
    </div>

    {{-- Clear All Confirmation Modal --}}
    <template x-if="showClearConfirm">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            {{-- Backdrop --}}
            <div @click="showClearConfirm = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            
            {{-- Modal --}}
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                
                <h3 class="text-lg font-bold text-slate-900 text-center mb-2">Clear All Items?</h3>
                <p class="text-slate-600 text-center mb-6">Are you sure you want to remove all items from your wishlist? This action cannot be undone.</p>
                
                <div class="flex gap-3">
                    <button @click="showClearConfirm = false"
                            class="flex-1 px-4 py-2 bg-slate-100 text-slate-900 font-bold rounded-xl hover:bg-slate-200 transition-colors">
                        Cancel
                    </button>
                    <button @click="clearAllWishlist()"
                            class="flex-1 px-4 py-2 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition-colors">
                        Clear All
                    </button>
                </div>
            </div>
        </div>
    </template>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8" x-show="wishlistCount > 0">
        @foreach($wishlistItems as $item)
            <div class="group relative flex flex-col bg-white rounded-3xl border border-slate-100 p-4 hover:shadow-xl transition-all duration-300"
                 x-data="wishlistItem({{ json_encode($item) }})"
                 x-show="!removing"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                    {{-- Product Image --}}
                    <div class="aspect-square rounded-2xl bg-slate-50 overflow-hidden relative mb-4">
                        <a href="{{ route('product.details', $item['slug']) }}">
                            <img src="{{ $item['image'] }}"
                                 alt="{{ $item['name'] }}"
                                 class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700">
                        </a>

                        {{-- Category Tag --}}
                        <div class="absolute top-3 left-3">
                            <span class="px-2 py-1 bg-white/90 backdrop-blur rounded-lg text-[10px] font-bold text-slate-600 uppercase tracking-wider shadow-sm">
                                {{ $item['category'] }}
                            </span>
                        </div>

                        {{-- Remove from Wishlist Button --}}
                        <button @click="removing = true; await toggleWishlist(product.id)"
                                class="absolute top-3 right-3 w-8 h-8 rounded-lg bg-rose-500 text-white flex items-center justify-center shadow-lg hover:bg-rose-600 hover:scale-110 transition-all duration-200"
                                title="Remove from Wishlist">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Info Area --}}
                    <div class="px-1 flex-1 flex flex-col">
                        <h3 class="text-sm font-bold text-slate-900 leading-tight mb-3 line-clamp-2">
                            <a href="{{ route('product.details', $item['slug']) }}" class="hover:text-indigo-600 transition-colors">
                                {{ $item['name'] }}
                            </a>
                        </h3>

                        {{-- Variant Selection (Small Chips) --}}
                        <template x-if="hasVariants && uniqueVariantOptions.length > 0">
                            <div class="mb-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="(v, index) in uniqueVariantOptions" :key="v.id">
                                        <button type="button"
                                                @click="v.stock > 0 ? (selectedVariantIndex = String(index)) : null"
                                                :disabled="v.stock <= 0"
                                                :class="v.stock <= 0
                                                    ? 'border-slate-200 bg-slate-100 text-slate-300 cursor-not-allowed'
                                                    : (selectedVariantIndex === String(index)
                                                        ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                                                        : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600')"
                                                class="px-2 py-1 rounded-lg border text-[10px] font-bold leading-none transition-colors">
                                            <span x-text="`${(v.name || [v.color, v.size].filter(Boolean).join(' / ') || 'Variant')}${v.stock <= 0 ? ' - Out' : ''}`"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                        
                        <div class="mt-auto flex items-center justify-between">
                            @if(auth()->user()->hasRole('Outlet User'))
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest leading-none mb-1">Wholesale</span>
                                    <span class="text-lg font-black text-slate-900 leading-none">
                                        {{ $settings->currency_icon }}<span x-text="getSelectedPrice('outlet_price', 'wholesale')"></span>
                                    </span>
                                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest leading-none mb-1 mt-1">Selling Price</span>
                                    <span class="text-md font-black text-slate-900 leading-none">
                                        {{ $settings->currency_icon }}<span x-text="getSelectedPrice('price', 'selling')"></span>
                                    </span>
                                </div>
                            @elseif(auth()->user()->hasRole('User'))
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest leading-none mb-1">Wholesale</span>
                                    <span class="text-lg font-black text-slate-900 leading-none">
                                        {{ $settings->currency_icon }}<span x-text="getSelectedPrice('outlet_price', 'wholesale')"></span>
                                    </span>
                                </div>
                            @else
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Price</span>
                                    <span class="text-lg font-black text-slate-900 leading-none">
                                        {{ $settings->currency_icon }}<span x-text="getSelectedPrice('price', 'regular')"></span>
                                    </span>
                                </div>
                            @endif

                            {{-- <p class="text-[10px] font-bold text-slate-400 mb-2">
                                Min Order: <span x-text="minimumOrderQty"></span>
                            </p> --}}

                            <div class="flex items-center gap-2">
                                {{-- Quantity Input --}}
                                <input type="number" 
                                       x-model.number="qty" 
                                       :min="minimumOrderQty"
                                       :step="minimumOrderQty"
                                       @input="lastRawQty = $event.target.value"
                                       @change="normalizeQty()"
                                       class="w-12 h-10 text-center bg-slate-100 border-none rounded-md text-xs font-black text-slate-900 p-0 focus:ring-1 focus:ring-indigo-200 focus:outline-red-100">
                                
                                {{-- Add to Cart Button --}}
                                <button @click="addToCart()"
                                        :disabled="hasVariants && !selectedVariant"
                                        :class="hasVariants && !selectedVariant 
                                            ? 'bg-slate-200 cursor-not-allowed text-slate-400' 
                                            : 'bg-slate-900 hover:bg-indigo-600 text-white'"
                                        class="w-10 h-10 rounded-xl flex items-center justify-center transition-all active:scale-95 shadow-lg shadow-slate-100"
                                        :title="hasVariants && !selectedVariant ? 'Select a variant' : 'Add to Cart'">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
            </div>
        @endforeach
    </div>

    {{-- Empty Wishlist State --}}
    <div class="text-center py-32" x-show="wishlistCount === 0">
        <div class="w-28 h-28 bg-rose-50 rounded-[3rem] flex items-center justify-center mx-auto mb-8 border-2 border-rose-100">
            <svg class="w-14 h-14 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-black text-slate-900 mb-4">Your wishlist is empty</h2>
        <p class="text-slate-500 font-medium mb-10 max-w-md mx-auto">
            Save products you love by clicking the heart icon on any product card.
        </p>
        <a href="{{ route('shop') }}" class="inline-flex items-center gap-3 px-12 py-5 bg-slate-900 text-white font-black rounded-3xl hover:bg-indigo-600 transition-all shadow-2xl shadow-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            Browse Products
        </a>
    </div>
</div>
@endsection

@section('scripts')
    @include('frontend.partials.wishlist-page-script')
@endsection
