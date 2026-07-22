{{-- =====================================================
     GLOBAL ALERT / NOTIFICATION SYSTEM
     ===================================================== --}}
<div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none">
    <template x-for="note in notifications" :key="note.id">
        <div x-show="note.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="pointer-events-auto bg-white border-l-4 rounded-xl shadow-lg px-3.5 py-3 w-auto max-w-[360px] flex items-start gap-3"
             :class="{
                'border-indigo-600': note.type === 'success',
                'border-amber-500': note.type === 'warning',
                'border-rose-500': note.type === 'error'
             }">
            <div class="shrink-0 mt-0.5">
                <template x-if="note.type === 'success'">
                    <svg class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                </template>
                <template x-if="note.type === 'warning'">
                    <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                </template>
                <template x-if="note.type === 'error'">
                    <svg class="h-5 w-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.536-10.95a1 1 0 10-1.414-1.414L10 7.757 7.878 5.636a1 1 0 10-1.414 1.414L8.586 9.17l-2.122 2.122a1 1 0 001.414 1.414L10 10.585l2.121 2.121a1 1 0 001.415-1.414L11.414 9.17l2.122-2.121z" clip-rule="evenodd"></path></svg>
                </template>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] leading-snug font-medium text-slate-800 break-words" x-text="note.message"></p>
            </div>
            <button @click="hideNotification(note.id)" class="text-slate-400 hover:text-slate-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </template>
</div>

{{-- =====================================================
     NAVIGATION BAR
     ===================================================== --}}
<nav class="bg-white border-b border-slate-200 sticky top-0 z-50" x-data="navbarSearch(@js($settings->currency_icon ?? 'Tk'))">
    @php
        $isAdminAuth = auth()->check() && auth()->user()->hasRole('Admin');
        $isFrontendCustomer = auth()->check() && !$isAdminAuth;
        $authUser = auth()->user();
        $userImage = null;
        if ($authUser && !empty($authUser->image)) {
            $rawImage = (string) $authUser->image;
            if (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://') || str_starts_with($rawImage, '//') || str_starts_with($rawImage, 'data:')) {
                $userImage = $rawImage;
            } else {
                $userImage = asset(ltrim($rawImage, '/'));
            }
        }
    @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 md:h-20">

            {{-- Logo & Site Name --}}
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 overflow-hidden rounded-md border border-slate-100 flex items-center justify-center p-1 bg-white transition-all duration-300">
                        <img src="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}" alt="{{ config('app.name') }}" class="w-full h-full object-contain">
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="truncate text-[14px] md:text-xl font-bold text-slate-900 leading-none group-hover:text-indigo-600 transition-colors">{{ config('app.name', 'Inventory B2B') }}</span>
                        <span class="truncate text-[10px] md:text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-1">{{ optional($settings)->site_name ?? 'B2B Portal' }}</span>
                    </div>
                </a>
            </div>

            {{-- Navigation Links (desktop only, to avoid tablet overflow) --}}
            <div class="hidden lg:flex items-center gap-8">
                <a href="{{ route('home') }}"
                   class="text-[12px] font-medium uppercase tracking-[0.1em] transition-colors {{ request()->routeIs('home') ? 'text-blue-600' : 'text-slate-600 hover:text-slate-900' }}">
                    Home
                </a>
                <a href="{{ route('shop') }}"
                   class="text-[12px] font-medium uppercase tracking-[0.1em] transition-colors {{ request()->routeIs('shop') ? 'text-blue-600' : 'text-slate-600 hover:text-slate-900' }}">
                    B2B Shop
                </a>
                <a href="{{ route('about') }}"
                   class="text-[12px] font-medium uppercase tracking-[0.1em] transition-colors {{ request()->routeIs('about') ? 'text-blue-600' : 'text-slate-600 hover:text-slate-900' }}">
                    About
                </a>
                <a href="{{ route('contact') }}"
                   class="text-[12px] font-medium uppercase tracking-[0.1em] transition-colors {{ request()->routeIs('contact') ? 'text-blue-600' : 'text-slate-600 hover:text-slate-900' }}">
                    Contact Us
                </a>
            </div>

            {{-- Right Icons --}}
            <div class="flex items-center gap-1 md:gap-5">
                

                {{-- Search --}}
                <button
                    @click="toggleSearch()"
                    :aria-expanded="searchOpen.toString()"
                    class="relative grid h-10 w-10 place-items-center rounded-xl text-slate-500 transition duration-300 hover:bg-slate-100/80 hover:text-slate-900"
                    aria-label="Search">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>

                {{-- Wishlist --}}
                @if($isFrontendCustomer)
                    {{-- Logged in: link to wishlist page with live count badge --}}
                    <a href="{{ route('wishlist.index') }}"
                       class="relative grid h-10 w-10 place-items-center rounded-xl text-slate-500 transition duration-300 hover:bg-slate-100/80 hover:text-slate-900"
                       title="My Wishlist">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        <span
                            x-show="wishlistCount > 0"
                            x-text="wishlistCount"
                            class="pointer-events-none absolute -top-1 -right-1 inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-gradient-to-b from-rose-500 to-rose-600 px-1.5 text-[10px] font-extrabold tabular-nums leading-none text-white shadow-[0_8px_18px_rgba(244,63,94,0.35)] ring-2 ring-white whitespace-nowrap"
                            x-cloak
                        ></span>
                    </a>

                    {{-- <a href="{{ route('account.index') }}"
                       class="p-2.5 rounded-xl text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all duration-300 relative"
                       title="My Orders">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-3V3a1 1 0 00-2 0v2H9V3a1 1 0 00-2 0v2H6a2 2 0 00-2 2v6m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4m5 4h6"></path></svg>
                    </a> --}}
                @elseif($isAdminAuth)
                    <a href="{{ route('admin.dashboard') }}"
                       class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] text-slate-900 border border-slate-200 hover:border-slate-400 transition-all duration-300"
                       title="Go to Admin Dashboard">
                        Admin Dashboard
                    </a>
                @else
                    {{-- Guest: redirect to login --}}
                    <a href="{{ route('login') }}"
                       class="relative grid h-10 w-10 place-items-center rounded-xl text-slate-500 transition duration-300 hover:bg-slate-100/80 hover:text-slate-900"
                       title="Login to use Wishlist">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    </a>
                @endif

                {{-- Cart --}}
                <button @click="isCartOpen = true" class="relative grid h-10 w-10 place-items-center rounded-xl text-slate-500 transition duration-300 hover:bg-slate-100/80 hover:text-slate-900">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span
                        x-show="cartCount > 0"
                        x-text="cartCount"
                        class="pointer-events-none absolute -top-1 -right-1 inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-gradient-to-b from-rose-500 to-rose-600 px-1.5 text-[10px] font-extrabold tabular-nums leading-none text-white shadow-[0_8px_18px_rgba(244,63,94,0.35)] ring-2 ring-white whitespace-nowrap"
                    ></span>
                </button>

                <div class="h-8 w-px bg-slate-200 mx-2 hidden sm:block"></div>

                
                {{-- User Menu --}}
                @if($isFrontendCustomer)
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 p-1 pl-2 rounded-full hover:bg-slate-100 transition-all duration-300 group">
                            <span class="text-[12px] font-semibold tracking-[0.05em] text-slate-700 hidden lg:block">{{ Auth::user()->name }}</span>
                            <div class="w-9 h-9 border border-slate-200 rounded-full overflow-hidden flex items-center justify-center text-slate-700 font-bold bg-white">
                                @if($userImage)
                                    <img src="{{ $userImage }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                                @else
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                @endif
                            </div>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-2xl py-2 z-50 overflow-hidden" x-cloak>
                            <div class="px-4 py-3 border-b border-slate-50">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">My Account</p>
                                <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->name }}</p>
                            </div>
                            <a href="{{ route('account.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.879 17.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                My Account
                            </a>
                            @if(Auth::user()->hasRole('Admin'))
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-indigo-600 border-t border-slate-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                    Admin Dashboard
                                </a>
                            @endif
                            <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-rose-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                My Wishlist
                                <span x-show="wishlistCount > 0"
                                      x-text="'(' + wishlistCount + ')'"
                                      class="ml-auto text-xs font-bold text-rose-500" x-cloak></span>
                            </a>
                            {{-- <a href="{{ route('account.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-3V3a1 1 0 00-2 0v2H9V3a1 1 0 00-2 0v2H6a2 2 0 00-2 2v6m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4m5 4h6"></path></svg>
                                My Orders
                            </a> --}}
                            {{-- Logout: clear cart/wishlist from localStorage before submitting --}}
                            <form method="POST" action="{{ route('logout') }}" @submit.prevent="handleLogout($el)">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                    {{-- Mobile/Tablet Menu --}}
                <button type="button"
                        @click="openMobileNav()"
                        class="lg:hidden relative grid h-10 w-10 place-items-center rounded-xl text-slate-500 transition duration-300 hover:bg-slate-100/80 hover:text-slate-900"
                        :aria-expanded="mobileNavOpen.toString()"
                        aria-label="Open menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                @elseif($isAdminAuth)
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 transition-all duration-300 group flex items-center gap-2">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span class="text-sm font-bold hidden sm:block">Logout</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="p-2 text-slate-500 hover:text-slate-900 transition-all duration-300 group flex items-center gap-2">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="text-[12px] font-semibold uppercase tracking-[0.1em] hidden sm:block">Login</span>
                    </a>
                @endif
            </div>

        </div>
        <div x-show="searchOpen"
             x-transition
             x-cloak
             @keydown.escape.window="closeSearch()"
             @click.away="closeSearch()"
             class="pb-3">
            <form action="{{ route('shop') }}" method="GET" class="relative" @submit="closeSearch()">
                <input
                    type="text"
                    name="search"
                    x-model.trim="searchQuery"
                    x-ref="searchInput"
                    @input="handleSearchInput()"
                    placeholder="Search products by name / SKU / number"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-24 text-sm font-medium text-slate-700 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <button type="submit"
                        class="absolute right-1.5 top-1/2 h-8 -translate-y-1/2 rounded-lg bg-slate-900 px-3 text-[11px] font-bold uppercase tracking-[0.1em] text-white hover:bg-indigo-600">
                    Search
                </button>
            </form>

            <div x-show="searchOpen && searchQuery.length >= 2"
                 x-transition
                 class="mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                <div x-show="searchLoading" class="px-4 py-3 text-xs font-semibold text-slate-500">
                    Searching products...
                </div>

                <template x-if="!searchLoading && searchResults.length > 0">
                    <ul class="max-h-96 overflow-y-auto divide-y divide-slate-100">
                        <template x-for="item in searchResults" :key="item.id">
                            <li>
                                <a :href="item.url"
                                   @click="closeSearch()"
                                   class="flex items-center gap-3 px-3 py-2.5 transition hover:bg-slate-50">
                                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-md border border-slate-200 bg-slate-100">
                                        <img x-show="item.image" :src="item.image" :alt="item.name" class="h-full w-full object-cover">
                                        <div x-show="!item.image" class="flex h-full w-full items-center justify-center text-[9px] font-bold uppercase text-slate-400">No Img</div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-800" x-text="item.name"></p>
                                        <p class="truncate text-[11px] text-slate-500">
                                            <span x-text="item.category"></span>
                                            <span x-show="item.product_number"> • #<span x-text="item.product_number"></span></span>
                                        </p>
                                    </div>
                                    <div class="text-right text-xs font-bold text-indigo-600" x-text="formatPrice(item.price)"></div>
                                </a>
                            </li>
                        </template>
                    </ul>
                </template>

                <div x-show="showNoResults" class="px-4 py-3 text-xs font-semibold text-slate-500">
                    No product found for "<span x-text="searchQuery"></span>".
                </div>

                <a :href="shopSearchUrl"
                   @click="closeSearch()"
                   class="block border-t border-slate-100 px-4 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-slate-600 transition hover:bg-slate-50 hover:text-indigo-600">
                    View all results in shop
                </a>
            </div>
        </div>

        <!-- Mobile Nav Drawer -->
        <div x-show="mobileNavOpen"
             x-cloak
             @keydown.escape.window="closeMobileNav()"
             class="fixed inset-0 z-[90] lg:hidden"
             role="dialog"
             aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                 x-transition.opacity
                 @click="closeMobileNav()"></div>

            <div class="absolute inset-y-0 left-0 w-[min(92vw,360px)] bg-white shadow-2xl flex h-full flex-col"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4">
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Menu</p>
                        <p class="truncate text-base font-semibold text-slate-900">{{ config('app.name', 'Inventory B2B') }}</p>
                    </div>
                    <button type="button"
                            @click="closeMobileNav()"
                            class="grid h-10 w-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition"
                            aria-label="Close menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="px-2 py-3 flex-1 overflow-y-auto">
                    @if(request()->routeIs('shop'))
                        <button type="button"
                                @click="openShopFilters()"
                                class="lg:hidden w-full flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            <span>Filters</span>
                            <span class="text-slate-400">→</span>
                        </button>
                    @endif
                    <a href="{{ route('home') }}"
                       @click="closeMobileNav()"
                       class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('home') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span>Home</span>
                        <span class="text-slate-400">→</span>
                    </a>
                    <a href="{{ route('shop') }}"
                       @click="closeMobileNav()"
                       class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('shop') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span>B2B Shop</span>
                        <span class="text-slate-400">→</span>
                    </a>
                    <a href="{{ route('about') }}"
                       @click="closeMobileNav()"
                       class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('about') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span>About</span>
                        <span class="text-slate-400">→</span>
                    </a>
                    <a href="{{ route('contact') }}"
                       @click="closeMobileNav()"
                       class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('contact') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span>Contact Us</span>
                        <span class="text-slate-400">→</span>
                    </a>

                    @if($isFrontendCustomer)
                        <div class="my-3 border-t border-slate-100"></div>
                        <a href="{{ route('account.index') }}"
                           @click="closeMobileNav()"
                           class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            <span>My Account</span>
                            <span class="text-slate-400">→</span>
                        </a>
                    @elseif($isAdminAuth)
                        <div class="my-3 border-t border-slate-100"></div>
                        <a href="{{ route('admin.dashboard') }}"
                           @click="closeMobileNav()"
                           class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            <span>Admin Dashboard</span>
                            <span class="text-slate-400">→</span>
                        </a>
                    @endif
                </div>

                @if($isFrontendCustomer)
                    <div class="mt-auto border-t border-slate-100 px-4 py-4">
                        <form method="POST" action="{{ route('logout') }}" @submit.prevent="handleLogout($el); closeMobileNav()">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-rose-50 px-4 py-3 text-[11px] font-extrabold uppercase tracking-[0.2em] text-rose-700 hover:bg-rose-100 transition">
                                Logout
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('alpine:init', () => {
        if (window.__navbarSearchRegistered) {
            return;
        }
        window.__navbarSearchRegistered = true;

        Alpine.data('navbarSearch', (currencyIcon = 'Tk') => ({
            mobileNavOpen: false,
            searchOpen: false,
            searchQuery: @js((string) request('search', '')),
            searchResults: [],
            searchLoading: false,
            searchDebounceTimer: null,

            openMobileNav() {
                this.mobileNavOpen = true;
                this.searchOpen = false;
                document.documentElement.classList.add('overflow-hidden');
            },

            closeMobileNav() {
                this.mobileNavOpen = false;
                document.documentElement.classList.remove('overflow-hidden');
            },

            openShopFilters() {
                window.dispatchEvent(new CustomEvent('shop-open-filters'));
                this.closeMobileNav();
            },

            toggleSearch() {
                this.searchOpen = !this.searchOpen;
                if (this.searchOpen) {
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                    if (this.searchQuery.length >= 2) {
                        this.fetchLiveProducts();
                    }
                }
            },

            closeSearch() {
                this.searchOpen = false;
            },

            handleSearchInput() {
                if (this.searchDebounceTimer) {
                    clearTimeout(this.searchDebounceTimer);
                }

                if (this.searchQuery.length < 2) {
                    this.searchResults = [];
                    this.searchLoading = false;
                    return;
                }

                this.searchDebounceTimer = setTimeout(() => {
                    this.fetchLiveProducts();
                }, 250);
            },

            async fetchLiveProducts() {
                const query = this.searchQuery.trim();
                if (query.length < 2) {
                    this.searchResults = [];
                    this.searchLoading = false;
                    return;
                }

                this.searchLoading = true;

                try {
                    const endpoint = `{{ route('frontend.products.live-search') }}?q=${encodeURIComponent(query)}`;
                    const response = await fetch(endpoint, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Live search request failed');
                    }

                    const data = await response.json();
                    this.searchResults = Array.isArray(data.results) ? data.results : [];
                } catch (error) {
                    this.searchResults = [];
                } finally {
                    this.searchLoading = false;
                }
            },

            formatPrice(price) {
                const amount = Number(price || 0);
                return `${currencyIcon}${amount.toFixed(2)}`;
            },

            get showNoResults() {
                return this.searchQuery.length >= 2 && !this.searchLoading && this.searchResults.length === 0;
            },

            get shopSearchUrl() {
                const url = new URL('{{ route('shop') }}', window.location.origin);
                if (this.searchQuery.trim() !== '') {
                    url.searchParams.set('search', this.searchQuery.trim());
                }
                return url.toString();
            },
        }));
    });
</script>
