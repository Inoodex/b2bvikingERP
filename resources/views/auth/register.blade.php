<x-guest-layout>
    <x-slot:heading>
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">
            Create an account
        </h2>
    </x-slot:heading>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div class="animate-fade-in-up" style="animation-delay: 100ms;">
            <div class="relative">
                <input id="name" 
                       name="name" 
                       type="text" 
                       autocomplete="name" 
                       required 
                       value="{{ old('name') }}"
                       placeholder=" "
                       class="peer block w-full rounded-lg border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-transparent focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all duration-300 ease-in-out hover:ring-indigo-400 bg-white/50 backdrop-blur-sm focus:outline-none">
                <label for="name" 
                       class="absolute left-3 top-0 z-10 -translate-y-1/2 px-1 text-xs font-medium text-gray-500 transition-all duration-200 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-placeholder-shown:bg-transparent peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:text-xs peer-focus:text-indigo-600 bg-white peer-focus:bg-white rounded-sm">
                    Full Name
                </label>
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="animate-fade-in-up" style="animation-delay: 200ms;">
            <div class="relative mt-2">
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
        <div class="animate-fade-in-up" style="animation-delay: 300ms;">
            <div class="relative mt-2" x-data="{ showPassword: false }">
                <input id="password" 
                       name="password" 
                       :type="showPassword ? 'text' : 'password'"
                       autocomplete="new-password" 
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

        <!-- Confirm Password -->
        <div class="animate-fade-in-up" style="animation-delay: 400ms;">
            <div class="relative mt-2" x-data="{ showPassword: false }">
                <input id="password_confirmation" 
                       name="password_confirmation" 
                       :type="showPassword ? 'text' : 'password'"
                       autocomplete="new-password" 
                       required 
                       placeholder=" "
                       class="peer block w-full rounded-lg border-0 py-2.5 px-3 pr-11 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-transparent focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all duration-300 ease-in-out hover:ring-indigo-400 bg-white/50 backdrop-blur-sm focus:outline-none">
                <label for="password_confirmation" 
                       class="absolute left-3 top-0 z-10 -translate-y-1/2 px-1 text-xs font-medium text-gray-500 transition-all duration-200 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-placeholder-shown:bg-transparent peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:text-xs peer-focus:text-indigo-600 bg-white peer-focus:bg-white rounded-sm">
                    Confirm Password
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
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div x-data="{ role: '{{ old('role_id', '2') }}' }" class="space-y-6">
            <!-- Role Selection -->
            <div class="animate-fade-in-up" style="animation-delay: 450ms;">
                <div class="relative mt-2">
                    <select id="role_id" 
                            name="role_id" 
                            x-model="role"
                            required 
                            class="peer block w-full rounded-lg border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all duration-300 ease-in-out hover:ring-indigo-400 bg-white/50 backdrop-blur-sm focus:outline-none">
                        <option value="2">User</option>
                        <option value="3">Outlet User</option>
                    </select>
                    <label for="role_id" 
                           class="absolute left-3 top-0 z-10 -translate-y-1/2 px-1 text-xs font-medium text-gray-500 transition-all duration-200 bg-white peer-focus:text-indigo-600 rounded-sm">
                        Select Role
                    </label>
                </div>
                <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
            </div>

            <!-- Outlet Details (Shown for both roles) -->
            <div class="space-y-6 animate-fade-in">
                <!-- Outlet Name -->
                <div class="animate-fade-in-up">
                    <div class="relative mt-2">
                        <input id="outlet_name" 
                               name="outlet_name" 
                               type="text" 
                               value="{{ old('outlet_name') }}"
                               placeholder=" "
                               class="peer block w-full rounded-lg border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-transparent focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all duration-300 ease-in-out hover:ring-indigo-400 bg-white/50 backdrop-blur-sm focus:outline-none">
                        <label for="outlet_name" 
                               class="absolute left-3 top-0 z-10 -translate-y-1/2 px-1 text-xs font-medium text-gray-500 transition-all duration-200 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-placeholder-shown:bg-transparent peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:text-xs peer-focus:text-indigo-600 bg-white peer-focus:bg-white rounded-sm">
                            Outlet/Shop Name
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('outlet_name')" class="mt-2" />
                </div>

                <!-- Address -->
                <div class="animate-fade-in-up">
                    <div class="relative mt-2">
                        <textarea id="address" 
                               name="address" 
                               placeholder=" "
                               class="peer block w-full rounded-lg border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-transparent focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all duration-300 ease-in-out hover:ring-indigo-400 bg-white/50 backdrop-blur-sm focus:outline-none">{{ old('address') }}</textarea>
                        <label for="address" 
                               class="absolute left-3 top-0 z-10 -translate-y-1/2 px-1 text-xs font-medium text-gray-500 transition-all duration-200 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-placeholder-shown:bg-transparent peer-focus:top-0 peer-focus:-translate-y-1/2 peer-focus:text-xs peer-focus:text-indigo-600 bg-white peer-focus:bg-white rounded-sm">
                             Address
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="animate-fade-in-up" style="animation-delay: 500ms;">
            <button type="submit" class="flex w-full justify-center rounded-lg bg-linear-to-r from-indigo-600 to-purple-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-lg hover:from-indigo-500 hover:to-purple-500 hover:shadow-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transform transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                Register
            </button>
        </div>

        <div class="text-sm leading-6 text-center animate-fade-in-up" style="animation-delay: 600ms;">
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 hover:underline transition-all duration-200">
                Already registered? Sign in
            </a>
        </div>
    </form>
</x-guest-layout>
