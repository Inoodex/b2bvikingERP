<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SalesQuotationDataTable;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\DocumentSequence;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SalesQuotation;
use App\Models\SalesQuotationItem;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PDF;

class SalesQuotationController extends Controller
{
    public function index(SalesQuotationDataTable $dataTable)
    {
        $sequences = DocumentSequence::orderBy('model_type')->get();

        return $dataTable->render('backend.sales_quotation.index', compact('sequences'));
    }

    public function create(): View
    {
        $customers = User::orderBy('name')->get();
        $currencies = Currency::where('status', 1)->get();
        $taxes = Tax::where('status', 1)->get();
        $products = Product::where('status', 1)->with('variants')->get();
        $nextQuotationNo = DocumentSequence::generateNext('SalesQuotation');

        return view('backend.sales_quotation.create', compact(
            'customers', 'currencies', 'taxes', 'products', 'nextQuotationNo'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0.000001',
            'tax_id' => 'nullable|exists:taxes,id',
            'incoterm' => 'nullable|string|max:50',
            'valid_until' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $quotationNo = DocumentSequence::generateNext('SalesQuotation');
            $currency = Currency::find($request->currency_id);
            $exchangeRate = $request->exchange_rate ?? $currency?->exchange_rate ?? 1.0;

            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += ($item['qty'] * $item['unit_price']);
            }

            $taxAmount = 0;
            if ($request->tax_id) {
                $tax = Tax::find($request->tax_id);
                if ($tax) {
                    $taxAmount = $tax->type === 'percent'
                        ? ($subtotal * ($tax->value / 100))
                        : (float)$tax->value;
                }
            }

            $discountAmount = (float)($request->discount_amount ?? 0);
            $totalAmount = max(0, round($subtotal + $taxAmount - $discountAmount, 2));

            $salesQuotation = SalesQuotation::create([
                'quotation_no' => $quotationNo,
                'customer_id' => $request->customer_id,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $exchangeRate,
                'tax_id' => $request->tax_id,
                'incoterm' => $request->incoterm,
                'valid_until' => $request->valid_until,
                'status' => 'draft',
                'subtotal_amount' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'discount_amount' => round($discountAmount, 2),
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                SalesQuotationItem::create([
                    'sales_quotation_id' => $salesQuotation->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            DB::commit();

            toastr()->success('Sales Quotation ' . $quotationNo . ' created successfully!');

            return redirect()->route('admin.sales-quotations.show', $salesQuotation->id);
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Failed to create Sales Quotation: ' . $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function show(SalesQuotation $salesQuotation): View
    {
        $salesQuotation->load(['customer', 'currency', 'tax', 'creator', 'items.product', 'items.variant']);

        return view('backend.sales_quotation.show', compact('salesQuotation'));
    }

    public function edit(SalesQuotation $salesQuotation)
    {
        if ($salesQuotation->status !== 'draft') {
            toastr()->error('Locked Quotation: Converted or processed Sales Quotations cannot be edited according to Enterprise ERP audit rules. Please use Clone instead.');
            return redirect()->route('admin.sales-quotations.show', $salesQuotation->id);
        }

        $salesQuotation->load(['items.product', 'items.variant']);
        $customers = User::orderBy('name')->get();
        $currencies = Currency::where('status', 1)->get();
        $taxes = Tax::where('status', 1)->get();
        $products = Product::where('status', 1)->with('variants')->get();

        return view('backend.sales_quotation.edit', compact(
            'salesQuotation', 'customers', 'currencies', 'taxes', 'products'
        ));
    }

    public function update(Request $request, SalesQuotation $salesQuotation): RedirectResponse
    {
        if ($salesQuotation->status !== 'draft') {
            toastr()->error('Locked Quotation: Converted or processed Sales Quotations cannot be edited according to Enterprise ERP audit rules. Please use Clone instead.');
            return redirect()->route('admin.sales-quotations.show', $salesQuotation->id);
        }
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0.000001',
            'tax_id' => 'nullable|exists:taxes,id',
            'incoterm' => 'nullable|string|max:50',
            'valid_until' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += ($item['qty'] * $item['unit_price']);
            }

            $taxRate = 0;
            if ($request->tax_id) {
                $tax = Tax::find($request->tax_id);
                if ($tax) {
                    $taxRate = (float) $tax->rate;
                }
            }

            $taxAmount = ($subtotal * $taxRate) / 100;
            $discountAmount = (float) ($request->discount_amount ?? 0);
            $totalAmount = round($subtotal + $taxAmount - $discountAmount, 2);

            $exchangeRate = 1.000000;
            if ($request->currency_id) {
                $currency = Currency::find($request->currency_id);
                if ($currency && $currency->exchange_rate) {
                    $exchangeRate = (float) $currency->exchange_rate;
                }
            }
            if ($request->filled('exchange_rate')) {
                $exchangeRate = (float) $request->exchange_rate;
            }

            $salesQuotation->update([
                'customer_id' => $request->customer_id,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $exchangeRate,
                'tax_id' => $request->tax_id,
                'incoterm' => $request->incoterm,
                'valid_until' => $request->valid_until,
                'subtotal_amount' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'discount_amount' => round($discountAmount, 2),
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);

            $salesQuotation->items()->delete();
            foreach ($request->items as $item) {
                SalesQuotationItem::create([
                    'sales_quotation_id' => $salesQuotation->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            DB::commit();

            toastr()->success('Sales Quotation ' . $salesQuotation->quotation_no . ' updated successfully!');

            return redirect()->route('admin.sales-quotations.show', $salesQuotation->id);
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Failed to update Sales Quotation: ' . $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function clone(SalesQuotation $salesQuotation): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $newQuotationNo = DocumentSequence::generateNext('SalesQuotation');

            $newQuotation = $salesQuotation->replicate([
                'quotation_no', 'status', 'created_at', 'updated_at'
            ]);
            $newQuotation->quotation_no = $newQuotationNo;
            $newQuotation->status = 'draft';
            $newQuotation->created_by = auth()->id();
            $newQuotation->save();

            foreach ($salesQuotation->items as $item) {
                $newItem = $item->replicate(['sales_quotation_id', 'created_at', 'updated_at']);
                $newItem->sales_quotation_id = $newQuotation->id;
                $newItem->save();
            }

            DB::commit();

            toastr()->success('Quotation cloned successfully as ' . $newQuotationNo);

            return redirect()->route('admin.sales-quotations.show', $newQuotation->id);
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Failed to clone quotation: ' . $e->getMessage());

            return redirect()->back();
        }
    }

    public function convertToOrder(SalesQuotation $salesQuotation): RedirectResponse
    {
        if ($salesQuotation->status === 'converted') {
            toastr()->warning('This quotation has already been converted to a Sales Order.');
            return redirect()->back();
        }

        try {
            DB::beginTransaction();

            $soNo = DocumentSequence::generateNext('SalesOrder');

            $customer = $salesQuotation->customer;

            $order = Order::create([
                'order_no' => $soNo,
                'user_id' => $salesQuotation->customer_id,
                'billing_name' => $customer?->name ?? 'Customer',
                'billing_email' => $customer?->email ?? 'customer@example.com',
                'billing_phone' => $customer?->phone ?? '00000000',
                'billing_address' => $customer?->address ?? 'N/A',
                'subtotal_amount' => $salesQuotation->subtotal_amount,
                'tax_amount' => $salesQuotation->tax_amount,
                'discount_amount' => $salesQuotation->discount_amount,
                'total_amount' => $salesQuotation->total_amount,
                'paid_amount' => 0,
                'due_amount' => $salesQuotation->total_amount,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'placed_at' => now(),
            ]);

            foreach ($salesQuotation->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product?->name ?? 'Product',
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->qty,
                    'line_total' => round($item->qty * $item->unit_price, 2),
                ]);
            }

            $salesQuotation->update(['status' => 'converted']);

            DB::commit();

            toastr()->success('Sales Quotation converted successfully to Sales Order ' . $soNo . '!');

            return redirect()->route('admin.sales-quotations.show', $salesQuotation->id);
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Failed to convert Sales Quotation: ' . $e->getMessage());

            return redirect()->back();
        }
    }

    public function pdf(SalesQuotation $salesQuotation)
    {
        $salesQuotation->load(['customer', 'currency', 'tax', 'creator', 'items.product', 'items.variant']);
        $settings = GeneralSetting::first();

        $pdf = PDF::loadView('backend.sales_quotation.pdf', compact('salesQuotation', 'settings'))
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->stream('Sales_Quotation_' . $salesQuotation->quotation_no . '.pdf');
    }

    public function destroy(string $id)
    {
        try {
            $salesQuotation = SalesQuotation::findOrFail($id);
            $salesQuotation->items()->delete();
            $salesQuotation->delete();

            return response(['status' => 'success', 'message' => 'Sales Quotation deleted successfully!']);
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => 'Failed to delete Sales Quotation: ' . $e->getMessage()], 500);
        }
    }
}
