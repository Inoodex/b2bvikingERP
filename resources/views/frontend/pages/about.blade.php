@extends('layouts.frontend')
@section('title', 'About Copenhagen Tourist Point')

@section('content')
    <div class="bg-slate-50 py-10 sm:py-12">
        <div class="mx-auto flex max-w-6xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
            <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="pointer-events-none absolute -right-16 -top-24 h-56 w-56 rounded-full bg-slate-100 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-[#efe7d9] blur-3xl"></div>

                <div class="relative z-10 max-w-3xl">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">About Copenhagen Tourist Point</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Welcome to Copenhagen Tourist Point</h1>
                    <p class="mt-4 text-base leading-relaxed text-slate-600">
                        Where comfort meets conscience. Inspired by the Danish art of living well -
                        <span class="font-semibold text-indigo-600">Copenhagen Tourist Point</span> - we craft everyday essentials that feel soft on your
                        skin and gentle on the earth.
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Our Story</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Our Story</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">
                        Hygge Cotton was born in the heart of Copenhagen as a brother brand of
                        <span class="font-semibold text-indigo-600">Danish Souvenir</span>.
                    </p>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">
                        Founded by <span class="font-semibold text-slate-900">Mohammed Tofayel</span> with nothing but a dream, a
                        small budget, and an unbreakable passion for authentic Danish craftsmanship - what started as a one-man
                        journey has now blossomed into a beloved lifestyle brand.
                    </p>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">
                        In 2025, we took a brave step forward. We wanted more than souvenirs. We wanted to bring true comfort into
                        people's daily lives - sustainably, thoughtfully, and beautifully.
                    </p>
                    {{-- <p class="mt-4 text-sm font-semibold text-slate-900">That's when Hygge Cotton was born.</p> --}}

                    <div class="mt-6 inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">
                            MT
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-semibold text-slate-900">Mohammed Tofayel</p>
                            <p class="text-[11px] font-medium text-slate-500">Founder &amp; Dreamer</p>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <div class="text-center">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Our Philosophy</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Our Philosophy</h2>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-50 text-rose-500">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.001 4.529c2.349-2.532 6.153-2.532 8.502 0 2.349 2.533 2.349 6.638 0 9.171l-7.086 7.645a1 1 0 01-1.416 0L4.915 13.7c-2.349-2.533-2.349-6.638 0-9.171 2.349-2.532 6.153-2.532 8.502 0l.584.631.583-.631z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-slate-900">Comfort is a Way of Life</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Soft fabrics, calm minds, warm hearts - every piece is made to help you slow down and feel at home.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 3a1 1 0 00-1 1v8.268a2 2 0 00.416 1.2l6.4 8.266a2 2 0 001.584.766h5.1a2 2 0 001.963-1.608l2.037-9.17A2 2 0 0019.54 9H13V4a1 1 0 00-1-1H5z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-slate-900">Sustainability First</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Natural, recycled cotton. Ethical production. A lighter footprint - because comfort should never cost the earth.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2a1 1 0 01.894.553l2.618 5.3 5.852.851a1 1 0 01.554 1.705l-4.235 4.126 1 5.828a1 1 0 01-1.451 1.055L12 18.896l-5.238 2.522a1 1 0 01-1.451-1.055l1-5.828L2.076 10.41a1 1 0 01.554-1.705l5.852-.851 2.618-5.3A1 1 0 0112 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-slate-900">Made with Love &amp; Honesty</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            From sketch to stitch, every product carries the care of skilled hands and a transparent heart.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white text-rose-500 shadow-sm">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.001 4.529c2.349-2.532 6.153-2.532 8.502 0 2.349 2.533 2.349 6.638 0 9.171l-7.086 7.645a1 1 0 01-1.416 0L4.915 13.7c-2.349-2.533-2.349-6.638 0-9.171 2.349-2.532 6.153-2.532 8.502 0l.584.631.583-.631z" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-semibold text-slate-900">Peace of Mind</h3>
                        <p class="mt-2 text-xs text-slate-600">Shop with confidence. We're here for you - always.</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white text-indigo-600 shadow-sm">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2a1 1 0 01.894.553L14.5 6h3.5a1 1 0 01.707 1.707l-2.9 2.9 1.1 4.8a1 1 0 01-1.465 1.114L12 14.5l-3.442 2.02a1 1 0 01-1.465-1.114l1.1-4.8-2.9-2.9A1 1 0 016 6h3.5l1.606-3.447A1 1 0 0112 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-semibold text-slate-900">Dedication</h3>
                        <p class="mt-2 text-xs text-slate-600">Every stitch tells a story of care and craftsmanship.</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white text-amber-500 shadow-sm">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2a1 1 0 01.894.553L14.5 6h3.5a1 1 0 01.707 1.707l-2.9 2.9 1.1 4.8a1 1 0 01-1.465 1.114L12 14.5l-3.442 2.02a1 1 0 01-1.465-1.114l1.1-4.8-2.9-2.9A1 1 0 016 6h3.5l1.606-3.447A1 1 0 0112 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-semibold text-slate-900">Our Promise</h3>
                        <p class="mt-2 text-xs text-slate-600">Never compromise on quality, ethics, or comfort.</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow-sm">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 3a1 1 0 00-1 1v8.268a2 2 0 00.416 1.2l6.4 8.266a2 2 0 001.584.766h5.1a2 2 0 001.963-1.608l2.037-9.17A2 2 0 0019.54 9H13V4a1 1 0 00-1-1H5z" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-semibold text-slate-900">Conscious Growth</h3>
                        <p class="mt-2 text-xs text-slate-600">Growing slowly, sustainably, and with purpose.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Our Mission</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Our Mission</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            To make comfort a part of everyday life - physically, emotionally, and ethically. We want every Hygge Cotton
                            piece to feel like a warm hug from someone who truly cares.
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Our Vision</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Our Vision</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            To become a globally trusted name for simplicity, sustainability, and soulful living - one soft, honest,
                            beautiful product at a time.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm sm:p-10">
                <p class="text-2xl font-bold text-slate-900 sm:text-3xl">You're not just buying cotton.</p>
                <p class="text-2xl font-bold text-slate-700 sm:text-3xl">You're choosing comfort.</p>
                <a href="{{ route('shop') }}"
                    class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:bg-indigo-600">
                    Explore Our Collection
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6" />
                    </svg>
                </a>
            </section>
        </div>
    </div>
@endsection
