@php
    $currency = $currency ?? ($settings->currency_icon ?? '$');
@endphp
@forelse($orders as $order)
    <tr class="border-b border-slate-100">
        <td class="py-3 pr-4 text-sm font-semibold text-slate-800">#{{ $order->order_no }}</td>
        <td class="py-3 px-4 text-sm text-slate-700">{{ $order->created_at?->format('F j, Y') }}</td>
        <td class="py-3 px-4">
            @php $status = strtolower($order->status); @endphp
            <span class="text-xs font-black px-2.5 py-1 rounded-full
                {{ $status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                {{ $status === 'approved' ? 'bg-sky-100 text-sky-700' : '' }}
                {{ $status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}
                {{ $status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                {{ ucfirst($order->status) }}
            </span>
        </td>
        <td class="py-3 px-4">
            @php $pStatus = strtolower($order->payment_status ?? 'pending'); @endphp
            <div class="flex flex-col gap-1">
                @if($pStatus === 'paid')
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full w-fit">
                        <i class="fas fa-check-circle text-emerald-600"></i> Paid
                    </span>
                @elseif($pStatus === 'partial')
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-full w-fit">
                        <i class="fas fa-adjust text-sky-600"></i> Partial
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full w-fit">
                        <i class="fas fa-clock text-amber-600"></i> Pending
                    </span>
                @endif

                @if($order->payment_method === 'paypal')
                    <span class="text-[10px] text-slate-500 font-semibold flex items-center gap-1">
                        <i class="fab fa-paypal text-blue-600"></i> PayPal
                    </span>
                @elseif($order->payment_method === 'cod')
                    <span class="text-[10px] text-slate-500 font-semibold flex items-center gap-1">
                        <i class="fas fa-truck text-slate-500"></i> COD
                    </span>
                @elseif($order->payment_method)
                    <span class="text-[10px] text-slate-400 uppercase font-semibold">
                        {{ $order->payment_method }}
                    </span>
                @endif
            </div>
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
        <td colspan="6" class="py-8 text-center text-sm text-slate-500">No orders found.</td>
    </tr>
@endforelse
