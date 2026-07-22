@php
    $currency = $currency ?? ($settings->currency_icon ?? '$');
@endphp
@forelse($orders as $order)
    <tr class="border-b border-slate-100">
        <td class="py-3 pr-4 text-sm font-semibold text-slate-800">#{{ $order->order_no }}</td>
        <td class="py-3 px-4 text-sm text-slate-700">{{ $order->created_at?->format('F j, Y') }}</td>
        <td class="py-3 px-4">
            @php $status = strtolower($order->status); @endphp
            <span class="text-xs font-black px-2 py-1 rounded
                {{ $status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                {{ $status === 'approved' ? 'bg-sky-100 text-sky-700' : '' }}
                {{ $status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}
                {{ $status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                {{ ucfirst($order->status) }}
            </span>
        </td>
        <td class="py-3 px-4 text-sm text-slate-800">
            <span class="font-semibold">{{ $currency }}{{ number_format($order->total_amount, 2) }}</span>
            <span class="text-slate-500"> for {{ (int) ($order->total_units ?? 0) }} units</span>
        </td>
        <td class="py-3 pl-4 text-[11px] uppercase tracking-[0.12em] font-black">
            @php
                $hasPi = \App\Support\PiInfoSupport::hasContent($order->pi_info);
            @endphp
            <a href="{{ route('orders.show', $order->id) }}" class="text-slate-700 hover:text-indigo-600">View</a>
            <span class="text-slate-300 mx-1">|</span>
            <form method="POST" action="{{ route('orders.reorder', $order->id) }}" class="inline">
                @csrf
                <button type="submit" class="text-slate-700 hover:text-indigo-600">Reorder</button>
            </form>
            @if($hasPi)
                <span class="text-slate-300 mx-1">|</span>
                <a href="{{ route('orders.pi-invoice', $order->id) }}" class="text-slate-700 hover:text-indigo-600">PI</a>
                <span class="text-slate-300 mx-1">|</span>
                <a href="{{ route('orders.pi-invoice.download', $order->id) }}" class="text-slate-700 hover:text-indigo-600">PI PDF</a>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="py-8 text-center text-sm text-slate-500">No orders found.</td>
    </tr>
@endforelse
