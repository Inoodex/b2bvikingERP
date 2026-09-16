<?php

namespace App\DataTables;

use App\Models\PurchaseDetail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductPurchaseHistoryDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('purchase_date', function ($row) {
                return $row->purchase?->date ? Carbon::parse($row->purchase->date)->format('d M Y') : 'N/A';
            })
            ->addColumn('po_no', function ($row) {
                if (!$row->purchase_id) {
                    return '<span class="text-muted">N/A</span>';
                }
                $poText = $row->purchase?->po_no ?: ($row->purchase?->invoice_no ?: ('PO-' . $row->purchase_id));
                $html = '<a href="' . route('admin.purchase-orders.show', $row->purchase_id) . '" class="font-weight-bold text-primary" target="_blank" title="View PO Details">' . e($poText) . '</a>';
                if ($row->purchase?->po_no && $row->purchase?->invoice_no) {
                    $html .= '<br><small class="text-muted"><i class="fas fa-receipt mr-1"></i>' . e($row->purchase->invoice_no) . '</small>';
                }
                return $html;
            })
            ->addColumn('product_info', function ($row) {
                $pName = e($row->product?->name ?? 'N/A');
                $sku = e($row->product?->product_number ?? $row->product?->sku ?? ('PROD-' . $row->product_id));
                $html = '<strong>' . $pName . '</strong><br><small class="text-muted">SKU: ' . $sku . '</small>';
                if (!empty($row->variant_info) && is_array($row->variant_info)) {
                    $spec = implode(' / ', array_filter($row->variant_info));
                    if ($spec) {
                        $html .= '<br><span class="badge badge-info p-1" style="font-size: 10px;">' . e($spec) . '</span>';
                    }
                }
                return $html;
            })
            ->addColumn('vendor_name', function ($row) {
                $vendor = $row->purchase?->vendor;
                if ($vendor) {
                    return '<a href="' . route('admin.vendor-ledger.show', $vendor->id) . '" class="font-weight-600 text-dark" target="_blank" title="View Vendor Ledger">' . e($vendor->shop_name ?? $vendor->name) . '</a>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('user_name', function ($row) {
                return e($row->purchase?->user?->name ?? 'System');
            })
            ->editColumn('qty', function ($row) {
                return '<span class="badge badge-light border font-weight-bold">' . number_format($row->qty) . '</span>';
            })
            ->editColumn('unit_cost', function ($row) {
                return formatConverted($row->unit_cost);
            })
            ->addColumn('vendor_unit_cost', function ($row) {
                $vendor = $row->purchase?->vendor;
                if ($vendor) {
                    return formatWithVendor($row->unit_cost, $vendor->currency_icon, $vendor->currency_rate);
                }
                return formatConverted($row->unit_cost);
            })
            ->editColumn('total', function ($row) {
                return '<strong class="text-dark">' . formatConverted($row->total) . '</strong>';
            })
            ->addColumn('vendor_total', function ($row) {
                $vendor = $row->purchase?->vendor;
                if ($vendor) {
                    return '<strong class="text-primary">' . formatWithVendor($row->total, $vendor->currency_icon, $vendor->currency_rate) . '</strong>';
                }
                return '<strong class="text-primary">' . formatConverted($row->total) . '</strong>';
            })
            ->addColumn('action', function ($row) {
                if (!$row->purchase_id) {
                    return '—';
                }
                return '<a href="' . route('admin.purchase-orders.show', $row->purchase_id) . '" class="btn btn-sm btn-info shadow-sm" target="_blank" title="View PO Details"><i class="fas fa-eye"></i> View PO</a>';
            })
            ->rawColumns(['po_no', 'product_info', 'vendor_name', 'qty', 'unit_cost', 'vendor_unit_cost', 'total', 'vendor_total', 'action'])
            ->setRowId('id');
    }

    public function query(PurchaseDetail $model): QueryBuilder
    {
        $request = request();
        $query = $model->newQuery()->with(['product', 'purchase.vendor', 'purchase.user', 'variant']);

        if ($request->filled('product_id')) {
            $query->where('purchase_details.product_id', $request->product_id);
        }

        if ($request->filled('vendor_id')) {
            $query->whereHas('purchase', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor_id);
            });
        }

        if ($request->filled('start_date')) {
            $query->whereHas('purchase', function ($q) use ($request) {
                $q->whereDate('date', '>=', $request->start_date);
            });
        }

        if ($request->filled('end_date')) {
            $query->whereHas('purchase', function ($q) use ($request) {
                $q->whereDate('date', '<=', $request->end_date);
            });
        }

        return $query->latest('purchase_details.id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-purchase-history-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                    d.product_id = $("select[name=\"product_id\"]").val();
                    d.vendor_id = $("select[name=\"vendor_id\"]").val();
                    d.start_date = $("input[name=\"start_date\"]").val();
                    d.end_date = $("input[name=\"end_date\"]").val();
                }'
            ])
            ->stateSave(false)
            ->pageLength(10)
            ->responsive(true)
            ->autoWidth(false)
            ->parameters([
                'lengthMenu' => [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ]
            ])
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('purchase_date')->title('Date')->orderable(false)->searchable(false),
            Column::computed('po_no')->title('PO / Invoice No')->orderable(false)->searchable(false),
            Column::computed('product_info')->title('Product Details')->orderable(false)->searchable(false),
            Column::computed('vendor_name')->title('Supplier / Vendor')->orderable(false)->searchable(false),
            Column::computed('user_name')->title('Purchaser')->orderable(false)->searchable(false),
            Column::make('qty')->title('Qty')->addClass('text-center'),
            Column::make('unit_cost')->title('Base Unit Cost')->addClass('text-right'),
            Column::computed('vendor_unit_cost')->title('Vendor Unit Cost')->addClass('text-right')->orderable(false)->searchable(false),
            Column::make('total')->title('Base Total')->addClass('text-right'),
            Column::computed('vendor_total')->title('Vendor Total')->addClass('text-right')->orderable(false)->searchable(false),
            Column::computed('action')->title('Action')->addClass('text-center')->orderable(false)->searchable(false),
        ];
    }
}
