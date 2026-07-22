@extends('layouts.frontend')
@section('title', 'Terms & Conditions')

@section('content')
    <div class="bg-slate-50 py-10 sm:py-12">
        <div class="mx-auto flex max-w-6xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
            <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="pointer-events-none absolute -right-16 -top-24 h-56 w-56 rounded-full bg-slate-100 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-[#efe7d9] blur-3xl"></div>

                <div class="relative z-10 max-w-3xl">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Terms &amp; Conditions</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">General Terms of Service</h1>
                    <p class="mt-4 text-base leading-relaxed text-slate-600">
                        These terms govern the use of the Copenhagen Tourist Point B2B platform and purchases made
                        through approved business accounts. By placing an order, you agree to these terms.
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Account Use</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Account Responsibilities</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">
                        You are responsible for safeguarding login credentials, maintaining accurate company information,
                        and ensuring that orders submitted by your team are authorized.
                    </p>
                </div>
            </section>

            <section>
                <div class="text-center">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Orders</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Product Information &amp; Orders</h2>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-900">Product Details</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            We aim to display accurate specifications. Minor variations in color or packaging may occur due
                            to production updates.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-900">Pricing &amp; Taxes</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Prices are exclusive to your approved account and may change without notice. Taxes, duties,
                            and fees are the buyer's responsibility unless stated otherwise.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-900">Order Acceptance</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Orders are confirmed after payment or approved credit terms. We may decline or adjust orders if
                            inventory constraints arise.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Payments</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Payment Terms</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Payments are due according to the terms shown at checkout or in a signed agreement. Late payments
                            may incur fees and impact future ordering privileges.
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Shipping</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Shipping &amp; Risk</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Shipping timelines are estimates and begin once payment is confirmed. Title and risk transfer
                            upon dispatch unless otherwise agreed in writing.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Returns</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Returns &amp; Claims</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Return requests must be submitted within 7 business days of receipt and may require prior approval.
                            Custom or clearance items are final sale unless defective.
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Intellectual Property</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Intellectual Property</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            All content, images, and branding on this platform remain the property of Copenhagen Tourist Point.
                            Use of assets requires written permission.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Liability</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Limitation of Liability</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            We are not liable for indirect or consequential damages. Our maximum liability is limited to the
                            value of the products purchased in the affected order.
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Updates</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Changes to Terms</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            We may update these terms from time to time. Continued use of the platform after updates indicates
                            acceptance of the revised terms.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm sm:p-10">
                <p class="text-2xl font-bold text-slate-900 sm:text-3xl">Questions about these terms?</p>
                <p class="mt-2 text-sm text-slate-600">Reach out and we will help you with a clear answer.</p>
                <a href="{{ route('contact') }}"
                    class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:bg-indigo-600">
                    Contact Support
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6" />
                    </svg>
                </a>
            </section>
        </div>
    </div>
@endsection
