<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\AdminOrderNotificationMail;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CheckoutDiscountResolver;
use App\Services\CheckoutTaxResolver;
use App\Services\ApprovalService;
use App\Support\PiInfoSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Show authenticated user's orders
     */
    public function index(Request $request)
    {
        // Legacy route support: always redirect old /my-orders to the new account orders panel.
        $params = ['panel' => 'orders'];
        if ($request->filled('page')) {
            $params['page'] = (int) $request->query('page');
        }

        return redirect()->route('account.index', $params);
    }

    /**
     * Show single order details for authenticated user
     */
    public function show(Order $order)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        $order->load(['items.product', 'items.variant', 'user']);

        return view('frontend.pages.orders.show', compact('order'));
    }

    /**
     * View PI invoice (HTML) for authenticated user.
     */
    public function piInvoice(Order $order)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        $order->load([
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant.color',
            'items.variant.size',
            'user',
        ]);

        $settings = GeneralSetting::first();
        $piInfo = PiInfoSupport::prepare($order->pi_info, $order->items, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        return view('backend.orders.pi_invoice', [
            'order' => $order,
            'settings' => $settings,
            'piInfo' => $piInfo,
            'piTotals' => $piTotals,
            'hasSavedPiInfo' => $hasSavedPiInfo,
            'isFrontend' => true,
            'backUrl' => route('orders.show', $order->id),
            'downloadUrl' => route('orders.pi-invoice.download', $order->id),
        ]);
    }

    /**
     * Download PI invoice (PDF) for authenticated user.
     */
    public function downloadPiInvoice(Order $order)
    {
        abort_if((int) $order->user_id !== (int) Auth::id(), 403);

        $path = 'invoices/pi-invoice-' . $order->order_no . '.pdf';
        
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
        }

        \App\Jobs\GeneratePdfJob::dispatch($order->id, 'pi_invoice', Auth::id());

        return redirect()->back()->with('success', 'PI Invoice generation has been queued. Please refresh the page and download again when ready.');
    }

    /**
     * Direct reorder: create new order from an existing order
     * using current product price and current tax rules.
     */
    public function reorder(Order $order)
    {
        $user = Auth::user();
        abort_if((int) $order->user_id !== (int) $user->id, 403);

        $order->load('items');
        if ($order->items->isEmpty()) {
            return redirect()
                ->route('orders.show', $order->id)
                ->with('error', 'Reorder failed: this order has no items.');
        }

        $productIds = $order->items
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $variantIds = $order->items
            ->pluck('variant_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $products = Product::query()
            ->with('category')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $variants = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $missingLines = [];
        $preparedLines = [];
        $subtotal = 0.0;

        $taxResolver = app(CheckoutTaxResolver::class);
        $discountResolver = app(CheckoutDiscountResolver::class);
        $taxAmount = 0.0;
        $discountAmount = 0.0;
        $appliedTaxSignatures = [];
        $hasDefaultFlatTax = false;
        $defaultFlatTaxValue = 0.0;

        foreach ($order->items as $oldItem) {
            $product = $products->get((int) $oldItem->product_id);
            if (!$product) {
                $missingLines[] = $oldItem->product_name ?: ('Item #' . $oldItem->id);
                continue;
            }

            $variant = null;
            if (!empty($oldItem->variant_id)) {
                $variant = $variants->get((int) $oldItem->variant_id);
                if (!$variant || (int) $variant->product_id !== (int) $product->id) {
                    $missingLines[] = ($oldItem->product_name ?: $product->name) . ' (variant unavailable)';
                    continue;
                }
            }

            $qty = max(1, (int) $oldItem->quantity);
            $unitPrice = $this->resolveCurrentUnitPrice($product, $variant, $user);
            $lineSubtotal = round($unitPrice * $qty, 2);
            $subtotal += $lineSubtotal;

            $lineDiscount = $discountResolver->resolveForLine($product, $lineSubtotal, $qty);
            $discountAmount += (float) ($lineDiscount['amount'] ?? 0);

            $lineTax = $taxResolver->resolveForLine($product, $lineSubtotal);
            if ($lineTax['source'] === 'default' && $lineTax['type'] === 'flat') {
                $hasDefaultFlatTax = true;
                $defaultFlatTaxValue = max($defaultFlatTaxValue, (float) $lineTax['value']);
            } else {
                $taxAmount += (float) $lineTax['amount'];
            }

            if ($lineTax['source'] !== 'none') {
                $appliedTaxSignatures[] = ($lineTax['source'] . ':' . ($lineTax['type'] ?? 'none') . ':' . (string) $lineTax['value']);
            }

            $preparedLines[] = [
                'product_id' => (int) $product->id,
                'variant_id' => $variant?->id,
                'vendor_id' => $product->vendor_id ?: $oldItem->vendor_id,
                'product_name' => $product->name,
                'category_name' => $product->category->name ?? ($oldItem->category_name ?: 'General'),
                'variant_label' => $this->resolveVariantLabel($variant, $oldItem),
                'product_image' => $product->thumb_image ?: $oldItem->product_image,
                'unit_price' => (float) $unitPrice,
                'quantity' => $qty,
                'line_total' => $lineSubtotal,
            ];
        }

        if (!empty($missingLines)) {
            $preview = implode(', ', array_slice($missingLines, 0, 3));
            $moreCount = max(0, count($missingLines) - 3);
            $suffix = $moreCount > 0 ? (' and ' . $moreCount . ' more') : '';

            return redirect()
                ->route('orders.show', $order->id)
                ->with('error', 'Reorder failed. Unavailable item(s): ' . $preview . $suffix . '.');
        }

        if (empty($preparedLines)) {
            return redirect()
                ->route('orders.show', $order->id)
                ->with('error', 'Reorder failed: no valid items found.');
        }

        if ($hasDefaultFlatTax && !empty($preparedLines)) {
            $taxAmount += $defaultFlatTaxValue;
        }

        $subtotal = round($subtotal, 2);
        $taxAmount = round($taxAmount, 2);
        $discountAmount = round($discountAmount, 2);
        $total = round(max(0, $subtotal + $taxAmount - $discountAmount), 2);

        $taxMeta = $this->buildTaxMeta($taxResolver, $appliedTaxSignatures);

        DB::beginTransaction();
        try {
            $newOrder = Order::create([
                'order_no' => $this->generateUniqueOrderNoForUser($user),
                'user_id' => $user->id,
                'status' => 'pending',
                'shipping_method' => 'frontend_reorder',
                'ship_different' => (bool) $order->ship_different,
                'billing_name' => $order->billing_name,
                'billing_email' => $order->billing_email,
                'billing_phone' => $order->billing_phone,
                'billing_address' => $order->billing_address,
                'billing_outlet_name' => $order->billing_outlet_name,
                'shipping_name' => $order->ship_different ? $order->shipping_name : null,
                'shipping_email' => $order->ship_different ? $order->shipping_email : null,
                'shipping_phone' => $order->ship_different ? $order->shipping_phone : null,
                'shipping_address' => $order->ship_different ? $order->shipping_address : null,
                'shipping_city' => $order->ship_different ? $order->shipping_city : null,
                'shipping_state' => $order->ship_different ? $order->shipping_state : null,
                'shipping_zip_code' => $order->ship_different ? $order->shipping_zip_code : null,
                'shipping_country' => $order->ship_different ? $order->shipping_country : null,
                'shipping_outlet_name' => $order->ship_different ? $order->shipping_outlet_name : null,
                'subtotal_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $total,
                'tax_label' => $taxMeta['tax_label'],
                'vat_rate' => $taxMeta['vat_rate'],
                'placed_at' => now(),
            ]);

            foreach ($preparedLines as $line) {
                OrderItem::create([
                    'order_id' => $newOrder->id,
                    'product_id' => $line['product_id'],
                    'variant_id' => $line['variant_id'],
                    'vendor_id' => $line['vendor_id'],
                    'product_name' => $line['product_name'],
                    'category_name' => $line['category_name'],
                    'variant_label' => $line['variant_label'],
                    'product_image' => $line['product_image'],
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);
            }

            // Submit order for Multi-Level Approval
            $approvalService = app(ApprovalService::class);
            $approvalService->submitForApproval($newOrder, (float)$newOrder->total_amount);

            DB::commit();

            // Send Admin Notification Email
            try {
                Mail::to('ctpwh2026@gmail.com')
                    ->send(new AdminOrderNotificationMail($newOrder));
            } catch (\Exception $e) {
                Log::error('Failed to send admin order notification on reorder: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('orders.show', $order->id)
                ->with('error', 'Reorder failed: ' . $e->getMessage());
        }

        return redirect()
            ->route('orders.show', $newOrder->id)
            ->with('success', 'Reorder placed successfully. Reference: ' . $newOrder->order_no);
    }

    private function resolveCurrentUnitPrice(Product $product, ?ProductVariant $variant, $user): float
    {
        $isOutletRole = $user->hasRole('Outlet User') || $user->hasRole('User');

        $price = $isOutletRole
            ? ($variant ? ($variant->outlet_price ?: $product->outlet_price ?: $product->price) : ($product->outlet_price ?: $product->price))
            : ($variant ? ($variant->price ?: $product->price) : $product->price);

        return (float) $price;
    }

    private function resolveVariantLabel(?ProductVariant $variant, OrderItem $oldItem): ?string
    {
        if (!$variant) {
            $fallback = trim((string) ($oldItem->variant_label ?? ''));
            return $fallback !== '' ? $fallback : null;
        }

        $label = trim((string) ($variant->name ?? ''));
        if ($label === '') {
            $parts = array_filter([
                trim((string) ($variant->color ?? '')),
                trim((string) ($variant->size ?? '')),
            ]);
            $label = implode(' - ', $parts);
        }

        return $label !== '' ? $label : null;
    }

    private function buildTaxMeta(CheckoutTaxResolver $taxResolver, array $signatures): array
    {
        $taxLabel = 'VAT / Tax';
        $vatRate = null;

        $uniqueSignatures = array_values(array_unique($signatures));
        $defaultTax = $taxResolver->getDefaultTax();

        if (count($uniqueSignatures) > 1) {
            $taxLabel = 'VAT / Tax (Mixed)';
        } elseif (count($uniqueSignatures) === 1) {
            [$source, $type, $value] = explode(':', $uniqueSignatures[0]);

            if ($type === 'percent') {
                $vatRate = (float) $value;
                $taxLabel = 'VAT (' . rtrim(rtrim(number_format($vatRate, 2, '.', ''), '0'), '.') . '%)';
            } elseif ($type === 'flat') {
                $taxLabel = $source === 'product' ? 'Product VAT (Flat)' : 'VAT (Flat)';
            }
        } elseif ($defaultTax && $defaultTax->type === 'percent') {
            $vatRate = (float) $defaultTax->value;
            $taxLabel = 'VAT (' . rtrim(rtrim(number_format($vatRate, 2, '.', ''), '0'), '.') . '%)';
        } elseif ($defaultTax && $defaultTax->type === 'flat') {
            $taxLabel = 'VAT (Flat)';
        }

        return [
            'tax_label' => $taxLabel,
            'vat_rate' => $vatRate,
        ];
    }

    private function generateUniqueOrderNoForUser($user): string
    {
        $isOutletUser = $user && ($user->hasRole('Outlet User') || $user->hasRole('Outlet'));
        $prefix = $isOutletUser ? 'DS' : 'ORD';

        return \App\Services\OrderNumberService::generate($prefix, \App\Models\Order::class);
    }
}
