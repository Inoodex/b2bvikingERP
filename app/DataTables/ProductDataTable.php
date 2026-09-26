<?php

namespace App\DataTables;

use App\Models\Product;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                if (!$user->can('Manage Products')) {
                    return '';
                }
                $history = '<button type="button" class="btn btn-info btn-sm view-stock-movement mr-1" data-id="' . $query->id . '" title="Stock Movement & History"><i class="fas fa-history"></i></button>';
                $edit = '<a href="' . route('admin.products.edit', $query->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>';
                $delete = '<a href="' . route('admin.products.destroy', $query->id) . '" class="btn btn-danger btn-sm delete-item ml-1"><i class="fas fa-trash"></i></a>';
                return $history . $edit . $delete;
            })
            ->addColumn('thumb_image', function ($query) {
                return $query->thumb_image ? '<img src="' . asset('storage/' . $query->thumb_image) . '" width="60px" class="img-thumbnail rounded shadow-sm">' : '';
            })
            ->addColumn('status', function ($query) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                if (!$user->can('Manage Products')) {
                    return $query->status ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
                }
                $checked = $query->status ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $query->id . '" class="custom-switch-input change-status" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>';
            })
            ->addColumn('category', function ($query) {
                return $query->category->name ?? '';
            })
            ->addColumn('price', function ($query) {
                return formatConverted($query->price);
            })
            ->addColumn('purchase_price', function ($query) {
                return formatConverted($query->purchase_price);
            })
            ->addColumn('outlet_price', function ($query) {
                return formatConverted($query->outlet_price);
            })
            ->editColumn('qty', function ($query) {
                $stock = $query->inventory_stock;
                $badgeClass = $stock > 0 ? 'badge-info' : 'badge-danger';
                return '<span class="badge ' . $badgeClass . '">' . (float)$stock . '</span>';
            })
            ->addColumn('po_status', function ($query) {
                $activeDetails = $query->activePurchaseDetails;
                if (!$activeDetails || $activeDetails->isEmpty()) {
                    return '<span class="badge badge-light text-muted border" style="font-size: 11px;">No Active Order</span>';
                }

                $latestDetail = $activeDetails->sortByDesc('created_at')->first();
                $purchase = $latestDetail->purchase;
                if (!$purchase) {
                    return '<span class="badge badge-light text-muted border" style="font-size: 11px;">No Active Order</span>';
                }

                $milestone = $purchase->milestone_status ?? 'draft';
                $vendorName = $purchase->vendor?->shop_name ?? $purchase->vendor?->name ?? 'Supplier';
                $poNo = $purchase->po_no ?? ('PO-' . $purchase->id);
                $orderedQty = number_format($latestDetail->qty ?? 0);

                $shipment = $purchase->shipments?->sortByDesc('id')->first();
                $eta = $shipment?->eta ? date('d M', strtotime($shipment->eta)) : null;

                $badgeClass = match ($milestone) {
                    'shipped'                  => 'badge-primary',
                    'goods_partial'            => 'badge-info',
                    'approved', 'po_sent'      => 'badge-warning text-dark',
                    'pi_attached', 'lc_opened' => 'badge-secondary',
                    default                    => 'badge-light border text-dark',
                };

                $icon = match ($milestone) {
                    'shipped'                  => 'fa-shipping-fast',
                    'goods_partial'            => 'fa-boxes',
                    'approved'                 => 'fa-check-circle',
                    'po_sent'                  => 'fa-paper-plane',
                    'pi_attached'              => 'fa-file-invoice',
                    'lc_opened'                => 'fa-university',
                    default                    => 'fa-clock',
                };

                $statusLabel = ucfirst(str_replace('_', ' ', $milestone));
                $badgeText = $statusLabel . ($eta ? " (Exp: {$eta})" : '');

                $popoverContent = htmlspecialchars("<strong>PO:</strong> {$poNo}<br><strong>Supplier:</strong> {$vendorName}<br><strong>Qty on Order:</strong> {$orderedQty} pcs" . ($eta ? "<br><strong>Expected ETA:</strong> {$eta}" : ''), ENT_QUOTES, 'UTF-8');

                return '<span class="badge ' . $badgeClass . ' px-2 py-1 po-popover-trigger" style="font-size: 11px; cursor: pointer;" data-toggle="popover" data-trigger="hover focus" data-html="true" data-title="Active Purchase Order" data-content="' . $popoverContent . '">
                            <i class="fas ' . $icon . ' mr-1"></i>' . e($badgeText) . '
                        </span>';
            })
            ->rawColumns(['action', 'status', 'thumb_image', 'price', 'purchase_price', 'outlet_price', 'qty', 'po_status'])
            ->setRowId('id');
    }

    public function query(Product $model)
    {
        $query = $model->newQuery()->with([
            'category',
            'unit',
            'inventoryStocks',
            'activePurchaseDetails.purchase.vendor',
            'activePurchaseDetails.purchase.shipments'
        ]);

        $request = request();
        if ($request->filled('po_status_filter')) {
            $filter = $request->po_status_filter;
            if ($filter === 'in_stock') {
                $query->whereHas('inventoryStocks', function ($q) {
                    $q->havingRaw('SUM(quantity) > 0');
                });
            } elseif ($filter === 'on_order') {
                $query->whereHas('activePurchaseDetails');
            } elseif ($filter === 'out_of_stock_not_ordered') {
                $query->whereDoesntHave('activePurchaseDetails')
                    ->where(function ($q) {
                        $q->whereDoesntHave('inventoryStocks')
                            ->orWhereHas('inventoryStocks', function ($sq) {
                                $sq->havingRaw('SUM(quantity) <= 0');
                            });
                    });
            }
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.po_status_filter = $("#po_status_filter").val();')
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ]);
    }

    public function getColumns(): array
    {
        $settings = getSettings();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $columns = [
            Column::make('thumb_image')->title('Image'),
            Column::make('name')->title('Product Name')->addClass('text-center'),
            Column::make('category')->title('Category Name')->addClass('text-center'),
        ];

        if ($user->can('Manage Purchases')) {
            $columns[] = Column::make('purchase_price')->title('Purchase Price')->addClass('text-center');
        }

        if ($user->hasRole('Outlet User') || $user->hasRole('User')) {
            $columns[] = Column::make('outlet_price')->title('Buying Price');
            $columns[] = Column::make('price')->title('Selling Price');
        } else {
            $columns[] = Column::make('price')->title('Selling Price');
            if ($user->can('Manage Products')) {
                $columns[] = Column::make('outlet_price')->title('Outlet/Shop Price');
            }
        }

        $columns[] = Column::make('qty')->title('Qty');
        $columns[] = Column::make('po_status')->title('PO / Production Status')->addClass('text-center');

        if ($user->can('Manage Products')) {
            $columns[] = Column::make('status');
            $columns[] = Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center');
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Product_' . date('YmdHis');
    }
}
