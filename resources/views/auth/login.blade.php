<x-guest-layout>
    <x-slot:heading>
        <div class="text-center">
            <img src="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}" alt="Inventory System Logo" class="mx-auto h-20 w-auto mb-4">
            <h2 class="text-2xl font-bold leading-9 tracking-tight text-gray-900">
                Inventory Management Systems
            </h2>
            <p class="mt-2 text-sm text-gray-800">
                Danish Souvenirs, Viking Souvenirs, Ducky Memories, Duck Haven and Hygge Cotton
            </p>
        </div>
    </x-slot:heading>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="animate-fade-in-up" style="animation-delay: 100ms;">
            <div class="relative">
                <input id="email" 
                       name="email" 
                       type="email" 
                       autocomplete="email" 
                       required 
                       value="{{ old('email') }}"
                       placeholder=" "
                       class="peer block w-full rounded-lg border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-transparent focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all duration-300 ease-in-out hover:ring-indigo-400 bg-white/50 backdrop-blur-sm focus:outline-none">
                <label for="email" 
                       class="absolute left-3 top-0 z-10 -translate-y-1/2 px-1 text-xs font-medium text-gray-500 transition-all duration-200 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-placeholder-shown:bg-transparent peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:text-xs peer-focus:text-indigo-600 bg-white peer-focus:bg-white rounded-sm">
                    Email address
                </label>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="animate-fade-in-up" style="animation-delay: 200ms;">
            <div class="relative mt-2" x-data="{ showPassword: false }">
                <input id="password" 
                       name="password" 
                       :type="showPassword ? 'text' : 'password'"
                       autocomplete="current-password" 
                       required 
                       placeholder=" "
                       class="peer block w-full rounded-lg border-0 py-2.5 px-3 pr-11 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-transparent focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all duration-300 ease-in-out hover:ring-indigo-400 bg-white/50 backdrop-blur-sm focus:outline-none">
                <label for="password" 
                       class="absolute left-3 top-0 z-10 -translate-y-1/2 px-1 text-xs font-medium text-gray-500 transition-all duration-200 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-placeholder-shown:bg-transparent peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:text-xs peer-focus:text-indigo-600 bg-white peer-focus:bg-white rounded-sm">
                    Password
                </label>
                <button type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 transition"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'">
                    <svg x-show="!showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 3l18 18"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M10.58 10.58a3 3 0 004.24 4.24"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9.88 4.24A9.94 9.94 0 0112 4c4.478 0 8.268 2.943 9.542 7a10.49 10.49 0 01-4.043 5.383"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6.11 6.11C4.23 7.27 2.81 9.02 2.458 12c.58 1.85 1.75 3.44 3.315 4.59"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between animate-fade-in-up" style="animation-delay: 300ms;">
            <div class="flex items-center">
                <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 transition-colors duration-200">
                <label for="remember_me" class="ml-3 block text-sm leading-6 text-gray-900">Remember me</label>
            </div>

            @if (Route::has('password.request'))
                <div class="text-sm leading-6">
                    <a href="{{ route('password.request') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 hover:underline transition-all duration-200">Forgot password?</a>
                </div>
            @endif
        </div>

        <div class="animate-fade-in-up" style="animation-delay: 400ms;">
            <button type="submit" class="flex w-full justify-center rounded-lg bg-linear-to-r from-indigo-600 to-purple-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-lg hover:from-indigo-500 hover:to-purple-500 hover:shadow-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transform transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                Sign in
            </button>
        </div>

        <div class="text-sm leading-6 text-center animate-fade-in-up" style="animation-delay: 500ms;">
            <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 hover:underline transition-all duration-200">
                Don't have an account? Register here
            </a>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-gray-500 text-xs mb-2 text-uppercase tracking-widest font-semibold">Visit Our Store</p>
                <a href="https://danishsouvenir.dk/" target="_blank" class="font-bold  
                text-indigo-600 hover:text-red-900 transition-all duration-200 flex items-center justify-center gap-2">
                    <span>Explore danishsouvenir.dk</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
