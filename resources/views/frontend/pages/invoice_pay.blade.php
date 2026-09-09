<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_no }} — Payment Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
                        },
                        paypal: '#0070ba'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

    {{-- Top Navigation Bar --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-xl font-extrabold tracking-tight text-slate-900 flex items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-brand-600 text-2xl"></i>
                    <span>{{ $settings->site_name ?? 'B2B Viking ERP' }}</span>
                </span>
                <span class="hidden sm:inline-block text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full">
                    Secure Payment Portal
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="window.print()" class="inline-flex items-center px-3.5 py-2 text-xs font-bold rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition">
                    <i class="fas fa-print mr-1.5"></i> Print Invoice
                </button>
            </div>
        </div>
    </header>

    {{-- Notification Alerts --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        @if(session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 mb-6 flex items-start gap-3 shadow-sm">
                <i class="fas fa-check-circle text-emerald-600 text-xl mt-0.5"></i>
                <div>
                    <h4 class="font-bold text-emerald-900 text-sm">Payment Successful!</h4>
                    <p class="text-emerald-700 text-xs mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 mb-6 flex items-start gap-3 shadow-sm">
                <i class="fas fa-exclamation-triangle text-rose-600 text-xl mt-0.5"></i>
                <div>
                    <h4 class="font-bold text-rose-900 text-sm">Payment Notice</h4>
                    <p class="text-rose-700 text-xs mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 mb-6 flex items-start gap-3 shadow-sm">
                <i class="fas fa-info-circle text-amber-600 text-xl mt-0.5"></i>
                <div>
                    <h4 class="font-bold text-amber-900 text-sm">Notice</h4>
                    <p class="text-amber-700 text-xs mt-0.5">{{ session('warning') }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Main Container --}}
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Left 7/12: Itemized Invoice Document --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    
                    {{-- Invoice Header Bar --}}
                    <div class="p-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Commercial Invoice</span>
                            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">#{{ $invoice->invoice_no }}</h1>
                        </div>
                        <div>
                            @if($invoice->due_amount <= 0)
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <i class="fas fa-check-circle"></i> Paid in Full
                                </span>
                            @elseif($invoice->paid_amount > 0)
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                    <i class="fas fa-clock"></i> Partially Paid
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                    <i class="fas fa-exclamation-circle"></i> Payment Pending
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Dates & B2B Details Grid --}}
                    <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-6 border-b border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-400 font-semibold block">Invoice Date</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block">Due Date</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">
                                {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'Due upon receipt' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block">Order Reference</span>
                            <span class="font-bold text-brand-700 text-sm mt-0.5 block">{{ $invoice->order?->order_no ?? 'Direct Invoice' }}</span>
                        </div>
                    </div>

                    {{-- Customer & Seller Info --}}
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50/30 text-xs border-b border-slate-100">
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 mb-2 block">Billed To</span>
                            <p class="font-bold text-slate-900 text-sm">{{ $invoice->order?->user?->outlet_name ?: ($invoice->order?->user?->name ?? 'Valued Customer') }}</p>
                            <p class="text-slate-600 mt-1">{{ $invoice->order?->user?->email ?: ($invoice->order?->billing_email ?? '') }}</p>
                            @if($invoice->order?->user?->phone)
                                <p class="text-slate-600">{{ $invoice->order->user->phone }}</p>
                            @endif
                            @if($invoice->order?->user?->address)
                                <p class="text-slate-500 mt-1">{{ $invoice->order->user->address }}</p>
                            @endif
                        </div>
                        <div>
                            <span class="font-bold uppercase tracking-wider text-slate-400 mb-2 block">Issued By</span>
                            <p class="font-bold text-slate-900 text-sm">{{ $settings->site_name ?? 'B2B Viking ERP' }}</p>
                            <p class="text-slate-600 mt-1">{{ $settings->contact_email ?? 'billing@b2bviking.dk' }}</p>
                            <p class="text-slate-600">{{ $settings->contact_phone ?? '+45 00 00 00 00' }}</p>
                            <p class="text-slate-500 mt-1">Copenhagen, Denmark</p>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-6">Description</th>
                                    <th class="py-3 px-4 text-center">Qty</th>
                                    <th class="py-3 px-4 text-right">Unit Price</th>
                                    <th class="py-3 px-6 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($invoice->items as $item)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-3 px-6 font-semibold text-slate-800">
                                            {{ $item->product?->name ?? 'Product Line Item' }}
                                            @php
                                                $variantName = null;
                                                if (isset($item->variant) && $item->variant) {
                                                    $variantName = $item->variant->name;
                                                } elseif ($invoice->order && $invoice->order->items) {
                                                    $matchedOrderItem = $invoice->order->items->firstWhere('product_id', $item->product_id);
                                                    $variantName = $matchedOrderItem?->variant_label ?: $matchedOrderItem?->variant?->name;
                                                }
                                            @endphp
                                            @if ($variantName)
                                                <div class="text-[11px] font-normal text-slate-500 mt-0.5">
                                                    <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded text-[10px] font-medium border border-slate-200">
                                                        <i class="fas fa-layer-group text-[9px] text-slate-400"></i> Variant: {{ $variantName }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center text-slate-600">
                                            {{ number_format($item->qty, 0) }}
                                        </td>
                                        <td class="py-3 px-4 text-right text-slate-600">
                                            kr. {{ number_format($item->price, 2) }}
                                        </td>
                                        <td class="py-3 px-6 text-right font-bold text-slate-900">
                                            kr. {{ number_format($item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 px-6 text-center text-slate-400">Order lines aggregated from order total.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Financial Totals Box --}}
                    <div class="p-6 bg-slate-50 border-t border-slate-200">
                        <div class="max-w-xs ml-auto space-y-2 text-xs">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal:</span>
                                <span class="font-semibold text-slate-900">kr. {{ number_format($invoice->subtotal_amount ?: $invoice->total_amount, 2) }}</span>
                            </div>
                            @if($invoice->tax_amount > 0)
                                <div class="flex justify-between text-slate-600">
                                    <span>MOMS / VAT:</span>
                                    <span class="font-semibold text-slate-900">kr. {{ number_format($invoice->tax_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-slate-900 font-bold text-sm border-t border-slate-200 pt-2">
                                <span>Invoice Total:</span>
                                <span>kr. {{ number_format($invoice->total_amount, 2) }}</span>
                            </div>
                            @if($invoice->paid_amount > 0)
                                <div class="flex justify-between text-emerald-700 font-semibold">
                                    <span>Paid to Date:</span>
                                    <span>- kr. {{ number_format($invoice->paid_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-slate-900 font-extrabold text-base border-t-2 border-slate-300 pt-2">
                                <span>Balance Due:</span>
                                <span class="{{ $invoice->due_amount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    kr. {{ number_format($invoice->due_amount, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Right 5/12: Payment Action Box --}}
            <div class="lg:col-span-5 space-y-6">
                
                {{-- Payment Card --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sticky top-24">
                    
                    @if($invoice->due_amount <= 0)
                        {{-- Already Paid State --}}
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-check text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-black text-slate-900">Invoice Settled in Full</h3>
                            <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                                Thank you! Payment for Invoice #{{ $invoice->invoice_no }} has been completely received and verified.
                            </p>
                            <div class="mt-6">
                                <button onclick="window.print()" class="w-full inline-flex justify-center items-center py-2.5 px-4 rounded-xl font-bold text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                    <i class="fas fa-file-invoice mr-2"></i> Print Official Receipt
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- Active Payment State --}}
                        <div class="border-b border-slate-100 pb-4 mb-6">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Payment Due</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-3xl font-black text-slate-900">kr. {{ number_format($invoice->due_amount, 2) }}</span>
                                <span class="text-xs font-semibold px-2 py-0.5 bg-amber-50 text-amber-700 rounded-md border border-amber-200">
                                    DKK
                                </span>
                            </div>
                        </div>

                        {{-- Payment Gateways Options --}}
                        <div class="space-y-4">
                            
                            {{-- OPTION 1: PayPal Express Checkout --}}
                            @if(isset($gateways['paypal']) && $gateways['paypal']->isEnabled())
                                <div class="rounded-xl border-2 border-slate-200 p-4 hover:border-brand-600 transition bg-white">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                                            <i class="fab fa-paypal text-[#0070ba] text-lg"></i> PayPal Express
                                        </span>
                                        <span class="text-[10px] font-bold uppercase bg-blue-50 text-blue-700 px-2 py-0.5 rounded">Instant</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-4">Pay securely online with your PayPal balance or credit/debit card.</p>
                                    
                                    <form action="{{ route('invoices.pay.paypal', $invoice->payment_token) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full py-3 px-4 rounded-xl text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2" style="background-color: #0070ba;">
                                            <i class="fab fa-paypal"></i>
                                            <span>Pay kr. {{ number_format($invoice->due_amount, 2) }} with PayPal</span>
                                        </button>
                                    </form>
                                </div>
                            @endif

                            {{-- OPTION 2: Direct Bank Wire Transfer (SEPA / Straks) [TEMPORARILY COMMENTED OUT AS REQUESTED] --}}
                            {{--
                            <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-sm text-slate-900 flex items-center gap-2">
                                        <i class="fas fa-university text-slate-600"></i> Bank Transfer (SEPA)
                                    </span>
                                    <span class="text-[10px] font-bold uppercase bg-slate-200 text-slate-700 px-2 py-0.5 rounded">Offline Wire</span>
                                </div>
                                <p class="text-xs text-slate-500 mb-3">Transfer funds directly from your online bank using the reference below:</p>

                                <div class="bg-white rounded-lg p-3 border border-slate-200 space-y-1.5 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Bank:</span>
                                        <span class="font-bold text-slate-800">Danske Bank A/S</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Reg. & Account:</span>
                                        <span class="font-bold text-slate-800">3001 - 0012345678</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">IBAN:</span>
                                        <span class="font-bold text-slate-800">DK50 3001 0012 3456 78</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">SWIFT / BIC:</span>
                                        <span class="font-bold text-slate-800">DABADKKK</span>
                                    </div>
                                    <div class="flex justify-between border-t border-slate-100 pt-1.5">
                                        <span class="text-slate-500 font-bold">Payment Ref:</span>
                                        <span class="font-extrabold text-brand-700 bg-brand-50 px-1.5 py-0.5 rounded">INV-{{ $invoice->invoice_no }}</span>
                                    </div>
                                </div>
                                <small class="text-[11px] text-slate-400 mt-2 block italic">
                                    Please specify "INV-{{ $invoice->invoice_no }}" in the bank transfer message to ensure immediate automated allocation.
                                </small>
                            </div>
                            --}}

                        </div>
                    @endif

                </div>

            </div>

        </div>
    </main>

</body>
</html>
