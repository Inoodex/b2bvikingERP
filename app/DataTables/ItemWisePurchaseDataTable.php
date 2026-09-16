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
            ->editColumn('total_qty', fn($row) => '<span class="badge badge-light border font-weight-bold">' . number_format($row->total_qty ?? 0) . '</span>')
            ->editColumn('avg_unit_price', fn($row) => $icon . number_format($row->avg_unit_price ?? 0, 2))
            ->editColumn('avg_landed_cost', fn($row) => $icon . number_format($row->avg_landed_cost ?? 0, 2))
            ->editColumn('total_value', fn($row) => '<strong class="text-primary">' . $icon . number_format($row->total_value ?? 0, 2) . '</strong>')
            ->rawColumns(['name', 'total_qty', 'total_value'])
            ->setRowId('id');
    }

    public function query(Product $model): QueryBuilder
    {
        $request = request();

        // Single-pass database aggregation subquery (eliminates 100+ joined queries)
        $subQuery = DB::table('purchase_details')
            ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
            ->select(
                'purchase_details.product_id',
                DB::raw('COALESCE(SUM(purchase_details.qty), 0) as total_qty'),
                DB::raw('COALESCE(AVG(purchase_details.unit_cost), 0) as avg_unit_price'),
                DB::raw('COALESCE(AVG(purchase_details.landed_cost), 0) as avg_landed_cost'),
                DB::raw('COALESCE(SUM(purchase_details.total), 0) as total_value')
            )
            ->where('purchases.status', 1)
            ->when($request->filled('start_date'), fn($q) => $q->whereDate('purchases.date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn($q) => $q->whereDate('purchases.date', '<=', $request->end_date))
            ->when($request->filled('vendor_id'), fn($q) => $q->where('purchases.vendor_id', $request->vendor_id))
            ->groupBy('purchase_details.product_id');

        $query = $model->newQuery()
            ->select('products.*', 'pd_agg.total_qty', 'pd_agg.avg_unit_price', 'pd_agg.avg_landed_cost', 'pd_agg.total_value')
            ->joinSub($subQuery, 'pd_agg', function ($join) {
                $join->on('products.id', '=', 'pd_agg.product_id');
            })
            ->where('products.status', 1);

        if ($request->filled('product_id')) {
            $query->where('products.id', $request->product_id);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('item-wise-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) { d.start_date = $("input[name=\"start_date\"]").val(); d.end_date = $("input[name=\"end_date\"]").val(); d.vendor_id = $("select[name=\"vendor_id\"]").val(); d.product_id = $("select[name=\"product_id\"]").val(); }'
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
            Column::make('name')->title('Product / Item Details'),
            Column::make('total_qty')->title('Total Quantity Purchased')->addClass('text-center'),
            Column::make('avg_unit_price')->title('Average Unit Cost')->addClass('text-right'),
            Column::make('avg_landed_cost')->title('Average Landed Cost')->addClass('text-right'),
            Column::make('total_value')->title('Total Purchase Value')->addClass('text-right'),
        ];
    }
}
