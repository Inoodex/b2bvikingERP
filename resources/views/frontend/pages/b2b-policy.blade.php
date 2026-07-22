@extends('layouts.frontend')
@section('title', 'B2B Policy')

@section('content')
    <div class="bg-slate-50 py-10 sm:py-12">
        <div class="mx-auto flex max-w-6xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
            <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="pointer-events-none absolute -right-16 -top-24 h-56 w-56 rounded-full bg-slate-100 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-[#efe7d9] blur-3xl"></div>

                <div class="relative z-10 max-w-3xl">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">B2B Policy</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Wholesale &amp; Business Terms</h1>
                    <p class="mt-4 text-base leading-relaxed text-slate-600">
                        This policy outlines how we work with approved business partners at
                        <span class="font-semibold text-indigo-600">Copenhagen Tourist Point</span>. It covers eligibility,
                        ordering standards, pricing, payments, shipping, and claims.
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Eligibility</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Who This Policy Applies To</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">
                        Our B2B program is designed for verified outlets, retailers, and corporate buyers. Access is granted
                        after account review, and pricing is tailored to approved business profiles.
                    </p>
                </div>
            </section>

            <section>
                <div class="text-center">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Ordering</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Account, Ordering, and Pricing</h2>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-900">Account Approval</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            New B2B accounts require verification. We may request trade references, tax documents, or proof of business.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-900">Minimum Order</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Minimum order quantities may apply by category. Confirm your MOQ and pack size before checkout.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-semibold text-slate-900">Wholesale Pricing</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Prices are exclusive to approved B2B accounts and may vary by volume, season, and inventory status.
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
                            Orders are billed in advance unless net terms are approved in writing. Late payments may pause
                            future fulfillment until balances are resolved.
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Lead Times</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Lead Time &amp; Shipping</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Standard lead times vary by collection. Shipping timelines begin after payment confirmation.
                            Risk transfers on dispatch unless otherwise stated in writing.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Returns</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Returns, Claims, and Cancellations</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Claims for transit damage or shortages must be reported within 3 business days of delivery.
                            Customized or special-order items are non-returnable unless defective.
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Compliance</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-900">Compliance &amp; Documentation</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Buyers are responsible for local taxes, resale certificates, and import requirements.
                            We can provide standard invoices and packing documentation upon request.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm sm:p-10">
                <p class="text-2xl font-bold text-slate-900 sm:text-3xl">Need a tailored wholesale plan?</p>
                <p class="mt-2 text-sm text-slate-600">Our team will help you build a reliable supply pipeline.</p>
                <a href="{{ route('contact') }}"
                    class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:bg-indigo-600">
                    Contact Our Team
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6" />
                    </svg>
                </a>
            </section>
        </div>
    </div>
@endsection
