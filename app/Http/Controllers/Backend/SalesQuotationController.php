<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SalesQuotationDataTable;
use App\Exports\SalesQuotationExcelExport;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateBuyerCatalogPdfJob;
use App\Models\Category;
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
use App\Traits\HasEphemeralPdfReports;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalesQuotationController extends Controller
{
    use HasEphemeralPdfReports;
    public function index(SalesQuotationDataTable $dataTable)
    {
        $sequences = DocumentSequence::orderBy('model_type')->get();

        return $dataTable->render('backend.sales_quotation.index', compact('sequences'));
    }

    public function create(Request $request): View
    {
        $outlets = User::role('Outlet User')->where('status', 1)->latest('id')->get();
        $regularCustomers = User::customers()->where('status', 1)->latest('id')->get();
        $customers = $outlets->concat($regularCustomers);
        $currencies = Currency::where('status', 1)->get();
        $taxes = Tax::where('status', 1)->get();
        $categories = Category::where('status', 1)->orderBy('name')->get();
        $products = Product::where('status', 1)
            ->with(['variants.color', 'variants.size', 'category'])
            ->latest('id')
            ->get();
        $nextQuotationNo = DocumentSequence::previewNext('SalesQuotation');
        $prospectUser = $this->getOrCreateProspectUser();

        $cartItems = collect();
        if (in_array($request->query('source'), ['cart', 'basket'])) {
            $cartItems = \App\Models\Cart::where('user_id', auth()->id())
                ->where('cart_type', 'request')
                ->with(['product.variants', 'variant.color', 'variant.size'])
                ->get();
        }

        return view('backend.sales_quotation.create', compact(
            'customers', 'outlets', 'regularCustomers', 'currencies', 'taxes', 'categories', 'products', 'nextQuotationNo', 'cartItems', 'prospectUser'
        ));
    }

    /**
     * Get or create a default system user for walk-in / inquiry prospects
     */
    public function getOrCreateProspectUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'prospect@b2bviking.local'],
            [
                'name' => 'General Prospect / Catalog Inquiry',
                'password' => bcrypt('Prospect#2026!Sec'),
                'status' => 1,
            ]
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $isProspect = $request->customer_type === 'prospect' || empty($request->customer_id);
        if ($isProspect) {
            $prospectUser = $this->getOrCreateProspectUser();
            $request->merge(['customer_id' => $prospectUser->id]);
        }

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

            $notes = $request->notes ?? '';
            if ($isProspect) {
                $pName = trim($request->prospect_name ?? 'Valued Prospective Buyer');
                $pPhone = trim($request->prospect_phone ?? '');
                $leadTag = "[PROSPECT_LEAD: Name: {$pName} | Phone: {$pPhone}]";
                $notes = $notes ? "{$leadTag}\n{$notes}" : $leadTag;
            }

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
                'notes' => $notes,
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

            if ($request->filled('from_cart') || in_array($request->input('source'), ['cart', 'basket'])) {
                \App\Models\Cart::where('user_id', auth()->id())
                    ->where('cart_type', 'request')
                    ->delete();
            }

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

        $salesQuotation->load(['items.product.category', 'items.variant.color', 'items.variant.size']);
        $outlets = User::role('Outlet User')->where('status', 1)->latest('id')->get();
        $regularCustomers = User::customers()->where('status', 1)->latest('id')->get();
        $customers = $outlets->concat($regularCustomers);
        $currencies = Currency::where('status', 1)->get();
        $taxes = Tax::where('status', 1)->get();
        $categories = Category::where('status', 1)->orderBy('name')->get();
        $products = Product::where('status', 1)->with(['variants.color', 'variants.size', 'category'])->latest('id')->get();
        $prospectUser = $this->getOrCreateProspectUser();
        $isProspect = $salesQuotation->is_prospect;
        $prospectName = $salesQuotation->buyer_display_name;
        $prospectPhone = $salesQuotation->buyer_phone;
        $cleanNotes = $salesQuotation->clean_notes;

        return view('backend.sales_quotation.edit', compact(
            'salesQuotation', 'customers', 'outlets', 'regularCustomers', 'currencies', 'taxes', 'categories', 'products', 'prospectUser', 'isProspect', 'prospectName', 'prospectPhone', 'cleanNotes'
        ));
    }

    public function update(Request $request, SalesQuotation $salesQuotation): RedirectResponse
    {
        if ($salesQuotation->status !== 'draft') {
            toastr()->error('Locked Quotation: Converted or processed Sales Quotations cannot be edited according to Enterprise ERP audit rules. Please use Clone instead.');
            return redirect()->route('admin.sales-quotations.show', $salesQuotation->id);
        }

        $isProspect = $request->customer_type === 'prospect' || empty($request->customer_id);
        if ($isProspect) {
            $prospectUser = $this->getOrCreateProspectUser();
            $request->merge(['customer_id' => $prospectUser->id]);
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

            $notes = $request->notes ?? '';
            if ($isProspect) {
                $pName = trim($request->prospect_name ?? 'Valued Prospective Buyer');
                $pPhone = trim($request->prospect_phone ?? '');
                $leadTag = "[PROSPECT_LEAD: Name: {$pName} | Phone: {$pPhone}]";
                $cleanExisting = trim(preg_replace('/\[PROSPECT_LEAD:[^\]]+\]\s*/', '', $notes));
                $notes = $cleanExisting ? "{$leadTag}\n{$cleanExisting}" : $leadTag;
            } else {
                $notes = trim(preg_replace('/\[PROSPECT_LEAD:[^\]]+\]\s*/', '', $notes));
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
                'notes' => $notes,
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

        $pdf = PDF::loadView('backend.sales_quotation.sq_pdf', compact('salesQuotation', 'settings'))
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->stream('SQ_' . $salesQuotation->quotation_no . '.pdf');
    }

    /**
     * Download Excel Order Sheet for B2B Buyer
     */
    public function excel(SalesQuotation $salesQuotation): BinaryFileResponse
    {
        $fileName = 'Order_Sheet_' . $salesQuotation->quotation_no . '.xlsx';
        return Excel::download(new SalesQuotationExcelExport($salesQuotation), $fileName);
    }

    /**
     * Dispatch Background Asynchronous PDF Lookbook/Catalog Generation (Report Section Pattern)
     */
    public function catalogPdfAsync(Request $request, SalesQuotation $salesQuotation): JsonResponse
    {
        $userId = auth()->id() ?? 0;
        GenerateBuyerCatalogPdfJob::dispatch($salesQuotation->id, $userId);

        return response()->json([
            'status'        => 'success',
            'message'       => "Visual Lookbook generation for #{$salesQuotation->quotation_no} started in background.",
            'dispatched_at' => time(),
        ]);
    }

    /**
     * Check if Asynchronous Buyer Catalog PDF is ready in ephemeral storage
     */
    public function checkCatalogStatus(Request $request): JsonResponse
    {
        $quotationId = (int) $request->get('quotation_id', 0);
        $reportType = "catalog_sq{$quotationId}";
        $after = (int) $request->get('after', 0);

        $latest = $this->getLatestReportFile($reportType, 'admin.sales-quotations.catalog-pdf.download');
        if (!$latest) {
            return response()->json(['ready' => false]);
        }

        if ($after > 0 && ($latest['timestamp'] ?? 0) < $after) {
            return response()->json(['ready' => false]);
        }

        return response()->json([
            'ready'        => true,
            'download_url' => $latest['url'],
            'filename'     => $latest['filename'],
            'time'         => $latest['time'],
            'date'         => $latest['date'],
            'timestamp'    => $latest['timestamp'],
        ]);
    }

    /**
     * Download ephemeral generated Catalog PDF
     */
    public function downloadCatalogPdf(string $file): BinaryFileResponse|RedirectResponse
    {
        $cleanFile = basename($file);
        $path = storage_path('app/temp_reports/' . $cleanFile);

        if (!file_exists($path)) {
            toastr()->error('The requested catalog PDF has expired or was purged. Please generate a fresh catalog.');
            return redirect()->back();
        }

        return response()->download($path, $cleanFile, [
            'Content-Type' => 'application/pdf',
        ]);
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
