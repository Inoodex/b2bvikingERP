<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DeliveryOrder;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockLedger;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StockLedger::with(['product.category', 'variant', 'outlet', 'bin'])->select('stock_ledgers.*');

            // 1. Category Cascading Filters
            if ($request->filled('category_id')) {
                $data->whereHas('product', fn($q) => $q->where('category_id', $request->integer('category_id')));
            }

            if ($request->filled('sub_category_id')) {
                $data->whereHas('product', fn($q) => $q->where('sub_category_id', $request->integer('sub_category_id')));
            }

            if ($request->filled('child_category_id')) {
                $data->whereHas('product', fn($q) => $q->where('child_category_id', $request->integer('child_category_id')));
            }

            // 2. Product & Variant Filters
            if ($request->filled('product_id')) {
                $data->where('product_id', $request->integer('product_id'));
            }

            if ($request->filled('variant_id')) {
                $data->where('variant_id', $request->integer('variant_id'));
            }

            // 3. Movement Type Filter
            if ($request->filled('movement_type')) {
                $movementType = $request->string('movement_type')->toString();

                if ($movementType === 'in') {
                    $data->where('in_qty', '>', 0);
                }

                if ($movementType === 'out') {
                    $data->where('out_qty', '>', 0);
                }
            }

            // 4. Reference Type Filter
            if ($request->filled('reference_type')) {
                $data->where('reference_type', $request->string('reference_type')->toString());
            }

            // 5. Date Range Filters
            if ($request->filled('date_from')) {
                $data->whereDate('created_at', '>=', $request->date('date_from')->toDateString());
            }

            if ($request->filled('date_to')) {
                $data->whereDate('created_at', '<=', $request->date('date_to')->toDateString());
            }

            // 6. Outlet / Target Destination Filter
            if ($request->filled('outlet_id')) {
                $targetId = $request->integer('outlet_id');
                $issueIds = DB::table('issues')->where('outlet_id', $targetId)->pluck('id');
                $doIds = DeliveryOrder::whereHas('order', fn($q) => $q->where('user_id', $targetId))->pluck('id');

                $data->where(function($q) use ($targetId, $issueIds, $doIds) {
                    $q->where('outlet_id', $targetId)
                      ->orWhere(function($sub) use ($issueIds) {
                          $sub->where('reference_type', 'issue')->whereIn('reference_id', $issueIds);
                      })
                      ->orWhere(function($sub) use ($doIds) {
                          $sub->where('reference_type', 'DeliveryOrder')->whereIn('reference_id', $doIds);
                      });
                });
            }

            // In-memory per-page caches to guarantee zero N+1 overhead
            $purchaseCache = [];
            $issueCache = [];
            $orderCache = [];
            $userCache = [];
            $doCache = [];

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function($row) {
                    return Carbon::parse($row->created_at)->format('Y-m-d h:i A');
                })
                ->addColumn('product_info', function($row) {
                    $product = $row->product;
                    if (!$product) {
                        return '<span class="text-danger font-weight-bold">Deleted Product #' . $row->product_id . '</span>';
                    }

                    $imgUrl = $product->thumb_image 
                        ? (str_starts_with($product->thumb_image, 'http') ? $product->thumb_image : asset('storage/' . $product->thumb_image))
                        : asset('uploads/default.jpg');

                    $categoryName = $product->category?->name ?: 'Uncategorized';
                    $productEditUrl = route('admin.products.edit', $product->id);

                    return '<div class="d-flex align-items-center">
                        <div class="mr-2" style="width: 38px; height: 38px; flex-shrink: 0; background: #f8f9fa; border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #e9ecef;">
                            <img src="' . e($imgUrl) . '" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain;" onerror="this.onerror=null;this.parentElement.innerHTML=\'<i class=\\\'fas fa-box text-secondary\\\'></i>\';">
                        </div>
                        <div>
                            <div class="font-weight-bold"><a href="' . e($productEditUrl) . '" target="_blank" class="text-dark font-weight-600">' . e($product->name) . '</a></div>
                            <span class="badge badge-light border text-muted py-0" style="font-size: 11px;">' . e($categoryName) . '</span>
                        </div>
                    </div>';
                })
                ->addColumn('variant_name', function($row) {
                    return $row->variant ? '<span class="badge badge-light border text-dark font-weight-500">' . e($row->variant->name) . '</span>' : '<span class="text-muted">—</span>';
                })
                ->addColumn('type', function($row) {
                    if ($row->in_qty > 0) {
                        return '<span class="badge badge-success px-2 py-1 font-weight-bold"><i class="fas fa-arrow-down mr-1"></i> IN</span>';
                    } elseif ($row->out_qty > 0) {
                        return '<span class="badge badge-danger px-2 py-1 font-weight-bold"><i class="fas fa-arrow-up mr-1"></i> OUT</span>';
                    } else {
                        return '<span class="badge badge-info px-2 py-1 font-weight-bold"><i class="fas fa-sync-alt mr-1"></i> TRANSFER</span>';
                    }
                })
                ->addColumn('party_destination', function($row) use (&$purchaseCache, &$issueCache, &$orderCache, &$userCache, &$doCache) {
                    if ($row->in_qty > 0) {
                        if ($row->reference_type === 'purchase') {
                            if (!isset($purchaseCache[$row->reference_id])) {
                                $purchaseCache[$row->reference_id] = Purchase::with('vendor:id,shop_name')->find($row->reference_id);
                            }
                            $p = $purchaseCache[$row->reference_id];
                            $vendorName = $p?->vendor?->shop_name ?? 'Vendor';
                            return '<div>
                                <span class="badge badge-light border text-dark font-weight-bold"><i class="fas fa-truck text-success mr-1"></i> Supplier: ' . e($vendorName) . '</span>
                                <div class="small text-muted mt-1"><i class="fas fa-arrow-down text-success mr-1"></i> Source: Supplier</div>
                            </div>';
                        } elseif ($row->reference_type === 'opening') {
                            return '<div>
                                <span class="badge badge-light border text-info font-weight-bold"><i class="fas fa-boxes text-info mr-1"></i> Opening Stock</span>
                                <div class="small text-muted mt-1"><i class="fas fa-warehouse text-info mr-1"></i> Source: Initial Intake</div>
                            </div>';
                        } elseif ($row->reference_type === 'adjustment') {
                            return '<div>
                                <span class="badge badge-light border text-warning font-weight-bold"><i class="fas fa-sliders-h text-warning mr-1"></i> Stock Adjustment</span>
                                <div class="small text-muted mt-1"><i class="fas fa-wrench text-warning mr-1"></i> Source: Audit Correction</div>
                            </div>';
                        } elseif (str_contains($row->reference_type, 'bin_relocation')) {
                            return '<div>
                                <span class="badge badge-light border text-secondary font-weight-bold"><i class="fas fa-dolly text-primary mr-1"></i> Bin Transfer IN</span>
                                <div class="small text-muted mt-1">To Bin: ' . e($row->bin?->name ?? 'Default') . '</div>
                            </div>';
                        } else {
                            return '<span class="text-muted">' . e(ucfirst($row->reference_type)) . ' #' . e($row->reference_id) . '</span>';
                        }
                    } else {
                        // Outbound
                        $targetUser = null;
                        $orderNo = null;
                        $partyName = null;

                        if ($row->reference_type === 'issue') {
                            if (!isset($issueCache[$row->reference_id])) {
                                $issueCache[$row->reference_id] = DB::table('issues')->find($row->reference_id);
                            }
                            $iss = $issueCache[$row->reference_id];
                            $targetUserId = $iss?->outlet_id;
                            if ($targetUserId && !isset($userCache[$targetUserId])) {
                                $userCache[$targetUserId] = User::with('roles')->find($targetUserId);
                            }
                            $targetUser = $targetUserId ? ($userCache[$targetUserId] ?? null) : null;
                            if ($targetUser) {
                                if (!empty($targetUser->outlet_name) && !empty($targetUser->name) && $targetUser->outlet_name !== $targetUser->name) {
                                    $partyName = $targetUser->outlet_name . ' (' . $targetUser->name . ')';
                                } else {
                                    $partyName = $targetUser->outlet_name ?: $targetUser->name;
                                }
                            } else {
                                $partyName = $row->outlet ? ($row->outlet->name . ($row->outlet->outlet_name ? ' (' . $row->outlet->outlet_name . ')' : '')) : ('Outlet #' . ($targetUserId ?? $row->outlet_id));
                            }

                            if ($iss) {
                                if ($iss->order_id) {
                                    if (!isset($orderCache[$iss->order_id])) {
                                        $orderCache[$iss->order_id] = Order::find($iss->order_id);
                                    }
                                    $orderNo = $orderCache[$iss->order_id]?->order_no;
                                }
                                if (!$orderNo && !empty($iss->note) && preg_match('/Source Order:\s*([^\s,]+)/', $iss->note, $m)) {
                                    $orderNo = $m[1];
                                }
                            }
                        } elseif ($row->reference_type === 'DeliveryOrder') {
                            if (!isset($doCache[$row->reference_id])) {
                                $doCache[$row->reference_id] = DeliveryOrder::with(['order.user.roles'])->find($row->reference_id);
                            }
                            $do = $doCache[$row->reference_id];
                            $targetUser = $do?->order?->user;
                            $orderNo = $do?->order?->order_no;
                            if ($targetUser) {
                                if (!empty($targetUser->outlet_name) && !empty($targetUser->name) && $targetUser->outlet_name !== $targetUser->name) {
                                    $partyName = $targetUser->outlet_name . ' (' . $targetUser->name . ')';
                                } else {
                                    $partyName = $targetUser->outlet_name ?: $targetUser->name;
                                }
                            } else {
                                $partyName = 'Customer';
                            }
                        } elseif ($row->reference_type === 'adjustment') {
                            return '<div>
                                <span class="badge badge-light border text-warning font-weight-bold"><i class="fas fa-sliders-h text-warning mr-1"></i> Stock Reduction</span>
                                <div class="small text-muted mt-1"><i class="fas fa-wrench text-warning mr-1"></i> Reason: Audit / Damage</div>
                            </div>';
                        } elseif (str_contains($row->reference_type, 'bin_relocation')) {
                            return '<div>
                                <span class="badge badge-light border text-secondary font-weight-bold"><i class="fas fa-dolly text-primary mr-1"></i> Bin Transfer OUT</span>
                                <div class="small text-muted mt-1">From Bin: ' . e($row->bin?->name ?? 'Default') . '</div>
                            </div>';
                        } else {
                            $outletName = $row->outlet ? ($row->outlet->name . ($row->outlet->outlet_name ? ' (' . $row->outlet->outlet_name . ')' : '')) : 'Main Warehouse';
                            return '<span class="badge badge-secondary">' . e($outletName) . '</span>';
                        }

                        // Dynamic Destination Type Resolution
                        $isOutlet = ($targetUser && $targetUser->hasRole('Outlet User')) 
                                    || !empty($targetUser?->outlet_name) 
                                    || !empty($row->outlet_id) 
                                    || str_starts_with($orderNo ?? '', 'DS-');

                        $isWebOrder = str_starts_with($orderNo ?? '', 'ORD-');

                        if ($isOutlet) {
                            $destText = 'Destination: Outlet';
                            $badgeIcon = 'fas fa-store text-danger';
                            $locIcon = 'fas fa-map-marker-alt text-info';
                        } elseif ($isWebOrder) {
                            $destText = 'Destination: Web Order';
                            $badgeIcon = 'fas fa-globe text-primary';
                            $locIcon = 'fas fa-map-marker-alt text-primary';
                        } else {
                            $destText = 'Destination: Customer';
                            $badgeIcon = 'fas fa-user-check text-info';
                            $locIcon = 'fas fa-map-marker-alt text-info';
                        }

                        return '<div>
                            <span class="badge badge-light border text-primary font-weight-bold" style="font-size: 12.5px;"><i class="' . $badgeIcon . ' mr-1"></i> ' . e($partyName) . '</span>
                            <div class="small text-muted mt-1"><i class="' . $locIcon . ' mr-1"></i> ' . e($destText) . '</div>
                        </div>';
                    }
                })
                ->addColumn('reference', function($row) use (&$purchaseCache, &$issueCache, &$orderCache, &$doCache) {
                    if ($row->reference_type === 'issue') {
                        if (!isset($issueCache[$row->reference_id])) {
                            $issueCache[$row->reference_id] = DB::table('issues')->find($row->reference_id);
                        }
                        $iss = $issueCache[$row->reference_id];
                        $orderNo = null;
                        $orderId = null;
                        if ($iss) {
                            if ($iss->order_id) {
                                $orderId = $iss->order_id;
                                if (!isset($orderCache[$orderId])) {
                                    $orderCache[$orderId] = Order::find($orderId);
                                }
                                $orderNo = $orderCache[$orderId]?->order_no;
                            }
                            if (!$orderNo && !empty($iss->note) && preg_match('/Source Order:\s*([^\s,]+)/', $iss->note, $m)) {
                                $orderNo = $m[1];
                            }
                        }

                        if ($orderNo) {
                            $orderUrl = $orderId ? route('admin.orders.show', $orderId) : null;
                            return $orderUrl 
                                ? '<a href="' . e($orderUrl) . '" target="_blank" class="badge badge-primary px-2 py-1 font-weight-bold"><i class="fas fa-shopping-cart mr-1"></i> Order #' . e($orderNo) . '</a>'
                                : '<span class="badge badge-primary px-2 py-1 font-weight-bold"><i class="fas fa-shopping-cart mr-1"></i> Order #' . e($orderNo) . '</span>';
                        } elseif ($iss && !empty($iss->note) && preg_match('/Product Request:\s*([^\s,]+)/', $iss->note, $m)) {
                            return '<span class="badge badge-info px-2 py-1 font-weight-bold"><i class="fas fa-clipboard-list mr-1"></i> Request #' . e($m[1]) . '</span>';
                        } else {
                            return '<span class="badge badge-light border text-dark font-weight-bold"><i class="fas fa-file-invoice mr-1"></i> Ref #' . e($row->reference_id) . '</span>';
                        }
                    } elseif ($row->reference_type === 'DeliveryOrder') {
                        if (!isset($doCache[$row->reference_id])) {
                            $doCache[$row->reference_id] = DeliveryOrder::with('order')->find($row->reference_id);
                        }
                        $do = $doCache[$row->reference_id];
                        $doNo = $do?->delivery_no ?? ('#' . $row->reference_id);
                        $parentOrderNo = $do?->order?->order_no;
                        $parentOrderId = $do?->order?->id;
                        $doUrl = route('admin.delivery-orders.show', $row->reference_id);
                        $orderUrl = $parentOrderId ? route('admin.orders.show', $parentOrderId) : null;

                        return '<div>
                            <a href="' . e($doUrl) . '" target="_blank" class="badge badge-primary px-2 py-1 font-weight-bold"><i class="fas fa-truck-loading mr-1"></i> DO #' . e($doNo) . '</a>
                            ' . ($parentOrderNo ? '<div class="small mt-1"><a href="' . e($orderUrl ?? '#') . '" target="_blank" class="text-muted"><i class="fas fa-shopping-cart mr-1"></i> Order #' . e($parentOrderNo) . '</a></div>' : '') . '
                        </div>';
                    } elseif ($row->reference_type === 'purchase') {
                        if (!isset($purchaseCache[$row->reference_id])) {
                            $purchaseCache[$row->reference_id] = Purchase::find($row->reference_id);
                        }
                        $p = $purchaseCache[$row->reference_id];
                        $refNo = $p?->po_no ?? $p?->invoice_no ?? ('#' . $row->reference_id);
                        $pUrl = route('admin.purchases.show', $row->reference_id);
                        return '<a href="' . e($pUrl) . '" target="_blank" class="badge badge-dark px-2 py-1 font-weight-bold"><i class="fas fa-file-invoice mr-1"></i> PO #' . e($refNo) . '</a>';
                    } elseif ($row->reference_type === 'adjustment') {
                        return '<span class="badge badge-warning text-dark font-weight-bold"><i class="fas fa-sliders-h mr-1"></i> ADJ #' . e($row->reference_id) . '</span>';
                    } elseif ($row->reference_type === 'opening') {
                        return '<span class="badge badge-info text-white font-weight-bold"><i class="fas fa-boxes mr-1"></i> Opening Stock</span>';
                    } elseif (str_contains($row->reference_type, 'bin_relocation')) {
                        return '<span class="badge badge-secondary font-weight-bold"><i class="fas fa-dolly mr-1"></i> Bin Transfer</span>';
                    } else {
                        return '<span class="badge badge-light border text-dark font-weight-600">' . e(ucfirst($row->reference_type)) . ' #' . e($row->reference_id) . '</span>';
                    }
                })
                ->addColumn('bin_location', function($row) {
                    if ($row->bin) {
                        return '<span class="badge badge-light border text-dark font-weight-bold"><i class="fas fa-boxes text-primary mr-1"></i>' . e($row->bin->name) . ' (' . e($row->bin->barcode) . ')</span>';
                    }
                    return '<span class="badge badge-light border text-muted">Unassigned</span>';
                })
                ->addColumn('in_qty', function($row) {
                    return $row->in_qty > 0 
                        ? '<span class="text-success font-weight-bold" style="font-size: 13.5px;">+' . number_format($row->in_qty) . '</span>'
                        : '<span class="text-muted">—</span>';
                })
                ->addColumn('out_qty', function($row) {
                    return $row->out_qty > 0 
                        ? '<span class="text-danger font-weight-bold" style="font-size: 13.5px;">-' . number_format($row->out_qty) . '</span>'
                        : '<span class="text-muted">—</span>';
                })
                ->addColumn('balance_qty', function($row) {
                    return '<span class="font-weight-bold text-dark" style="font-size: 13.5px;">' . number_format($row->balance_qty) . '</span>';
                })
                ->filterColumn('product_info', function($query, $keyword) {
                    $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
                })
                ->filterColumn('variant_name', function($query, $keyword) {
                    $query->whereHas('variant', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
                })
                ->filterColumn('reference', function($query, $keyword) {
                    if (empty(trim((string)$keyword))) {
                        return;
                    }
                    $matchedIssueIds = DB::table('issues')
                        ->where('issue_no', 'like', "%{$keyword}%")
                        ->orWhere('note', 'like', "%{$keyword}%")
                        ->pluck('id');
                    $matchedDoIds = DeliveryOrder::where('delivery_no', 'like', "%{$keyword}%")->pluck('id');
                    $matchedOrderIds = Order::where('order_no', 'like', "%{$keyword}%")->pluck('id');
                    if ($matchedOrderIds->isNotEmpty()) {
                        $extraIssueIds = DB::table('issues')->whereIn('order_id', $matchedOrderIds)->pluck('id');
                        $matchedIssueIds = $matchedIssueIds->merge($extraIssueIds)->unique();
                    }

                    $query->where(function($q) use ($keyword, $matchedIssueIds, $matchedDoIds) {
                        $q->where('reference_id', 'like', "%{$keyword}%")
                          ->orWhere('reference_type', 'like', "%{$keyword}%");
                        if ($matchedIssueIds->isNotEmpty()) {
                            $q->orWhere(function($sub) use ($matchedIssueIds) {
                                $sub->where('reference_type', 'issue')->whereIn('reference_id', $matchedIssueIds);
                            });
                        }
                        if ($matchedDoIds->isNotEmpty()) {
                            $q->orWhere(function($sub) use ($matchedDoIds) {
                                $sub->where('reference_type', 'DeliveryOrder')->whereIn('reference_id', $matchedDoIds);
                            });
                        }
                    });
                })
                ->rawColumns(['product_info', 'variant_name', 'type', 'party_destination', 'reference', 'bin_location', 'in_qty', 'out_qty', 'balance_qty'])
                ->make(true);
        }

        $categories = Category::where('status', 1)->orderBy('name')->get(['id', 'name']);

        $products = Product::query()
            ->select('products.id', 'products.name', 'products.category_id', 'products.sub_category_id', 'products.child_category_id')
            ->where('status', 1)
            ->with(['variants' => function ($query) {
                $query->select('id', 'product_id', 'name', 'color', 'size');
            }])
            ->orderBy('products.name')
            ->get();

        $ledgerProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'sub_category_id' => $product->sub_category_id,
                'child_category_id' => $product->child_category_id,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'label' => $variant->name ?: trim(collect([$variant->color, $variant->size])->filter()->implode(' ')) ?: 'Variant #' . $variant->id,
                    ];
                })->values()->all(),
            ];
        })->keyBy('id')->toArray();

        $referenceTypes = StockLedger::query()
            ->whereNotNull('reference_type')
            ->distinct()
            ->orderBy('reference_type')
            ->pluck('reference_type');

        $users = User::role(['Outlet User', 'User'])->orderBy('name')->get(['id', 'name', 'outlet_name']);

        return view('backend.stock_ledger.index', compact('categories', 'products', 'ledgerProducts', 'referenceTypes', 'users'));
    }
}
