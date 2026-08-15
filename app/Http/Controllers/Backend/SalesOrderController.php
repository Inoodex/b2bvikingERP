<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SalesOrderDataTable;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\DocumentSequence;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tax;
use App\Models\User;
use App\Services\Credit\CreditValidationService;
use App\Services\Pricing\PricelistResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    protected CreditValidationService $creditService;
    protected PricelistResolverService $pricingService;

    public function __construct(CreditValidationService $creditService, PricelistResolverService $pricingService)
    {
        $this->creditService = $creditService;
        $this->pricingService = $pricingService;
    }

    public function index(SalesOrderDataTable $dataTable)
    {
        return $dataTable->render('backend.sales_orders.index');
    }

    public function create(): View
    {
        $customers = User::orderBy('name')->get();
        $currencies = Currency::where('status', 1)->get();
        $taxes = Tax::where('status', 1)->get();
        $products = Product::where('status', 1)->with('variants')->get();

        return view('backend.sales_orders.create', compact('customers', 'currencies', 'taxes', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_method' => 'nullable|string|max:100',
            'billing_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $customer = User::findOrFail($request->user_id);
            $orderNo = DocumentSequence::generateNext('SalesOrder');

            // Calculate Subtotal
            $subtotal = 0.00;
            $itemsData = [];
            foreach ($request->items as $item) {
                $lineTotal = round($item['qty'] * $item['unit_price'], 2);
                $subtotal += $lineTotal;
                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                ];
            }

            // Calculate Tax
            $taxAmount = 0.00;
            $taxLabel = 'No Tax';
            $vatRate = 0.00;
            if ($request->tax_id) {
                $tax = Tax::find($request->tax_id);
                if ($tax) {
                    $taxLabel = $tax->name;
                    $vatRate = (float) $tax->value;
                    $taxAmount = $tax->type === 'percent' ? round($subtotal * ($vatRate / 100), 2) : $vatRate;
                }
            }

            $discountAmount = (float) ($request->discount_amount ?? 0.00);
            $totalAmount = max(0, round($subtotal + $taxAmount - $discountAmount, 2));

            // Evaluate Credit Exposure
            $creditEvaluation = $this->creditService->evaluateCreditExposure($customer->id, $totalAmount);
            $orderStatus = $creditEvaluation['status'] === 'credit_hold' ? 'credit_hold' : 'approved';

            $order = Order::create([
                'order_no' => $orderNo,
                'user_id' => $customer->id,
                'status' => $orderStatus,
                'shipping_method' => $request->shipping_method ?? 'Standard Delivery',
                'billing_name' => $customer->name,
                'billing_email' => $customer->email,
                'billing_phone' => !empty($customer->phone) ? $customer->phone : 'N/A',
                'billing_address' => !empty($request->billing_address) ? $request->billing_address : (!empty($customer->address) ? $customer->address : 'N/A'),
                'subtotal_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'due_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'tax_label' => $taxLabel,
                'vat_rate' => $vatRate,
                'placed_at' => now(),
            ]);

            foreach ($itemsData as $item) {
                $product = Product::find($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'vendor_id' => $product?->vendor_id ?? null,
                    'product_name' => $product?->name ?? 'Product Item',
                    'category_name' => $product?->category?->name ?? null,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['qty'],
                    'line_total' => $item['line_total'],
                ]);
            }

            DB::commit();

            if ($orderStatus === 'credit_hold') {
                toastr()->warning('Order ' . $orderNo . ' placed under CREDIT HOLD: Exposure (' . number_format($creditEvaluation['total_exposure'], 2) . ') exceeds approved limit (' . number_format($creditEvaluation['credit_limit'], 2) . ').');
            } else {
                toastr()->success('Sales Order ' . $orderNo . ' created and approved successfully!');
            }

            return redirect()->route('admin.sales-orders.show', $order->id);
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Failed to create Sales Order: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(string $id): View
    {
        $order = Order::with(['user', 'items.product', 'items.variant'])->findOrFail($id);
        $creditEvaluation = $this->creditService->evaluateCreditExposure($order->user_id, (float)$order->total_amount, $order->id);
        return view('backend.sales_orders.show', compact('order', 'creditEvaluation'));
    }

    public function releaseCreditHold(Request $request, string $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'override_reason' => 'required|string|max:255',
        ]);

        if ($order->status !== 'credit_hold') {
            toastr()->info('Order is not under Credit Hold.');
            return redirect()->back();
        }

        $order->update([
            'status' => 'approved',
            'pi_email' => 'Credit Override Granted: ' . $request->override_reason . ' (By ' . (auth()->user()?->name ?? 'Admin') . ' at ' . now()->format('d M Y H:i') . ')',
        ]);

        toastr()->success('Credit Hold released successfully for Sales Order ' . $order->order_no . '!');
        return redirect()->route('admin.sales-orders.show', $order->id);
    }

    public function checkCustomerCredit(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'nullable|integer',
            'order_amount' => 'required|numeric|min:0',
        ]);

        $evaluation = $this->creditService->evaluateCreditExposure(
            $request->user_id ? (int) $request->user_id : null,
            (float) $request->order_amount
        );

        return response()->json($evaluation);
    }

    public function destroy(string $id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->items()->delete();
            $order->delete();

            return response(['status' => 'success', 'message' => 'Sales Order deleted successfully!']);
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => 'Failed to delete Sales Order: ' . $e->getMessage()], 500);
        }
    }
}
