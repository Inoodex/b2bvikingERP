<x-guest-layout>
    <x-slot:heading>
        <div class="text-center">
            <img src="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}" alt="Inventory System Logo" class="mx-auto h-20 w-auto mb-4">
            <h2 class="text-2xl font-bold leading-9 tracking-tight text-gray-900">
                Admin Verification
            </h2>
            <p class="mt-2 text-sm text-gray-800">
                We sent a verification code to your email address. Enter it to continue.
            </p>
        </div>
    </x-slot:heading>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('admin.two-factor.verify') }}" class="space-y-6">
        @csrf

        <div class="animate-fade-in-up" style="animation-delay: 100ms;">
            <div class="relative">
                <input id="code"
                       name="code"
                       type="text"
                       autocomplete="one-time-code"
                       required
                       maxlength="8"
                       value="{{ old('code') }}"
                       placeholder=" "
                       class="peer block w-full rounded-lg border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-transparent focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all duration-300 ease-in-out hover:ring-indigo-400 bg-white/50 backdrop-blur-sm focus:outline-none tracking-[0.25em] text-center">
                <label for="code"
                       class="absolute left-3 top-0 z-10 -translate-y-1/2 px-1 text-xs font-medium text-gray-500 transition-all duration-200 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-placeholder-shown:bg-transparent peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:text-xs peer-focus:text-indigo-600 bg-white peer-focus:bg-white rounded-sm">
                    Verification code
                </label>
            </div>
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="animate-fade-in-up" style="animation-delay: 200ms;">
            <button type="submit" class="flex w-full justify-center rounded-lg bg-linear-to-r from-indigo-600 to-purple-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-lg hover:from-indigo-500 hover:to-purple-500 hover:shadow-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transform transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                Verify and continue
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.two-factor.resend') }}" class="mt-6 text-center animate-fade-in-up" style="animation-delay: 300ms;">
        @csrf
        <button type="submit" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 hover:underline transition-all duration-200">
            Resend code
        </button>
    </form>
</x-guest-layout>
