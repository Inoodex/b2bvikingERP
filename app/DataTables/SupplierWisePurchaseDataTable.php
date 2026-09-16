<?php

namespace App\DataTables;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SupplierWisePurchaseDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $settings = DB::table('general_settings')->first();
        $icon = $settings->currency_icon ?? 'Kr.';

        return (new EloquentDataTable($query))
            ->editColumn('shop_name', fn($row) => '<a href="' . route('admin.vendor-ledger.show', $row->id) . '" class="font-weight-bold text-dark" target="_blank" title="View Vendor Ledger">' . e($row->shop_name ?? $row->name) . '</a>')
            ->addColumn('vendor_code', fn($row) => '<code>' . e($row->code ?? ('V-'.str_pad($row->id, 4, '0', STR_PAD_LEFT))) . '</code>')
            ->editColumn('po_count', fn($row) => '<span class="badge badge-light border font-weight-bold">' . number_format($row->po_count ?? 0) . '</span>')
            ->editColumn('total_base_amount', fn($row) => '<strong class="text-primary">' . $icon . number_format($row->total_base_amount ?? 0, 2) . '</strong>')
            ->editColumn('total_paid', fn($row) => '<span class="text-success font-weight-600">' . $icon . number_format($row->total_paid ?? 0, 2) . '</span>')
            ->editColumn('total_due', fn($row) => '<strong class="text-danger">' . $icon . number_format($row->total_due ?? 0, 2) . '</strong>')
            ->rawColumns(['shop_name', 'vendor_code', 'po_count', 'total_base_amount', 'total_paid', 'total_due'])
            ->setRowId('id');
    }

    public function query(Vendor $model): QueryBuilder
    {
        $request = request();

        // Single-pass database aggregation subquery (eliminates 200+ N+1 queries)
        $subQuery = DB::table('purchases')
            ->select(
                'vendor_id',
                DB::raw('COUNT(id) as po_count'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_base_amount'),
                DB::raw('COALESCE(SUM(paid_amount), 0) as total_paid'),
                DB::raw('COALESCE(SUM(due_amount), 0) as total_due')
            )
            ->where('status', 1)
            ->when($request->filled('start_date'), fn($q) => $q->whereDate('date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn($q) => $q->whereDate('date', '<=', $request->end_date))
            ->groupBy('vendor_id');

        $query = $model->newQuery()
            ->select('vendors.*', 'p_agg.po_count', 'p_agg.total_base_amount', 'p_agg.total_paid', 'p_agg.total_due')
            ->joinSub($subQuery, 'p_agg', function ($join) {
                $join->on('vendors.id', '=', 'p_agg.vendor_id');
            })
            ->where('vendors.status', 1);

        if ($request->filled('vendor_id')) {
            $query->where('vendors.id', $request->vendor_id);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('supplier-wise-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) { d.start_date = $("input[name=\"start_date\"]").val(); d.end_date = $("input[name=\"end_date\"]").val(); d.vendor_id = $("select[name=\"vendor_id\"]").val(); }'
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
            Column::make('shop_name')->title('Supplier Name'),
            Column::computed('vendor_code')->title('Supplier Code')->orderable(false)->searchable(false),
            Column::make('po_count')->title('Total POs Issued')->addClass('text-center'),
            Column::make('total_base_amount')->title('Total Purchase Value')->addClass('text-right'),
            Column::make('total_paid')->title('Total Paid')->addClass('text-right'),
            Column::make('total_due')->title('Total Outstanding')->addClass('text-right'),
        ];
    }
}
