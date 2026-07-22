<x-guest-layout>
    <x-slot:heading>
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">
            Verify your email
        </h2>
    </x-slot:heading>

    <div class="mb-4 text-sm text-gray-600 text-center animate-fade-in-up" style="animation-delay: 100ms;">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600 text-center animate-fade-in-up" style="animation-delay: 200ms;">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-6 flex flex-col space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div class="animate-fade-in-up" style="animation-delay: 300ms;">
                <button type="submit" class="flex w-full justify-center rounded-lg bg-linear-to-r from-indigo-600 to-purple-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-lg hover:from-indigo-500 hover:to-purple-500 hover:shadow-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transform transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                    Resend Verification Email
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center animate-fade-in-up" style="animation-delay: 400ms;">
            @csrf

            <button type="submit" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-500 hover:underline transition-all duration-200">
                Log Out
            </button>
        </form>
    </div>
</x-guest-layout>
