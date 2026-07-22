{{-- Footer --}}
@php
    $footerSiteName = optional($settings)->site_name ?? config('app.name', 'Inventory Management System');
    $footerPhone = optional($settings)->phone ?? '+4553713518';
    $footerEmail = optional($settings)->contact_email ?? 'contact@yourcompany.com';
    $footerAddress = optional($settings)->address ?? '';
    $footerPhoneHref = $footerPhone ? 'tel:' . preg_replace('/[^0-9+]/', '', $footerPhone) : null;
    $footerEmailHref = $footerEmail ? 'mailto:' . $footerEmail : null;
@endphp

<footer class="bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-10">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div class="space-y-5 lg:col-span-1">
                <div class="flex items-start gap-3">
                    <div class="h-12 w-12 rounded-xl border border-slate-200 bg-white shadow-sm flex items-center justify-center overflow-hidden">
                        <img src="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}"
                             alt="{{ $footerSiteName }}"
                             class="h-8 w-8 object-contain">
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900">{{ $footerSiteName }}</p>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">B2B Portal</p>
                    </div>
                </div>

                <div class="space-y-2 text-sm text-slate-600 leading-relaxed">
                    <p>{!! nl2br(e($footerAddress)) !!}</p>
                    <div class="flex flex-col gap-1">
                        <a href="{{ $footerPhoneHref ?? '#' }}" class="font-semibold text-slate-700 hover:text-indigo-600 transition-colors">
                            {{ $footerPhone }}
                        </a>
                        <a href="{{ $footerEmailHref ?? '#' }}" class="text-slate-600 hover:text-indigo-600 transition-colors">
                            {{ $footerEmail }}
                        </a>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-900">Policies</p>
                <ul class="mt-4 space-y-3 text-sm text-slate-600">
                    <li><a href="{{ route('terms.conditions') }}" class="hover:text-slate-900 transition-colors">Terms &amp; Conditions</a></li>
                    <li><a href="{{ route('b2b.policy') }}" class="hover:text-slate-900 transition-colors">B2B Policy</a></li>
                    {{-- <li><a href="#" class="text-rose-600 hover:text-rose-700 transition-colors">Cookie Policy</a></li> --}}
                    {{-- <li><a href="#" class="hover:text-slate-900 transition-colors">Compliance Report</a></li> --}}
                </ul>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-900">Company</p>
                <ul class="mt-4 space-y-3 text-sm text-slate-600">
                    <li><a href="{{ route('about') }}" class="hover:text-slate-900 transition-colors">About Us</a></li>
                    {{-- <li><a href="{{ route('shop') }}" class="hover:text-slate-900 transition-colors">Showroom</a></li> --}}
                    <li><a href="{{ route('contact') }}" class="hover:text-slate-900 transition-colors">Contact Info</a></li>
                    {{-- <li><a href="{{ route('home') }}" class="hover:text-slate-900 transition-colors">Blog</a></li> --}}
                    <li><a href="{{ route('shop') }}" class="hover:text-slate-900 transition-colors">Sales Inventory</a></li>
                    <li><a href="{{ route('account.index') }}" class="text-rose-600 hover:text-rose-700 transition-colors">My Account</a></li>
                </ul>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-900">Opening Hours</p>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <div class="flex items-center justify-between gap-4">
                        <span>Mon - Fri</span>
                        <span class="font-semibold text-slate-900">09:00 - 18:00</span>
                    </div>
                    {{-- <div class="flex items-center justify-between gap-4">
                        <span>Friday</span>
                        <span class="font-semibold text-slate-900">08:30 - 15:30</span>
                    </div> --}}
                    <div class="flex items-center justify-between gap-4">
                        <span>Sat - Sun</span>
                        <span class="font-semibold text-slate-900">Closed</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 border-t flex lg:flex-row flex-col text-center space-y-3 items-center justify-between border-slate-200 pt-6">
            <p class='text-sm text-slate-500 '>
                Ideation & Design Shahadat
            </p>
            <p class="text-sm text-slate-500 ">
                &copy; {{ date('Y') }} {{ $footerSiteName }} - Copenhagen Tourist Point . All rights reserved.
            </p>
            <p class="text-sm text-slate-500 text-center">
                Developed by <span class='text-orange-700 font-medium'>Inoodex</span>
            </p>
        </div>
    </div>
</footer>
