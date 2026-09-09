<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class PaymentTransactionController extends Controller
{
    /**
     * Display list of all online and physical payment transactions.
     */
    public function index(Request $request)
    {
        $query = PaymentTransaction::with(['order', 'invoice', 'customer', 'codCollection'])
            ->latest();

        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('transaction_no', 'like', "%{$s}%")
                  ->orWhere('external_reference', 'like', "%{$s}%")
                  ->orWhereHas('invoice', fn($iq) => $iq->where('invoice_no', 'like', "%{$s}%"))
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%"));
            });
        }

        $transactions = $query->paginate(25);

        // Stats
        $totalCaptured = (float) PaymentTransaction::where('status', 'captured')->sum('amount');
        $paypalCount = PaymentTransaction::where('gateway', 'paypal')->count();
        $codCount = PaymentTransaction::where('gateway', 'cod')->count();

        return view('backend.payments.transactions.index', compact(
            'transactions',
            'totalCaptured',
            'paypalCount',
            'codCount'
        ));
    }
}
