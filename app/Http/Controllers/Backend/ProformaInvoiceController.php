<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProformaInvoice;
use App\Models\Purchase;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProformaInvoiceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'pi_no' => 'required|string|max:50|unique:proforma_invoices,pi_no',
            'issue_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'pi_no.unique' => 'This Proforma Invoice (PI) Number has already been used. Please enter a unique PI Number.',
        ]);

        $po = Purchase::findOrFail($request->purchase_id);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('pi_attachments', 'public');
        }

        $pi = ProformaInvoice::create([
            'pi_no' => $request->pi_no,
            'vendor_id' => $po->vendor_id,
            'rfq_id' => $po->rfq_id,
            'currency_id' => $po->currency_id ?? $po->vendor?->currency_id,
            'total_amount' => $request->total_amount,
            'issue_date' => $request->issue_date,
            'status' => 'confirmed',
            'attachment_path' => $attachmentPath,
            'remarks' => $request->remarks ?? null,
        ]);

        $po->update([
            'proforma_invoice_id' => $pi->id,
            'milestone_status' => 'pi_attached',
        ]);

        Toastr::success('Proforma Invoice (PI) attached successfully!');
        return redirect()->back();
    }
}
