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
            ->editColumn('shop_name', fn($row) => '<strong>' . e($row->shop_name ?? $row->name) . '</strong>')
            ->addColumn('vendor_code', fn($row) => '<code>' . e($row->code ?? ('V-'.str_pad($row->id, 4, '0', STR_PAD_LEFT))) . '</code>')
            ->addColumn('po_count', function($row) {
                $request = request();
                return DB::table('purchases')
                    ->where('vendor_id', $row->id)
                    ->where('status', 1)
                    ->when($request->filled('start_date'), fn($q) => $q->whereDate('date', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn($q) => $q->whereDate('date', '<=', $request->end_date))
                    ->count();
            })
            ->addColumn('total_base_amount', function($row) use ($icon) {
                $request = request();
                $total = DB::table('purchases')
                    ->where('vendor_id', $row->id)
                    ->where('status', 1)
                    ->when($request->filled('start_date'), fn($q) => $q->whereDate('date', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn($q) => $q->whereDate('date', '<=', $request->end_date))
                    ->sum('total_amount');
                return '<strong class="text-primary">' . $icon . number_format($total, 2) . '</strong>';
            })
            ->addColumn('total_paid', function($row) use ($icon) {
                $request = request();
                $paid = DB::table('purchases')
                    ->where('vendor_id', $row->id)
                    ->where('status', 1)
                    ->when($request->filled('start_date'), fn($q) => $q->whereDate('date', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn($q) => $q->whereDate('date', '<=', $request->end_date))
                    ->sum('paid_amount');
                return '<span class="text-success">' . $icon . number_format($paid, 2) . '</span>';
            })
            ->addColumn('total_due', function($row) use ($icon) {
                $request = request();
                $due = DB::table('purchases')
                    ->where('vendor_id', $row->id)
                    ->where('status', 1)
                    ->when($request->filled('start_date'), fn($q) => $q->whereDate('date', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn($q) => $q->whereDate('date', '<=', $request->end_date))
                    ->sum('due_amount');
                return '<strong class="text-danger">' . $icon . number_format($due, 2) . '</strong>';
            })
            ->rawColumns(['shop_name', 'vendor_code', 'po_count', 'total_base_amount', 'total_paid', 'total_due'])
            ->setRowId('id');
    }

    public function query(Vendor $model): QueryBuilder
    {
        $request = request();
        $query = $model->newQuery()->where('status', 1);

        if ($request->filled('vendor_id')) {
            $query->where('id', $request->vendor_id);
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
            Column::computed('po_count')->title('Total POs Issued')->addClass('text-center')->orderable(false)->searchable(false),
            Column::computed('total_base_amount')->title('Total Purchase Value')->addClass('text-right')->orderable(false)->searchable(false),
            Column::computed('total_paid')->title('Total Paid')->addClass('text-right')->orderable(false)->searchable(false),
            Column::computed('total_due')->title('Total Outstanding')->addClass('text-right')->orderable(false)->searchable(false),
        ];
    }
}
