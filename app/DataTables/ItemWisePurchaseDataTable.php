<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ItemWisePurchaseDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $settings = DB::table('general_settings')->first();
        $icon = $settings->currency_icon ?? 'Kr.';

        return (new EloquentDataTable($query))
            ->editColumn('name', fn($row) => '<strong>' . e($row->name) . '</strong><br><small class="text-muted">SKU / Code: ' . e($row->product_number ?? $row->sku ?? ('PROD-' . $row->id)) . '</small>')
            ->addColumn('total_qty', function($row) {
                $request = request();
                $qty = DB::table('purchase_details')
                    ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
                    ->where('purchase_details.product_id', $row->id)
                    ->where('purchases.status', 1)
                    ->when($request->filled('start_date'), fn($q) => $q->whereDate('purchases.date', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn($q) => $q->whereDate('purchases.date', '<=', $request->end_date))
                    ->when($request->filled('vendor_id'), fn($q) => $q->where('purchases.vendor_id', $request->vendor_id))
                    ->sum('purchase_details.qty');
                return number_format($qty, 2);
            })
            ->addColumn('avg_unit_price', function($row) use ($icon) {
                $request = request();
                $avg = DB::table('purchase_details')
                    ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
                    ->where('purchase_details.product_id', $row->id)
                    ->where('purchases.status', 1)
                    ->when($request->filled('start_date'), fn($q) => $q->whereDate('purchases.date', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn($q) => $q->whereDate('purchases.date', '<=', $request->end_date))
                    ->when($request->filled('vendor_id'), fn($q) => $q->where('purchases.vendor_id', $request->vendor_id))
                    ->avg('purchase_details.unit_cost');
                return $icon . number_format($avg ?? 0, 2);
            })
            ->addColumn('avg_landed_cost', function($row) use ($icon) {
                $request = request();
                $avg = DB::table('purchase_details')
                    ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
                    ->where('purchase_details.product_id', $row->id)
                    ->where('purchases.status', 1)
                    ->when($request->filled('start_date'), fn($q) => $q->whereDate('purchases.date', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn($q) => $q->whereDate('purchases.date', '<=', $request->end_date))
                    ->when($request->filled('vendor_id'), fn($q) => $q->where('purchases.vendor_id', $request->vendor_id))
                    ->avg('purchase_details.landed_cost');
                return $icon . number_format($avg ?? 0, 2);
            })
            ->addColumn('total_value', function($row) use ($icon) {
                $request = request();
                $total = DB::table('purchase_details')
                    ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
                    ->where('purchase_details.product_id', $row->id)
                    ->where('purchases.status', 1)
                    ->when($request->filled('start_date'), fn($q) => $q->whereDate('purchases.date', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn($q) => $q->whereDate('purchases.date', '<=', $request->end_date))
                    ->when($request->filled('vendor_id'), fn($q) => $q->where('purchases.vendor_id', $request->vendor_id))
                    ->sum('purchase_details.total');
                return '<strong class="text-primary">' . $icon . number_format($total, 2) . '</strong>';
            })
            ->rawColumns(['name', 'total_value'])
            ->setRowId('id');
    }

    public function query(Product $model): QueryBuilder
    {
        $request = request();
        $query = $model->newQuery()->where('status', 1);

        if ($request->filled('product_id')) {
            $query->where('id', $request->product_id);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('item-wise-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
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
            Column::make('name')->title('Product / Item Details'),
            Column::computed('total_qty')->title('Total Quantity Purchased')->addClass('text-center')->orderable(false)->searchable(false),
            Column::computed('avg_unit_price')->title('Average Unit Cost')->addClass('text-right')->orderable(false)->searchable(false),
            Column::computed('avg_landed_cost')->title('Average Landed Cost')->addClass('text-right')->orderable(false)->searchable(false),
            Column::computed('total_value')->title('Total Purchase Value')->addClass('text-right')->orderable(false)->searchable(false),
        ];
    }
}
