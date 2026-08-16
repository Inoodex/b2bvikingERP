<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CreditNoteDataTable;
use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\Order;
use App\Models\OrderPayment;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class CreditNoteController extends Controller
{
    public function index(CreditNoteDataTable $dataTable)
    {
        return $dataTable->render('backend.credit_notes.index');
    }

    public function show($id)
    {
        $creditNote = CreditNote::with([
            'salesReturn.order.user',
            'salesReturn.items.product',
            'salesReturn.items.variant.color',
            'salesReturn.items.variant.size',
            'customer',
            'creator'
        ])->findOrFail($id);

        $customerOrders = [];
        if ($creditNote->customer_id) {
            $customerOrders = Order::where('user_id', $creditNote->customer_id)
                ->whereIn('status', ['approved', 'processing', 'completed'])
                ->where('due_amount', '>', 0)
                ->latest()
                ->get();
        }

        return view('backend.credit_notes.show', compact('creditNote', 'customerOrders'));
    }

    public function settle(Request $request, $id)
    {
        $creditNote = CreditNote::with('salesReturn.order')->findOrFail($id);

        $request->validate([
            'settlement_mode' => 'required|in:invoice_offset,replacement,direct_refund',
            'settle_amount'   => 'required|numeric|min:0.01|max:' . $creditNote->remaining_amount,
            'target_order_id' => 'nullable|required_if:settlement_mode,invoice_offset|exists:orders,id',
            'notes'           => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $settleAmount = (float)$request->settle_amount;
            $newSettled = (float)$creditNote->settled_amount + $settleAmount;
            $newRemaining = max(0, (float)$creditNote->amount - $newSettled);

            $status = 'unsettled';
            if ($newRemaining == 0) {
                $status = 'settled';
            } elseif ($newSettled > 0) {
                $status = 'partial';
            }

            if ($request->settlement_mode === 'invoice_offset' && $request->target_order_id) {
                $targetOrder = Order::findOrFail($request->target_order_id);
                
                OrderPayment::create([
                    'order_id'       => $targetOrder->id,
                    'amount'         => $settleAmount,
                    'payment_method' => 'credit_note',
                    'payment_status' => 'completed',
                    'transaction_id' => 'CN-' . $creditNote->credit_note_no,
                    'notes'          => 'Settled via Credit Note #' . $creditNote->credit_note_no,
                    'created_by'     => Auth::id(),
                ]);

                $newPaid = (float)$targetOrder->paid_amount + $settleAmount;
                $newDue = max(0, round((float)$targetOrder->total_amount - $newPaid, 2));

                $targetOrder->update([
                    'paid_amount' => $newPaid,
                    'due_amount'  => $newDue,
                ]);
            }

            $creditNote->update([
                'settled_amount'    => $newSettled,
                'remaining_amount'  => $newRemaining,
                'settlement_status' => $status,
                'settlement_mode'   => $request->settlement_mode,
                'notes'             => $request->notes ? $creditNote->notes . "\n" . $request->notes : $creditNote->notes,
            ]);

            DB::commit();

            Toastr::success('Credit Note settled successfully!', 'Success');
            return redirect()->route('admin.credit-notes.show', $creditNote->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Credit Note Settlement Error: ' . $e->getMessage());
            Toastr::error('Failed to settle Credit Note: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }

    public function downloadPdf($id)
    {
        $creditNote = CreditNote::with([
            'salesReturn.order.user',
            'salesReturn.items.product',
            'salesReturn.items.variant.color',
            'salesReturn.items.variant.size',
            'customer',
            'creator'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('backend.pdf.credit_note', compact('creditNote'));
        return $pdf->stream('Credit_Note_' . $creditNote->credit_note_no . '.pdf');
    }
}
