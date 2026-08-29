<?php

namespace App\DataTables;

use App\Models\WarehouseBin;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WarehouseBinDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<WarehouseBin> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function($row){
                $printBtn = '<a href="'.route('admin.warehouse-bins.show', $row->id).'" class="btn btn-sm btn-info mr-1" target="_blank" title="Print Barcode"><i class="fas fa-barcode mr-1"></i> Print</a>';
                $editBtn = '<a href="'.route('admin.warehouse-bins.edit', $row->id).'" class="btn btn-sm btn-primary mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
                $deleteBtn = '<a href="'.route('admin.warehouse-bins.destroy', $row->id).'" class="btn btn-sm btn-danger delete-item" title="Delete"><i class="fas fa-trash"></i></a>';
                return '<div class="btn-group" role="group">' . $printBtn . $editBtn . $deleteBtn . '</div>';
            })
            ->addColumn('zone', function($row){
                $zoneName = $row->zone->name ?? 'N/A';
                $outletName = $row->zone->outlet->name ?? '';
                return '<span class="font-weight-bold text-dark">' . e($zoneName) . '</span>' . ($outletName ? ' <br><small class="text-muted"><i class="fas fa-warehouse mr-1"></i>' . e($outletName) . '</small>' : '');
            })
            ->editColumn('barcode', function($row){
                return '<span class="badge badge-light border text-dark font-weight-bold px-2 py-1"><i class="fas fa-barcode text-primary mr-1"></i>' . e($row->barcode) . '</span>';
            })
            ->editColumn('status', function($row){
                return $row->status 
                    ? '<span class="badge badge-success">Active</span>' 
                    : '<span class="badge badge-secondary">Inactive</span>';
            })
            ->rawColumns(['zone', 'barcode', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<WarehouseBin>
     */
    public function query(WarehouseBin $model): QueryBuilder
    {
        return $model->newQuery()->with(['zone.outlet']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('warehousebin-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0, 'desc')
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel')->className('btn btn-primary btn-sm'),
                        Button::make('csv')->className('btn btn-primary btn-sm'),
                        Button::make('pdf')->className('btn btn-primary btn-sm'),
                        Button::make('print')->className('btn btn-primary btn-sm'),
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#ID')->width(60)->addClass('text-center'),
            Column::make('zone')->name('zone.name')->title('Warehouse Zone & Outlet'),
            Column::make('name')->title('Bin / Shelf Name'),
            Column::make('barcode')->title('Location Barcode')->addClass('text-center'),
            Column::make('status')->title('Status')->addClass('text-center'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(180)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'WarehouseBin_' . date('YmdHis');
    }
}
