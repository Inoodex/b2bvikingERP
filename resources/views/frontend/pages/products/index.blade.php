@extends('layouts.frontend')
@section('content')
<!-- Hero Section -->
<div class="relative bg-slate-900 py-24 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20"></div>
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-600/20 rounded-full blur-[120px]"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-[120px]"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-block px-4 py-1.5 bg-indigo-500/10 border border-indigo-500/20 rounded-full text-indigo-400 text-xs font-black uppercase tracking-[0.2em] mb-6 animate-pulse">
            Premium B2B Commerce
        </span>
        <h1 class="text-5xl md:text-7xl font-black text-white tracking-tighter mb-6">
            Elevate Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-indigo-200">Inventory</span>
        </h1>
        <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto font-medium leading-relaxed">
            Direct access to specialized Viking souvenirs and premium Danish collections. Designed for efficiency, built for growth.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20 pb-24">
    <!-- Search & Filter Bar -->
    <div class="bg-white/80 backdrop-blur-2xl border border-slate-200 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 p-4 mb-16">
        <div class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative group">
                <input type="text" name="search" value="{{ request('search') }}"
                       x-model="searchQuery"
                       @keyup.enter="window.location.href = `?search=${searchQuery}`"
                       placeholder="Search by product name or serial number..."
                       class="w-full bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl pl-14 pr-6 py-4 outline-none transition-all font-bold text-slate-700 placeholder-slate-400">
                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <div class="lg:w-64 relative">
                <select @change="window.location.href = `?category=${$event.target.value}`"
                        class="w-full bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl px-6 py-4 outline-none transition-all font-bold text-slate-700 appearance-none cursor-pointer">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>

            <button @click="window.location.href = `?search=${searchQuery}`" class="bg-slate-900 text-white px-10 py-4 rounded-2xl font-black hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200 active:scale-95">
                FILTER
            </button>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10">
        @forelse($products as $product)
            <div class="group relative flex flex-col bg-white rounded-[2.5rem] border border-slate-100 p-4 hover:border-indigo-200 hover:shadow-[0_20px_60px_-15px_rgba(79,70,229,0.15)] transition-all duration-500">
                <!-- Image Container -->
                <div class="aspect-4/5 rounded-4xl bg-slate-50 overflow-hidden relative mb-6">
                    @php
                        $imagePath = $product->thumb_image;
                        $displayPath = (strpos($imagePath, 'http') === 0)
                            ? $imagePath
                            : (file_exists(public_path($imagePath))
                                ? asset($imagePath)
                                : asset('storage/' . $imagePath));
                    @endphp
                    <img src="{{ $displayPath }}"
                         alt="{{ $product->name }}"
                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-1000">

                    <!-- Overlay for Guests -->
                    @guest
                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-500">
                            <svg class="w-10 h-10 text-white mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <a href="{{ route('login') }}" class="bg-white text-slate-900 px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest shadow-2xl transform hover:scale-105 active:scale-95 transition-all">
                                Authorized Login
                            </a>
                        </div>
                    @endguest

                    <!-- Category Tag -->
                    <div class="absolute top-5 left-5">
                        <span class="px-4 py-1.5 bg-white/90 backdrop-blur border border-slate-100 rounded-full text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] shadow-sm">
                            {{ $product->category->name ?? 'General' }}
                        </span>
                    </div>

                    {{-- Wishlist Heart Button (top-right) --}}
                    @auth
                        <button @click.prevent="toggleWishlist({{ $product->id }})"
                                class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/90 backdrop-blur border border-slate-100 flex items-center justify-center shadow-md hover:scale-110 transition-all duration-200 group/heart"
                                :class="isWishlisted({{ $product->id }}) ? 'text-rose-500 border-rose-200 bg-rose-50' : 'text-slate-400 hover:text-rose-500'"
                                :title="isWishlisted({{ $product->id }}) ? 'Remove from Wishlist' : 'Add to Wishlist'">
                            {{-- solid heart when wishlisted --}}
                            <template x-if="isWishlisted({{ $product->id }})">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                            </template>
                            {{-- outline heart when not wishlisted --}}
                            <template x-if="!isWishlisted({{ $product->id }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </template>
                        </button>
                    @endauth
                </div>

                <!-- Info Area -->
                <div class="px-2 flex-1 flex flex-col">
                    <h3 class="text-xl font-bold text-slate-900 leading-tight mb-2 line-clamp-2">
                        <a href="{{ route('product.details', $product->slug) }}" class="hover:text-indigo-600 transition-colors">{{ $product->name }}</a>
                    </h3>

                    <div class="mt-auto pt-6 flex items-center justify-between border-t border-slate-50">
                        @auth
                            <div class="flex flex-col">
                                @if(auth()->user()->hasRole('Outlet User'))
                                    <div class="space-y-1">
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">Wholesale</span>
                                            <span class="text-xl font-black text-slate-900 leading-none">
                                                {{$settings->currency_icon}}{{ number_format($product->outlet_price, 2) }}
                                            </span>
                                        </div>
                                        <div class="flex flex-col border-t border-slate-50 pt-1">
                                            <span class="text-[9px] font-black text-red-500 uppercase tracking-widest">Selling Price</span>
                                            <span class="text-xs font-bold text-slate-500">
                                                {{$settings->currency_icon}}{{ number_format($product->price, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Price</span>
                                    <span class="text-2xl font-black text-slate-900 leading-none">
                                        {{$settings->currency_icon}}{{ number_format($product->outlet_price ?? $product->price, 2) }}
                                    </span>
                                @endif
                            </div>
                            <button @click="addToCart({{ $product->toJson() }})" class="w-14 h-14 bg-slate-900 text-white rounded-2xl flex items-center justify-center hover:bg-indigo-600 hover:rotate-6 shadow-xl shadow-slate-200 transition-all active:scale-95">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            </button>
                        @else
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Price</span>
                                <span class="text-xs font-bold text-rose-500 uppercase tracking-wider bg-rose-50 px-3 py-1 rounded-lg">Verification Required</span>
                            </div>
                            <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="col-span-full py-24 text-center">
                <div class="w-32 h-32 bg-slate-100 rounded-[3rem] flex items-center justify-center mx-auto mb-8 border border-slate-200 border-dashed">
                    <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-4">No matching inventory found</h3>
                <p class="text-slate-500 font-medium mb-10">We couldn't find anything matching your current filters.</p>
                <a href="{{ route('home') }}" class="inline-block px-12 py-5 bg-slate-900 text-white font-black rounded-3xl hover:bg-indigo-600 transition-all shadow-2xl shadow-slate-200">
                    CLEAR FILTERS
                </a>
            </div>
        @endforelse
    </div>

    <!-- Professional Pagination -->
    <div class="mt-24 border-t border-slate-100">
        {{ $products->links('vendor.pagination.tailwind') }}
    </div>
</div>
@endsection
