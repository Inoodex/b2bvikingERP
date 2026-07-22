@extends('layouts.frontend')

@section('title', 'Product Request Details')

@section('content')
@php
    $status = strtolower((string) $productRequest->status);
    $currency = $settings->currency_icon ?? '$';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    {{-- Header --}}
    <div class="mb-6 sm:mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Product Request</h1>
            <p class="text-sm text-gray-500 mt-1">Request No: <span class="font-semibold text-gray-700">{{ $productRequest->request_no }}</span></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('account.index', ['panel' => 'product-requests']) }}" 
               class="inline-flex items-center gap-2 text-blue-600 font-semibold hover:text-blue-700 transition-colors duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                </svg>
                Back to Requests
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Request Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Request Info
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-700 min-w-[100px]">Status:</span>
                        <span class="px-3 py-1 text-xs font-bold rounded-full
                            {{ $status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                            {{ ucfirst($productRequest->status) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-700 min-w-[100px]">Required Days:</span>
                        <span>{{ $productRequest->required_days ?? '—' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-700 min-w-[100px]">Total Qty:</span>
                        <span class="font-bold text-gray-800">{{ (int) ($productRequest->total_qty ?? 0) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-700 min-w-[100px]">Total Amount:</span>
                        <span class="font-bold text-blue-600">{{ $currency }}{{ number_format((float) ($productRequest->total_amount ?? 0), 2) }}</span>
                    </div>
                    <div class="flex items-center gap-2 sm:col-span-2">
                        <span class="font-semibold text-gray-700 min-w-[100px]">Date:</span>
                        <span>{{ $productRequest->created_at?->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>

            {{-- Requested Items --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Requested Items
                </h2>
                <div class="overflow-x-auto -mx-5 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Variant</th>
                                    <th class="py-3 px-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Qty</th>
                                    <th class="py-3 px-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Unit Price</th>
                                    <th class="py-3 px-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($productRequest->items as $item)
                                    @php
                                        $variant = $item->variant;
                                        $variantLabel = null;
                                        if ($variant) {
                                            $name = trim((string) ($variant->name ?? ''));
                                            $color = trim((string) ($variant->color?->name ?? $variant->color ?? ''));
                                            $size = trim((string) ($variant->size?->name ?? $variant->size ?? ''));
                                            $parts = array_filter([$name, $color, $size]);
                                            $variantLabel = !empty($parts) ? implode(' / ', array_unique($parts)) : null;
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="py-3 px-4 text-sm font-semibold text-gray-800">{{ $item->product?->name ?? '—' }}</td>
                                        <td class="py-3 px-4 text-sm text-gray-600">{{ $variantLabel ?? '—' }}</td>
                                        <td class="py-3 px-4 text-sm text-center font-semibold text-gray-700">{{ (int) ($item->qty ?? 0) }}</td>
                                        <td class="py-3 px-4 text-sm text-right font-medium text-gray-600">{{ $currency }}{{ number_format((float) ($item->unit_price ?? 0), 2) }}</td>
                                        <td class="py-3 px-4 text-sm text-right font-bold text-blue-600">{{ $currency }}{{ number_format((float) ($item->subtotal ?? 0), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-sm text-gray-500">No items found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($productRequest->items->count() > 0)
                                <tfoot>
                                    <tr class="bg-gray-50">
                                        <td colspan="4" class="py-3 px-4 text-right text-sm font-bold text-gray-700">Total</td>
                                        <td class="py-3 px-4 text-right text-sm font-bold text-blue-600">{{ $currency }}{{ number_format((float) ($productRequest->total_amount ?? 0), 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Request Note --}}
            @if(!empty($productRequest->note))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Request Note
                    </h2>
                    <p class="text-sm text-gray-600 whitespace-pre-line bg-gray-50 rounded-lg p-4">{{ $productRequest->note }}</p>
                </div>
            @endif

            {{-- Admin Note --}}
            @if(!empty($productRequest->admin_note))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Admin Note
                    </h2>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $productRequest->admin_note }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                {{-- Status Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Status
                    </h3>
                    <div class="mb-4">
                        <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-full
                            {{ $status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                            <span class="w-2 h-2 rounded-full
                                {{ $status === 'completed' ? 'bg-green-500' : '' }}
                                {{ $status === 'cancelled' ? 'bg-red-500' : '' }}
                                {{ $status === 'pending' ? 'bg-yellow-500' : '' }}">
                            </span>
                            {{ ucfirst($productRequest->status) }}
                        </span>
                    </div>
                    <div class="space-y-3 text-sm border-t border-gray-100 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Total Qty</span>
                            <span class="font-bold text-gray-800">{{ (int) ($productRequest->total_qty ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Total Amount</span>
                            <span class="font-bold text-blue-600">{{ $currency }}{{ number_format((float) ($productRequest->total_amount ?? 0), 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Customer Info --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Customer Info
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <div>
                                <span class="font-semibold text-gray-700">Name:</span>
                                <span class="text-gray-600">{{ $productRequest->user?->name ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <span class="font-semibold text-gray-700">Email:</span>
                                <span class="text-gray-600">{{ $productRequest->user?->email ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <div>
                                <span class="font-semibold text-gray-700">Phone:</span>
                                <span class="text-gray-600">{{ $productRequest->user?->phone ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div>
                                <span class="font-semibold text-gray-700">Outlet/Shop:</span>
                                <span class="text-gray-600">{{ $productRequest->user?->outlet_name ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div>
                                <span class="font-semibold text-gray-700">Address:</span>
                                <span class="text-gray-600">{{ $productRequest->user?->address ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* =============================================
       PRODUCT REQUEST DETAILS - PREMIUM DESIGN
       ============================================= */

    /* Smooth transitions */
    .transition-all {
        transition: all 0.2s ease;
    }

    /* Hover effects */
    .hover\:bg-gray-50:hover {
        background-color: #f9fafb;
    }

    /* Table responsive */
    @media (max-width: 640px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Status badge pulse animation */
    .bg-green-100 {
        animation: pulse-green 2s infinite;
    }

    .bg-yellow-100 {
        animation: pulse-yellow 2s infinite;
    }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.3); }
        70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    @keyframes pulse-yellow {
        0% { box-shadow: 0 0 0 0 rgba(234, 179, 8, 0.3); }
        70% { box-shadow: 0 0 0 6px rgba(234, 179, 8, 0); }
        100% { box-shadow: 0 0 0 0 rgba(234, 179, 8, 0); }
    }

    /* Card hover effect */
    .bg-white {
        transition: all 0.3s ease;
    }

    .bg-white:hover {
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #2563eb;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #1d4ed8;
    }
</style>
@endpush