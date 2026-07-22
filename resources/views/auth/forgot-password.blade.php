<x-guest-layout>
    <x-slot:heading>
         <div class="text-center">
            <img src="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}" alt="Inventory System Logo" class="mx-auto h-20 w-auto mb-4">
            <h2 class="text-2xl font-bold leading-9 tracking-tight text-gray-900">
                Inventory Management Systems
            </h2>
        </div>
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">
            Forgot password?
        </h2>
    </x-slot:heading>

    <div class="mb-4 text-sm text-gray-600 text-center animate-fade-in-up" style="animation-delay: 100ms;">
        {{ __('Just let us know your email address and we will email you a password reset link.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 animate-fade-in-up" style="animation-delay: 200ms;" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="animate-fade-in-up" style="animation-delay: 300ms;">
            <div class="relative mt-2">
                <input id="email" 
                       name="email" 
                       type="email" 
                       required 
                       autofocus
                       value="{{ old('email') }}"
                       placeholder=" "
                       class="peer block w-full rounded-lg border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-transparent focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all duration-300 ease-in-out hover:ring-indigo-400 bg-white/50 backdrop-blur-sm focus:outline-none">
                <label for="email" 
                       class="absolute left-3 top-0 z-10 -translate-y-1/2 px-1 text-xs font-medium text-gray-500 transition-all duration-200 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:text-xs peer-focus:text-indigo-600 bg-white/0 backdrop-blur-none peer-focus:bg-white peer-focus:backdrop-blur-sm rounded-sm">
                    Email address
                </label>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="animate-fade-in-up" style="animation-delay: 400ms;">
            <button type="submit" class="flex w-full justify-center rounded-lg bg-linear-to-r from-indigo-600 to-purple-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-lg hover:from-indigo-500 hover:to-purple-500 hover:shadow-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transform transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                Email Password Reset Link
            </button>
        </div>

        <div class="text-sm leading-6 text-center animate-fade-in-up" style="animation-delay: 500ms;">
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 hover:underline transition-all duration-200">
                Back to sign in
            </a>
        </div>
    </form>
</x-guest-layout>
