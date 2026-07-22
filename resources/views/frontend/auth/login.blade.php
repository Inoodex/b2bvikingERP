@extends('layouts.frontend')

@section('title', 'Login - ' . config('app.name', 'Inventory B2B'))

@section('content')
    @php
        $currencyIcon = optional($settings)->currency_icon ?? 'Tk';
        $sliders = collect($sliders ?? []);
    @endphp

    @include('frontend.partials.home-slider', ['sliders' => $sliders, 'currencyIcon' => $currencyIcon])

    <section class="border-b border-slate-200 bg-[#f1efeb]">
        <div class="mx-auto max-w-6xl px-4 py-10 text-center sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold uppercase tracking-[0.35em] text-slate-900 sm:text-3xl">My Account</h1>
            <p class="mt-2 text-xs uppercase tracking-[0.25em] text-slate-500">Home</p>
        </div>
    </section>

    <section class="py-12">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-xl font-semibold uppercase tracking-[0.3em] text-slate-900 sm:text-2xl">Login</h2>
            <p class="mt-3 text-center text-sm text-slate-500">Thank you for visiting our site!</p>

            <div class="mt-8 border border-dashed border-slate-300 bg-white/80 p-8 sm:p-10">
                @if (session('error'))
                    <div class="mb-6 border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-600">Username or Email *</label>
                        <input type="email"
                            id="email"
                            name="email"
                            required
                            autofocus
                            value="{{ old('email') }}"
                            class="mt-2 w-full border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-slate-900"
                            placeholder="outlet@merchant.com">
                        @error('email')
                            <p class="mt-2 text-[11px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ showPassword: false }">
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-600">Password *</label>
                            <a href="{{ route('password.request') }}" class="text-[11px] uppercase tracking-[0.1em] text-slate-600 hover:text-slate-900">Forgotten password?</a>
                        </div>
                        <div class="relative mt-2">
                            <input :type="showPassword ? 'text' : 'password'"
                                id="password"
                                name="password"
                                required
                                class="w-full border border-slate-300 bg-white px-4 py-2.5 pr-11 text-sm text-slate-900 outline-none focus:border-slate-900"
                                placeholder="........">
                            <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-700"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'">
                                <svg x-show="!showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 3l18 18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M10.58 10.58a3 3 0 004.24 4.24" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9.88 4.24A9.94 9.94 0 0112 4c4.478 0 8.268 2.943 9.542 7a10.49 10.49 0 01-4.043 5.383" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6.11 6.11C4.23 7.27 2.81 9.02 2.458 12c.58 1.85 1.75 3.44 3.315 4.59" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-[11px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="remember" id="remember" class="h-4 w-4 border-slate-300">
                        <label for="remember" class="text-sm text-slate-600">Remember me</label>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 py-3 text-[12px] font-semibold uppercase tracking-[0.35em] text-white transition-colors hover:bg-black">
                        Login
                    </button>
                </form>
            </div>

            <div class="mt-6 space-y-2 text-center">
                <a href="{{ route('register') }}" class="block text-xs font-medium uppercase tracking-[0.2em] text-slate-600 hover:text-slate-900 sm:text-sm">
                    New here? Create an account
                </a>
                <a href="{{ route('admin.login') }}" class="block text-xs font-medium uppercase tracking-[0.2em] text-slate-600 hover:text-slate-900 sm:text-sm">
                    Administrative Staff Access Portal ->
                </a>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('frontend.partials.home-slider-script')
@endsection
