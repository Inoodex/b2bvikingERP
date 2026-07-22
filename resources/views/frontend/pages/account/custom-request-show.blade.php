@extends('layouts.frontend')
@section('title', 'Request Details')

@section('content')
@php
    $status = strtolower((string) $customProductRequest->status);
    $currency = $settings->currency_icon ?? '$';
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-black text-slate-900">Request Details</h1>
            <p class="text-sm text-slate-500 mt-2">Request No: {{ $customProductRequest->request_no }}</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('account.custom-product-requests.reorder', $customProductRequest->id) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:border-indigo-200 hover:text-indigo-600">
                    Reorder
                </button>
            </form>
            <a href="{{ route('account.index', ['panel' => 'custom-requests']) }}" class="inline-flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path></svg>
                Back to Requests
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white border border-slate-100 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Request Info</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Product Name:</span> {{ $customProductRequest->product_name ?: 'Custom Product' }}</p>
                    <p><span class="font-semibold text-slate-900">Quantity:</span> {{ $customProductRequest->quantity_needed }}</p>
                    <p><span class="font-semibold text-slate-900">Expected Price:</span> {{ $customProductRequest->expected_price ? $currency . number_format((float) $customProductRequest->expected_price, 2) : 'Not specified' }}</p>
                    <p><span class="font-semibold text-slate-900">Date:</span> {{ $customProductRequest->created_at?->format('d M Y, h:i A') }}</p>
                </div>
            </section>

            <section class="bg-white border border-slate-100 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Product Description</h2>
                <p class="text-sm text-slate-600 whitespace-pre-line">{{ $customProductRequest->product_description }}</p>
            </section>

            @if(!empty($customProductRequest->example_image))
                <section class="bg-white border border-slate-100 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Example Images</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($customProductRequest->example_image as $index => $image)
                            @if($customProductRequest->resolveExampleImagePath($index))
                                <img src="{{ route('account.custom-product-requests.images.show', [$customProductRequest->id, $index]) }}" alt="Example image" class="w-full aspect-square rounded-lg border border-slate-100 object-cover">
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!empty($customProductRequest->admin_note))
                <section class="bg-white border border-slate-100 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Admin Note</h2>
                    <p class="text-sm text-slate-600 whitespace-pre-line">{{ $customProductRequest->admin_note }}</p>
                </section>
            @endif
        </div>

        <aside class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
            <div class="bg-white border border-slate-100 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Status</h3>
                <p class="mb-4">
                    <span class="text-xs font-black px-3 py-1 rounded-full
                        {{ $status === 'approved' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $status === 'rejected' ? 'bg-rose-100 text-rose-700' : '' }}
                        {{ $status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                        {{ ucfirst($customProductRequest->status) }}
                    </span>
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Quantity</span>
                        <span class="font-semibold text-slate-900">{{ $customProductRequest->quantity_needed }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Expected Price</span>
                        <span class="font-semibold text-slate-900">{{ $customProductRequest->expected_price ? $currency . number_format((float) $customProductRequest->expected_price, 2) : '—' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-100 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Customer Info</h3>
                <div class="space-y-2 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Name:</span> {{ $customProductRequest->user?->name ?? '—' }}</p>
                    <p><span class="font-semibold text-slate-900">Email:</span> {{ $customProductRequest->user?->email ?? '—' }}</p>
                    <p><span class="font-semibold text-slate-900">Phone:</span> {{ $customProductRequest->user?->phone ?? '—' }}</p>
                    <p><span class="font-semibold text-slate-900">Outlet/Shop:</span> {{ $customProductRequest->user?->outlet_name ?? '—' }}</p>
                    <p><span class="font-semibold text-slate-900">Address:</span> {{ $customProductRequest->user?->address ?? '—' }}</p>
                </div>
            </div>
            </div>
        </aside>
    </div>
</div>
@endsection
