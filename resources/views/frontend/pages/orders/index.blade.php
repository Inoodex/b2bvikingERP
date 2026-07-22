@extends('layouts.frontend')
@section('title', 'My Orders')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-slate-900">My Orders</h1>
            <p class="text-sm text-slate-500 mt-2">Your frontend order history.</p>
        </div>
        <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path></svg>
            Continue Shopping
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white border border-slate-100 rounded-2xl p-10 text-center">
            <p class="text-slate-500 font-semibold">You have no orders yet.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <a href="{{ route('orders.show', $order->id) }}" class="block bg-white border border-slate-100 rounded-2xl p-5 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <p class="text-sm font-black text-slate-900">{{ $order->order_no }}</p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $order->created_at?->format('d M Y, h:i A') }} | {{ $order->items_count }} items
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-bold text-slate-900">${{ number_format($order->total_amount, 2) }}</span>
                            @php $status = strtolower($order->status); @endphp
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
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $orders->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>
@endsection
