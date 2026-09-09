@extends('layouts.frontend')
@section('title', 'Order Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-slate-900">Order Details</h1>
            <p class="text-sm text-slate-500 mt-2">Order No: {{ $order->order_no }}</p>
        </div>
        <div class="flex items-center gap-3">
            @php
                $hasPi = \App\Support\PiInfoSupport::hasContent($order->pi_info);
                $invoice = \App\Models\SalesInvoice::where('order_id', $order->id)->latest()->first();
            @endphp
            @if($order->payment_method === 'paypal' && $order->payment_status !== 'paid')
                <form method="POST" action="{{ route('orders.retry-paypal', $order->id) }}" class="inline-block">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#ffc439] hover:bg-[#f4b82d] px-4 py-2 text-sm font-bold text-slate-900 shadow transition">
                        <i class="fab fa-paypal text-blue-800"></i> Pay Now with PayPal
                    </button>
                </form>
            @endif
            @if($invoice && $invoice->due_amount > 0)
                <a href="{{ $invoice->public_payment_url }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow hover:bg-emerald-700 transition">
                    <i class="fas fa-credit-card"></i> Pay Invoice (kr. {{ number_format($invoice->due_amount, 2) }})
                </a>
            @endif
            @if($hasPi)
                <a href="{{ route('orders.pi-invoice', $order->id) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800">
                    View PI
                </a>
                <a href="{{ route('orders.pi-invoice.download', $order->id) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:border-indigo-200 hover:text-indigo-600">
                    Download PI
                </a>
            @endif
            <form method="POST" action="{{ route('orders.reorder', $order->id) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:border-indigo-200 hover:text-indigo-600">
                    Reorder Now
                </button>
            </form>
            <a href="{{ route('account.index', ['panel' => 'orders']) }}" class="inline-flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path></svg>
                Back to Orders
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white border border-slate-100 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Items</h2>
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-center justify-between border border-slate-100 rounded-xl p-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <img
                                    src="{{ str_starts_with((string) $item->product_image, 'http') ? $item->product_image : asset('storage/' . ltrim((string) $item->product_image, '/')) }}"
                                    alt="{{ $item->product_name }}"
                                    class="w-14 h-14 rounded-lg border border-slate-100 object-cover"
                                >
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ $item->product_name }}</p>
                                    @if($item->variant_label)
                                        <p class="text-xs text-slate-500">{{ $item->variant_label }}</p>
                                    @endif
                                    <p class="text-xs text-slate-500">Qty: {{ $item->quantity }}</p>
                                </div>
                            </div>
                            <p class="text-sm font-bold text-slate-900">{{$settings->currency_icon}}{{ number_format($item->line_total, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-white border border-slate-100 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Shipping & Billing Info</h2>
                <div class="text-sm text-slate-600 space-y-1">
                    <p><span class="font-semibold text-slate-900">Name:</span> {{ $order->billing_name }}</p>
                    <p><span class="font-semibold text-slate-900">Email:</span> {{ $order->billing_email }}</p>
                    <p><span class="font-semibold text-slate-900">Phone:</span> {{ $order->billing_phone }}</p>
                    <p><span class="font-semibold text-slate-900">Address:</span> {{ $order->billing_address }}</p>
                    @if($order->billing_outlet_name)
                        <p><span class="font-semibold text-slate-900">Outlet:</span> {{ $order->billing_outlet_name }}</p>
                    @endif
                </div>
            </section>

            @if($order->ship_different)
                <section class="bg-white border border-slate-100 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Shipping Info</h2>
                    <div class="text-sm text-slate-600 space-y-1">
                        <p><span class="font-semibold text-slate-900">Name:</span> {{ $order->shipping_name }}</p>
                        <p><span class="font-semibold text-slate-900">Email:</span> {{ $order->shipping_email }}</p>
                        <p><span class="font-semibold text-slate-900">Phone:</span> {{ $order->shipping_phone }}</p>
                        <p><span class="font-semibold text-slate-900">Address:</span> {{ $order->shipping_address }}</p>
                        <p><span class="font-semibold text-slate-900">City:</span> {{ $order->shipping_city }}</p>
                        <p><span class="font-semibold text-slate-900">State:</span> {{ $order->shipping_state }}</p>
                        <p><span class="font-semibold text-slate-900">Zip:</span> {{ $order->shipping_zip_code }}</p>
                        <p><span class="font-semibold text-slate-900">Country:</span> {{ $order->shipping_country }}</p>
                        @if($order->shipping_outlet_name)
                            <p><span class="font-semibold text-slate-900">Outlet:</span> {{ $order->shipping_outlet_name }}</p>
                        @endif
                    </div>
                </section>
            @endif
        </div>

        <aside class="lg:col-span-1">
            <div class="bg-white border border-slate-100 rounded-2xl p-6 sticky top-24">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Summary</h3>
                @php $status = strtolower($order->status); @endphp
                <p class="mb-3">
                    <span class="text-xs font-black px-3 py-1 rounded-full
                        {{ $status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $status === 'processing' ? 'bg-indigo-100 text-indigo-700' : '' }}
                        {{ $status === 'approved' ? 'bg-sky-100 text-sky-700' : '' }}
                        {{ $status === 'shipped' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $status === 'rejected' ? 'bg-rose-100 text-rose-700' : '' }}
                        {{ $status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}
                        {{ $status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </p>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-semibold text-slate-900">{{$settings->currency_icon}}{{ number_format($order->subtotal_amount ?: $order->total_amount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Discount</span><span class="font-semibold text-emerald-600">-{{$settings->currency_icon}}{{ number_format($order->discount_amount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">VAT</span><span class="font-semibold text-slate-900">{{$settings->currency_icon}}{{ number_format($order->tax_amount, 2) }}</span></div>
                    <div class="pt-2 border-t border-slate-100 flex justify-between"><span class="font-bold text-slate-900">Total</span><span class="font-black text-indigo-600">{{$settings->currency_icon}}{{ number_format($order->total_amount, 2) }}</span></div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Payment Method</span>
                        <span class="font-bold text-slate-800">
                            @if($order->payment_method === 'paypal')
                                <span class="inline-flex items-center gap-1.5 text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full text-xs font-bold border border-indigo-100">
                                    <i class="fab fa-paypal text-blue-600"></i> PayPal Express
                                </span>
                            @elseif($order->payment_method === 'cod')
                                <span class="inline-flex items-center gap-1.5 text-slate-700 bg-slate-100 px-2.5 py-1 rounded-full text-xs font-bold border border-slate-200">
                                    <i class="fas fa-truck text-slate-500"></i> Cash On Delivery
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-slate-700 bg-slate-100 px-2.5 py-1 rounded-full text-xs font-bold">
                                    {{ strtoupper($order->payment_method ?: 'N/A') }}
                                </span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Payment Status</span>
                        <span>
                            @if($order->payment_status === 'paid')
                                <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full text-xs font-bold border border-emerald-200">
                                    <i class="fas fa-check-circle text-emerald-600"></i> Paid
                                </span>
                            @elseif($order->payment_status === 'partial')
                                <span class="inline-flex items-center gap-1 text-sky-700 bg-sky-50 px-2.5 py-1 rounded-full text-xs font-bold border border-sky-200">
                                    <i class="fas fa-adjust text-sky-600"></i> Partial
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full text-xs font-bold border border-amber-200">
                                    <i class="fas fa-clock text-amber-600"></i> Pending
                                </span>
                            @endif
                        </span>
                    </div>
                    @if($order->paid_amount > 0)
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400">Paid Amount</span>
                            <span class="font-bold text-emerald-600">{{$settings->currency_icon}}{{ number_format($order->paid_amount, 2) }}</span>
                        </div>
                    @endif
                    @if($order->due_amount > 0 && $order->paid_amount > 0)
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400">Remaining Due</span>
                            <span class="font-bold text-rose-600">{{$settings->currency_icon}}{{ number_format($order->due_amount, 2) }}</span>
                        </div>
                    @endif
                </div>

                @if($order->payment_method === 'paypal' && $order->payment_status !== 'paid')
                    <div class="mt-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-center shadow-sm">
                        <p class="text-xs font-semibold text-amber-800 mb-3 flex items-center justify-center gap-1.5">
                            <i class="fas fa-exclamation-circle text-amber-600"></i> Payment is pending. Complete checkout now:
                        </p>
                        <form method="POST" action="{{ route('orders.retry-paypal', $order->id) }}">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#ffc439] hover:bg-[#f4b82d] text-slate-900 font-bold text-sm rounded-lg shadow-sm transition">
                                <i class="fab fa-paypal text-blue-800 text-base"></i> Pay kr. {{ number_format($order->due_amount > 0 ? $order->due_amount : $order->total_amount, 2) }} with PayPal
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
