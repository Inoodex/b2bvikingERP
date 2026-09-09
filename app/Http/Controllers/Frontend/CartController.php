<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\AdminOrderNotificationMail;
use App\Models\Cart;
use App\Models\DocumentSequence;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SavedPurchaseForm;
use App\Models\Wishlist;
use App\Services\CheckoutDiscountResolver;
use App\Services\CheckoutTaxResolver;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Show the frontend cart page
     */
    public function index()
    {
        return view('frontend.pages.cart');
    }

    /**
     * Show the checkout page
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $items = $this->getUserCartItems();
        $summary = $this->calculateCheckoutSummary($items);
        $requestedSavedFormId = (int) $request->query('saved_form', 0);
        $savedFormId = null;

        if ($requestedSavedFormId > 0) {
            $savedFormId = SavedPurchaseForm::query()
                ->where('user_id', (int) $user->id)
                ->whereKey($requestedSavedFormId)
                ->value('id');
            $savedFormId = $savedFormId ? (int) $savedFormId : null;
        }

        $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? '')));
        $firstName = $nameParts[0] ?? '';
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        $enabledGateways = \App\Models\PaymentSetting::where('status', 'enable')->get()->keyBy('key');
        if ($enabledGateways->isEmpty()) {
            $enabledGateways = collect([
                'cod' => new \App\Models\PaymentSetting(['key' => 'cod', 'name' => 'Cash On Delivery', 'status' => 'enable']),
            ]);
        }

        return view('frontend.pages.checkout', [
            'cartItems' => $items,
            'subtotal' => $summary['subtotal'],
            'vatRate' => $summary['vat_rate'],
            'vatAmount' => $summary['tax_amount'],
            'taxLabel' => $summary['tax_label'],
            'taxBreakdown' => $summary['tax_breakdown'],
            'discountBreakdown' => $summary['discount_breakdown'],
            'discountAmount' => $summary['discount_amount'],
            'total' => $summary['total'],
            'firstName' => $firstName,
            'lastName' => $lastName,
            'savedFormId' => $savedFormId,
            'user' => $user,
            'enabledGateways' => $enabledGateways,
        ]);
    }

    /**
     * Get all frontend cart items for the authenticated user
     */
    public function items()
    {
        $items = $this->getUserCartItems();

        return response()->json([
            'items' => $items,
            'count' => $items->count(),
        ]);
    }

    /**
     * Add or update a product in the frontend cart
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        $productId = $validated['product_id'];
        $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;
        $product = Product::with(['inventoryStocks', 'productType'])
            ->where('status', 1)
            ->whereHas('category', function ($query) {
                $query->where('status', 1);
            })
            ->findOrFail($productId);

        // Check if product is upcoming - prevent adding to cart
        $productTypeName = trim((string) optional($product->productType)->name);
        if ($productTypeName === '') {
            $productTypeName = trim((string) ($product->product_type ?? ''));
        }
        if (strtolower($productTypeName) === 'upcoming' || str_contains(strtolower($productTypeName), 'upcoming')) {
            return response()->json([
                'success' => false,
                'message' => 'This product is coming soon and not available for purchase yet.',
            ], 422);
        }

        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::with('inventoryStocks')
                ->where('status', 1)
                ->findOrFail($variantId);
            if ((int) $variant->product_id !== (int) $productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid variant for this product.',
                ], 422);
            }
        }

        $minimumOrderQty = max(1, (int) ($product->minimum_order_qty ?? 1));
        $quantity  = max(1, (int) ($validated['quantity'] ?? 1));

        if ($quantity < $minimumOrderQty) {
            $quantity = $minimumOrderQty;
        } elseif ($quantity > $minimumOrderQty) {
            $quantity = (int) (ceil($quantity / $minimumOrderQty) * $minimumOrderQty);
        }

        $cartItem = Cart::where('user_id', Auth::id())
                        ->where('product_id', $productId)
                        ->where('cart_type', 'frontend')
                        ->when(
                            $variantId !== null,
                            fn ($q) => $q->where('variant_id', $variantId),
                            fn ($q) => $q->whereNull('variant_id')
                        )
                        ->first();

        $availableStock = $this->resolveAvailableStock($product, $variant);
        $currentCartQty = (int) ($cartItem->quantity ?? 0);
        $requestedCartQty = $currentCartQty + $quantity;

        if ($availableStock < 1) {
            return response()->json([
                'success' => false,
                'message' => 'This item is out of stock.',
                'available_stock' => 0,
            ], 422);
        }

        if ($requestedCartQty > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => 'Requested quantity exceeds available stock. Available stock: ' . $availableStock . '.',
                'available_stock' => $availableStock,
                'requested_quantity' => $requestedCartQty,
            ], 422);
        }

        if ($cartItem) {
            $cartItem->quantity = $requestedCartQty;
            $cartItem->save();
            $action = 'updated';
        } else {
            Cart::create([
                'user_id'    => Auth::id(),
                'product_id' => $productId,
                'variant_id' => $variantId,
                'cart_type'  => 'frontend',
                'vendor_id'  => $product->vendor_id ?? null,
                'quantity'   => $quantity,
            ]);
            $action = 'added';
        }

        $count = Cart::where('user_id', Auth::id())
                     ->where('cart_type', 'frontend')
                     ->sum('quantity');

        $removedFromWishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete() > 0;

        $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

        return response()->json([
            'success'               => true,
            'action'                => $action,
            'count'                 => (int) $count,
            'variant_id'            => $variantId,
            'removed_from_wishlist' => $removedFromWishlist,
            'wishlist_count'        => (int) $wishlistCount,
            'applied_quantity'      => (int) $quantity,
            'minimum_order_qty'     => (int) $minimumOrderQty,
        ]);
    }

    /**
     * Remove a product from the frontend cart
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'cart_id'    => 'nullable|integer|exists:carts,id',
            'product_id' => 'nullable|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $query = Cart::where('user_id', Auth::id())
            ->where('cart_type', 'frontend');

        if (!empty($validated['cart_id'])) {
            $query->where('id', (int) $validated['cart_id']);
        } elseif (!empty($validated['product_id'])) {
            $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;
            $query->where('product_id', (int) $validated['product_id'])
                ->when(
                    $variantId !== null,
                    fn ($q) => $q->where('variant_id', $variantId),
                    fn ($q) => $q->whereNull('variant_id')
                );
        } else {
            return response()->json([
                'success' => false,
                'message' => 'cart_id or product_id is required.',
            ], 422);
        }

        $query->delete();

        $count = Cart::where('user_id', Auth::id())
                     ->where('cart_type', 'frontend')
                     ->sum('quantity');

        return response()->json([
            'success' => true,
            'count'   => (int) $count,
        ]);
    }

    /**
     * Update quantity of a cart item
     */
    public function updateQuantity(Request $request)
    {
        $validated = $request->validate([
            'cart_id'    => 'nullable|integer|exists:carts,id',
            'product_id' => 'nullable|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $query = Cart::where('user_id', Auth::id())
            ->where('cart_type', 'frontend');

        if (!empty($validated['cart_id'])) {
            $query->where('id', (int) $validated['cart_id']);
        } elseif (!empty($validated['product_id'])) {
            $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;
            $query->where('product_id', (int) $validated['product_id'])
                ->when(
                    $variantId !== null,
                    fn ($q) => $q->where('variant_id', $variantId),
                    fn ($q) => $q->whereNull('variant_id')
                );
        } else {
            return response()->json([
                'success' => false,
                'message' => 'cart_id or product_id is required.',
            ], 422);
        }

        $cartItem = $query->first();

        if ($cartItem) {
            $cartItem->loadMissing(['product.inventoryStocks', 'variant.inventoryStocks']);

            if (!$cartItem->product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found for this cart item.',
                ], 422);
            }

            if ((int) ($cartItem->product->status ?? 0) !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is inactive.',
                ], 422);
            }

            if ($cartItem->variant && (int) $cartItem->variant->product_id !== (int) $cartItem->product_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid variant for this cart item.',
                ], 422);
            }

            $availableStock = $this->resolveAvailableStock($cartItem->product, $cartItem->variant);
            $minimumOrderQty = max(1, (int) ($cartItem->product->minimum_order_qty ?? 1));
            $requestedQty = max(1, (int) $validated['quantity']);

            if ($requestedQty < $minimumOrderQty) {
                $requestedQty = $minimumOrderQty;
            } elseif ($requestedQty > $minimumOrderQty) {
                $requestedQty = (int) (ceil($requestedQty / $minimumOrderQty) * $minimumOrderQty);
            }

            if ($availableStock < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'This item is out of stock.',
                    'available_stock' => 0,
                ], 422);
            }

            if ($requestedQty > $availableStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested quantity exceeds available stock. Available stock: ' . $availableStock . '.',
                    'available_stock' => $availableStock,
                    'requested_quantity' => $requestedQty,
                ], 422);
            }

            $cartItem->quantity = $requestedQty;
            $cartItem->save();
        }

        return response()->json([
            'success' => true,
            'applied_quantity' => (int) ($cartItem?->quantity ?? 0),
        ]);
    }

    /**
     * Clear all frontend cart items for the user
     */
    public function clear()
    {
        Cart::where('user_id', Auth::id())
            ->where('cart_type', 'frontend')
            ->delete();

        return response()->json(['success' => true, 'count' => 0]);
    }

    /**
     * Place order from frontend checkout
     */
    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255|required_unless:ship_different,1',
            'email' => 'nullable|email|max:255|required_unless:ship_different,1',
            'phone' => 'nullable|string|max:50|required_unless:ship_different,1',
            'address' => 'nullable|string|max:500|required_unless:ship_different,1',
            'outlet_name' => 'nullable|string|max:255',
            'pi_email' => 'nullable|email|max:255',
            'saved_form_id' => 'nullable|integer',
            'ship_different' => 'nullable|boolean',
            'payment_method' => 'required|string|in:cod,paypal',
            'shipping_first_name' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_last_name' => 'nullable|string|max:255',
            'shipping_email' => 'nullable|email|max:255|required_if:ship_different,1',
            'shipping_phone' => 'nullable|string|max:50|required_if:ship_different,1',
            'shipping_street_address' => 'nullable|string|max:500|required_if:ship_different,1',
            'shipping_city' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_state' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_zip_code' => 'nullable|string|max:50|required_if:ship_different,1',
            'shipping_country' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_outlet_name' => 'nullable|string|max:255',
        ]);

        $cartRows = Cart::where('user_id', Auth::id())
            ->where('cart_type', 'frontend')
            ->with(['product.category', 'variant'])
            ->get()
            ->filter(fn ($item) => $item->product && (int) ($item->product->status ?? 0) === 1)
            ->values();

        if ($cartRows->isEmpty()) {
            return redirect()->route('checkout.index')->with('error', 'Your cart is empty.');
        }

        $shipDifferent = (bool) ($validated['ship_different'] ?? false);
        $checkoutItems = $this->getUserCartItems();
        $summary = $this->calculateCheckoutSummary($checkoutItems);

        $shippingName = trim((($validated['shipping_first_name'] ?? '') . ' ' . ($validated['shipping_last_name'] ?? '')));
        $shippingName = $shippingName !== '' ? $shippingName : null;

        // DB billing_* columns are non-nullable. If ship_different is enabled, reuse shipping info as billing source.
        $billingName = $shipDifferent ? ($shippingName ?? ($validated['shipping_first_name'] ?? null)) : ($validated['first_name'] ?? null);
        $billingEmail = $shipDifferent ? ($validated['shipping_email'] ?? null) : ($validated['email'] ?? null);
        $billingPhone = $shipDifferent ? ($validated['shipping_phone'] ?? null) : ($validated['phone'] ?? null);
        $billingAddress = $shipDifferent ? ($validated['shipping_street_address'] ?? null) : ($validated['address'] ?? null);
        $billingOutletName = $shipDifferent
            ? ($validated['shipping_outlet_name'] ?? null)
            : ($validated['outlet_name'] ?? null);
        $piEmail = $validated['pi_email'] ?? null;

        $paymentMethod = $validated['payment_method'];

        // --- GATEWAY-FIRST ARCHITECTURE FOR PAYPAL ---
        // Do NOT create order, delete cart, or trigger approvals until payment is verified and captured!
        if ($paymentMethod === 'paypal') {
            $outletId = $this->resolveOrderOutletId();
            $requestedLines = $this->buildRequestedLinesFromCart($cartRows);
            $stockRows = $this->lockInventoryForRequestedLines($requestedLines, $outletId);

            foreach ($requestedLines as $key => $line) {
                $available = (int) optional($stockRows->get($key))->quantity;
                $requestedQty = (int) $line['requested_qty'];
                if ($available < $requestedQty) {
                    return redirect()
                        ->route('checkout.index')
                        ->with('error', 'Insufficient stock for ' . $line['name'] . '. Available: ' . $available . ', requested: ' . $requestedQty . '.')
                        ->withInput();
                }
            }

            session([
                'pending_paypal_checkout' => [
                    'user_id'              => Auth::id(),
                    'validated'            => $validated,
                    'summary'              => $summary,
                    'ship_different'       => $shipDifferent,
                    'shipping_name'        => $shippingName,
                    'billing_name'         => $billingName,
                    'billing_email'        => $billingEmail,
                    'billing_phone'        => $billingPhone,
                    'billing_address'      => $billingAddress,
                    'billing_outlet_name'  => $billingOutletName,
                    'pi_email'             => $piEmail,
                    'saved_form_id'        => (int) ($validated['saved_form_id'] ?? 0),
                ]
            ]);

            try {
                $paypalService = app(\App\Services\Payment\PayPalService::class);
                $returnUrl = route('checkout.paypal.success');
                $cancelUrl = route('checkout.paypal.cancel');
                $cartReference = 'CART-' . Auth::id() . '-' . time();

                $paypalOrder = $paypalService->createOrderFromAmount(
                    (float) $summary['total'],
                    $cartReference,
                    'Checkout Order - ' . config('app.name', 'Copenhagen Tourist Point'),
                    $returnUrl,
                    $cancelUrl
                );

                $approvalUrl = $paypalOrder['approval_url'] ?? ($paypalOrder['approve_url'] ?? null);

                if (!empty($approvalUrl)) {
                    session(['paypal_checkout_order_id' => $paypalOrder['id']]);
                    return redirect()->away($approvalUrl);
                }

                return redirect()->route('checkout.index')->with('error', 'Could not obtain PayPal approval link. Please verify payment settings.');
            } catch (\Throwable $ex) {
                Log::error('PayPal checkout initiation failed: ' . $ex->getMessage());
                return redirect()->route('checkout.index')->with('error', 'PayPal initialization failed: ' . $ex->getMessage());
            }
        }

        // --- CASH ON DELIVERY (COD) & OFFLINE CLEARING FLOW ---
        DB::beginTransaction();
        try {
            $outletId = $this->resolveOrderOutletId();
            $requestedLines = $this->buildRequestedLinesFromCart($cartRows);
            $stockRows = $this->lockInventoryForRequestedLines($requestedLines, $outletId);

            foreach ($requestedLines as $key => $line) {
                $available = (int) optional($stockRows->get($key))->quantity;
                $requestedQty = (int) $line['requested_qty'];
                if ($available < $requestedQty) {
                    DB::rollBack();
                    return redirect()
                        ->route('checkout.index')
                        ->with('error', 'Insufficient stock for ' . $line['name'] . '. Available: ' . $available . ', requested: ' . $requestedQty . '.')
                        ->withInput();
                }
            }

            $orderNo = $this->generateUniqueOrderNoForUser(Auth::user());

            $order = Order::create([
                'order_no'             => $orderNo,
                'user_id'              => Auth::id(),
                'status'               => 'pending',
                'shipping_method'      => 'frontend_checkout',
                'payment_method'       => $validated['payment_method'],
                'ship_different'       => $shipDifferent,
                'billing_name'         => $billingName,
                'billing_email'        => $billingEmail,
                'billing_phone'        => $billingPhone,
                'billing_address'      => $billingAddress,
                'billing_outlet_name'  => $billingOutletName,
                'pi_email'             => $piEmail,
                'shipping_name'        => $shipDifferent ? $shippingName : null,
                'shipping_email'       => $shipDifferent ? ($validated['shipping_email'] ?? null) : null,
                'shipping_phone'       => $shipDifferent ? ($validated['shipping_phone'] ?? null) : null,
                'shipping_address'     => $shipDifferent ? ($validated['shipping_street_address'] ?? null) : null,
                'shipping_city'        => $shipDifferent ? ($validated['shipping_city'] ?? null) : null,
                'shipping_state'       => $shipDifferent ? ($validated['shipping_state'] ?? null) : null,
                'shipping_zip_code'    => $shipDifferent ? ($validated['shipping_zip_code'] ?? null) : null,
                'shipping_country'     => $shipDifferent ? ($validated['shipping_country'] ?? null) : null,
                'shipping_outlet_name' => $shipDifferent ? ($validated['shipping_outlet_name'] ?? null) : null,
                'subtotal_amount'      => $summary['subtotal'],
                'tax_amount'           => $summary['tax_amount'],
                'discount_amount'      => $summary['discount_amount'],
                'total_amount'         => $summary['total'],
                'paid_amount'          => 0,
                'due_amount'           => $summary['total'],
                'payment_status'       => 'pending',
                'tax_label'            => $summary['tax_label'],
                'vat_rate'             => $summary['vat_rate'],
                'placed_at'            => now(),
            ]);

            foreach ($cartRows as $item) {
                $product = $item->product;
                $variant = $item->variant;
                $unitPrice = (float) $this->resolveCartItemUnitPrice($product, $variant);
                $variantLabel = $this->resolveVariantLabel($variant);

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $product->id,
                    'variant_id'     => $variant?->id,
                    'vendor_id'      => $item->vendor_id ?: $product->vendor_id,
                    'product_name'   => $product->name,
                    'category_name'  => $product->category->name ?? 'General',
                    'variant_label'  => $variantLabel,
                    'product_image'  => $product->thumb_image,
                    'unit_price'     => $unitPrice,
                    'quantity'       => (int) $item->quantity,
                    'line_total'     => round($unitPrice * (int) $item->quantity, 2),
                ]);
            }

            Cart::where('user_id', Auth::id())
                ->where('cart_type', 'frontend')
                ->delete();

            $savedFormId = (int) ($validated['saved_form_id'] ?? 0);
            if ($savedFormId > 0) {
                SavedPurchaseForm::query()
                    ->where('user_id', (int) Auth::id())
                    ->whereKey($savedFormId)
                    ->delete();
            }

            // Evaluate dynamic multi-step approval workflow for B2B commercial order
            $approvalService = app(\App\Services\ApprovalService::class);
            $approvalService->submitForApproval($order, (float)$order->total_amount);

            DB::commit();

            // Auto-create COD collection ledger entry
            try {
                $codService = app(\App\Services\Payment\CodService::class);
                $codService->createCodCollection($order);
            } catch (\Throwable $ex) {
                Log::warning('COD collection auto-creation notice: ' . $ex->getMessage());
            }

            // Send Admin Notification Email
            try {
                Mail::to('ctpwh2026@gmail.com')
                    ->send(new AdminOrderNotificationMail($order));
            } catch (\Exception $e) {
                Log::error('Failed to send admin order notification: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Order placement failed: ' . $e->getMessage())
                ->withInput();
        }

        return redirect()
            ->route('orders.show', $order->id)
            ->with('success', 'Order placed successfully. Reference: ' . $order->order_no);
    }

    /**
     * Handle PayPal Return URL capture from frontend checkout.
     */
    public function paypalSuccess(Request $request)
    {
        $paypalToken = $request->query('token');

        // Case 1: Fresh checkout session completion
        if (session()->has('pending_paypal_checkout')) {
            $checkoutData = session('pending_paypal_checkout');
            $validated = $checkoutData['validated'] ?? [];
            $summary = $checkoutData['summary'] ?? [];

            $cartRows = Cart::where('user_id', Auth::id())
                ->where('cart_type', 'frontend')
                ->with(['product.category', 'variant'])
                ->get()
                ->filter(fn ($item) => $item->product && (int) ($item->product->status ?? 0) === 1)
                ->values();

            if ($cartRows->isEmpty()) {
                session()->forget(['pending_paypal_checkout', 'paypal_checkout_order_id']);
                return redirect()->route('checkout.index')->with('error', 'Cart session expired. Please re-add items.');
            }

            try {
                $paypalService = app(\App\Services\Payment\PayPalService::class);
                $capture = $paypalService->captureOrder($paypalToken);

                if (($capture['status'] ?? '') === 'COMPLETED') {
                    $txnRef = $capture['id'] ?? ($capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalToken);

                    DB::beginTransaction();
                    try {
                        $outletId = $this->resolveOrderOutletId();
                        $requestedLines = $this->buildRequestedLinesFromCart($cartRows);
                        $stockRows = $this->lockInventoryForRequestedLines($requestedLines, $outletId);

                        foreach ($requestedLines as $key => $line) {
                            $available = (int) optional($stockRows->get($key))->quantity;
                            $requestedQty = (int) $line['requested_qty'];
                            if ($available < $requestedQty) {
                                DB::rollBack();
                                return redirect()
                                    ->route('checkout.index')
                                    ->with('error', 'Insufficient stock for ' . $line['name'] . '. Please contact support.');
                            }
                        }

                        $orderNo = $this->generateUniqueOrderNoForUser(Auth::user());
                        $shipDifferent = (bool) ($checkoutData['ship_different'] ?? false);

                        $order = Order::create([
                            'order_no'             => $orderNo,
                            'user_id'              => Auth::id(),
                            'status'               => 'pending',
                            'shipping_method'      => 'frontend_checkout',
                            'payment_method'       => 'paypal',
                            'ship_different'       => $shipDifferent,
                            'billing_name'         => $checkoutData['billing_name'] ?? null,
                            'billing_email'        => $checkoutData['billing_email'] ?? null,
                            'billing_phone'        => $checkoutData['billing_phone'] ?? null,
                            'billing_address'      => $checkoutData['billing_address'] ?? null,
                            'billing_outlet_name'  => $checkoutData['billing_outlet_name'] ?? null,
                            'pi_email'             => $checkoutData['pi_email'] ?? null,
                            'shipping_name'        => $shipDifferent ? ($checkoutData['shipping_name'] ?? null) : null,
                            'shipping_email'       => $shipDifferent ? ($validated['shipping_email'] ?? null) : null,
                            'shipping_phone'       => $shipDifferent ? ($validated['shipping_phone'] ?? null) : null,
                            'shipping_address'     => $shipDifferent ? ($validated['shipping_street_address'] ?? null) : null,
                            'shipping_city'        => $shipDifferent ? ($validated['shipping_city'] ?? null) : null,
                            'shipping_state'       => $shipDifferent ? ($validated['shipping_state'] ?? null) : null,
                            'shipping_zip_code'    => $shipDifferent ? ($validated['shipping_zip_code'] ?? null) : null,
                            'shipping_country'     => $shipDifferent ? ($validated['shipping_country'] ?? null) : null,
                            'shipping_outlet_name' => $shipDifferent ? ($validated['shipping_outlet_name'] ?? null) : null,
                            'subtotal_amount'      => $summary['subtotal'] ?? 0,
                            'tax_amount'           => $summary['tax_amount'] ?? 0,
                            'discount_amount'      => $summary['discount_amount'] ?? 0,
                            'total_amount'         => $summary['total'] ?? 0,
                            'paid_amount'          => $summary['total'] ?? 0,
                            'due_amount'           => 0,
                            'payment_status'       => 'paid',
                            'tax_label'            => $summary['tax_label'] ?? null,
                            'vat_rate'             => $summary['vat_rate'] ?? 0,
                            'placed_at'            => now(),
                        ]);

                        foreach ($cartRows as $item) {
                            $product = $item->product;
                            $variant = $item->variant;
                            $unitPrice = (float) $this->resolveCartItemUnitPrice($product, $variant);
                            $variantLabel = $this->resolveVariantLabel($variant);

                            OrderItem::create([
                                'order_id'       => $order->id,
                                'product_id'     => $product->id,
                                'variant_id'     => $variant?->id,
                                'vendor_id'      => $item->vendor_id ?: $product->vendor_id,
                                'product_name'   => $product->name,
                                'category_name'  => $product->category->name ?? 'General',
                                'variant_label'  => $variantLabel,
                                'product_image'  => $product->thumb_image,
                                'unit_price'     => $unitPrice,
                                'quantity'       => (int) $item->quantity,
                                'line_total'     => round($unitPrice * (int) $item->quantity, 2),
                            ]);
                        }

                        Cart::where('user_id', Auth::id())
                            ->where('cart_type', 'frontend')
                            ->delete();

                        $savedFormId = (int) ($checkoutData['saved_form_id'] ?? 0);
                        if ($savedFormId > 0) {
                            SavedPurchaseForm::query()
                                ->where('user_id', (int) Auth::id())
                                ->whereKey($savedFormId)
                                ->delete();
                        }

                        \App\Models\PaymentTransaction::create([
                            'transaction_no'     => 'TXN-' . date('Ym') . '-' . str_pad((string)(\App\Models\PaymentTransaction::count() + 1), 5, '0', STR_PAD_LEFT),
                            'gateway'            => 'paypal',
                            'order_id'           => $order->id,
                            'customer_id'        => $order->user_id,
                            'amount'             => $order->total_amount,
                            'currency'           => 'DKK',
                            'external_reference' => $txnRef,
                            'status'             => 'completed',
                            'gateway_response'   => $capture,
                        ]);

                        \App\Models\OrderPayment::create([
                            'order_id'       => $order->id,
                            'transaction_id' => $txnRef,
                            'payment_method' => 'paypal',
                            'amount'         => (float)$order->total_amount,
                            'note'           => 'Paid online via PayPal Express Checkout',
                        ]);

                        try {
                            $journalService = app(\App\Services\Accounting\JournalEntryService::class);
                            $lines = [
                                ['account_code' => '1020', 'debit' => (float)$order->total_amount, 'credit' => 0],
                                ['account_code' => '1030', 'debit' => 0, 'credit' => (float)$order->total_amount],
                            ];
                            $journalService->postJournal("PayPal Payment Capture - Order #{$order->order_no}", $order, $lines);
                        } catch (\Throwable $je) {
                            Log::error("PayPal GL posting notice: " . $je->getMessage());
                        }

                        $approvalService = app(\App\Services\ApprovalService::class);
                        $approvalService->submitForApproval($order, (float)$order->total_amount);

                        DB::commit();

                        try {
                            Mail::to('ctpwh2026@gmail.com')->send(new AdminOrderNotificationMail($order));
                        } catch (\Exception $e) {
                            Log::error('Failed to send admin order notification: ' . $e->getMessage());
                        }

                        session()->forget(['pending_paypal_checkout', 'paypal_checkout_order_id']);

                        return redirect()->route('orders.show', $order->id)->with('success', "Payment of kr. {$order->total_amount} via PayPal was successfully verified and captured! Order #{$order->order_no} has been confirmed.");
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        Log::error('Order creation after PayPal capture error: ' . $e->getMessage());
                        return redirect()->route('checkout.index')->with('error', 'Order placement failed: ' . $e->getMessage());
                    }
                }

                return redirect()->route('checkout.index')->with('error', 'PayPal payment was not completed.');
            } catch (\Throwable $e) {
                Log::error('PayPal capture error: ' . $e->getMessage());
                return redirect()->route('checkout.index')->with('error', 'Payment capture failed: ' . $e->getMessage());
            }
        }

        // Case 2: Existing order retry capture (via order_id)
        $orderId = $request->query('order_id') ?: session('paypal_checkout_order_id');
        session()->forget('paypal_checkout_order_id');

        if (!$orderId || !$paypalToken) {
            return redirect()->route('orders.index')->with('error', 'Invalid payment session or token.');
        }

        $order = Order::find($orderId);
        if (!$order) {
            return redirect()->route('orders.index')->with('error', 'Order not found.');
        }

        try {
            $paypalService = app(\App\Services\Payment\PayPalService::class);
            $capture = $paypalService->captureOrder($paypalToken);

            if (($capture['status'] ?? '') === 'COMPLETED') {
                DB::transaction(function () use ($order, $capture, $paypalToken) {
                    $order->update([
                        'payment_status' => 'paid',
                        'paid_amount'    => $order->total_amount,
                        'due_amount'     => 0,
                        'payment_method' => 'paypal',
                    ]);

                    $txnRef = $capture['id'] ?? ($capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalToken);

                    \App\Models\PaymentTransaction::create([
                        'transaction_no'     => 'TXN-' . date('Ym') . '-' . str_pad((string)(\App\Models\PaymentTransaction::count() + 1), 5, '0', STR_PAD_LEFT),
                        'gateway'            => 'paypal',
                        'order_id'           => $order->id,
                        'customer_id'        => $order->user_id,
                        'amount'             => $order->total_amount,
                        'currency'           => 'DKK',
                        'external_reference' => $txnRef,
                        'status'             => 'completed',
                        'gateway_response'   => $capture,
                    ]);

                    \App\Models\OrderPayment::create([
                        'order_id'       => $order->id,
                        'transaction_id' => $txnRef,
                        'payment_method' => 'paypal',
                        'amount'         => (float)$order->total_amount,
                        'note'           => 'Paid online via PayPal Express Checkout',
                    ]);

                    if ($order->salesInvoice) {
                        $order->salesInvoice->update([
                            'payment_status' => 'paid',
                            'paid_amount'    => $order->total_amount,
                            'due_amount'     => 0,
                        ]);
                    }

                    try {
                        $journalService = app(\App\Services\Accounting\JournalEntryService::class);
                        $lines = [
                            ['account_code' => '1020', 'debit' => (float)$order->total_amount, 'credit' => 0],
                            ['account_code' => '1030', 'debit' => 0, 'credit' => (float)$order->total_amount],
                        ];
                        $journalService->postJournal("PayPal Payment Capture - Order #{$order->order_no}", $order, $lines);
                    } catch (\Throwable $je) {
                        Log::error("PayPal GL posting notice: " . $je->getMessage());
                    }
                });

                return redirect()->route('orders.show', $order->id)->with('success', "Payment of kr. {$order->total_amount} via PayPal was successfully verified and captured!");
            }

            return redirect()->route('orders.show', $order->id)->with('error', 'PayPal payment was not completed.');
        } catch (\Throwable $e) {
            Log::error('PayPal capture error: ' . $e->getMessage());
            return redirect()->route('orders.show', $order->id)->with('error', 'Payment capture failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle PayPal Cancel URL from frontend checkout.
     */
    public function paypalCancel(Request $request)
    {
        // 1. Fresh checkout session cancellation: Keep cart 100% intact, no ghost order!
        if (session()->has('pending_paypal_checkout')) {
            session()->forget(['pending_paypal_checkout', 'paypal_checkout_order_id']);
            return redirect()->route('checkout.index')->with('warning', 'PayPal checkout was cancelled. Your items remain safely in your cart below. You can choose another payment method or try again.');
        }

        // 2. Retry payment cancellation for existing order
        $orderId = $request->query('order_id') ?: session('paypal_checkout_order_id');
        session()->forget('paypal_checkout_order_id');

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                return redirect()->route('orders.show', $order->id)->with('warning', 'PayPal checkout was cancelled. You can retry payment anytime below.');
            }
            return redirect()->route('orders.show', $orderId)->with('warning', 'PayPal checkout was cancelled. You can retry payment later.');
        }

        return redirect()->route('checkout.index')->with('warning', 'PayPal checkout was cancelled.');
    }

    /**
     * Retry PayPal payment for an existing unpaid order.
     */
    public function retryPayPalPayment(Request $request, Order $order)
    {
        if ((int) $order->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized access to order.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order->id)->with('info', 'This order has already been paid.');
        }

        try {
            $paypalService = app(\App\Services\Payment\PayPalService::class);
            $returnUrl = route('checkout.paypal.success', ['order_id' => $order->id]);
            $cancelUrl = route('checkout.paypal.cancel', ['order_id' => $order->id]);
            $paypalOrder = $paypalService->createOrder($order, $returnUrl, $cancelUrl);

            $approvalUrl = $paypalOrder['approval_url'] ?? ($paypalOrder['approve_url'] ?? null);

            if (!empty($approvalUrl)) {
                session(['paypal_checkout_order_id' => $order->id]);
                return redirect()->away($approvalUrl);
            }

            return redirect()->route('orders.show', $order->id)->with('error', 'Could not obtain PayPal approval link. Please verify payment settings.');
        } catch (\Throwable $ex) {
            Log::error('PayPal retry payment error: ' . $ex->getMessage());
            return redirect()->route('orders.show', $order->id)->with('error', 'PayPal initialization failed: ' . $ex->getMessage());
        }
    }

    private function resolveVariantLabel(?ProductVariant $variant): ?string
    {
        if (!$variant) {
            return null;
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

    private function resolveCartItemUnitPrice(Product $product, ?ProductVariant $variant): float
    {
        $user = Auth::user();
        $isOutletRole = $user->hasRole('Outlet User') || $user->hasRole('User');

        $price = $isOutletRole
            ? ($variant ? ($variant->outlet_price ?: $product->outlet_price ?: $product->price) : ($product->outlet_price ?? $product->price))
            : ($variant ? ($variant->price ?: $product->price) : $product->price);

        return (float) $price;
    }

    private function calculateCheckoutSummary($items): array
    {
        $subtotal = $items->sum(function ($item) {
            return ((float) $item['price']) * ((int) $item['quantity']);
        });

        $productsById = Product::query()
            ->whereIn('id', $items->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        $taxResolver = app(CheckoutTaxResolver::class);
        $discountResolver = app(CheckoutDiscountResolver::class);
        $taxAmount = 0.0;
        $productTaxAmount = 0.0;
        $defaultTaxAmount = 0.0;
        $discountAmount = 0.0;
        $appliedTaxSignatures = [];
        $appliedDiscountSignatures = [];
        $productDiscountAmount = 0.0;
        $userDiscountAmount = 0.0;
        $defaultDiscountAmount = 0.0;
        $productTaxRates = [];
        $defaultTaxRates = [];
        $productDiscountRates = [];
        $userDiscountRates = [];
        $defaultDiscountRates = [];
        $hasDefaultFlatTax = false;
        $defaultFlatTaxValue = 0.0;

        foreach ($items as $item) {
            $lineSubtotal = ((float) $item['price']) * ((int) $item['quantity']);
            $lineQty = max(1, (int) ($item['quantity'] ?? 1));
            $product = $productsById->get((int) $item['product_id']);

            $lineDiscount = $discountResolver->resolveForLine($product, $lineSubtotal, $lineQty);
            $lineDiscountAmount = (float) ($lineDiscount['amount'] ?? 0);
            $discountAmount += $lineDiscountAmount;

            foreach ($lineDiscount['discounts'] ?? [] as $subDiscount) {
                $subAmount = (float) ($subDiscount['amount'] ?? 0);
                $source = $subDiscount['source'] ?? 'none';
                $type = $subDiscount['type'] ?? null;
                $value = $subDiscount['value'] ?? 0;

                if ($source === 'product') {
                    $productDiscountAmount += $subAmount;
                    $this->addAppliedRate($productDiscountRates, $type, $value);
                } elseif ($source === 'default') {
                    $defaultDiscountAmount += $subAmount;
                    $this->addAppliedRate($defaultDiscountRates, $type, $value);
                }

                if ($source !== 'none') {
                    $appliedDiscountSignatures[] = ($source . ':' . ($type ?? 'none') . ':' . (string) $value);
                }
            }

            $lineTax = $taxResolver->resolveForLine($product, $lineSubtotal);
            $lineTaxAmount = (float) ($lineTax['amount'] ?? 0);

            if ($lineTax['source'] === 'default' && $lineTax['type'] === 'flat') {
                $hasDefaultFlatTax = true;
                $defaultFlatTaxValue = max($defaultFlatTaxValue, (float) $lineTax['value']);
                $this->addAppliedRate($defaultTaxRates, $lineTax['type'] ?? null, $lineTax['value'] ?? 0);
            } else {
                $taxAmount += $lineTaxAmount;
                if (($lineTax['source'] ?? 'none') === 'product') {
                    $productTaxAmount += $lineTaxAmount;
                    $this->addAppliedRate($productTaxRates, $lineTax['type'] ?? null, $lineTax['value'] ?? 0);
                } elseif (($lineTax['source'] ?? 'none') === 'default') {
                    $defaultTaxAmount += $lineTaxAmount;
                    $this->addAppliedRate($defaultTaxRates, $lineTax['type'] ?? null, $lineTax['value'] ?? 0);
                }
            }

            if ($lineTax['source'] !== 'none') {
                $appliedTaxSignatures[] = ($lineTax['source'] . ':' . ($lineTax['type'] ?? 'none') . ':' . (string) $lineTax['value']);
            }
        }

        if ($hasDefaultFlatTax && $items->isNotEmpty()) {
            $taxAmount += $defaultFlatTaxValue;
            $defaultTaxAmount += $defaultFlatTaxValue;
        }

        $taxAmount = round($taxAmount, 2);
        $productTaxAmount = round($productTaxAmount, 2);
        $defaultTaxAmount = round($defaultTaxAmount, 2);
        $productDiscountAmount = round($productDiscountAmount, 2);
        $defaultDiscountAmount = round($defaultDiscountAmount, 2);

        // Calculate User Level Discount at Order Level
        $userDiscountAmount = 0.0;
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->discount_type && $user->discount_value > 0) {
                $subtotalAfterProductDiscounts = $subtotal - $productDiscountAmount - $defaultDiscountAmount;
                $canApply = true;

                // Check Minimum Order Amount for Flat Discount
                if ($user->discount_type === 'flat') {
                    $minOrder = (float) ($user->min_order_amount ?? 0);
                    if ($subtotalAfterProductDiscounts < $minOrder) {
                        $canApply = false;
                    }
                }

                if ($canApply) {
                    if ($user->discount_type === 'percent') {
                        $userDiscountAmount = round(($subtotalAfterProductDiscounts * $user->discount_value) / 100, 2);
                    } else {
                        // Flat
                        $userDiscountAmount = round(min($subtotalAfterProductDiscounts, (float) $user->discount_value), 2);
                    }

                    if ($userDiscountAmount > 0) {
                        $this->addAppliedRate($userDiscountRates, $user->discount_type, $user->discount_value);
                        $appliedDiscountSignatures[] = ('user:' . $user->discount_type . ':' . (string) $user->discount_value);
                    }
                }
            }
        }

        $discountAmount = round($productDiscountAmount + $defaultDiscountAmount + $userDiscountAmount, 2);
        $userDiscountAmount = round($userDiscountAmount, 2);
        $taxLabel = 'VAT / Tax';
        $vatRate = null;

        $defaultTax = $taxResolver->getDefaultTax();
        $uniqueSignatures = array_values(array_unique($appliedTaxSignatures));
        $isMixedTax = count($uniqueSignatures) > 1;
        $uniqueDiscountSignatures = array_values(array_unique($appliedDiscountSignatures));
        $isMixedDiscount = count($uniqueDiscountSignatures) > 1;
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

        $total = max(0, $subtotal + $taxAmount - $discountAmount);

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total' => round($total, 2),
            'tax_label' => $taxLabel,
            'vat_rate' => $vatRate,
            'tax_breakdown' => [
                'product_vat' => $productTaxAmount,
                'default_vat' => $defaultTaxAmount,
                'total_vat' => $taxAmount,
                'product_rate_label' => $this->buildAppliedRateLabel(array_values($productTaxRates)),
                'default_rate_label' => $this->buildAppliedRateLabel(array_values($defaultTaxRates)),
                'total_rate_label' => $this->buildCombinedRateLabel(array_values(array_merge($defaultTaxRates, $productTaxRates))),
                'is_mixed' => $isMixedTax,
            ],
            'discount_breakdown' => [
                'product_discount' => $productDiscountAmount,
                'user_discount' => $userDiscountAmount,
                'default_discount' => $defaultDiscountAmount,
                'total_discount' => $discountAmount,
                'product_rate_label' => $this->buildAppliedRateLabel(array_values($productDiscountRates)),
                'user_rate_label' => $this->buildAppliedRateLabel(array_values($userDiscountRates)),
                'default_rate_label' => $this->buildAppliedRateLabel(array_values($defaultDiscountRates)),
                'total_rate_label' => $this->buildCombinedRateLabel(array_values(array_merge($defaultDiscountRates, $productDiscountRates, $userDiscountRates))),
                'is_mixed' => $isMixedDiscount,
            ],
        ];
    }

    private function addAppliedRate(array &$bucket, ?string $type, $value): void
    {
        if (!$type) {
            return;
        }

        $normalizedType = strtolower((string) $type);
        if (!in_array($normalizedType, ['percent', 'flat'], true)) {
            return;
        }

        $normalizedValue = max(0, (float) $value);
        if ($normalizedValue <= 0) {
            return;
        }

        if ($normalizedType === 'percent' && $normalizedValue > 100) {
            $normalizedValue = 100.0;
        }

        $key = $normalizedType . ':' . number_format($normalizedValue, 4, '.', '');
        $bucket[$key] = [
            'type' => $normalizedType,
            'value' => $normalizedValue,
        ];
    }

    private function buildAppliedRateLabel(array $rates): ?string
    {
        if (empty($rates)) {
            return null;
        }

        $labels = [];
        foreach ($rates as $rate) {
            $type = strtolower((string) ($rate['type'] ?? ''));
            $value = max(0, (float) ($rate['value'] ?? 0));

            if ($value <= 0) {
                continue;
            }

            $formattedValue = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
            if ($type === 'percent') {
                $labels[] = $formattedValue . '%';
            } elseif ($type === 'flat') {
                $labels[] = 'Flat ' . $formattedValue;
            }
        }

        $labels = array_values(array_unique($labels));
        sort($labels, SORT_NATURAL);

        return !empty($labels) ? implode(', ', $labels) : null;
    }

    private function buildCombinedRateLabel(array $rates): ?string
    {
        if (empty($rates)) {
            return null;
        }

        $percentTotal = 0.0;
        $flatTotal = 0.0;

        foreach ($rates as $rate) {
            $type = strtolower((string) ($rate['type'] ?? ''));
            $value = max(0, (float) ($rate['value'] ?? 0));

            if ($value <= 0) {
                continue;
            }

            if ($type === 'percent') {
                $percentTotal += $value;
            } elseif ($type === 'flat') {
                $flatTotal += $value;
            }
        }

        $parts = [];

        if ($percentTotal > 0) {
            $parts[] = rtrim(rtrim(number_format($percentTotal, 2, '.', ''), '0'), '.') . '%';
        }

        if ($flatTotal > 0) {
            $parts[] = 'Flat ' . rtrim(rtrim(number_format($flatTotal, 2, '.', ''), '0'), '.');
        }

        return !empty($parts) ? implode(' + ', $parts) : null;
    }

    private function getUserCartItems()
    {
        $discountResolver = app(CheckoutDiscountResolver::class);

        return Cart::where('user_id', Auth::id())
                    ->where('cart_type', 'frontend')
                    ->with(['product.category', 'product.inventoryStocks', 'variant.inventoryStocks'])
                    ->get()
                    ->map(function ($item) use ($discountResolver) {
                        if (!$item->product) {
                            return null;
                        }

                        if ((int) ($item->product->status ?? 0) !== 1) {
                            return null;
                        }

                        $product = $item->product;
                        $imagePath = (string) ($product->thumb_image ?? '');
                        $imageUrl = (strpos($imagePath, 'http') === 0)
                            ? $imagePath
                            : ($imagePath !== '' && file_exists(public_path($imagePath))
                                ? asset($imagePath)
                                : asset('storage/' . $imagePath));

                        $variant = $item->variant;
                        $price = (float) $this->resolveCartItemUnitPrice($product, $variant);
                        $variantLabel = $this->resolveVariantLabel($variant);
                        $availableStock = $this->resolveAvailableStock($product, $variant);
                        $quantity = (int) ($item->quantity ?? 1);
                        $lineSubtotal = round($price * $quantity, 2);

                        $lineDiscount = $discountResolver->resolveForLine($product, $lineSubtotal, $quantity);
                        $lineDiscountAmount = round((float) ($lineDiscount['amount'] ?? 0), 2);
                        $discountPerUnit = $quantity > 0 ? ($lineDiscountAmount / $quantity) : 0.0;
                        $displayPrice = round(max(0, $price - $discountPerUnit), 2);
                        $lineTotalAfterDiscount = round(max(0, $lineSubtotal - $lineDiscountAmount), 2);

                        return [
                            'id' => $item->id,
                            'product_id' => $product->id,
                            'variant_id' => $variant?->id,
                            'name' => $product->name,
                            'price' => (float) $price,
                            'original_price' => (float) $price,
                            'display_price' => (float) $displayPrice,
                            'has_discount' => $lineDiscountAmount > 0,
                            'discount_source' => (string) ($lineDiscount['source'] ?? 'none'),
                            'discount_type' => (string) ($lineDiscount['type'] ?? ''),
                            'discount_value' => (float) ($lineDiscount['value'] ?? 0),
                            'discounts' => $lineDiscount['discounts'] ?? [],
                            'line_discount' => (float) $lineDiscountAmount,
                            'line_total' => (float) $lineSubtotal,
                            'line_total_after_discount' => (float) $lineTotalAfterDiscount,
                            'image' => $imageUrl,
                            'category' => $product->category->name ?? 'General',
                            'variant_label' => $variantLabel,
                            'quantity' => $quantity,
                            'minimum_order_qty' => max(1, (int) ($product->minimum_order_qty ?? 1)),
                            'available_stock' => (int) $availableStock,
                        ];
                    })
                    ->filter()
                    ->values();
    }

    private function resolveAvailableStock(Product $product, ?ProductVariant $variant): int
    {
        $outletId = $this->resolveOrderOutletId();
        $stocks = $variant ? $variant->inventoryStocks : $product->inventoryStocks;

        $stockRow = $this->findInventoryStockRowInCollection($stocks, $outletId)
            ?? $this->fetchInventoryStockRow((int) $product->id, $variant?->id, $outletId);

        return max(0, (int) ($stockRow?->quantity ?? 0));
    }

    private function buildRequestedLinesFromCart($cartRows): array
    {
        $requestedLines = [];
        foreach ($cartRows as $item) {
            $productId = (int) $item->product_id;
            $variantId = $item->variant_id ? (int) $item->variant_id : null;
            $key = $this->buildStockKey($productId, $variantId);
            if (!isset($requestedLines[$key])) {
                $requestedLines[$key] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'requested_qty' => 0,
                    'name' => (string) ($item->product?->name ?? ('Product #' . $productId)),
                ];
            }
            $requestedLines[$key]['requested_qty'] += max(1, (int) $item->quantity);
        }
        return $requestedLines;
    }

    private function lockInventoryForRequestedLines(array $requestedLines, int $outletId)
    {
        if (empty($requestedLines)) {
            return collect();
        }

        $query = InventoryStock::query()
            ->where('outlet_id', $outletId)
            ->where(function ($query) use ($requestedLines) {
                foreach ($requestedLines as $line) {
                    $query->orWhere(function ($or) use ($line) {
                        $or->where('product_id', (int) $line['product_id']);
                        if ($line['variant_id'] !== null) {
                            $or->where('variant_id', (int) $line['variant_id']);
                        } else {
                            $or->whereNull('variant_id');
                        }
                    });
                }
            })
            ->lockForUpdate();

        return $query->get()
            ->keyBy(fn ($row) => $this->buildStockKey((int) $row->product_id, $row->variant_id ? (int) $row->variant_id : null));
    }

    private function resolveOrderOutletId(): int
    {
        $user = Auth::user();
        $userOutletId = $user?->outlet_id ?? null;

        if (!empty($userOutletId)) {
            return (int) $userOutletId;
        }

        return (int) config('inventory.default_outlet_id', 1);
    }

    private function findInventoryStockRowInCollection($stocks, int $outletId): ?InventoryStock
    {
        if (!$stocks) {
            return null;
        }

        foreach ($stocks as $stock) {
            if ((int) ($stock->outlet_id ?? 0) === $outletId) {
                return $stock;
            }
        }

        return null;
    }

    private function fetchInventoryStockRow(int $productId, ?int $variantId, int $outletId, bool $lock = false): ?InventoryStock
    {
        $query = InventoryStock::query()
            ->where('product_id', $productId)
            ->where('outlet_id', $outletId);

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function buildStockKey(int $productId, ?int $variantId): string
    {
        return $productId . '|' . ($variantId ?? 0);
    }

    // private function generateUniqueOrderNoForUser($user): string
    // {
    //     $isOutletUser = $user && ($user->hasRole('Outlet User') || $user->hasRole('Outlet'));
    //     $prefix = $isOutletUser ? 'DS' : 'ORD';

    //     do {
    //         $orderNo = $prefix . '-' . strtoupper(Str::random(10));
    //     } while (Order::query()->where('order_no', $orderNo)->exists());

    //     return $orderNo;
    // }
    


    private function generateUniqueOrderNoForUser($user): string
    {
        return DocumentSequence::generateForOrder($user);
    }
}
