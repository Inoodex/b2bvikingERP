<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\LcDataTable;
use App\Http\Controllers\Controller;
use App\Models\LetterOfCredit;
use App\Models\LcExpense;
use App\Models\LcAmendment;
use App\Models\Purchase;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LetterOfCreditController extends Controller
{
    public function index(LcDataTable $dataTable)
    {
        return $dataTable->render('backend.purchase.lc_register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'lc_no' => 'required|string|max:50|unique:letters_of_credit,lc_no',
            'issuing_bank' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'margin_percent' => 'nullable|numeric|min:0|max:100',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:issue_date',
        ]);

        $po = Purchase::findOrFail($request->purchase_id);

        $lc = LetterOfCredit::create([
            'lc_no' => $request->lc_no,
            'proforma_invoice_id' => $po->proforma_invoice_id,
            'vendor_id' => $po->vendor_id,
            'issuing_bank' => $request->issuing_bank,
            'margin_percent' => $request->margin_percent ?? 0,
            'amount' => $request->amount,
            'currency_id' => $po->currency_id ?? $po->vendor?->currency_id,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'status' => 'open',
        ]);

        // Save 13 Normalized LC Expenses if present
        if ($request->has('expenses') && is_array($request->expenses)) {
            foreach ($request->expenses as $costElement => $amount) {
                if ($amount > 0) {
                    LcExpense::create([
                        'lc_id' => $lc->id,
                        'cost_element' => $costElement,
                        'amount' => $amount,
                        'currency_id' => $po->currency_id ?? $po->vendor?->currency_id,
                        'goes_to_unit_cost' => true,
                    ]);
                }
            }
        }

        $po->update([
            'lc_id' => $lc->id,
        ]);
        $po->advanceMilestone('lc_opened');

        Toastr::success('Letter of Credit (LC) registered successfully!');
        return redirect()->route('admin.letters-of-credit.show', $lc->id);
    }

    public function show($id): View
    {
        $lc = LetterOfCredit::with(['vendor', 'currency', 'proformaInvoice', 'purchases', 'expenses', 'amendments'])->findOrFail($id);
        return view('backend.purchase.lc_show', compact('lc'));
    }

    public function addAmendment(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'amendment_no' => 'required|string|max:50',
            'change_details' => 'required|string',
            'amended_date' => 'required|date',
        ]);

        $lc = LetterOfCredit::findOrFail($id);

        LcAmendment::create([
            'lc_id' => $lc->id,
            'amendment_no' => $request->amendment_no,
            'change_details' => $request->change_details,
            'amended_date' => $request->amended_date,
        ]);

        $lc->update(['status' => 'amended']);

        Toastr::success('LC Amendment recorded successfully!');
        return redirect()->back();
    }
}
